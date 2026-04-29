<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Throwable;

class MailerController extends Controller
{
    /* =========================================================
     |  Activity Log helpers
     |=========================================================*/

    private function cacheVerKey(string $type, int $id): string
    {
        return "mailer:ver:{$type}:{$id}";
    }

    private function bumpOwnerCache(string $type, int $id): void
    {
        if (!Cache::has($this->cacheVerKey($type, $id))) {
            Cache::forever($this->cacheVerKey($type, $id), 1);
        }
        Cache::increment($this->cacheVerKey($type, $id));
    }

    private function indexCacheKey(Request $r, string $type, int $id): string
    {
        $ver  = (int) Cache::get($this->cacheVerKey($type, $id), 1);
        $q    = trim((string) $r->query('q',''));
        $drv  = strtolower((string) $r->query('driver',''));
        $enc  = strtolower((string) $r->query('encryption',''));
        $rows = (int) $r->query('rows', 50);
        return "mailer:index:v{$ver}:{$type}:{$id}:{$drv}:{$enc}:{$rows}:".md5($q);
    }

    /**
     * Resolve actor (id, displayName, roleAlias) from CheckRole middleware.
     * Model-free; supports only two roles: admin | user (tables: admins, users).
     *
     * Middleware should set:
     *  - auth_tokenable_type (string discriminator; not a model class)
     *  - auth_tokenable_id   (int PK)
     *  - auth_role           ("admin" | "user")
     * Optional:
     *  - auth_table          ("admins" | "users") — overrides table selection
     */
    private function actor(Request $r): array
    {
        $type = (string) $r->attributes->get('auth_tokenable_type');
        $id   = (int)    $r->attributes->get('auth_tokenable_id');
        $role =          $r->attributes->get('auth_role');   // "admin" | "user"

        if (!$type || !$id) {
            return [0, 'Unknown User', $role];
        }

        // Prefer explicit table if provided.
        $tableFromAttr = $r->attributes->get('auth_table');
        $table = is_string($tableFromAttr) && $tableFromAttr !== '' ? $tableFromAttr : null;

        // Only two roles → two tables
        if (!$table) {
            $roleToTable = [
                'admin' => 'admins',
                'user'  => 'users',
            ];
            if (is_string($role) && isset($roleToTable[$role])) {
                $table = $roleToTable[$role];
            }
        }

        // Final fallback: probe only these two tables for the id
        if (!$table) {
            foreach (['admins','users'] as $cand) {
                try {
                    $probe = DB::table($cand)->where('id', $id)->limit(1)->first();
                    if ($probe) { $table = $cand; break; }
                } catch (\Throwable $e) { /* ignore */ }
            }
        }

        if (!$table) {
            return [$id, "#{$id}", $role];
        }

        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) {
            return [$id, "#{$id}", $role];
        }

        foreach (['name','full_name','username','email'] as $c) {
            if (isset($row->{$c}) && (string)$row->{$c} !== '') {
                return [$id, (string)$row->{$c}, $role];
            }
        }

        return [$id, "#{$id}", $role];
    }

    /**
     * Best-effort secret masking for arrays used in activity_logs.meta
     */
    private function maskSecrets($data) {
        if (!is_array($data)) return $data;
        $masked = $data;

        $keysToMask = ['password','pass','pwd','secret','token','api_key','apikey','authorization','auth'];
        foreach ($masked as $k => &$v) {
            if (is_array($v)) {
                $v = $this->maskSecrets($v);
                continue;
            }
            if (in_array(strtolower((string)$k), $keysToMask, true)) {
                $v = '[hidden]';
            }
            // Mask Authorization-like values
            if (is_string($v) && preg_match('/^(Bearer|Basic)\s+/i', $v)) {
                $v = '[hidden]';
            }
        }
        return $masked;
    }

    /**
     * Write audit rows:
     *  1) user_data_activity_log (existing behavior)
     *  2) activity_logs (generic log; safe to skip if table absent)
     *
     * $changed can be a list of field names or an assoc array (keys are used).
     */
    private function audit(
        Request $r,
        string $activity,                 // index|show|store|update|default|destroy
        string $note,
        ?int $recordId,
        ?array $changed = null,
        ?array $old = null,
        ?array $new = null
    ): void {
        [$by, $name, $role] = $this->actor($r);

        // normalize changed => flat list
        $changedFields = null;
        if (is_array($changed)) {
            $changedFields = array_values(array_unique(
                array_map('strval',
                    array_is_list($changed) ? $changed : array_keys($changed)
                )
            ));
        }

        // 1) Original table insert (kept as-is)
        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $by,
                'performed_by_role' => $role,
                'ip'                => $r->ip(),
                'user_agent'        => (string) $r->userAgent(),
                'activity'          => $activity,
                'module'            => 'Mailer',
                'table_name'        => 'mailer_settings',
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Mailer audit failed (user_data_activity_log)', ['err' => $e->getMessage()]);
        }

        // 2) Generic activity_logs insert (non-fatal if table missing)
        try {
            $meta = [
                'route'        => optional($r->route())->uri(),
                'http_method'  => $r->method(),
                'query'        => $this->maskSecrets($r->query()),
                'payload'      => $this->maskSecrets($r->except(['password'])),
                'changed'      => $changedFields,
                'old'          => $old ? $this->maskSecrets($old) : null,
                'new'          => $new ? $this->maskSecrets($new) : null,
                'user_display' => $name,
                'user_role'    => $role,
                'user_agent'   => (string) $r->userAgent(),
            ];

            DB::table('activity_logs')->insert([
                'occurred_at'        => now(),
                'module'             => 'Mailer',
                'activity'           => $activity,
                'performed_by'       => $by,
                'performed_by_role'  => $role,
                'record_id'          => $recordId,
                'route'              => $meta['route'],
                'http_method'        => $meta['http_method'],
                'ip'                 => $r->ip(),
                'user_agent'         => $meta['user_agent'],
                'meta'               => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'note'               => $note,
                'created_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Mailer audit (activity_logs) skipped', ['err' => $e->getMessage()]);
        }
    }

    /** Mask sensitive fields before returning/logging row snapshots. */
    private function hideSecrets(array $row): array
    {
        if (array_key_exists('password', $row)) {
            $row['password'] = '[hidden]';
        }
        return $row;
    }

    /* =========================================================
     |  Owner + validation helpers
     |=========================================================*/

    /** Get polymorphic owner from CheckRole middleware. */
    private function owner(Request $request): array
    {
        $type = (string) $request->attributes->get('auth_tokenable_type');
        $id   = (int)    $request->attributes->get('auth_tokenable_id');

        if (!$type || !$id) {
            abort(response()->json(['status'=>'error','message'=>'Unauthorized'], 403));
        }
        return [$type, $id];
    }

    /** Normalize incoming keys + values from form. */
    private function normalize(Request $r): void
    {
        $r->merge([
            'mailer'       => $r->input('mailer', $r->input('driver')),
            'from_address' => $r->input('from_address', $r->input('fromAddress')),
            'from_name'    => $r->input('from_name',    $r->input('fromName')),
        ]);

        $mailer = strtolower((string) $r->input('mailer'));
        $enc    = strtolower((string) $r->input('encryption'));

        if ($enc === 'none' || $enc === '')            $enc = null;
        elseif ($enc === 'starttls')                    $enc = 'tls';
        elseif (!in_array($enc, ['tls','ssl'], true))   $enc = null;

        $r->merge([
            'mailer'     => $mailer ?: 'smtp',
            'encryption' => $enc
        ]);
    }

    /** Validation rules (host/port/creds required for SMTP). */
    private function validator(Request $r)
    {
        $rules = [
            'mailer'       => 'required|string|in:smtp,sendmail,ses,mailgun,postmark,log,array',
            'host'         => 'nullable|string',
            'port'         => 'nullable|integer|min:1|max:65535',
            'username'     => 'nullable|string',
            'password'     => 'nullable|string',
            'encryption'   => 'nullable|in:tls,ssl',
            'from_address' => 'required|email',
            'from_name'    => 'required|string|max:190',
            'label'        => 'sometimes|nullable|string|max:190',
            'is_default'   => 'sometimes|boolean',
        ];

        if (strtolower((string)$r->input('mailer')) === 'smtp') {
            $rules['host']     = 'required|string';
            $rules['port']     = 'required|integer|min:1|max:65535';
            $rules['username'] = 'required|string';
        }

        $v = Validator::make($r->all(), $rules);

        // On create, require password for SMTP
        $v->after(function ($validator) use ($r) {
            if ($r->isMethod('post') && strtolower((string)$r->input('mailer')) === 'smtp') {
                if (!$r->filled('password')) {
                    $validator->errors()->add('password', 'The password field is required for smtp.');
                }
            }
        });

        return $v;
    }

    /** Build payload for insert/update; encrypt password if provided. */
    private function buildPayload(Request $r, bool $isUpdate = false, ?string $existingEncryptedPwd = null): array
    {
        $payload = [
            'label'        => $r->input('label'),
            'mailer'       => $r->input('mailer'),
            'host'         => $r->input('host'),
            'port'         => (int) $r->input('port'),
            'username'     => $r->input('username'),
            'encryption'   => $r->input('encryption'),
            'from_address' => $r->input('from_address'),
            'from_name'    => $r->input('from_name'),
        ];

        $pwd = $r->input('password');
        if ($isUpdate) {
            if ($pwd !== null && $pwd !== '' && $pwd !== '******') {
                $payload['password'] = Crypt::encryptString($pwd);
            } elseif ($existingEncryptedPwd !== null) {
                $payload['password'] = $existingEncryptedPwd; // keep current
            }
        } else {
            if ($pwd !== null && $pwd !== '') {
                $payload['password'] = Crypt::encryptString($pwd);
            }
        }

        return $payload;
    }

    /** Query builder scoped to owner. */
    private function baseQuery(string $ownerType, int $ownerId)
    {
        return DB::table('mailer_settings')
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);
    }

    /* =========================================================
     |  Endpoints
     |=========================================================*/

    // GET /api/mailer
    public function index(Request $request)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        // Optional filters: ?q=&driver=smtp&encryption=tls|ssl|none&rows=50
        $q          = trim((string) $request->query('q', ''));
        $driver     = strtolower((string) $request->query('driver', ''));
        $encryption = strtolower((string) $request->query('encryption', ''));
        $rows       = min(100, max(1, (int) $request->query('rows', 50)));

        $cacheKey = $this->indexCacheKey($request, $ownerType, $ownerId);

        $list = Cache::remember($cacheKey, 15, function () use ($ownerType, $ownerId, $q, $driver, $encryption, $rows) {
            $qb = $this->baseQuery($ownerType, $ownerId)
                ->select([
                    'id','label','mailer','host','port','username','encryption',
                    'from_address','from_name','is_default','created_at'
                ])
                ->orderByDesc('is_default')
                ->orderByDesc('id');

            if ($driver !== '') {
                $qb->where('mailer', $driver);
            }

            if ($encryption !== '') {
                if ($encryption === 'none') $qb->whereNull('encryption');
                else $qb->where('encryption', $encryption);
            }

            if ($q !== '') {
                $like = "%{$q}%";
                $qb->where(function ($w) use ($like) {
                    $w->where('mailer','like',$like)
                      ->orWhere('host','like',$like)
                      ->orWhere('username','like',$like)
                      ->orWhere('from_address','like',$like)
                      ->orWhere('from_name','like',$like)
                      ->orWhere('label','like',$like);
                });
            }

            return $qb->limit($rows)->get();
        })->map(function ($row) {
            $row->password = '******'; // never expose real value
            return $row;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Mailer settings fetched.',
            'data'    => $list,
        ]);
    }

    // POST /api/mailer
    public function store(Request $request)
    {
        [$ownerType, $ownerId] = $this->owner($request);
        $this->normalize($request);

        $v = $this->validator($request);
        if ($v->fails()) {
            $this->audit($request, 'store', 'Validation failed (create)', null, null, null, ['errors' => $v->errors()->toArray()]);
            return response()->json([
                'status'=>'error','message'=>'Validation failed','errors'=>$v->errors()
            ], 422);
        }

        // Always create as non-default
        $payload = $this->buildPayload($request, false);
        $payload += [
            'owner_type' => $ownerType,
            'owner_id'   => $ownerId,
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::beginTransaction();
            $id = DB::table('mailer_settings')->insertGetId($payload);
            DB::commit();

            $this->bumpOwnerCache($ownerType, $ownerId);

            $created = (array) $this->baseQuery($ownerType, $ownerId)->where('id',$id)->first();
            $createdMasked = $this->hideSecrets($created);

            $this->audit(
                $request,
                'store',
                'Created mailer setting',
                $id,
                array_keys($payload),
                null,
                $createdMasked
            );

            $created['password'] = '******';
            return response()->json([
                'status'=>'success',
                'message'=>'Mailer setting created (not default).',
                'data'=>(object)$created,
            ], 201);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Mailer store failed', ['err'=>$e->getMessage()]);
            $this->audit($request, 'store', 'Create failed', null, null, null, ['error' => $e->getMessage()]);
            return response()->json([
                'status'=>'error','message'=>'Could not create','error'=>$e->getMessage()
            ], 500);
        }
    }

    // GET /api/mailer/{id}
    public function show(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $row = $this->baseQuery($ownerType, $ownerId)->where('id',$id)->first();
        if (!$row) {
            $this->audit($request, 'show', 'Mailer not found', $id);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        $this->audit($request, 'show', 'Viewed mailer', $id);

        $row->password = '******';
        return response()->json([
            'status'=>'success','message'=>'Mailer setting retrieved.','data'=>$row
        ]);
    }

    // PUT /api/mailer/{id}
    public function update(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);
        $this->normalize($request);

        $existing = $this->baseQuery($ownerType, $ownerId)->where('id',$id)->first();
        if (!$existing) {
            $this->audit($request, 'update', 'Mailer not found', $id);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        $v = $this->validator($request);
        if ($v->fails()) {
            $this->audit($request, 'update', 'Validation failed (update)', $id, null, null, ['errors'=>$v->errors()->toArray()]);
            return response()->json([
                'status'=>'error','message'=>'Validation failed','errors'=>$v->errors()
            ], 422);
        }

        $payload = $this->buildPayload($request, true, $existing->password);
        $payload['updated_at'] = now();

        $makeDefault = $request->boolean('is_default', false);

        try {
            DB::beginTransaction();

            if ($makeDefault) {
                $this->baseQuery($ownerType, $ownerId)->update(['is_default' => 0]);
                $payload['is_default'] = 1;
            }

            $this->baseQuery($ownerType, $ownerId)->where('id',$id)->update($payload);

            DB::commit();

            $this->bumpOwnerCache($ownerType, $ownerId);

            $fresh = (array) $this->baseQuery($ownerType, $ownerId)->where('id',$id)->first();
            $this->audit(
                $request,
                'update',
                $makeDefault ? 'Updated mailer & set default' : 'Updated mailer',
                $id,
                array_keys($payload),
                $this->hideSecrets((array)$existing),
                $this->hideSecrets($fresh)
            );

            $fresh['password'] = '******';
            return response()->json([
                'status'=>'success','message'=>'Mailer setting updated.','data'=>(object)$fresh
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Mailer update failed', ['err'=>$e->getMessage()]);
            $this->audit($request, 'update', 'Update failed', $id, null, $this->hideSecrets((array)$existing), ['error'=>$e->getMessage()]);
            return response()->json([
                'status'=>'error','message'=>'Could not update','error'=>$e->getMessage()
            ], 500);
        }
    }

    // PUT /api/mailer/{id}/default
    public function setDefault(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $exists = $this->baseQuery($ownerType, $ownerId)->where('id', $id)->exists();
        if (!$exists) {
            $this->audit($request, 'default', 'Mailer not found for default', $id);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        try {
            // Single-statement, atomic: set selected id=1, others=0
            DB::statement(
                "UPDATE mailer_settings
                 SET is_default = CASE WHEN id = ? THEN 1 ELSE 0 END,
                     updated_at = NOW()
                 WHERE owner_type = ? AND owner_id = ?",
                [$id, $ownerType, $ownerId]
            );

            // bust cached lists for this owner
            $this->bumpOwnerCache($ownerType, $ownerId);

            $updated = $this->baseQuery($ownerType, $ownerId)
                ->select(['id','label','mailer','host','port','username','encryption','from_address','from_name','is_default'])
                ->orderByDesc('is_default')->orderByDesc('id')
                ->get()
                ->map(function ($r) { $r->password = '******'; return $r; });

            $this->audit(
                $request,
                'default',
                'Marked mailer as default (others off)',
                $id,
                ['is_default'],
                null,
                ['id'=>$id,'is_default'=>1]
            );

            return response()->json([
                'status'=>'success',
                'message'=>'Default mailer set. Others disabled.',
                'data'=>$updated
            ]);

        } catch (Throwable $e) {
            $this->audit($request, 'default', 'Default toggle failed', $id, null, null, ['error'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Could not set default','error'=>$e->getMessage()], 500);
        }
    }

    // DELETE /api/mailer/{id}
    public function destroy(Request $request, int $id)
    {
        [$ownerType, $ownerId] = $this->owner($request);

        $row = $this->baseQuery($ownerType, $ownerId)->where('id',$id)->first();
        if (!$row) {
            $this->audit($request, 'destroy', 'Mailer not found (delete)', $id);
            return response()->json(['status'=>'error','message'=>'Not found'], 404);
        }

        try {
            DB::beginTransaction();

            DB::table('mailer_settings')->where('id',$id)->delete();

            // If deleted one was default, promote most recent (if any)
            if ((int)$row->is_default === 1) {
                $candidate = $this->baseQuery($ownerType, $ownerId)->orderByDesc('id')->first();
                if ($candidate) {
                    $this->baseQuery($ownerType, $ownerId)->where('id',$candidate->id)->update([
                        'is_default' => 1, 'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            $this->bumpOwnerCache($ownerType, $ownerId);

            $this->audit(
                $request,
                'destroy',
                'Deleted mailer setting',
                $id,
                null,
                $this->hideSecrets((array)$row),
                null
            );

            return response()->json(['status'=>'success','message'=>'Mailer setting deleted.']);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Mailer delete failed', ['err'=>$e->getMessage()]);
            $this->audit($request, 'destroy', 'Delete failed', $id, null, $this->hideSecrets((array)$row), ['error'=>$e->getMessage()]);
            return response()->json([
                'status'=>'error','message'=>'Could not delete','error'=>$e->getMessage()
            ], 500);
        }
    }
}
