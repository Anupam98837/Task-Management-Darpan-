<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class FcmTokenController extends Controller
{
    private function logTokenEvent(string $event, array $context = []): void
    {
        $payload = array_merge([
            'module' => 'fcm_token',
            'event' => $event,
        ], $context);

        if (isset($payload['fcm_token'])) {
            $token = (string) $payload['fcm_token'];
            $payload['fcm_token_preview'] = substr($token, 0, 16);
            $payload['fcm_token_length'] = strlen($token);
            unset($payload['fcm_token']);
        }

        unset($payload['personal_access_token']);

        Log::info('[FCMTokenController]', $payload);
    }

    private function actor(Request $request): array
    {
        $this->logTokenEvent('actor_resolve_started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        $actor = [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];

        $this->logTokenEvent('actor_resolved', [
            'role' => $actor['role'],
            'type' => $actor['type'],
            'id' => $actor['id'],
        ]);

        return $actor;
    }

    /**
     * Resolve which table + token column to use based on actor role.
     */
    private function resolveContext(Request $request): array
    {
        $this->logTokenEvent('resolve_context_started', [
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        $actor = $this->actor($request);

        $role   = strtolower((string) ($actor['role'] ?? ''));
        $userId = (int) ($actor['id'] ?? 0);

        $this->logTokenEvent('resolve_context_actor_checked', [
            'role' => $role,
            'user_id' => $userId,
            'type' => $actor['type'] ?? null,
        ]);

        if (!$role || $userId <= 0) {
            $this->logTokenEvent('resolve_context_failed_missing_actor', [
                'role' => $role,
                'user_id' => $userId,
            ]);

            return [
                'ok' => false,
                'status' => 401,
                'message' => 'Unauthorized: missing auth_role/auth_tokenable_id in request attributes.',
            ];
        }

        if (!in_array($role, ['admin', 'assignee'], true)) {
            $this->logTokenEvent('resolve_context_failed_invalid_role', [
                'role' => $role,
                'user_id' => $userId,
            ]);

            return [
                'ok' => false,
                'status' => 403,
                'message' => 'Forbidden: invalid role for FCM token operations.',
            ];
        }

        // Support both singular/plural table names (safe)
        $adminTableCandidates    = ['fcm_tokens_admin', 'fcm_token_admin'];
        $assigneeTableCandidates = ['fcm_tokens_assignee', 'fcm_token_assignee'];

        $this->logTokenEvent('resolve_context_table_lookup_started', [
            'role' => $role,
            'user_id' => $userId,
            'admin_candidates' => $adminTableCandidates,
            'assignee_candidates' => $assigneeTableCandidates,
        ]);

        $adminTable    = $this->firstExistingTable($adminTableCandidates);
        $assigneeTable = $this->firstExistingTable($assigneeTableCandidates);

        $this->logTokenEvent('resolve_context_table_lookup_finished', [
            'role' => $role,
            'user_id' => $userId,
            'admin_table' => $adminTable,
            'assignee_table' => $assigneeTable,
        ]);

        if ($role === 'admin') {
            if (!$adminTable) {
                $this->logTokenEvent('resolve_context_failed_admin_table_missing', [
                    'role' => $role,
                    'user_id' => $userId,
                ]);

                return [
                    'ok' => false,
                    'status' => 500,
                    'message' => 'FCM admin table not found (expected: fcm_tokens_admin / fcm_token_admin).',
                ];
            }

            $ctx = [
                'ok'        => true,
                'actor'     => $actor,
                'role'      => 'admin',
                'user_id'   => $userId,
                'table'     => $adminTable,
                'token_col' => 'fcm_admin',
            ];

            $this->logTokenEvent('resolve_context_success', [
                'role' => $ctx['role'],
                'user_id' => $ctx['user_id'],
                'table' => $ctx['table'],
                'token_col' => $ctx['token_col'],
            ]);

            return $ctx;
        }

        if (!$assigneeTable) {
            $this->logTokenEvent('resolve_context_failed_assignee_table_missing', [
                'role' => $role,
                'user_id' => $userId,
            ]);

            return [
                'ok' => false,
                'status' => 500,
                'message' => 'FCM assignee table not found (expected: fcm_tokens_assignee / fcm_token_assignee).',
            ];
        }

        $ctx = [
            'ok'        => true,
            'actor'     => $actor,
            'role'      => 'assignee',
            'user_id'   => $userId,
            'table'     => $assigneeTable,
            'token_col' => 'fcm_assignee',
        ];

        $this->logTokenEvent('resolve_context_success', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
            'table' => $ctx['table'],
            'token_col' => $ctx['token_col'],
        ]);

        return $ctx;
    }

    private function firstExistingTable(array $names): ?string
    {
        $this->logTokenEvent('first_existing_table_started', [
            'candidates' => $names,
        ]);

        foreach ($names as $t) {
            $exists = Schema::hasTable($t);

            $this->logTokenEvent('first_existing_table_checked', [
                'table' => $t,
                'exists' => $exists,
            ]);

            if ($exists) {
                $this->logTokenEvent('first_existing_table_found', [
                    'table' => $t,
                ]);

                return $t;
            }
        }

        $this->logTokenEvent('first_existing_table_not_found', [
            'candidates' => $names,
        ]);

        return null;
    }

   /**
 * POST /api/fcm/token
 * ✅ Upsert rules:
 *  1) If SAME token already exists -> update that row (re-assign user + meta)
 *  2) Else if SAME user repeats -> override that user's existing row (optionally per device_id)
 *  3) Else insert new
 */
public function store(Request $request)
{
    $this->logTokenEvent('store_api_started', [
        'method' => $request->method(),
        'path' => $request->path(),
        'ip' => $request->ip(),
        'has_bearer_token' => $request->bearerToken() ? true : false,
    ]);

    $this->logTokenEvent('store_resolve_context_before');

    $ctx = $this->resolveContext($request);

    $this->logTokenEvent('store_resolve_context_after', [
        'ok' => $ctx['ok'] ?? false,
        'status' => $ctx['status'] ?? null,
        'role' => $ctx['role'] ?? null,
        'user_id' => $ctx['user_id'] ?? null,
        'table' => $ctx['table'] ?? null,
        'token_col' => $ctx['token_col'] ?? null,
    ]);

    if (!$ctx['ok']) {
        $this->logTokenEvent('store_denied', [
            'status' => $ctx['status'],
            'message' => $ctx['message'],
        ]);
        return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
    }

    $this->logTokenEvent('store_validation_before', [
        'role' => $ctx['role'],
        'user_id' => $ctx['user_id'],
    ]);

    $data = $request->validate([
        'fcm_token'    => ['required', 'string', 'max:512'],
        'platform'     => ['nullable', 'string', 'max:20'],
        'device_id'    => ['nullable', 'string', 'max:120'],
        'device_model' => ['nullable', 'string', 'max:120'],
        'app_version'  => ['nullable', 'string', 'max:40'],
    ]);

    $this->logTokenEvent('store_validation_success', [
        'role' => $ctx['role'],
        'user_id' => $ctx['user_id'],
        'has_fcm_token' => !empty($data['fcm_token']),
        'platform' => $data['platform'] ?? null,
        'device_id' => $data['device_id'] ?? null,
        'device_model' => $data['device_model'] ?? null,
        'app_version' => $data['app_version'] ?? null,
        'fcm_token' => $data['fcm_token'] ?? null,
    ]);

    $table    = $ctx['table'];
    $tokenCol = $ctx['token_col'];
    $userId   = (int) $ctx['user_id'];
    $now      = now();

    $token    = $data['fcm_token'];
    $deviceId = $data['device_id'] ?? null;

    $this->logTokenEvent('store_context_ready', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'table' => $table,
        'token_col' => $tokenCol,
        'device_id' => $deviceId,
        'fcm_token' => $token,
    ]);

    $this->logTokenEvent('store_received', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'table' => $table,
        'device_id' => $deviceId,
        'platform' => $data['platform'] ?? null,
        'device_model' => $data['device_model'] ?? null,
        'app_version' => $data['app_version'] ?? null,
        'fcm_token' => $token,
    ]);

    // ✅ store the personal access token used in the request (nullable)
    $this->logTokenEvent('store_personal_access_token_extract_before', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'has_bearer_token' => $request->bearerToken() ? true : false,
    ]);

    $pat = $request->bearerToken(); // if you use Bearer token auth
    $pat = $pat ? (string) $pat : null;

    $this->logTokenEvent('store_personal_access_token_extract_after', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'has_personal_access_token' => $pat !== null,
    ]);

    // ✅ hash it like sanctum personal_access_tokens.token (sha256 of plaintext token part)
    if ($pat !== null) {
        $this->logTokenEvent('store_personal_access_token_hash_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'token_contains_pipe' => str_contains($pat, '|'),
        ]);

        $plain = $pat;
        if (str_contains($pat, '|')) {
            [, $plain] = explode('|', $pat, 2);
        }
        $pat = hash('sha256', $plain);

        $this->logTokenEvent('store_personal_access_token_hash_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'hashed_token_length' => strlen($pat),
        ]);
    } else {
        $this->logTokenEvent('store_personal_access_token_hash_skipped', [
            'role' => $ctx['role'],
            'user_id' => $userId,
        ]);
    }

    $this->logTokenEvent('store_payload_build_before', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'table' => $table,
        'token_col' => $tokenCol,
        'device_id' => $deviceId,
        'fcm_token' => $token,
    ]);

    $payload = [
        'user_id'               => $userId,
        'personal_access_token' => $pat,
        $tokenCol               => $token,
        'platform'              => $data['platform']     ?? null,
        'device_id'             => $deviceId,
        'device_model'          => $data['device_model'] ?? null,
        'app_version'           => $data['app_version']  ?? null,
        'is_active'             => true,
        'last_seen_at'          => $now,
        'updated_at'            => $now,
    ];

    $this->logTokenEvent('store_payload_build_after', [
        'role' => $ctx['role'],
        'user_id' => $userId,
        'table' => $table,
        'token_col' => $tokenCol,
        'payload_keys' => array_keys($payload),
        'has_personal_access_token' => !empty($payload['personal_access_token']),
        'device_id' => $deviceId,
        'fcm_token' => $token,
    ]);

    try {
        $this->logTokenEvent('store_db_flow_started', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        // 1) If SAME token exists -> update that row
        $this->logTokenEvent('store_lookup_by_token_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $token,
        ]);

        $byToken = DB::table($table)->where($tokenCol, $token)->first();

        $this->logTokenEvent('store_lookup_by_token_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'found' => $byToken ? true : false,
            'row_id' => $byToken->id ?? null,
            'fcm_token' => $token,
        ]);

        if ($byToken) {
            $this->logTokenEvent('store_update_by_token_before', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byToken->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            DB::table($table)->where('id', $byToken->id)->update($payload);

            $this->logTokenEvent('store_update_by_token_after', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byToken->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_updated_by_token', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byToken->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_api_finished', [
                'success' => true,
                'mode' => 'updated_by_token',
                'role' => $ctx['role'],
                'user_id' => $userId,
                'id' => (int) $byToken->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $byToken->id,
                'mode'    => 'updated_by_token',
            ]);
        }

        // 2) If SAME user repeats -> override user row (prefer same device_id if given)
        $this->logTokenEvent('store_lookup_by_user_query_build_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
        ]);

        $byUserQ = DB::table($table)->where('user_id', $userId);

        if (!empty($deviceId)) {
            $this->logTokenEvent('store_lookup_by_user_device_filter_applied', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
                'device_id' => $deviceId,
            ]);

            $byUserQ->where('device_id', $deviceId);
        } else {
            $this->logTokenEvent('store_lookup_by_user_device_filter_skipped', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
            ]);
        }

        $this->logTokenEvent('store_lookup_by_user_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
        ]);

        $byUser = $byUserQ->orderByDesc('id')->first();

        $this->logTokenEvent('store_lookup_by_user_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'found' => $byUser ? true : false,
            'row_id' => $byUser->id ?? null,
            'device_id' => $deviceId,
        ]);

        if ($byUser) {
            $this->logTokenEvent('store_override_by_user_before', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byUser->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            DB::table($table)->where('id', $byUser->id)->update($payload);

            $this->logTokenEvent('store_override_by_user_after', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byUser->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_overridden_by_user', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $byUser->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_api_finished', [
                'success' => true,
                'mode' => 'overridden_by_user',
                'role' => $ctx['role'],
                'user_id' => $userId,
                'id' => (int) $byUser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $byUser->id,
                'mode'    => 'overridden_by_user',
            ]);
        }

        // 3) Insert new
        $this->logTokenEvent('store_insert_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        $payload['created_at'] = $now;
        $id = (int) DB::table($table)->insertGetId($payload);

        $this->logTokenEvent('store_insert_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'row_id' => $id,
            'table' => $table,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        $this->logTokenEvent('store_inserted', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'row_id' => $id,
            'table' => $table,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        $this->logTokenEvent('store_api_finished', [
            'success' => true,
            'mode' => 'inserted',
            'role' => $ctx['role'],
            'user_id' => $userId,
            'id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved.',
            'role'    => $ctx['role'],
            'user_id' => $userId,
            'id'      => $id,
            'mode'    => 'inserted',
        ]);
    } catch (QueryException $e) {
        $this->logTokenEvent('store_query_exception', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
            'error' => $e->getMessage(),
            'fcm_token' => $token,
        ]);

        $this->logTokenEvent('store_race_fallback_started', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        // Duplicate unique token / race condition fallback:
        // Try update by token first; if not found, override by user.
        $this->logTokenEvent('store_race_lookup_by_token_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $token,
        ]);

        $row = DB::table($table)->where($tokenCol, $token)->first();

        $this->logTokenEvent('store_race_lookup_by_token_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'found' => $row ? true : false,
            'row_id' => $row->id ?? null,
            'fcm_token' => $token,
        ]);

        if ($row) {
            $this->logTokenEvent('store_race_update_by_token_before', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            DB::table($table)->where('id', $row->id)->update($payload);

            $this->logTokenEvent('store_race_update_by_token_after', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_race_updated_by_token', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_api_finished', [
                'success' => true,
                'mode' => 'race_updated_by_token',
                'role' => $ctx['role'],
                'user_id' => $userId,
                'id' => (int) $row->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $row->id,
                'mode'    => 'race_updated_by_token',
            ]);
        }

        $this->logTokenEvent('store_race_lookup_by_user_query_build_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
        ]);

        $byUserQ = DB::table($table)->where('user_id', $userId);
        if (!empty($deviceId)) {
            $this->logTokenEvent('store_race_lookup_by_user_device_filter_applied', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
                'device_id' => $deviceId,
            ]);

            $byUserQ->where('device_id', $deviceId);
        } else {
            $this->logTokenEvent('store_race_lookup_by_user_device_filter_skipped', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
            ]);
        }

        $this->logTokenEvent('store_race_lookup_by_user_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
        ]);

        $row2 = $byUserQ->orderByDesc('id')->first();

        $this->logTokenEvent('store_race_lookup_by_user_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'found' => $row2 ? true : false,
            'row_id' => $row2->id ?? null,
            'device_id' => $deviceId,
        ]);

        if ($row2) {
            $this->logTokenEvent('store_race_override_by_user_before', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row2->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            DB::table($table)->where('id', $row2->id)->update($payload);

            $this->logTokenEvent('store_race_override_by_user_after', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row2->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_race_overridden_by_user', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'row_id' => (int) $row2->id,
                'table' => $table,
                'device_id' => $deviceId,
                'fcm_token' => $token,
            ]);

            $this->logTokenEvent('store_api_finished', [
                'success' => true,
                'mode' => 'race_overridden_by_user',
                'role' => $ctx['role'],
                'user_id' => $userId,
                'id' => (int) $row2->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $row2->id,
                'mode'    => 'race_overridden_by_user',
            ]);
        }

        $this->logTokenEvent('store_failed', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'device_id' => $deviceId,
            'fcm_token' => $token,
        ]);

        $this->logTokenEvent('store_api_finished', [
            'success' => false,
            'mode' => 'failed',
            'role' => $ctx['role'],
            'user_id' => $userId,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Could not save token (DB error).',
        ], 500);
    }
}
public function touch(Request $request)
    {
        $this->logTokenEvent('touch_api_started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
        ]);

        $this->logTokenEvent('touch_resolve_context_before');

        $ctx = $this->resolveContext($request);

        $this->logTokenEvent('touch_resolve_context_after', [
            'ok' => $ctx['ok'] ?? false,
            'status' => $ctx['status'] ?? null,
            'role' => $ctx['role'] ?? null,
            'user_id' => $ctx['user_id'] ?? null,
            'table' => $ctx['table'] ?? null,
            'token_col' => $ctx['token_col'] ?? null,
        ]);

        if (!$ctx['ok']) {
            $this->logTokenEvent('touch_denied', [
                'status' => $ctx['status'],
                'message' => $ctx['message'],
            ]);
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $this->logTokenEvent('touch_validation_before', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
        ]);

        $data = $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:512'],
        ]);

        $this->logTokenEvent('touch_validation_success', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
            'has_fcm_token' => !empty($data['fcm_token']),
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];
        $now      = now();

        $this->logTokenEvent('touch_context_ready', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'has_fcm_token_filter' => !empty($data['fcm_token']),
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $this->logTokenEvent('touch_query_build_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
        ]);

        $q = DB::table($table)
            ->where('user_id', $userId)
            ->where('is_active', true);

        $this->logTokenEvent('touch_query_base_ready', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'is_active' => true,
        ]);

        if (!empty($data['fcm_token'])) {
            $this->logTokenEvent('touch_token_filter_applied', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
                'token_col' => $tokenCol,
                'fcm_token' => $data['fcm_token'],
            ]);

            $q->where($tokenCol, $data['fcm_token']);
        } else {
            $this->logTokenEvent('touch_token_filter_skipped', [
                'role' => $ctx['role'],
                'user_id' => $userId,
                'table' => $table,
            ]);
        }

        $this->logTokenEvent('touch_update_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $updated = $q->update([
            'last_seen_at' => $now,
            'updated_at'   => $now,
        ]);

        $this->logTokenEvent('touch_update_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'updated_rows' => (int) $updated,
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $this->logTokenEvent('touch_updated', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'updated_rows' => (int) $updated,
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $this->logTokenEvent('touch_api_finished', [
            'success' => true,
            'role' => $ctx['role'],
            'user_id' => $userId,
            'updated' => (int) $updated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Last seen updated.',
            'role'    => $ctx['role'],
            'updated' => $updated,
        ]);
    }

    public function deactivate(Request $request)
    {
        $this->logTokenEvent('deactivate_api_started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
        ]);

        $this->logTokenEvent('deactivate_resolve_context_before');

        $ctx = $this->resolveContext($request);

        $this->logTokenEvent('deactivate_resolve_context_after', [
            'ok' => $ctx['ok'] ?? false,
            'status' => $ctx['status'] ?? null,
            'role' => $ctx['role'] ?? null,
            'user_id' => $ctx['user_id'] ?? null,
            'table' => $ctx['table'] ?? null,
            'token_col' => $ctx['token_col'] ?? null,
        ]);

        if (!$ctx['ok']) {
            $this->logTokenEvent('deactivate_denied', [
                'status' => $ctx['status'],
                'message' => $ctx['message'],
            ]);
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $this->logTokenEvent('deactivate_validation_before', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
        ]);

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $this->logTokenEvent('deactivate_validation_success', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
            'has_fcm_token' => !empty($data['fcm_token']),
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];
        $now      = now();

        $this->logTokenEvent('deactivate_context_ready', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('deactivate_update_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $data['fcm_token'],
        ]);

        $updated = DB::table($table)
            ->where('user_id', $userId)
            ->where($tokenCol, $data['fcm_token'])
            ->update([
                'is_active'  => false,
                'updated_at' => $now,
            ]);

        $this->logTokenEvent('deactivate_update_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'updated_rows' => (int) $updated,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('deactivate_updated', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'updated_rows' => (int) $updated,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('deactivate_api_finished', [
            'success' => true,
            'role' => $ctx['role'],
            'user_id' => $userId,
            'updated' => (int) $updated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token deactivated.',
            'role'    => $ctx['role'],
            'updated' => $updated,
        ]);
    }

    public function destroy(Request $request)
    {
        $this->logTokenEvent('destroy_api_started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
        ]);

        $this->logTokenEvent('destroy_resolve_context_before');

        $ctx = $this->resolveContext($request);

        $this->logTokenEvent('destroy_resolve_context_after', [
            'ok' => $ctx['ok'] ?? false,
            'status' => $ctx['status'] ?? null,
            'role' => $ctx['role'] ?? null,
            'user_id' => $ctx['user_id'] ?? null,
            'table' => $ctx['table'] ?? null,
            'token_col' => $ctx['token_col'] ?? null,
        ]);

        if (!$ctx['ok']) {
            $this->logTokenEvent('destroy_denied', [
                'status' => $ctx['status'],
                'message' => $ctx['message'],
            ]);
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $this->logTokenEvent('destroy_validation_before', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
        ]);

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $this->logTokenEvent('destroy_validation_success', [
            'role' => $ctx['role'],
            'user_id' => $ctx['user_id'],
            'has_fcm_token' => !empty($data['fcm_token']),
            'fcm_token' => $data['fcm_token'] ?? null,
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];

        $this->logTokenEvent('destroy_context_ready', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('destroy_delete_before', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'token_col' => $tokenCol,
            'fcm_token' => $data['fcm_token'],
        ]);

        $deleted = DB::table($table)
            ->where('user_id', $userId)
            ->where($tokenCol, $data['fcm_token'])
            ->delete();

        $this->logTokenEvent('destroy_delete_after', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'deleted_rows' => (int) $deleted,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('destroy_deleted', [
            'role' => $ctx['role'],
            'user_id' => $userId,
            'table' => $table,
            'deleted_rows' => (int) $deleted,
            'fcm_token' => $data['fcm_token'],
        ]);

        $this->logTokenEvent('destroy_api_finished', [
            'success' => true,
            'role' => $ctx['role'],
            'user_id' => $userId,
            'deleted' => (int) $deleted,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token deleted.',
            'role'    => $ctx['role'],
            'deleted' => $deleted,
        ]);
    }

    public function myTokens(Request $request)
    {
        $this->logTokenEvent('my_tokens_api_started', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
        ]);

        $this->logTokenEvent('my_tokens_resolve_context_before');

        $ctx = $this->resolveContext($request);

        $this->logTokenEvent('my_tokens_resolve_context_after', [
            'ok' => $ctx['ok'] ?? false,
            'status' => $ctx['status'] ?? null,
            'role' => $ctx['role'] ?? null,
            'user_id' => $ctx['user_id'] ?? null,
            'table' => $ctx['table'] ?? null,
            'token_col' => $ctx['token_col'] ?? null,
        ]);

        if (!$ctx['ok']) {
            $this->logTokenEvent('my_tokens_denied', [
                'status' => $ctx['status'],
                'message' => $ctx['message'],
            ]);
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $this->logTokenEvent('my_tokens_context_ready', [
            'role' => $ctx['role'],
            'user_id' => (int) $ctx['user_id'],
            'table' => $ctx['table'],
        ]);

        $this->logTokenEvent('my_tokens_query_before', [
            'role' => $ctx['role'],
            'user_id' => (int) $ctx['user_id'],
            'table' => $ctx['table'],
        ]);

        $rows = DB::table($ctx['table'])
            ->where('user_id', (int) $ctx['user_id'])
            ->orderByDesc('id')
            ->get();

        $this->logTokenEvent('my_tokens_query_after', [
            'role' => $ctx['role'],
            'user_id' => (int) $ctx['user_id'],
            'table' => $ctx['table'],
            'count' => $rows->count(),
        ]);

        $this->logTokenEvent('my_tokens_listed', [
            'role' => $ctx['role'],
            'user_id' => (int) $ctx['user_id'],
            'table' => $ctx['table'],
            'count' => $rows->count(),
        ]);

        $this->logTokenEvent('my_tokens_api_finished', [
            'success' => true,
            'role' => $ctx['role'],
            'user_id' => (int) $ctx['user_id'],
            'count' => $rows->count(),
        ]);

        return response()->json([
            'success' => true,
            'role'    => $ctx['role'],
            'data'    => $rows,
        ]);
    }
}