<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationDispatchService
{
    public function dispatch(array $payload): ?int
    {
        Log::info('[Notification] Starting persistNotification', [
            'payload_type' => $payload['type'] ?? 'unknown',
            'action' => $payload['metadata']['action'] ?? 'unknown',
        ]);

        $title     = (string) ($payload['title'] ?? 'Notification');
        $message   = (string) ($payload['message'] ?? '');
        $receivers = array_values(array_map(function ($x) {
            return [
                'id' => isset($x['id']) ? (int) $x['id'] : null,
                'role' => (string) ($x['role'] ?? 'unknown'),
                'read' => (int) ($x['read'] ?? 0),
            ];
        }, $payload['receivers'] ?? []));

        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        $type     = (string) ($payload['type'] ?? 'general');
        $linkUrl  = $payload['link_url'] ?? null;
        $priority = in_array(($payload['priority'] ?? 'normal'), ['low', 'normal', 'high', 'urgent'], true)
            ? $payload['priority'] : 'normal';
        $status   = in_array(($payload['status'] ?? 'active'), ['active', 'archived', 'deleted'], true)
            ? $payload['status'] : 'active';

        $notificationId = null;

        try {
            $notificationId = (int) DB::table('notifications')->insertGetId([
                'title' => $title,
                'message' => $message,
                'receivers' => json_encode($receivers, JSON_UNESCAPED_UNICODE),
                'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
                'type' => $type,
                'link_url' => $linkUrl,
                'priority' => $priority,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('[Notification] Saved to database', [
                'notification_id' => $notificationId,
                'title' => $title,
                'receivers_count' => count($receivers),
            ]);
        } catch (\Throwable $e) {
            Log::error('[Notification] Failed to save to database', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        try {
            Log::debug('[Notification] Getting FCM tokens');
            $tokens = $this->resolveFcmTokensForReceivers($receivers);

            Log::info('[Notification] FCM tokens found', [
                'tokens_count' => count($tokens),
            ]);

            if (!empty($tokens)) {
                /** @var \App\Services\FCMService $fcm */
                $fcm = app(FCMService::class);

                $data = [
                    'type' => (string) $type,
                    'priority' => (string) $priority,
                    'link_url' => $linkUrl ? (string) $linkUrl : '',
                    'action' => (string) ($metadata['action'] ?? 'notification'),
                    'job_id' => (string) ($metadata['job_id'] ?? ''),
                ];

                $jobMeta = $metadata['job'] ?? null;
                $clientMeta = $metadata['client'] ?? null;
                $actorMeta = $metadata['actor'] ?? null;

                if (!empty($metadata['job_title'])) {
                    $data['job_title'] = (string) $metadata['job_title'];
                } elseif (is_array($jobMeta) && !empty($jobMeta['title'])) {
                    $data['job_title'] = (string) $jobMeta['title'];
                } elseif (is_string($jobMeta) && $jobMeta !== '') {
                    $data['job_title'] = $jobMeta;
                } else {
                    $data['job_title'] = '';
                }

                if (!empty($metadata['client_name'])) {
                    $data['client_name'] = (string) $metadata['client_name'];
                } elseif (is_array($clientMeta) && !empty($clientMeta['name'])) {
                    $data['client_name'] = (string) $clientMeta['name'];
                } else {
                    $data['client_name'] = '';
                }

                if (!empty($metadata['sender_name'])) {
                    $data['sender_name'] = (string) $metadata['sender_name'];
                } elseif (is_array($actorMeta) && !empty($actorMeta['name'])) {
                    $data['sender_name'] = (string) $actorMeta['name'];
                } elseif (is_array($actorMeta) && !empty($actorMeta['role'])) {
                    $data['sender_name'] = ucfirst((string) $actorMeta['role']);
                } else {
                    $data['sender_name'] = '';
                }

                if (!empty($metadata['message_id'])) {
                    $data['message_id'] = (string) $metadata['message_id'];
                }

                if (is_array($jobMeta) && !empty($jobMeta['status'])) {
                    $data['job_status'] = (string) $jobMeta['status'];
                } elseif (!empty($metadata['new_status'])) {
                    $data['job_status'] = (string) $metadata['new_status'];
                } elseif (!empty($metadata['old_status'])) {
                    $data['job_status'] = (string) $metadata['old_status'];
                }

                if (is_array($actorMeta) && !empty($actorMeta['id'])) {
                    $data['actor_id'] = (string) $actorMeta['id'];
                }
                if (is_array($actorMeta) && !empty($actorMeta['role'])) {
                    $data['actor_role'] = (string) $actorMeta['role'];
                }

                $data['metadata'] = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : '';

                Log::info('[Notification] Calling FCM service', [
                    'tokens_count' => count($tokens),
                    'title' => $title,
                    'data_fields' => array_keys($data),
                ]);

                Log::debug('[Notification] FCM data details', [
                    'job_title' => $data['job_title'] ?? 'not_set',
                    'job_id' => $data['job_id'] ?? 'not_set',
                    'action' => $data['action'] ?? 'not_set',
                ]);

                $fcm->sendToTokens($tokens, $title, $message, $data);
            } else {
                Log::info('[Notification] No FCM tokens found, skipping push');
            }
        } catch (\Throwable $e) {
            Log::error('[Notification] FCM push failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $notificationId;
    }

    private function resolveFcmTokensForReceivers(array $receivers): array
    {
        Log::debug('[FCM] Resolving tokens for receivers', [
            'receivers_count' => count($receivers),
            'receivers_sample' => array_slice($receivers, 0, 3),
        ]);

        $tokens = [];
        $hasAdminTable = Schema::hasTable('fcm_tokens_admin') || Schema::hasTable('fcm_token_admin');
        $hasAssigneeTable = Schema::hasTable('fcm_tokens_assignee') || Schema::hasTable('fcm_token_assignee');

        $adminIds = [];
        $assigneeIds = [];

        foreach ($receivers as $r) {
            $id = (int) ($r['id'] ?? 0);
            $role = strtolower((string) ($r['role'] ?? ''));

            if ($id <= 0 || $role === '') {
                continue;
            }

            if ($role === 'admin') {
                $adminIds[] = $id;
            } elseif ($role === 'assignee') {
                $assigneeIds[] = $id;
            }
        }

        $adminIds = array_values(array_unique($adminIds));
        $assigneeIds = array_values(array_unique($assigneeIds));

        if (!empty($adminIds) && $hasAdminTable) {
            $adminTable = Schema::hasTable('fcm_tokens_admin') ? 'fcm_tokens_admin' : 'fcm_token_admin';

            $rows = DB::table($adminTable)
                ->whereIn('user_id', $adminIds)
                ->where('is_active', true)
                ->whereNotNull('fcm_admin')
                ->pluck('fcm_admin')
                ->all();

            Log::debug('[FCM] Admin tokens fetched', [
                'table' => $adminTable,
                'user_ids' => $adminIds,
                'tokens_found' => count($rows),
            ]);

            foreach ($rows as $t) {
                $t = trim((string) $t);
                if ($t !== '') {
                    $tokens[$t] = true;
                }
            }
        }

        if (!empty($assigneeIds) && $hasAssigneeTable) {
            $assigneeTable = Schema::hasTable('fcm_tokens_assignee') ? 'fcm_tokens_assignee' : 'fcm_token_assignee';

            $rows = DB::table($assigneeTable)
                ->whereIn('user_id', $assigneeIds)
                ->where('is_active', true)
                ->whereNotNull('fcm_assignee')
                ->pluck('fcm_assignee')
                ->all();

            Log::debug('[FCM] Assignee tokens fetched', [
                'table' => $assigneeTable,
                'user_ids' => $assigneeIds,
                'tokens_found' => count($rows),
            ]);

            foreach ($rows as $t) {
                $t = trim((string) $t);
                if ($t !== '') {
                    $tokens[$t] = true;
                }
            }
        }

        Log::info('[FCM] Tokens resolved', [
            'unique_tokens' => count($tokens),
            'admin_receivers' => count($adminIds),
            'assignee_receivers' => count($assigneeIds),
        ]);

        return array_keys($tokens);
    }
}
