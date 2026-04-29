<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    /** -------- Actor helper (from middleware-injected attributes) -------- */
    private function actor(Request $request): array
    {
        $id = $request->attributes->get('auth_actor_id')
            ?? $request->attributes->get('auth_id')
            ?? $request->attributes->get('auth_tokenable_id');

        return [
            'id'             => $id ? (int) $id : null,
            'role'           => $request->attributes->get('auth_role'),
            'tokenable_type' => $request->attributes->get('auth_tokenable_type'),
            'tokenable_id'   => $request->attributes->get('auth_tokenable_id'),
        ];
    }

    /** Helper: check read status for a user from receivers JSON array */
    private function isReadBy(array $receivers, int $userId, ?string $role = null): bool
    {
        foreach ($receivers as $r) {
            if ((int)($r['id'] ?? 0) === $userId && ($role === null || ($r['role'] ?? null) === $role)) {
                return (int)($r['read'] ?? 0) === 1;
            }
        }
        return false;
    }

    /** -------- Utility helpers -------- */

    private function decodeJsonArray(?string $json): array
    {
        if ($json === null || $json === '') return [];
        $val = json_decode($json, true);
        return is_array($val) ? $val : [];
    }

    private function decodeJsonObject(?string $json): array
    {
        if ($json === null || $json === '') return [];
        $val = json_decode($json, true);
        return is_array($val) ? $val : [];
    }

    private function normalizeReceivers(array $receivers): array
    {
        return array_map(function ($r) {
            return [
                'id'      => (int)($r['id'] ?? 0),
                'role'    => (string)($r['role'] ?? ''),
                'read'    => (int)($r['read'] ?? 0),
                'read_at' => $r['read_at'] ?? null,
            ];
        }, $receivers);
    }

    /** Tiny SQL helper: constrain rows to current actor in receivers (optionally by role) */
    private function whereActorInReceivers($q, int $actorId, ?string $role = null)
    {
        if ($role !== null && $role !== '') {
            return $q->whereRaw(
                "JSON_CONTAINS(receivers, JSON_OBJECT('id', ?, 'role', ?), '$')",
                [$actorId, $role]
            );
        }
        return $q->whereRaw(
            "JSON_CONTAINS(receivers, JSON_OBJECT('id', ?), '$')",
            [$actorId]
        );
    }

    /** GET /api/notifications/my?role=&unread=1&type=&priority=&status=&limit=&page=&before_id=&since_id= */
    public function my(Request $req)
    {
        $actor   = $this->actor($req);
        $actorId = (int)($actor['id'] ?? 0);
        if (!$actorId) return response()->json(['message' => 'Unauthorized (no actor)'], 401);

        // default role filter to actor role unless explicitly overridden
        $role      = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);
        $limit     = min(100, max(10, (int)$req->get('limit', 20)));
        $page      = max(1, (int)$req->get('page', 1));
        $beforeId  = (int)$req->get('before_id', 0);
        $sinceId   = (int)$req->get('since_id', 0);

        // Base query: receivers contains actor (and optional role)
        $q = DB::table('notifications')->whereIn('status', ['active','archived']);
        $this->whereActorInReceivers($q, $actorId, $role);

        if ($req->filled('type'))     $q->where('type', (string)$req->type);
        if ($req->filled('priority')) $q->where('priority', (string)$req->priority);
        if ($req->filled('status'))   $q->where('status', (string)$req->status);
        if ($beforeId > 0)            $q->where('id', '<', $beforeId);
        if ($sinceId > 0)             $q->where('id', '>', $sinceId);

        $q->orderByDesc('id');

        // Unread filter: fetch a window, filter in PHP, then paginate
        if ($req->boolean('unread')) {
            $window = min(1000, max($limit * 5, 200));
            $rows = $q->limit($window)->get();

            $mapped = [];
            foreach ($rows as $row) {
                $row->receivers = $this->normalizeReceivers($this->decodeJsonArray($row->receivers ?? '[]'));
                $row->metadata  = $this->decodeJsonObject($row->metadata ?? '{}');
                if (!$this->isReadBy($row->receivers, $actorId, $role)) {
                    $mapped[] = $row;
                }
            }

            $total = count($mapped);
            $start = ($page - 1) * $limit;
            $slice = array_slice($mapped, $start, $limit);

            return response()->json([
                'data' => array_values($slice),
                'pagination' => [
                    'total'         => $total,
                    'per_page'      => $limit,
                    'current_page'  => $page,
                    'last_page'     => (int)ceil($total / max(1, $limit)),
                ],
            ]);
        }

        // Normal listing (paginate first; decode JSON for each item)
        $p = $q->paginate($limit, ['*'], 'page', $page);
        $p->getCollection()->transform(function ($row) {
            $row->receivers = $this->normalizeReceivers($this->decodeJsonArray($row->receivers ?? '[]'));
            $row->metadata  = $this->decodeJsonObject($row->metadata ?? '{}');
            return $row;
        });

        return response()->json([
            'data' => $p->items(),
            'pagination' => [
                'total'         => $p->total(),
                'per_page'      => $p->perPage(),
                'current_page'  => $p->currentPage(),
                'last_page'     => $p->lastPage(),
            ],
        ]);
    }

    /** GET /api/notifications/unread-count?role=&type=&priority= */
    public function unreadCount(Request $req)
    {
        $actor   = $this->actor($req);
        $actorId = (int)($actor['id'] ?? 0);
        if (!$actorId) return response()->json(['message' => 'Unauthorized (no actor)'], 401);

        $role = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);

        $q = DB::table('notifications')->where('status', 'active');
        $this->whereActorInReceivers($q, $actorId, $role);

        if ($req->filled('type'))     $q->where('type', (string)$req->type);
        if ($req->filled('priority')) $q->where('priority', (string)$req->priority);

        $count = 0;
        $q->orderByDesc('id')->cursor()->each(function ($row) use (&$count, $actorId, $role) {
            $receivers = $this->normalizeReceivers($this->decodeJsonArray($row->receivers ?? '[]'));
            if (!$this->isReadBy($receivers, $actorId, $role)) $count++;
        });

        return response()->json(['unread' => $count]);
    }

    /** POST /api/notifications */
    public function store(Request $req)
    {
        $data = $req->validate([
            'title'     => ['required','string','max:255'],
            'message'   => ['required','string'],
            'receivers' => ['required','array','min:1'],
            'receivers.*.id'   => ['required','integer','min:1'],
            'receivers.*.role' => ['required','string','max:64'],
            'metadata'  => ['sometimes','array'],
            'type'      => ['sometimes','string','max:50'],
            'link_url'  => ['sometimes','nullable','string','max:1024'],
            'priority'  => ['sometimes', Rule::in(['low','normal','high','urgent'])],
            'status'    => ['sometimes', Rule::in(['active','archived','deleted'])],
        ]);

        $receivers = array_map(function ($r) {
            return [
                'id'      => (int)$r['id'],
                'role'    => (string)$r['role'],
                'read'    => 0,
                'read_at' => null,
            ];
        }, $data['receivers']);

        $now = now();

        $id = DB::table('notifications')->insertGetId([
            'title'      => (string)$data['title'],
            'message'    => (string)$data['message'],
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => isset($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : null,
            'type'       => (string)($data['type'] ?? 'general'),
            'link_url'   => $data['link_url'] ?? null,
            'priority'   => (string)($data['priority'] ?? 'normal'),
            'status'     => (string)($data['status'] ?? 'active'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $row = DB::table('notifications')->where('id', $id)->first();
        $row->receivers = $this->normalizeReceivers($this->decodeJsonArray($row->receivers ?? '[]'));
        $row->metadata  = $this->decodeJsonObject($row->metadata ?? '{}');

        return response()->json($row, 201);
    }

    /** PATCH /api/notifications/{id}/read  body: {read:true|false, role?:string} */
    /** PATCH /api/notifications/{id}/read  body: {read:true|false, role?:string} */
public function markRead($id, Request $req)
{
    $actor   = $this->actor($req);
    $actorId = (int)($actor['id'] ?? 0);
    if (!$actorId) return response()->json(['message' => 'Unauthorized (no actor)'], 401);

    // FIXED: Use request role, fallback to actor role if not provided
    $role = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);
    $read = $req->boolean('read', true);

    $row = DB::table('notifications')->where('id', (int)$id)->first();
    if (!$row) return response()->json(['message'=>'Not found'], 404);

    $receivers = $this->normalizeReceivers($this->decodeJsonArray($row->receivers ?? '[]'));
    $changed = false;
    
    foreach ($receivers as &$rec) {
        $idMatch = (int)Arr::get($rec,'id') === $actorId;
        $roleMatch = $role === null || Arr::get($rec,'role') === $role;
        
        if ($idMatch && $roleMatch) {
            $rec['read']    = $read ? 1 : 0;
            $rec['read_at'] = $read ? now()->toIso8601String() : null;
            $changed = true;
        }
    }
    unset($rec);

    if ($changed) {
        DB::table('notifications')->where('id', (int)$id)->update([
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    $out = DB::table('notifications')->where('id', (int)$id)->first();
    $out->receivers = $this->normalizeReceivers($this->decodeJsonArray($out->receivers ?? '[]'));
    $out->metadata  = $this->decodeJsonObject($out->metadata ?? '{}');

    return response()->json(['ok'=>true, 'notification'=>$out]);
}
    /**
     * POST /api/notifications/mark-many-read
     * body: { ids: [..], read: true|false, role?: string }
     */
    public function markManyRead(Request $req)
    {
        $data = $req->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','min:1'],
            'read'  => ['sometimes','boolean'],
            'role'  => ['sometimes','nullable','string','max:64'],
        ]);

        $actor   = $this->actor($req);
        $actorId = (int)($actor['id'] ?? 0);
        if (!$actorId) return response()->json(['message' => 'Unauthorized (no actor)'], 401);

        $role = array_key_exists('role', $data) ? (string)$data['role'] : ($actor['role'] ?? null);
        $read = array_key_exists('read', $data) ? (bool)$data['read'] : true;

        $updated = 0;

        DB::table('notifications')->whereIn('id', $data['ids'])->cursor()->each(function ($row) use (&$updated, $actorId, $role, $read) {
            $receivers = json_decode($row->receivers ?? '[]', true);
            if (!is_array($receivers)) $receivers = [];
            $changed = false;

            foreach ($receivers as &$rec) {
                $idMatch   = (int)($rec['id'] ?? 0) === $actorId;
                $roleMatch = $role === null || ($rec['role'] ?? null) === $role;
                if ($idMatch && $roleMatch) {
                    $newRead = $read ? 1 : 0;
                    if ((int)($rec['read'] ?? 0) !== $newRead) {
                        $rec['read'] = $newRead;
                        $rec['read_at'] = $read ? now()->toIso8601String() : null;
                        $changed = true;
                        $updated++;
                    }
                }
            }
            unset($rec);

            if ($changed) {
                DB::table('notifications')->where('id', $row->id)->update([
                    'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json(['ok'=>true, 'updated'=>$updated]);
    }

    /** POST /api/notifications/mark-all-read  (optional role) */
    public function markAllRead(Request $req)
    {
        $actor   = $this->actor($req);
        $actorId = (int)($actor['id'] ?? 0);
        if (!$actorId) return response()->json(['message' => 'Unauthorized (no actor)'], 401);

        $role         = $req->filled('role') ? (string)$req->role : ($actor['role'] ?? null);
        $updatedCount = 0;

        $q = DB::table('notifications')->where('status', 'active');
        $this->whereActorInReceivers($q, $actorId, $role);

        $q->orderByDesc('id')
          ->cursor()
          ->each(function ($row) use (&$updatedCount, $actorId, $role) {
              $receivers = json_decode($row->receivers ?? '[]', true);
              if (!is_array($receivers)) $receivers = [];
              $changed = false;
              foreach ($receivers as &$rec) {
                  $idMatch   = (int)($rec['id'] ?? 0) === $actorId;
                  $roleMatch = $role === null || ($rec['role'] ?? null) === $role;
                  if ($idMatch && $roleMatch && ((int)($rec['read'] ?? 0) === 0)) {
                      $rec['read'] = 1;
                      $rec['read_at'] = now()->toIso8601String();
                      $changed = true;
                      $updatedCount++;
                  }
              }
              unset($rec);

              if ($changed) {
                  DB::table('notifications')->where('id', $row->id)->update([
                      'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
                      'updated_at' => now(),
                  ]);
              }
          });

        return response()->json(['ok'=>true, 'updated'=>$updatedCount]);
    }

    /** PATCH /api/notifications/{id}/archive */
    public function archive($id)
    {
        $n = DB::table('notifications')->where('id', (int)$id)->update([
            'status' => 'archived',
            'updated_at' => now(),
        ]);
        if (!$n) return response()->json(['message'=>'Not found'], 404);
        return response()->json(['ok'=>true]);
    }

    /** DELETE /api/notifications/{id} (soft delete via status) */
    public function destroy($id)
    {
        $n = DB::table('notifications')->where('id', (int)$id)->update([
            'status' => 'deleted',
            'updated_at' => now(),
        ]);
        if (!$n) return response()->json(['message'=>'Not found'], 404);
        return response()->json(['ok'=>true]);
    }
}
