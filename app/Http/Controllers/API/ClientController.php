<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class ClientController extends Controller
{
    /** =========================
     *   Auth/Role Helpers
     * ========================= */

    /** Get actor data injected by CheckRole middleware */
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),                 // 'admin' | 'user' | null
            'type' => $request->attributes->get('auth_tokenable_type'),       // e.g. 'admin' / 'user' or FQCN if you allowed that
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /** Return 403 JSON if actor role not in allowed list; otherwise null */
    private function requireRole(Request $request, array $allowed)
    {
        $actor = $this->actor($request);
        if (!$actor['role'] || !in_array($actor['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    /** Optional: quick log helper to enrich context (Laravel log) */
    private function logWithActor(string $msg, Request $request, array $extra = []): void
    {
        $a = $this->actor($request);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_type' => $a['type'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    /** =========================
     *   Activity Log (DB) Helper
     * ========================= */
    private function logActivity(
        Request $request,
        string $activity,                  // e.g. 'store' | 'update' | 'toggle' | 'destroy'
        string $module,                    // e.g. 'Clients'
        string $note,                      // human-readable note
        string $tableName,                 // e.g. 'clients'
        ?int $recordId = null,
        ?array $changed = null,            // list of changed field names
        ?array $oldValues = null,          // assoc snapshot before
        ?array $newValues = null           // assoc snapshot after
    ): void {
        $a = $this->actor($request);

        // Normalize changed fields to a flat list of strings.
        $changedFields = null;
        if (is_array($changed)) {
            $changedFields = array_values(array_unique(array_map(
                'strval',
                // If associative array passed, convert to keys; if indexed, keep as is
                array_keys($changed) === range(0, count($changed)-1) ? $changed : array_keys($changed)
            )));
        }

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $a['id'] ?: 0,
                'performed_by_role' => $a['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => $module,
                'table_name'        => $tableName ?: 'unknown',
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break the request because of logging
            Log::error('user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /** =========================
     *   Notification helpers (DB-only)
     * ========================= */

    /** Insert one notification row (no email). */
    private function persistNotification(array $payload): void
    {
        $title     = (string)($payload['title']    ?? 'Notification');
        $message   = (string)($payload['message']  ?? '');
        $receivers = array_values(array_map(function($x){
            return [
                'id'   => isset($x['id']) ? (int)$x['id'] : null,
                'role' => (string)($x['role'] ?? 'unknown'),
                'read' => (int)($x['read'] ?? 0),
            ];
        }, $payload['receivers'] ?? []));

        $metadata = $payload['metadata'] ?? [];
        $type     = (string)($payload['type'] ?? 'general');
        $linkUrl  = $payload['link_url'] ?? null;
        $priority = in_array(($payload['priority'] ?? 'normal'), ['low','normal','high','urgent'], true)
                    ? $payload['priority'] : 'normal';
        $status   = in_array(($payload['status'] ?? 'active'), ['active','archived','deleted'], true)
                    ? $payload['status'] : 'active';

        DB::table('notifications')->insert([
            'title'      => $title,
            'message'    => $message,
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'type'       => $type,
            'link_url'   => $linkUrl,
            'priority'   => $priority,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    /** =========================
     *   URL / Slug Helpers
     * ========================= */

    private function imageUrl(?string $imageUrl): ?string
    {
        if (!$imageUrl) return null;
        if (preg_match('#^https?://#i', $imageUrl)) return $imageUrl;
        return asset(ltrim($imageUrl, '/'));
    }

    private function generateUniqueSlug(int $length = 12): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;

        static $existing = null;
        if ($existing === null) {
            $existing = DB::table('clients')->pluck('slug')->toArray();
        }

        do {
            $slug = '';
            for ($i = 0; $i < $length; $i++) {
                $slug .= $alphabet[random_int(0, $max)];
            }
            $exists = in_array($slug, $existing, true)
                || DB::table('clients')->where('slug', $slug)->exists();
        } while ($exists);

        $existing[] = $slug;
        return $slug;
    }

    private function findBySlug(string $slug): ?object
    {
        return DB::table('clients')
            ->where('slug', mb_strtolower($slug))
            ->first();
    }

    private function findById(int $id): ?object
    {
        return DB::table('clients')->where('id', $id)->first();
    }

    private function uniqueErrorResponse(QueryException $e)
    {
        $map = [
            'clients_email_unique'           => 'email',
            'clients_phone_unique'           => 'phone',
        
        ];

        $msg = $e->getMessage() ?? '';
        $errors = [];
        foreach ($map as $indexName => $field) {
            if (str_contains($msg, $indexName)) {
                $errors[$field] = [match ($field) {
                    'email'          => 'This email is already associated with another client.',
                    'phone'          => 'This phone number is already associated with another client.',
                }];
            }
        }

        if (!$errors) {
            $errors['_all'] = ['Duplicate value detected.'];
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Duplicate value detected.',
            'errors'  => $errors,
        ], 422);
    }

    /** =========================
     *   Listing (GET) — unchanged
     * ========================= */
public function index(Request $request)
{
    if ($resp = $this->requireRole($request, ['admin','user'])) return $resp;

    $page     = max(1, (int) $request->query('page', 1));
    $perPage  = min(100, max(1, (int) $request->query('per_page', 10)));
    $q        = trim((string) $request->query('q', ''));
    $status   = trim((string) $request->query('status', ''));
    $orgType  = strtolower(trim((string) $request->query('org_type', '')));
    $sortRaw  = strtolower(trim((string) $request->query('sort', 'desc')));
    $orderDir = $sortRaw === 'asc' ? 'asc' : 'desc';

    $allowedOrg = ['company','hospital','clinic','ngo','individual','other'];

    $query = DB::table('clients');

    if ($q !== '') {
        $like = "%{$q}%";
        $query->where(function ($w) use ($like) {
            $w->where('name', 'LIKE', $like)
              ->orWhere('org_type', 'LIKE', $like)
              ->orWhere('email', 'LIKE', $like)
              ->orWhere('phone', 'LIKE', $like)
              ->orWhere('address', 'LIKE', $like)
              ->orWhere('city', 'LIKE', $like)
              ->orWhere('state', 'LIKE', $like)
              ->orWhere('postcode', 'LIKE', $like)
              ->orWhere('country', 'LIKE', $like)
              ->orWhere('timezone', 'LIKE', $like)
              ->orWhere('website_url', 'LIKE', $like)
              ->orWhere('contact_name', 'LIKE', $like)
              ->orWhere('contact_email', 'LIKE', $like)
              ->orWhere('contact_phone', 'LIKE', $like)
              ->orWhere('slug', 'LIKE', $like);
        });
    }

    if ($status !== '') {
        $query->where('status', $status);
    }

    if ($orgType !== '' && in_array($orgType, $allowedOrg, true)) {
        $query->where('org_type', $orgType);
    }

    // compute totals BEFORE pagination
    $total = (clone $query)->count();

    // compute total pages (allow zero when no rows)
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

    // clamp the requested page into the valid range
    if ($totalPages === 0) {
        $page = 1;
    } elseif ($page < 1) {
        $page = 1;
    } elseif ($page > $totalPages) {
        $page = $totalPages;
    }

    // apply ordering and pagination — forPage handles offset/limit cleanly
    $items = $query
        ->orderBy('created_at', $orderDir)
        ->orderBy('id', $orderDir)
        ->forPage($page, $perPage)
        ->get()
        ->map(function ($c) {
            $c->image_full_url = $this->imageUrl($c->image_url);
            return $c;
        });

    return response()->json([
        'status' => 'success',
        'message' => 'Clients fetched',
        'data' => $items,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ],
    ]);
}
    public function all(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','user'])) return $resp;

        $q        = trim((string) $request->query('q', ''));
        $status   = trim((string) $request->query('status', ''));
        $orgType  = strtolower(trim((string) $request->query('org_type', '')));
        $sortRaw  = strtolower(trim((string) $request->query('sort', 'asc')));
        $orderDir = $sortRaw === 'desc' ? 'desc' : 'asc';

        $allowedOrg = ['company','hospital','clinic','ngo','individual','other'];

        $query = DB::table('clients');

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($w) use ($like) {
                $w->where('name', 'LIKE', $like)
                    ->orWhere('org_type', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhere('address', 'LIKE', $like)
                    ->orWhere('city', 'LIKE', $like)
                    ->orWhere('state', 'LIKE', $like)
                    ->orWhere('postcode', 'LIKE', $like)
                    ->orWhere('country', 'LIKE', $like)
                    ->orWhere('timezone', 'LIKE', $like)
                    ->orWhere('website_url', 'LIKE', $like)
                    ->orWhere('contact_name', 'LIKE', $like)
                    ->orWhere('contact_email', 'LIKE', $like)
                    ->orWhere('contact_phone', 'LIKE', $like)
                    ->orWhere('slug', 'LIKE', $like);
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($orgType !== '' && in_array($orgType, $allowedOrg, true)) {
            $query->where('org_type', $orgType);
        }

        $items = $query
            ->orderBy('created_at', $orderDir)
            ->orderBy('id', $orderDir)
            ->get()
            ->map(function ($c) {
                $c->image_full_url = $this->imageUrl($c->image_url);
                return $c;
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'All clients fetched',
            'data'    => $items,
            'meta'    => ['count' => $items->count()],
        ]);
    }

    /** =========================
     *   Create (admin only)
     * ========================= */
    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $this->logWithActor('[Clients Store] start', $request);

        $data = $request->validate(
            [
                'name'           => 'required|string|max:255',
                'org_type'       => ['nullable', Rule::in(['company','hospital','clinic','ngo','individual','other'])],
                'email'          => ['nullable','email','max:255','unique:clients,email'],
                'phone'          => ['nullable','string','max:32','unique:clients,phone'],
                'address'        => 'nullable|string|max:255',
                'city'           => 'nullable|string|max:100',
                'state'          => 'nullable|string|max:100',
                'postcode'       => 'nullable|string|max:20',
                'country'        => 'nullable|string|size:2',
                'timezone'       => 'nullable|string|max:64',
                'website_url'    => 'nullable|url|max:255',
                'contact_name'   => 'nullable|string|max:120',
                'contact_email'  => ['nullable','email','max:255'],
                'contact_phone'  => ['nullable','string','max:32'],
                'status'         => 'nullable|string|in:active,inactive,archived',
                'metadata'       => 'nullable|array',
                'image_url'      => 'nullable|string|max:2048',
            ],
            [
                'email.unique'           => 'This email is already associated with another client.',
                'phone.unique'           => 'This phone number is already associated with another client.',
                'org_type.in'            => 'Org type must be one of: company, hospital, clinic, ngo, individual, other.',
            ]
        );

        if (!empty($data['email']))          $data['email'] = mb_strtolower($data['email']);
        if (!empty($data['contact_email']))  $data['contact_email'] = mb_strtolower($data['contact_email']);

        $normalizedImageUrl = !empty($data['image_url']) ? $this->imageUrl($data['image_url']) : null;

        $now  = now();
        $slug = $this->generateUniqueSlug(12);

        try {
            $id = DB::table('clients')->insertGetId([
                'name'           => $data['name'],
                'org_type'       => $data['org_type'] ?? null,
                'email'          => $data['email'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'address'        => $data['address'] ?? null,
                'city'           => $data['city'] ?? null,
                'state'          => $data['state'] ?? null,
                'postcode'       => $data['postcode'] ?? null,
                'country'        => $data['country'] ?? null,
                'timezone'       => $data['timezone'] ?? null,
                'website_url'    => $data['website_url'] ?? null,
                'image_url'      => $normalizedImageUrl,
                'contact_name'   => $data['contact_name'] ?? null,
                'contact_email'  => $data['contact_email'] ?? null,
                'contact_phone'  => $data['contact_phone'] ?? null,
                'status'         => $data['status'] ?? 'active',
                'slug'           => $slug,
                'metadata'       => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        } catch (QueryException $e) {
            // log failed attempt
            $this->logActivity($request, 'store', 'Clients', 'Create failed (duplicate or DB error)', 'clients', null, array_keys($data));
            if ($e->getCode() === '23000') {
                return $this->uniqueErrorResponse($e);
            }
            throw $e;
        }

        $client = DB::table('clients')->where('id', $id)->first();
        if ($client) $client->image_full_url = $this->imageUrl($client->image_url);

        // ✅ activity log
        $this->logActivity(
            $request,
            'store',
            'Clients',
            "Created client \"{$data['name']}\"",
            'clients',
            (int) $id,
            array_keys($data),
            null,
            $client ? (array) $client : null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Client created',
            'message'   => "Client \"{$data['name']}\" was created.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'created',
                'client'     => $client ? (array) $client : ['id'=>$id,'name'=>$data['name'],'slug'=>$slug],
                'actor'      => $this->actor($request),
                'client_id'  => $id,
            ],
            'type'      => 'client',
            'link_url'  => rtrim((string)config('app.url'), '/').'/clients/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        $this->logWithActor('[Clients Store] success', $request, ['client_id' => $id, 'slug' => $slug]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Client created successfully',
            'data'    => $client,
        ], 201);
    }

    /** =========================
     *   Read (GET) — unchanged
     * ========================= */
    public function show(string $slug, Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','user'])) return $resp;

        $client = $this->findBySlug($slug);
        if (!$client) return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        $client->image_full_url = $this->imageUrl($client->image_url);

        return response()->json([
            'status'  => 'success',
            'message' => 'Client fetched',
            'data'    => $client,
        ]);
    }

    public function showById(int $id, Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','user'])) return $resp;

        $client = $this->findById($id);
        if (!$client) return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        $client->image_full_url = $this->imageUrl($client->image_url);

        return response()->json([
            'status'  => 'success',
            'message' => 'Client fetched',
            'data'    => $client,
        ]);
    }

    /** =========================
     *   Update (admin only)
     * ========================= */
    public function update(Request $request, string $slug)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $this->logWithActor('[Clients Update] start', $request, [
            'slug'         => $slug,
            'http_method'  => $request->method(),
            'content_type' => $request->headers->get('content-type'),
            'input_keys'   => array_keys($request->all()),
        ]);

        try {
            $client = $this->findBySlug($slug);
            if (!$client) {
                // log not-found attempt
                $this->logActivity($request, 'update', 'Clients', 'Client not found', 'clients', null);
                return response()->json(['status'=>'error','message'=>'Client not found'], 404);
            }

            $data = $request->validate(
                [
                    'name'           => 'sometimes|string|max:255',
                    'org_type'       => ['sometimes','nullable', Rule::in(['company','hospital','clinic','ngo','individual','other'])],
                    'email'          => ['sometimes','nullable','email','max:255', Rule::unique('clients','email')->ignore($client->id)],
                    'phone'          => ['sometimes','nullable','string','max:32', Rule::unique('clients','phone')->ignore($client->id)],
                    'address'        => 'sometimes|nullable|string|max:255',
                    'city'           => 'sometimes|nullable|string|max:100',
                    'state'          => 'sometimes|nullable|string|max:100',
                    'postcode'       => 'sometimes|nullable|string|max:20',
                    'country'        => 'sometimes|nullable|string|size:2',
                    'timezone'       => 'sometimes|nullable|string|max:64',
                    'website_url'    => 'sometimes|nullable|url|max:255',
                    'contact_name'   => 'sometimes|nullable|string|max:120',
                    'contact_email'  => ['sometimes','nullable','email','max:255'],
                    'contact_phone'  => ['sometimes','nullable','string','max:32'],
                    'status'         => 'sometimes|nullable|string|in:active,inactive,archived',
                    'metadata'       => 'sometimes|nullable|array',
                    'image_url'      => 'sometimes|nullable|string|max:2048',
                ],
                [
                    'email.unique'           => 'This email is already associated with another client.',
                    'phone.unique'           => 'This phone number is already associated with another client.',
                   'org_type.in'            => 'Org type must be one of: company, hospital, clinic, ngo, individual, other.',
                ]
            );

            if (array_key_exists('email', $data) && !empty($data['email'])) {
                $data['email'] = mb_strtolower($data['email']);
            }
            if (array_key_exists('contact_email', $data) && !empty($data['contact_email'])) {
                $data['contact_email'] = mb_strtolower($data['contact_email']);
            }

            $update = [];
            foreach ([
                'name','org_type','email','phone','address','city','state','postcode','country',
                'timezone','website_url','contact_name','contact_email','contact_phone','status'
            ] as $f) {
                if (array_key_exists($f, $data)) $update[$f] = $data[$f];
            }
            if (array_key_exists('metadata', $data)) {
                $update['metadata'] = $data['metadata'] === null ? null : json_encode($data['metadata']);
            }
            if (array_key_exists('image_url', $data)) {
                $update['image_url'] = $data['image_url'] ? $this->imageUrl($data['image_url']) : null;
            }

            if (empty($update)) {
                $client->image_full_url = $this->imageUrl($client->image_url);
                // log no-op update attempt
                $this->logActivity($request, 'update', 'Clients', 'No changes detected', 'clients', (int) $client->id);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'No changes detected',
                    'data'    => $client,
                ]);
            }

            $oldSnapshot = (array) $client;

            $update['updated_at'] = now();

            try {
                DB::table('clients')->where('id', $client->id)->update($update);
            } catch (QueryException $e) {
                $this->logActivity($request, 'update', 'Clients', 'Update failed (duplicate or DB error)', 'clients', (int) $client->id, array_keys($update), $oldSnapshot);
                if ($e->getCode() === '23000') {
                    return $this->uniqueErrorResponse($e);
                }
                throw $e;
            }

            $fresh = DB::table('clients')->where('id', $client->id)->first();
            if ($fresh) $fresh->image_full_url = $this->imageUrl($fresh->image_url);

            // ✅ activity log
            $this->logActivity(
                $request,
                'update',
                'Clients',
                'Client updated successfully',
                'clients',
                (int) $client->id,
                array_keys($update),
                $oldSnapshot,
                $fresh ? (array) $fresh : null
            );

            // ✅ notify admins
            $changed = array_keys($update);
            $this->persistNotification([
                'title'     => 'Client updated',
                'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Client updated.',
                'receivers' => $this->adminReceivers(),
                'metadata'  => [
                    'action'     => 'updated',
                    'client'     => $fresh ? (array) $fresh : ['id'=>$client->id],
                    'client_id'  => $client->id,
                    'changed'    => $changed,
                ],
                'type'      => 'client',
                'link_url'  => rtrim((string)config('app.url'), '/').'/clients/'.$client->id,
                'priority'  => 'normal',
                'status'    => 'active',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Client updated successfully',
                'data'    => $fresh,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $this->logActivity($request, 'update', 'Clients', 'Validation failed', 'clients', null);
            throw $ve;
        } catch (\Throwable $ex) {
            Log::error('[Clients Update] unhandled exception', [
                'error' => $ex->getMessage(),
                'file'  => $ex->getFile(),
                'line'  => $ex->getLine(),
            ]);
            $this->logActivity($request, 'update', 'Clients', 'Unhandled exception', 'clients', null);
            return response()->json(['status'=>'error','message'=>'Unexpected error during update'], 500);
        }
    }

    /** Update by ID (admin only) */
    public function updateById(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $this->logWithActor('[Clients UpdateById] start', $request, [
            'id'          => $id,
            'http_method' => $request->method(),
            'content_type'=> $request->headers->get('content-type'),
            'input_keys'  => array_keys($request->all()),
        ]);

        try {
            $client = $this->findById($id);
            if (!$client) {
                $this->logActivity($request, 'update', 'Clients', 'Client not found', 'clients', $id);
                return response()->json(['status'=>'error','message'=>'Client not found'], 404);
            }

            $data = $request->validate(
                [
                    'name'           => 'sometimes|string|max:255',
                    'org_type'       => ['sometimes','nullable', Rule::in(['company','hospital','clinic','ngo','individual','other'])],
                    'email'          => ['sometimes','nullable','email','max:255', Rule::unique('clients','email')->ignore($client->id)],
                    'phone'          => ['sometimes','nullable','string','max:32', Rule::unique('clients','phone')->ignore($client->id)],
                    'address'        => 'sometimes|nullable|string|max:255',
                    'city'           => 'sometimes|nullable|string|max:100',
                    'state'          => 'sometimes|nullable|string|max:100',
                    'postcode'       => 'sometimes|nullable|string|max:20',
                    'country'        => 'sometimes|nullable|string|size:2',
                    'timezone'       => 'sometimes|nullable|string|max:64',
                    'website_url'    => 'sometimes|nullable|url|max:255',
                    'contact_name'   => 'sometimes|nullable|string|max:120',
                    'contact_email'  => ['sometimes','nullable','email','max:255'],
                    'contact_phone'  => ['sometimes','nullable','string','max:32'],
                    'status'         => 'sometimes|nullable|string|in:active,inactive,archived',
                    'metadata'       => 'sometimes|nullable|array',
                    'image_url'      => 'sometimes|nullable|string|max:2048',
                ],
                [
                    'email.unique'           => 'This email is already associated with another client.',
                    'phone.unique'           => 'This phone number is already associated with another client.',
                    'contact_email.unique'   => 'This contact email is already associated with another client.',
                    'contact_phone.unique'   => 'This contact phone number is already associated with another client.',
                    'org_type.in'            => 'Org type must be one of: company, hospital, clinic, ngo, individual, other.',
                ]
            );

            if (array_key_exists('email', $data) && !empty($data['email'])) {
                $data['email'] = mb_strtolower($data['email']);
            }
            if (array_key_exists('contact_email', $data) && !empty($data['contact_email'])) {
                $data['contact_email'] = mb_strtolower($data['contact_email']);
            }

            $update = [];
            foreach ([
                'name','org_type','email','phone','address','city','state','postcode','country',
                'timezone','website_url','contact_name','contact_email','contact_phone','status'
            ] as $f) {
                if (array_key_exists($f, $data)) $update[$f] = $data[$f];
            }
            // ✅ fixed: array_key_exists (not array_keyExists)
            if (array_key_exists('metadata', $data)) {
                $update['metadata'] = $data['metadata'] === null ? null : json_encode($data['metadata']);
            }
            if (array_key_exists('image_url', $data)) {
                $update['image_url'] = $data['image_url'] ? $this->imageUrl($data['image_url']) : null;
            }

            if (empty($update)) {
                $client->image_full_url = $this->imageUrl($client->image_url);
                $this->logActivity($request, 'update', 'Clients', 'No changes detected', 'clients', (int) $client->id);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'No changes detected',
                    'data'    => $client,
                ]);
            }

            $oldSnapshot = (array) $client;

            $update['updated_at'] = now();

            try {
                DB::table('clients')->where('id', $client->id)->update($update);
            } catch (QueryException $e) {
                $this->logActivity($request, 'update', 'Clients', 'Update failed (duplicate or DB error)', 'clients', (int) $client->id, array_keys($update), $oldSnapshot);
                if ($e->getCode() === '23000') {
                    return $this->uniqueErrorResponse($e);
                }
                throw $e;
            }

            $fresh = $this->findById($client->id);
            if ($fresh) $fresh->image_full_url = $this->imageUrl($fresh->image_url);

            // ✅ activity log
            $this->logActivity(
                $request,
                'update',
                'Clients',
                'Client updated successfully',
                'clients',
                (int) $client->id,
                array_keys($update),
                $oldSnapshot,
                $fresh ? (array) $fresh : null
            );

            // ✅ notify admins
            $changed = array_keys($update);
            $this->persistNotification([
                'title'     => 'Client updated',
                'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Client updated.',
                'receivers' => $this->adminReceivers(),
                'metadata'  => [
                    'action'     => 'updated',
                    'client'     => $fresh ? (array) $fresh : ['id'=>$client->id],
                    'client_id'  => $client->id,
                    'changed'    => $changed,
                ],
                'type'      => 'client',
                'link_url'  => rtrim((string)config('app.url'), '/').'/clients/'.$client->id,
                'priority'  => 'normal',
                'status'    => 'active',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Client updated successfully',
                'data'    => $fresh,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $this->logActivity($request, 'update', 'Clients', 'Validation failed', 'clients', $id);
            throw $ve;
        } catch (\Throwable $ex) {
            Log::error('[Clients UpdateById] unhandled exception', [
                'error' => $ex->getMessage(),
                'file'  => $ex->getFile(),
                'line'  => $ex->getLine(),
            ]);
            $this->logActivity($request, 'update', 'Clients', 'Unhandled exception', 'clients', $id);
            return response()->json(['status'=>'error','message'=>'Unexpected error during update'], 500);
        }
    }

    /** =========================
     *   Toggle (admin only)
     * ========================= */
    public function toggle(Request $request, string $slug)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $client = $this->findBySlug($slug);
        if (!$client) {
            $this->logActivity($request, 'toggle', 'Clients', 'Client not found', 'clients', null);
            return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        }

        if ($client->status === 'archived') {
            $this->logActivity($request, 'toggle', 'Clients', 'Archived clients cannot be toggled', 'clients', (int) $client->id);
            return response()->json([
                'status'=>'error',
                'message'=>'Archived clients cannot be toggled. Unarchive via update.',
            ], 422);
        }

        $newStatus = $client->status === 'active' ? 'inactive' : 'active';

        $old = ['status' => $client->status];

        DB::table('clients')->where('id', $client->id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        $fresh = DB::table('clients')->where('id', $client->id)->first();
        $fresh->image_full_url = $this->imageUrl($fresh->image_url);

        // ✅ activity log
        $this->logActivity(
            $request,
            'toggle',
            'Clients',
            "Status toggled to {$newStatus} for \"{$fresh->name}\"",
            'clients',
            (int) $client->id,
            ['status'],
            $old,
            ['status' => $newStatus]
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Client status updated',
            'message'   => "Status changed to {$newStatus} for client \"{$client->name}\".",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'status_changed',
                'client'     => $fresh ? (array) $fresh : (array) $client,
                'old_status' => $client->status,
                'new_status' => $newStatus,
            ],
            'type'      => 'client',
            'link_url'  => rtrim((string)config('app.url'), '/').'/clients/'.$client->id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Client status toggled to {$newStatus}",
            'data'    => $fresh,
        ]);
    }

    public function toggleById(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $client = $this->findById($id);
        if (!$client) {
            $this->logActivity($request, 'toggle', 'Clients', 'Client not found', 'clients', $id);
            return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        }

        if ($client->status === 'archived') {
            $this->logActivity($request, 'toggle', 'Clients', 'Archived clients cannot be toggled', 'clients', (int) $client->id);
            return response()->json([
                'status'=>'error',
                'message'=>'Archived clients cannot be toggled. Unarchive via update.',
            ], 422);
        }

        $newStatus = $client->status === 'active' ? 'inactive' : 'active';

        $old = ['status' => $client->status];

        DB::table('clients')->where('id', $client->id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        $fresh = $this->findById($client->id);
        if ($fresh) $fresh->image_full_url = $this->imageUrl($fresh->image_url);

        // ✅ activity log
        $this->logActivity(
            $request,
            'toggle',
            'Clients',
            "Status toggled to {$newStatus} for \"{$fresh->name}\"",
            'clients',
            (int) $client->id,
            ['status'],
            $old,
            ['status' => $newStatus]
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Client status updated',
            'message'   => "Status changed to {$newStatus} for client \"{$client->name}\".",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'status_changed',
                'client'     => $fresh ? (array) $fresh : (array) $client,
                'old_status' => $client->status,
                'new_status' => $newStatus,
            ],
            'type'      => 'client',
            'link_url'  => rtrim((string)config('app.url'), '/').'/clients/'.$client->id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Client status toggled to {$newStatus}",
            'data'    => $fresh,
        ]);
    }

    /** =========================
     *   Delete (admin only)
     * ========================= */
    public function destroy(Request $request, string $slug)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $client = $this->findBySlug($slug);
        if (!$client) {
            $this->logActivity($request, 'destroy', 'Clients', 'Client not found', 'clients', null);
            return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        }

        $snapshot = (array) $client;
        DB::table('clients')->where('id', $client->id)->delete();

        // ✅ activity log
        $this->logActivity(
            $request,
            'destroy',
            'Clients',
            "Deleted client \"{$client->name}\"",
            'clients',
            (int) $client->id,
            null,
            $snapshot,
            null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Client deleted',
            'message'   => "Client \"{$client->name}\" was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'deleted',
                'client'     => $snapshot,
                'client_id'  => (int) $client->id,
            ],
            'type'      => 'client',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Client deleted successfully',
        ]);
    }

    public function destroyById(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $client = $this->findById($id);
        if (!$client) {
            $this->logActivity($request, 'destroy', 'Clients', 'Client not found', 'clients', $id);
            return response()->json(['status'=>'error','message'=>'Client not found'], 404);
        }

        $snapshot = (array) $client;
        DB::table('clients')->where('id', $client->id)->delete();

        // ✅ activity log
        $this->logActivity(
            $request,
            'destroy',
            'Clients',
            "Deleted client \"{$client->name}\"",
            'clients',
            (int) $client->id,
            null,
            $snapshot,
            null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Client deleted',
            'message'   => "Client \"{$client->name}\" was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'deleted',
                'client'     => $snapshot,
                'client_id'  => (int) $client->id,
            ],
            'type'      => 'client',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Client deleted successfully',
        ]);
    }
}