<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class FcmTokenController extends Controller
{
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /**
     * Resolve which table + token column to use based on actor role.
     */
    private function resolveContext(Request $request): array
    {
        $actor = $this->actor($request);

        $role   = strtolower((string) ($actor['role'] ?? ''));
        $userId = (int) ($actor['id'] ?? 0);

        if (!$role || $userId <= 0) {
            return [
                'ok' => false,
                'status' => 401,
                'message' => 'Unauthorized: missing auth_role/auth_tokenable_id in request attributes.',
            ];
        }

        if (!in_array($role, ['admin', 'assignee'], true)) {
            return [
                'ok' => false,
                'status' => 403,
                'message' => 'Forbidden: invalid role for FCM token operations.',
            ];
        }

        // Support both singular/plural table names (safe)
        $adminTableCandidates    = ['fcm_tokens_admin', 'fcm_token_admin'];
        $assigneeTableCandidates = ['fcm_tokens_assignee', 'fcm_token_assignee'];

        $adminTable    = $this->firstExistingTable($adminTableCandidates);
        $assigneeTable = $this->firstExistingTable($assigneeTableCandidates);

        if ($role === 'admin') {
            if (!$adminTable) {
                return [
                    'ok' => false,
                    'status' => 500,
                    'message' => 'FCM admin table not found (expected: fcm_tokens_admin / fcm_token_admin).',
                ];
            }

            return [
                'ok'        => true,
                'actor'     => $actor,
                'role'      => 'admin',
                'user_id'   => $userId,
                'table'     => $adminTable,
                'token_col' => 'fcm_admin',
            ];
        }

        if (!$assigneeTable) {
            return [
                'ok' => false,
                'status' => 500,
                'message' => 'FCM assignee table not found (expected: fcm_tokens_assignee / fcm_token_assignee).',
            ];
        }

        return [
            'ok'        => true,
            'actor'     => $actor,
            'role'      => 'assignee',
            'user_id'   => $userId,
            'table'     => $assigneeTable,
            'token_col' => 'fcm_assignee',
        ];
    }

    private function firstExistingTable(array $names): ?string
    {
        foreach ($names as $t) {
            if (Schema::hasTable($t)) return $t;
        }
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
    $ctx = $this->resolveContext($request);
    if (!$ctx['ok']) {
        return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
    }

    $data = $request->validate([
        'fcm_token'    => ['required', 'string', 'max:512'],
        'platform'     => ['nullable', 'string', 'max:20'],
        'device_id'    => ['nullable', 'string', 'max:120'],
        'device_model' => ['nullable', 'string', 'max:120'],
        'app_version'  => ['nullable', 'string', 'max:40'],
    ]);

    $table    = $ctx['table'];
    $tokenCol = $ctx['token_col'];
    $userId   = (int) $ctx['user_id'];
    $now      = now();

    $token    = $data['fcm_token'];
    $deviceId = $data['device_id'] ?? null;

    // ✅ store the personal access token used in the request (nullable)
    $pat = $request->bearerToken(); // if you use Bearer token auth
    $pat = $pat ? (string) $pat : null;

    // ✅ hash it like sanctum personal_access_tokens.token (sha256 of plaintext token part)
    if ($pat !== null) {
        $plain = $pat;
        if (str_contains($pat, '|')) {
            [, $plain] = explode('|', $pat, 2);
        }
        $pat = hash('sha256', $plain);
    }

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

    try {
        // 1) If SAME token exists -> update that row
        $byToken = DB::table($table)->where($tokenCol, $token)->first();
        if ($byToken) {
            DB::table($table)->where('id', $byToken->id)->update($payload);

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
        $byUserQ = DB::table($table)->where('user_id', $userId);

        if (!empty($deviceId)) {
            $byUserQ->where('device_id', $deviceId);
        }

        $byUser = $byUserQ->orderByDesc('id')->first();

        if ($byUser) {
            DB::table($table)->where('id', $byUser->id)->update($payload);

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
        $payload['created_at'] = $now;
        $id = (int) DB::table($table)->insertGetId($payload);

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved.',
            'role'    => $ctx['role'],
            'user_id' => $userId,
            'id'      => $id,
            'mode'    => 'inserted',
        ]);
    } catch (QueryException $e) {
        // Duplicate unique token / race condition fallback:
        // Try update by token first; if not found, override by user.
        $row = DB::table($table)->where($tokenCol, $token)->first();

        if ($row) {
            DB::table($table)->where('id', $row->id)->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $row->id,
                'mode'    => 'race_updated_by_token',
            ]);
        }

        $byUserQ = DB::table($table)->where('user_id', $userId);
        if (!empty($deviceId)) $byUserQ->where('device_id', $deviceId);
        $row2 = $byUserQ->orderByDesc('id')->first();

        if ($row2) {
            DB::table($table)->where('id', $row2->id)->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved.',
                'role'    => $ctx['role'],
                'user_id' => $userId,
                'id'      => (int) $row2->id,
                'mode'    => 'race_overridden_by_user',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Could not save token (DB error).',
        ], 500);
    }
}
public function touch(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['ok']) {
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $data = $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:512'],
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];
        $now      = now();

        $q = DB::table($table)
            ->where('user_id', $userId)
            ->where('is_active', true);

        if (!empty($data['fcm_token'])) {
            $q->where($tokenCol, $data['fcm_token']);
        }

        $updated = $q->update([
            'last_seen_at' => $now,
            'updated_at'   => $now,
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
        $ctx = $this->resolveContext($request);
        if (!$ctx['ok']) {
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];
        $now      = now();

        $updated = DB::table($table)
            ->where('user_id', $userId)
            ->where($tokenCol, $data['fcm_token'])
            ->update([
                'is_active'  => false,
                'updated_at' => $now,
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
        $ctx = $this->resolveContext($request);
        if (!$ctx['ok']) {
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $table    = $ctx['table'];
        $tokenCol = $ctx['token_col'];
        $userId   = (int) $ctx['user_id'];

        $deleted = DB::table($table)
            ->where('user_id', $userId)
            ->where($tokenCol, $data['fcm_token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token deleted.',
            'role'    => $ctx['role'],
            'deleted' => $deleted,
        ]);
    }

    public function myTokens(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['ok']) {
            return response()->json(['success' => false, 'message' => $ctx['message']], $ctx['status']);
        }

        $rows = DB::table($ctx['table'])
            ->where('user_id', (int) $ctx['user_id'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'role'    => $ctx['role'],
            'data'    => $rows,
        ]);
    }
}
