<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssignedPeopleController extends Controller
{
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
            ->where('tokenable_type', 'assignee')
            ->first();

        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
        }

        $person = DB::table('assigned_people')->where('id', $row->tokenable_id)->first();

        if (!$person) {
            return response()->json(['status' => 'error', 'message' => 'Assigned person not found'], 404);
        }

        // Do NOT return sensitive fields
        $data = [
            'id'    => (int)$person->id,
            'name'  => (string)$person->name,
            'email' => (string)$person->email,
        ];

        return response()->json(['status' => 'success', 'data' => $data], 200);

    } catch (\Throwable $e) {
        Log::error('[AssignedPeopleController@me] failed', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Failed to fetch assignee info'], 500);
    }
}
    /* =========================
     *   Notification helpers (DB-only)
     * ========================= */

    /** Insert one notification row (no email). */
    private function persistNotification(array $payload): void
    {
        app(\App\Services\NotificationDispatchService::class)->dispatch($payload);
    }

    /** Admin receivers: all admins (id, role=admin). */
    private function adminReceivers(array $excludeIds = []): array
    {
        $exclude = array_flip(array_map('intval', $excludeIds));
        $rows = DB::table('admins')->select('id')->get();
        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->id;
            if (!isset($exclude[$id])) $out[] = ['id'=>$id, 'role'=>'admin', 'read'=>0];
        }
        return $out;
    }

    private function logAuthActivity(
        Request $request,
        string $activity,            // e.g. 'login' | 'logout'
        string $note,                // short human note
        ?int   $userId = null,       // actor id if known
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $userId ?: 0,
                'performed_by_role' => 'assignee',
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,      // 'login' | 'logout'
                'module'            => 'Auth',
                'table_name'        => 'assigned_people',
                'record_id'         => $userId,
                'changed_fields'    => null,           // not needed for auth events
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('user_data_activity_log insert failed (Auth assignedpeople)', ['error' => $e->getMessage()]);
        }
    }

    // assignedpeople login (email or username)
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // email or username
            'password'   => 'required|string',
            'remember'   => 'sometimes|boolean',
        ]);

        $identifier = $request->identifier;
        $remember = (bool) $request->boolean('remember', false);

        // Find assigned person by email or username
        $person = DB::table('assigned_people')
            ->where('email', $identifier)
            ->orWhere('name', $identifier) // adjust if your username field differs
            ->first();

        if (!$person || !Hash::check($request->password, $person->password)) {
            Log::warning('AssignedPeople login failed', ['identifier' => $identifier]);

            // LOG: failed login (no user id)
            $this->logAuthActivity(
                $request,
                'login',
                'Login failed (invalid credentials) for identifier: '.$identifier,
                null,
                null,
                null
            );

            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        // Generate token (PLAINTEXT returned to client)
        $plainText = bin2hex(random_bytes(40));
        $hash = hash('sha256', $plainText);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'assignee',
            'tokenable_id'   => $person->id,
            'name'           => 'auth_token',
            'token'          => $hash,
            'abilities'      => json_encode(['*']),
            'expires_at'     => $expiresAt,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // LOG: successful login
        $this->logAuthActivity(
            $request,
            'login',
            'Login successful',
            (int) $person->id,
            null,
            [
                // NEVER log plaintext token
                'token_hash_prefix' => substr($hash, 0, 12),
            ]
        );

        return response()->json([
            'status'         => 'success',
            'message'        => 'Login successful',
            'access_token'   => $plainText, // return plaintext to client
            'token_type'     => 'Bearer',
            'tokenable_type' => 'assignee',
            'remember'       => $remember,
            'expires_at'     => $expiresAt?->toIso8601String(),
        ], 200);
    }

    // assignedpeople logout
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            // LOG: logout attempt without token
            $this->logAuthActivity($request, 'logout', 'No token provided');
            return response()->json(['status' => 'error', 'message' => 'Token not provided'], 401);
        }

        $hashedToken = hash('sha256', $token);

        // Fetch token row first so we can capture user id for logging
        $row = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->where('tokenable_type', 'assignee')
            ->first();

        if (!$row) {
            // LOG: invalid/expired token
            $this->logAuthActivity(
                $request,
                'logout',
                'Invalid or expired token',
                null,
                ['token_hash_prefix' => substr($hashedToken, 0, 12)],
                null
            );
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
        }

        $deleted = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->where('tokenable_type', 'assignee')
            ->delete();

        if ($deleted) {
            // LOG: logout success
            $this->logAuthActivity(
                $request,
                'logout',
                'Logged out successfully',
                (int) $row->tokenable_id,
                ['token_hash_prefix' => substr($hashedToken, 0, 12)],
                null
            );
            return response()->json(['status' => 'success', 'message' => 'Logged out successfully'], 200);
        }

        // LOG: race/edge case where delete failed
        $this->logAuthActivity(
            $request,
            'logout',
            'Logout failed during token revoke',
            (int) ($row->tokenable_id ?? 0),
            ['token_hash_prefix' => substr($hashedToken, 0, 12)],
            null
        );

        return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
    }

    // Get all assigned people (with pagination, search, and filters)
 public function index(Request $request)
{
    $page     = max(1, (int) $request->query('page', 1));
    $perPage  = min(100, max(1, (int) $request->query('per_page', 10)));
    $q        = trim((string) $request->query('q', ''));
    $status   = trim((string) $request->query('status', ''));
    $created_from = trim((string)$request->query('created_from', ''));
    $created_to   = trim((string)$request->query('created_to', ''));
    // keep your sort_by / sort_dir (UI sends both)
    $sort_by  = trim((string)$request->query('sort_by', 'created_at'));
    $sort_dir = strtolower(trim((string)$request->query('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';

    // whitelist for sort_by
    $allowed = ['id','name','email','status','created_at','updated_at'];
    if (!in_array($sort_by, $allowed, true)) $sort_by = 'created_at';

    $query = DB::table('assigned_people');

    if ($q !== '') {
        $like = "%{$q}%";
        $query->where(function ($w) use ($like) {
            $w->where('name', 'LIKE', $like)
              ->orWhere('email', 'LIKE', $like)
              ->orWhere('contact_number', 'LIKE', $like);
        });
    }

    if ($status !== '') {
        $query->where('status', $status);
    }
    if ($created_from !== '') $query->whereDate('created_at', '>=', $created_from);
    if ($created_to   !== '') $query->whereDate('created_at', '<=', $created_to);

    // totals BEFORE pagination
    $total = (clone $query)->count();
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

    // clamp page
    if ($totalPages === 0) {
        $page = 1;
    } elseif ($page > $totalPages) {
        $page = $totalPages;
    }

    // stable ordering: primary + id tiebreaker
    $items = $query
        ->orderBy($sort_by, $sort_dir)
        ->orderBy('id', $sort_dir)
        ->forPage($page, $perPage)
        ->get()
        ->map(function ($a) {
            unset($a->password);
            return $a;
        });

    // from/to helpers (nice for UI)
    $from = $total ? (($page - 1) * $perPage) + 1 : 0;
    $to   = min($total, $page * $perPage);

    return response()->json([
        'status'  => 'success',
        'message' => 'Assigned people fetched',
        'data'    => $items,
        'meta'    => [
            'page'         => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'total_pages'  => $totalPages,
            'last_page'    => $totalPages,  
            'from'         => $from,
            'to'           => $to,
        ],
    ]);
}

    // Create a new assigned person
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:assigned_people,email'],
            'password' => ['nullable','string','min:8'],
            'status'   => ['nullable','string','in:active,inactive,archived'],
            'contact_number' => ['nullable','string','max:32'],
            'address' => ['nullable','string','max:255'],
            'metadata' => ['nullable','json'],
        ]);

        // Generate password if not provided
        $plain = $data['password'] ?? Str::random(12);
        $hash  = Hash::make($plain);

        $now = now();
        $id = DB::table('assigned_people')->insertGetId([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $hash,
            'status'     => $data['status'] ?? 'active',
            'contact_number' => $data['contact_number'] ?? null,
            'address' => $data['address'] ?? null,
            'metadata' => $data['metadata'] ?? json_encode([]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assignedPerson = DB::table('assigned_people')->where('id', $id)->first();
        unset($assignedPerson->password); // Don't return password

        // Log create activity
        $this->logActivity(
            $request,
            'store',
            'Assigned People',
            "Created assigned person \"{$data['name']}\"",
            'assigned_people',
            (int)$id,
            array_keys($data),
            null,
            (array)$assignedPerson
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Assigned person created',
            'message'   => "Assigned person \"{$data['name']}\" was created.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'           => 'created',
                'assigned_person'  => (array)$assignedPerson,
                'assigned_person_id' => $id,
            ],
            'type'      => 'assigned_person',
            'link_url'  => rtrim((string)config('app.url'), '/').'/assigned-people/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'              => 'success',
            'message'             => 'Assigned person created',
            'data'                => $assignedPerson,
        ], 201);
    }

    // Get a specific assigned person by ID
    public function show(int $id)
    {
        $assignedPerson = DB::table('assigned_people')->where('id', $id)->first();
        if (!$assignedPerson) {
            return response()->json(['status'=>'error','message'=>'Assigned person not found'], 404);
        }
        unset($assignedPerson->password); // Don't return password

        return response()->json(['status'=>'success','message'=>'Assigned person fetched','data'=>$assignedPerson]);
    }

    // Update an assigned person's information
    public function update(Request $request, int $id)
    {
        $assignedPerson = DB::table('assigned_people')->where('id', $id)->first();
        if (!$assignedPerson) {
            return response()->json(['status'=>'error','message'=>'Assigned person not found'], 404);
        }

        $data = $request->validate([
            'name'     => ['sometimes','string','max:255'],
            'email'    => ['sometimes','email','max:255', Rule::unique('assigned_people','email')->ignore($id)],
            'password' => ['nullable','string','min:8'],
            'status'   => ['sometimes','string','in:active,inactive,archived'],
            'contact_number' => ['nullable','string','max:32'],
            'address' => ['nullable','string','max:255'],
            'metadata' => ['nullable','json'],
        ]);

        $update = [];
        if (array_key_exists('name', $data))   $update['name'] = $data['name'];
        if (array_key_exists('email', $data))  $update['email'] = $data['email'];
        if (array_key_exists('status', $data)) $update['status'] = $data['status'];
        if (array_key_exists('contact_number', $data)) $update['contact_number'] = $data['contact_number'];
        if (array_key_exists('address', $data)) $update['address'] = $data['address'];
        if (array_key_exists('metadata', $data)) $update['metadata'] = $data['metadata'];

        if (array_key_exists('password', $data) && $data['password']) {
            $update['password'] = Hash::make($data['password']);
        }

        if (empty($update)) {
            unset($assignedPerson->password);
            return response()->json(['status'=>'success','message'=>'No changes','data'=>$assignedPerson]);
        }

        $update['updated_at'] = now();
        DB::table('assigned_people')->where('id', $id)->update($update);

        $fresh = DB::table('assigned_people')->where('id', $id)->first();
        unset($fresh->password); // Don't return password

        // Log update activity
        $this->logActivity(
            $request,
            'update',
            'Assigned People',
            "Updated assigned person \"".($update['name'] ?? $assignedPerson->name ?? "#{$id}")."\"",
            'assigned_people',
            $id,
            array_keys($update),
            (array)$assignedPerson,
            (array)$fresh
        );

        // ✅ notify admins with specific field updates
        $changed = array_keys($update);
        $this->persistNotification([
            'title'     => 'Assigned person updated',
            'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Assigned person updated.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'              => 'updated',
                'assigned_person'     => (array)$fresh,
                'assigned_person_id'  => $id,
                'changed'             => $changed,
                // Include specific field changes for important fields
                'specific_changes'    => $this->getSpecificFieldChanges($update, $assignedPerson, $fresh),
            ],
            'type'      => 'assigned_person',
            'link_url'  => rtrim((string)config('app.url'), '/').'/assigned-people/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>'success','message'=>'Assigned person updated','data'=>$fresh]);
    }

    // Toggle the status of an assigned person (active <-> inactive)
    public function toggle(int $id)
    {
        $assignedPerson = DB::table('assigned_people')->where('id', $id)->first();
        if (!$assignedPerson) {
            return response()->json(['status'=>'error','message'=>'Assigned person not found'], 404);
        }

        $newStatus = ($assignedPerson->status === 'active') ? 'inactive' : 'active';

        DB::table('assigned_people')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        $fresh = DB::table('assigned_people')->where('id', $id)->first();
        unset($fresh->password); // Don't return password

        // Log status toggle activity
        $this->logActivity(
            request(),
            'toggle',
            'Assigned People',
            "Status toggled to {$newStatus} for \"{$fresh->name}\"",
            'assigned_people',
            $id,
            ['status'],
            ['status' => $assignedPerson->status],
            ['status' => $newStatus]
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Assigned person status updated',
            'message'   => "Status changed to {$newStatus} for assigned person \"{$assignedPerson->name}\".",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'              => 'status_changed',
                'assigned_person'     => (array)$fresh,
                'assigned_person_id'  => $id,
                'old_status'          => $assignedPerson->status,
                'new_status'          => $newStatus,
            ],
            'type'      => 'assigned_person',
            'link_url'  => rtrim((string)config('app.url'), '/').'/assigned-people/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'=>'success',
            'message'=>"Assigned person status toggled to {$newStatus}",
            'data'=>$fresh
        ]);
    }

    // Delete an assigned person
    public function destroy(int $id)
    {
        $assignedPerson = DB::table('assigned_people')->where('id', $id)->first();
        if (!$assignedPerson) {
            return response()->json(['status'=>'error','message'=>'Assigned person not found'], 404);
        }

        DB::table('assigned_people')->where('id', $id)->delete();

        // Log delete activity
        $this->logActivity(
            request(),
            'destroy',
            'Assigned People',
            "Deleted assigned person \"{$assignedPerson->name}\"",
            'assigned_people',
            $id,
            null,
            (array)$assignedPerson,
            null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Assigned person deleted',
            'message'   => "Assigned person \"{$assignedPerson->name}\" was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'              => 'deleted',
                'assigned_person'     => (array)$assignedPerson,
                'assigned_person_id'  => $id,
            ],
            'type'      => 'assigned_person',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>'success','message'=>'Assigned person deleted']);
    }

    /* =========================
     *          Helpers
     * ========================= */

    protected function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changed = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ) {
        DB::table('user_data_activity_log')->insert([
            'performed_by'      => 0,
            'performed_by_role' => 'user',
            'ip'                => $request->ip(),
            'user_agent'        => (string) $request->userAgent(),
            'activity'          => $activity,
            'module'            => $module,
            'table_name'        => $tableName,
            'record_id'         => $recordId,
            'changed_fields'    => $changed ? json_encode($changed, JSON_UNESCAPED_UNICODE) : null,
            'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'log_note'          => $note,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    /**
     * Get specific field changes for detailed notification metadata
     */
    private function getSpecificFieldChanges(array $update, object $old, object $new): array
    {
        $specificChanges = [];

        foreach ($update as $field => $newValue) {
            $oldValue = $old->$field ?? null;
            
            // Skip password field for security
            if ($field === 'password') {
                $specificChanges[$field] = '*** (updated)';
                continue;
            }

            // Skip if values are the same
            if ($oldValue == $newValue) {
                continue;
            }

            // Format the change for notification
            $specificChanges[$field] = [
                'from' => $oldValue,
                'to'   => $newValue,
            ];
        }

        return $specificChanges;
    }
}
