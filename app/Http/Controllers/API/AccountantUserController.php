<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountantUserController extends Controller
{
    public function __construct(private ClientUserScopeService $scopeService)
    {
    }

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $request, array $allowed)
    {
        $actor = $this->actor($request);
        if (!$actor['role'] || !in_array($actor['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        return null;
    }

    private function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changed = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $actor = $this->actor($request);

        $changedFields = null;
        if (is_array($changed)) {
            $changedFields = array_values(array_unique(array_map(
                'strval',
                array_keys($changed) === range(0, count($changed) - 1) ? $changed : array_keys($changed)
            )));
        }

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $actor['id'] ?: 0,
                'performed_by_role' => $actor['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => $module,
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('user_data_activity_log insert failed (AccountantUser)', ['error' => $e->getMessage()]);
        }
    }

    private function logAuthActivity(
        Request $request,
        string $activity,
        string $note,
        ?int $accountantUserId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $accountantUserId ?: 0,
                'performed_by_role' => 'accountant_user',
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'Auth',
                'table_name'        => 'accountant_users',
                'record_id'         => $accountantUserId,
                'changed_fields'    => null,
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('user_data_activity_log insert failed (AccountantUser auth)', ['error' => $e->getMessage()]);
        }
    }

    private function decorateAccountantUser(?object $row): ?object
    {
        if (!$row) {
            return null;
        }

        $assigned = DB::table('accountant_user_clients as auc')
            ->join('clients as c', 'c.id', '=', 'auc.client_id')
            ->leftJoin('clients as p', 'p.id', '=', 'c.parent_id')
            ->where('auc.accountant_user_id', $row->id)
            ->orderBy('c.name')
            ->get([
                'c.id',
                'c.name',
                'c.parent_id',
                'p.name as parent_name',
            ]);

        unset($row->password);
        $row->clients = $assigned;
        $row->client_ids = $assigned->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $row->scope_client_count = count($row->client_ids);

        return $row;
    }

    private function syncClientAssignments(int $accountantUserId, array $clientIds): void
    {
        $clientIds = array_values(array_unique(array_map('intval', $clientIds)));

        DB::table('accountant_user_clients')->where('accountant_user_id', $accountantUserId)->delete();

        if (empty($clientIds)) {
            return;
        }

        $now = now();
        $rows = array_map(fn ($clientId) => [
            'accountant_user_id' => $accountantUserId,
            'client_id'          => $clientId,
            'created_at'         => $now,
            'updated_at'         => $now,
        ], $clientIds);

        DB::table('accountant_user_clients')->insert($rows);
    }

    public function me(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Token not provided'], 401);
            }

            $hashed = hash('sha256', $token);

            $row = DB::table('personal_access_tokens')
                ->where('token', $hashed)
                ->where('tokenable_type', 'accountant_user')
                ->first();

            if (!$row) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
            }

            $user = $this->decorateAccountantUser(
                DB::table('accountant_users')->where('id', $row->tokenable_id)->first()
            );

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Accountant user not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $user], 200);
        } catch (\Throwable $e) {
            Log::error('[AccountantUserController@me] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch accountant user info'], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
            'remember'   => 'sometimes|boolean',
        ]);

        $identifier = $request->identifier;
        $remember = (bool) $request->boolean('remember', false);

        $user = DB::table('accountant_users')
            ->where('email', $identifier)
            ->orWhere('name', $identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logAuthActivity(
                $request,
                'login',
                'Login failed (invalid credentials) for identifier: ' . $identifier
            );

            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        if (($user->status ?? 'inactive') !== 'active') {
            $this->logAuthActivity(
                $request,
                'login',
                'Login blocked because account is not active',
                (int) $user->id
            );

            return response()->json(['status' => 'error', 'message' => 'This account is not active'], 403);
        }

        $plainText = bin2hex(random_bytes(40));
        $hash = hash('sha256', $plainText);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'accountant_user',
            'tokenable_id'   => $user->id,
            'name'           => 'auth_token',
            'token'          => $hash,
            'abilities'      => json_encode(['*', 'role:accountant_user']),
            'expires_at'     => $expiresAt,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->logAuthActivity(
            $request,
            'login',
            'Login successful',
            (int) $user->id,
            null,
            ['token_hash_prefix' => substr($hash, 0, 12)]
        );

        return response()->json([
            'status'         => 'success',
            'message'        => 'Login successful',
            'access_token'   => $plainText,
            'token_type'     => 'Bearer',
            'tokenable_type' => 'accountant_user',
            'remember'       => $remember,
            'expires_at'     => $expiresAt?->toIso8601String(),
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['status' => 'error', 'message' => 'Token not provided'], 401);
            }

            $hashed = hash('sha256', $token);
            $pat = DB::table('personal_access_tokens')
                ->where('token', $hashed)
                ->where('tokenable_type', 'accountant_user')
                ->first();

            if (!$pat) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
            }

            DB::table('personal_access_tokens')
                ->where('id', $pat->id)
                ->delete();

            $this->logAuthActivity(
                $request,
                'logout',
                'Logout successful',
                (int) $pat->tokenable_id,
                ['token_hash_prefix' => substr($hashed, 0, 12)],
                null
            );

            return response()->json(['status' => 'success', 'message' => 'Logged out successfully'], 200);
        } catch (\Throwable $e) {
            Log::error('[AccountantUserController@logout] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to logout'], 500);
        }
    }

    public function index(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(100, max(1, (int) $request->query('per_page', 10)));
        $q        = trim((string) $request->query('q', ''));
        $status   = trim((string) $request->query('status', ''));
        $sortBy   = trim((string) $request->query('sort_by', 'created_at'));
        $sortDir  = strtolower(trim((string) $request->query('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';

        $allowed = ['id', 'name', 'email', 'role', 'status', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'created_at';
        }

        $query = DB::table('accountant_users');

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($w) use ($like) {
                $w->where('name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('contact_number', 'LIKE', $like)
                    ->orWhere('role', 'LIKE', $like)
                    ->orWhere('address', 'LIKE', $like);
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $total = (clone $query)->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

        if ($totalPages === 0) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $items = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($row) => $this->decorateAccountantUser($row));

        return response()->json([
            'status'  => 'success',
            'message' => 'Accountant users fetched',
            'data'    => $items,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $totalPages,
                'last_page'   => $totalPages,
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $row = $this->decorateAccountantUser(
            DB::table('accountant_users')->where('id', $id)->first()
        );

        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Accountant user not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $row]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:160'],
            'email'          => ['required', 'email', 'max:255', 'unique:accountant_users,email'],
            'password'       => ['nullable', 'string', 'min:8'],
            'contact_number' => ['nullable', 'string', 'max:32'],
            'address'        => ['nullable', 'string', 'max:255'],
            'role'           => ['nullable', 'string', 'max:120'],
            'status'         => ['nullable', 'string', 'in:active,inactive,archived'],
            'metadata'       => ['nullable', 'array'],
            'client_ids'     => ['required', 'array', 'min:1'],
            'client_ids.*'   => ['integer', 'exists:clients,id'],
        ]);

        $plain = $data['password'] ?? Str::random(12);
        $hash = Hash::make($plain);
        $now = now();

        DB::beginTransaction();
        try {
            $id = DB::table('accountant_users')->insertGetId([
                'name'           => $data['name'],
                'email'          => mb_strtolower($data['email']),
                'password'       => $hash,
                'contact_number' => $data['contact_number'] ?? null,
                'address'        => $data['address'] ?? null,
                'role'           => $data['role'] ?? null,
                'status'         => $data['status'] ?? 'active',
                'metadata'       => json_encode($data['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $this->syncClientAssignments($id, $data['client_ids']);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[AccountantUserController@store] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to create accountant user'], 500);
        }

        $fresh = $this->decorateAccountantUser(DB::table('accountant_users')->where('id', $id)->first());
        $this->logActivity(
            $request,
            'store',
            'Accountant Users',
            'Created accountant user "' . $data['name'] . '"',
            'accountant_users',
            (int) $id,
            array_keys($data),
            null,
            $fresh ? (array) $fresh : null
        );

        return response()->json([
            'status'         => 'success',
            'message'        => 'Accountant user created',
            'data'           => $fresh,
            'plain_password' => $data['password'] ?? $plain,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $existing = DB::table('accountant_users')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Accountant user not found'], 404);
        }
        unset($existing->password);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:160'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('accountant_users', 'email')->ignore($id)],
            'password'       => ['nullable', 'string', 'min:8'],
            'contact_number' => ['nullable', 'string', 'max:32'],
            'address'        => ['nullable', 'string', 'max:255'],
            'role'           => ['nullable', 'string', 'max:120'],
            'status'         => ['nullable', 'string', 'in:active,inactive,archived'],
            'metadata'       => ['nullable', 'array'],
            'client_ids'     => ['required', 'array', 'min:1'],
            'client_ids.*'   => ['integer', 'exists:clients,id'],
        ]);

        $update = [
            'name'           => $data['name'],
            'email'          => mb_strtolower($data['email']),
            'contact_number' => $data['contact_number'] ?? null,
            'address'        => $data['address'] ?? null,
            'role'           => $data['role'] ?? null,
            'status'         => $data['status'] ?? 'active',
            'metadata'       => json_encode($data['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
            'updated_at'     => now(),
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        DB::beginTransaction();
        try {
            DB::table('accountant_users')->where('id', $id)->update($update);
            $this->syncClientAssignments($id, $data['client_ids']);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[AccountantUserController@update] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update accountant user'], 500);
        }

        $fresh = $this->decorateAccountantUser(DB::table('accountant_users')->where('id', $id)->first());
        $this->logActivity(
            $request,
            'update',
            'Accountant Users',
            'Updated accountant user "' . $data['name'] . '"',
            'accountant_users',
            $id,
            array_keys($update),
            (array) $existing,
            $fresh ? (array) $fresh : null
        );

        return response()->json(['status' => 'success', 'message' => 'Accountant user updated', 'data' => $fresh]);
    }

    public function toggle(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $row = DB::table('accountant_users')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Accountant user not found'], 404);
        }

        $current = strtolower((string) ($row->status ?? 'inactive'));
        if ($current === 'archived') {
            return response()->json(['status' => 'error', 'message' => 'Archived accountant users cannot be toggled'], 422);
        }

        $next = $current === 'active' ? 'inactive' : 'active';
        DB::table('accountant_users')->where('id', $id)->update([
            'status'     => $next,
            'updated_at' => now(),
        ]);

        $fresh = $this->decorateAccountantUser(DB::table('accountant_users')->where('id', $id)->first());
        $this->logActivity(
            $request,
            'toggle',
            'Accountant Users',
            'Toggled accountant user "' . ($row->name ?? ('#' . $id)) . '" to ' . $next,
            'accountant_users',
            $id,
            ['status'],
            (array) $row,
            $fresh ? (array) $fresh : null
        );

        return response()->json(['status' => 'success', 'message' => 'Status updated', 'data' => $fresh]);
    }

    public function destroy(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) {
            return $resp;
        }

        $row = $this->decorateAccountantUser(DB::table('accountant_users')->where('id', $id)->first());
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Accountant user not found'], 404);
        }

        DB::table('accountant_users')->where('id', $id)->delete();

        $this->logActivity(
            $request,
            'destroy',
            'Accountant Users',
            'Deleted accountant user "' . ($row->name ?? ('#' . $id)) . '"',
            'accountant_users',
            $id,
            null,
            (array) $row,
            null
        );

        return response()->json(['status' => 'success', 'message' => 'Accountant user deleted']);
    }
}
