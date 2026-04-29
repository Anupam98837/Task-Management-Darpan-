<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DocumentTypeController extends Controller
{
    private array $sortable = ['name','status','created_at','updated_at'];

    /* =========================
     * Auth/Role + Activity Log
     * ========================= */

    /** Actor pulled from CheckRole middleware */
    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),                 // 'admin' | 'user' | null
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /**
     * Insert row into user_data_activity_log using DB facade.
     * Columns expected:
     * performed_by, performed_by_role, ip, user_agent, activity, module, table_name,
     * record_id, changed_fields (json), old_values (json), new_values (json),
     * log_note, created_at, updated_at
     */
    private function logActivity(
        Request $request,
        string $activity,                 // 'store'|'update'|'destroy'
        string $module,                   // 'DocumentTypes'
        string $note,                     // human-readable note
        string $tableName,                // 'document_types'
        ?int $recordId = null,
        ?array $changed = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->actor($request);

        $changedFields = null;
        if (is_array($changed)) {
            $changedFields = array_values(array_unique(
                array_keys($changed) === range(0, count($changed)-1) ? $changed : array_keys($changed)
            ));
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
            Log::error('user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /* =========================
     * Notification helpers (DB-only)
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

    /* ================= Query helpers ================= */

    private function applySearch($q, ?string $term)
    {
        if (!$term) return $q;
        $needle = mb_strtolower(trim($term));
        return $q->where(function($qq) use ($needle) {
            $qq->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"])
               ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$needle}%"])
               ->orWhereRaw('LOWER(COALESCE(note, \'\')) LIKE ?', ["%{$needle}%"]);
        });
    }

    private function applyFilters($q, Request $r)
    {
        if ($r->filled('status')) {
            $statuses = collect(explode(',', (string)$r->status))
                ->map(fn($s)=>trim($s))
                ->filter()
                ->values()
                ->all();
            if ($statuses) $q->whereIn('status', $statuses);
        }
        if ($r->filled('created_from')) $q->whereDate('created_at', '>=', $r->created_from);
        if ($r->filled('created_to'))   $q->whereDate('created_at', '<=', $r->created_to);
        return $q;
    }

    private function applySorting($q, Request $r)
    {
        $by  = in_array($r->get('sort_by'), $this->sortable, true) ? $r->get('sort_by') : 'name';
        $dir = strtolower($r->get('sort_dir','asc')) === 'desc' ? 'desc' : 'asc';
        return $q->orderBy($by, $dir);
    }

    /* ================= Endpoints ================= */

    public function index(Request $r)
    {
        $perPage = min(max((int)$r->get('per_page', 20), 1), 100);

        $q = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at');

        $this->applySearch($q, $r->get('q'));
        $this->applyFilters($q, $r);
        $this->applySorting($q, $r);

        $page = $q->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Fetched document types',
            'data'    => $page->items(),
            'meta'    => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ],
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'        => ['required','string','max:120','unique:document_types,name'],
            'description' => ['nullable','string','max:255'],
            'note'        => ['nullable','string','max:255'],
            'status'      => ['nullable', Rule::in(['active','inactive','archived'])],
        ]);

        $now = now();

        $id = DB::table('document_types')->insertGetId([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'note'        => $data['note'] ?? null,
            'status'      => $data['status'] ?? 'active',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $row = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        // ✅ activity log
        $this->logActivity(
            $r,
            'store',
            'DocumentTypes',
            "Created document type \"{$data['name']}\"",
            'document_types',
            (int)$id,
            array_keys($data),
            null,
            $row ? (array)$row : null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Document type created',
            'message'   => "Document type \"{$data['name']}\" was created.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'         => 'created',
                'document_type'  => $row ? (array)$row : ['id'=>$id,'name'=>$data['name']],
                'actor'          => $this->actor($r),
                'document_type_id' => $id,
            ],
            'type'      => 'document_type',
            'link_url'  => rtrim((string)config('app.url'), '/').'/document-types/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document type created',
            'data'    => $row
        ], 201);
    }

    public function show($id)
    {
        $row = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        if (!$row) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Fetched',
            'data'    => $row
        ]);
    }

    public function update(Request $r, $id)
    {
        $exists = DB::table('document_types')->where('id', $id)->exists();
        if (!$exists) {
            // Log not found attempt
            $this->logActivity($r, 'update', 'DocumentTypes', 'Document type not found', 'document_types', (int)$id);
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $data = $r->validate([
            'name'        => ['sometimes','string','max:120', Rule::unique('document_types','name')->ignore($id)],
            'description' => ['sometimes','nullable','string','max:255'],
            'note'        => ['sometimes','nullable','string','max:255'],
            'status'      => ['sometimes', Rule::in(['active','inactive','archived'])],
        ]);

        if (empty($data)) {
            $row = DB::table('document_types')
                ->select('id','name','description','note','status','created_at','updated_at')
                ->where('id',$id)
                ->first();

            $this->logActivity($r, 'update', 'DocumentTypes', 'No changes detected', 'document_types', (int)$id);
            return response()->json([
                'status'  => true,
                'message' => 'No changes',
                'data'    => $row
            ]);
        }

        $before = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        $data['updated_at'] = now();

        DB::table('document_types')->where('id', $id)->update($data);

        $row = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        // ✅ activity log
        $this->logActivity(
            $r,
            'update',
            'DocumentTypes',
            'Document type updated',
            'document_types',
            (int)$id,
            array_keys($data),
            $before ? (array)$before : null,
            $row ? (array)$row : null
        );

        // ✅ notify admins
        $changed = array_keys($data);
        $this->persistNotification([
            'title'     => 'Document type updated',
            'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Document type updated.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'           => 'updated',
                'document_type'    => $row ? (array)$row : ['id'=>$id],
                'document_type_id' => $id,
                'changed'          => $changed,
            ],
            'type'      => 'document_type',
            'link_url'  => rtrim((string)config('app.url'), '/').'/document-types/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document type updated',
            'data'    => $row
        ]);
    }

    public function destroy(Request $r, $id)
    {
        $row = DB::table('document_types')->where('id', $id)->first();

        if (!$row) {
            $this->logActivity($r, 'destroy', 'DocumentTypes', 'Document type not found', 'document_types', (int)$id);
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        DB::table('document_types')->where('id', $id)->delete();

        // ✅ activity log (include snapshot)
        $this->logActivity(
            $r,
            'destroy',
            'DocumentTypes',
            "Document type \"{$row->name}\" deleted",
            'document_types',
            (int)$id,
            ['id','name','description','note','status'],
            (array)$row,
            null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Document type deleted',
            'message'   => "Document type \"{$row->name}\" was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'           => 'deleted',
                'document_type'    => (array)$row,
                'document_type_id' => (int)$id,
            ],
            'type'      => 'document_type',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document type deleted'
        ]);
    }

    public function toggleStatus(Request $r, $id)
    {
        $row = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        if (!$row) {
            $this->logActivity($r, 'update', 'DocumentTypes', 'Document type not found (toggle)', 'document_types', (int)$id);
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $current = $row->status;

        // Handle archived -> active revival
        if ($current === 'archived') {
            if ($r->boolean('revive')) {
                DB::table('document_types')->where('id', $id)->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
                $fresh = DB::table('document_types')
                    ->select('id','name','description','note','status','created_at','updated_at')
                    ->where('id', $id)->first();

                // ✅ activity log
                $this->logActivity(
                    $r,
                    'update',
                    'DocumentTypes',
                    'Status changed to active (revived)',
                    'document_types',
                    (int)$id,
                    ['status'],
                    (array)$row,
                    $fresh ? (array)$fresh : null
                );

                // ✅ notify admins
                $this->persistNotification([
                    'title'     => 'Document type revived',
                    'message'   => "Status set to active for document type \"{$row->name}\".",
                    'receivers' => $this->adminReceivers(),
                    'metadata'  => [
                        'action'           => 'revived',
                        'document_type'    => $fresh ? (array)$fresh : (array)$row,
                        'old_status'       => $current,
                        'new_status'       => 'active',
                    ],
                    'type'      => 'document_type',
                    'link_url'  => rtrim((string)config('app.url'), '/').'/document-types/'.$id,
                    'priority'  => 'normal',
                    'status'    => 'active',
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Status changed to active',
                    'data'    => $fresh
                ]);
            }
            return response()->json([
                'status'  => false,
                'message' => 'Archived items cannot be toggled (pass ?revive=1 to activate)'
            ], 409);
        }

        // Toggle active <-> inactive
        $next = $current === 'active' ? 'inactive' : 'active';

        DB::table('document_types')->where('id', $id)->update([
            'status' => $next,
            'updated_at' => now(),
        ]);

        $fresh = DB::table('document_types')
            ->select('id','name','description','note','status','created_at','updated_at')
            ->where('id', $id)
            ->first();

        // ✅ activity log
        $this->logActivity(
            $r,
            'update',
            'DocumentTypes',
            "Status changed to {$next}",
            'document_types',
            (int)$id,
            ['status'],
            (array)$row,
            $fresh ? (array)$fresh : null
        );

        // ✅ notify admins
        $this->persistNotification([
            'title'     => 'Document type status updated',
            'message'   => "Status changed to {$next} for document type \"{$row->name}\".",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'           => 'status_changed',
                'document_type'    => $fresh ? (array)$fresh : (array)$row,
                'old_status'       => $current,
                'new_status'       => $next,
            ],
            'type'      => 'document_type',
            'link_url'  => rtrim((string)config('app.url'), '/').'/document-types/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => "Status changed to {$next}",
            'data'    => $fresh
        ]);
    }
}