<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    private array $sortable = [
        'created_at','updated_at','issue_date','expiry_date','doc_name','status'
    ];

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

    /** Optional: enrich Laravel log with actor context */
    private function logWithActor(string $msg, Request $request, array $extra = []): void
    {
        $a = $this->actor($request);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_type' => $a['type'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    /**
     * Insert row into user_data_activity_log using DB facade.
     */
    private function logActivity(
        Request $request,
        string $activity,                 // e.g., 'store'|'update'|'destroy'|'upload'
        string $module,                   // e.g., 'Documents'
        string $note,                     // human-readable note
        string $tableName,                // e.g., 'documents' or 'uploads'
        ?int $recordId = null,
        ?array $changed = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->actor($request);

        $changedFields = null;
        if (is_array($changed)) {
            $changedFields = array_values(array_unique(array_map(
                'strval',
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
            Log::error('user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    /* =========================
     * Notification helpers (DB-only)
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

    public function toggleStatus(Request $r, $id)
    {
        $row = DB::table('documents')
            ->select('id','doc_name','status','slug','client_id','document_type_id','created_at','updated_at')
            ->where('id', $id)
            ->first();

        if (!$row) {
            $this->logActivity($r, 'update', 'Documents', 'Document not found (toggle)', 'documents', (int)$id);
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $current = $row->status;

        // Handle archived -> active revival
        if ($current === 'archived') {
            if ($r->boolean('revive')) {
                DB::table('documents')->where('id', $id)->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
                $fresh = DB::table('documents')
                    ->select('id', 'doc_name', 'status','slug','client_id','document_type_id','created_at', 'updated_at')
                    ->where('id', $id)->first();

                // activity log
                $this->logActivity(
                    $r,
                    'update',
                    'Documents',
                    'Status changed to active (revived)',
                    'documents',
                    (int)$id,
                    ['status'],
                    (array)$row,
                    $fresh ? (array)$fresh : null
                );

                // notify admins
                $this->persistNotification([
                    'title'     => 'Document revived',
                    'message'   => "Status set to active for “{$row->doc_name}”.",
                    'receivers' => $this->adminReceivers(),
                    'metadata'  => [
                        'action'   => 'revived',
                        'document' => $fresh ? (array)$fresh : (array)$row,
                        'old_status' => $current,
                        'new_status' => 'active',
                    ],
                    'type'      => 'document',
                    'link_url'  => rtrim((string)config('app.url'), '/').'/documents/'.$id,
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

        DB::table('documents')->where('id', $id)->update([
            'status' => $next,
            'updated_at' => now(),
        ]);

        $fresh = DB::table('documents')
            ->select('id', 'doc_name', 'status','slug','client_id','document_type_id','created_at', 'updated_at')
            ->where('id', $id)
            ->first();

        // activity log
        $this->logActivity(
            $r,
            'update',
            'Documents',
            "Status changed to {$next}",
            'documents',
            (int)$id,
            ['status'],
            (array)$row,
            $fresh ? (array)$fresh : null
        );

        // notify admins
        $this->persistNotification([
            'title'     => 'Document status updated',
            'message'   => "Status changed to {$next} for “{$row->doc_name}”.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'status_changed',
                'document'   => $fresh ? (array)$fresh : (array)$row,
                'old_status' => $current,
                'new_status' => $next,
            ],
            'type'      => 'document',
            'link_url'  => rtrim((string)config('app.url'), '/').'/documents/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => "Status changed to {$next}",
            'data'    => $fresh
        ]);
    }

    /**
     * Generate a unique, lowercase slug for documents.
     */
    private function generateUniqueSlug(int $length = 10): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;

        static $existing = null;
        if ($existing === null) {
            $existing = DB::table('documents')->pluck('slug')->toArray();
        }

        do {
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= $alphabet[random_int(0, $max)];
            }
            $slug = 'doc-' . $random;

            $exists = in_array($slug, $existing, true)
                || DB::table('documents')->where('slug', $slug)->exists();
        } while ($exists);

        $existing[] = $slug;
        return $slug;
    }

    /* ========= Index: list + filter + sort + search (GET, no activity log) ========== */
    public function index(Request $r)
    {
        $perPage = min(max((int)$r->get('per_page', 20), 1), 100);

        $q = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select(
                'd.id','d.client_id','d.document_type_id','d.doc_name',
                'd.issue_date','d.expiry_date','d.issuing_authority',
                'd.file_url','d.stored_name','d.created_by_id','d.created_by_role',
                'd.slug','d.status','d.created_at','d.updated_at',
                DB::raw('c.name as client_name'),
                DB::raw('t.name as type_name')
            );

        // Search
        if ($r->filled('q')) {
            $needle = mb_strtolower(trim((string)$r->q));
            $q->where(function($qq) use ($needle) {
                $qq->whereRaw('LOWER(d.doc_name) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(COALESCE(d.issuing_authority, \'\')) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(COALESCE(d.file_url, \'\')) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(COALESCE(c.name, \'\')) LIKE ?', ["%{$needle}%"])
                   ->orWhereRaw('LOWER(COALESCE(t.name, \'\')) LIKE ?', ["%{$needle}%"]);
            });
        }

        // Filters
        if ($r->filled('client_id'))         $q->where('d.client_id',        $r->client_id);
        if ($r->filled('document_type_id'))  $q->where('d.document_type_id', $r->document_type_id);

        if ($r->filled('status')) {
            $statuses = collect(explode(',', (string)$r->status))
                ->map(fn($s)=>trim($s))->filter()->values()->all();
            if ($statuses) $q->whereIn('d.status', $statuses);
        }
        if ($r->filled('issue_from'))  $q->whereDate('d.issue_date',  '>=', $r->issue_from);
        if ($r->filled('issue_to'))    $q->whereDate('d.issue_date',  '<=', $r->issue_to);
        if ($r->filled('expiry_from')) $q->whereDate('d.expiry_date', '>=', $r->expiry_from);
        if ($r->filled('expiry_to'))   $q->whereDate('d.expiry_date', '<=', $r->expiry_to);

        // Sorting
        $by  = in_array($r->get('sort_by'), $this->sortable, true) ? $r->get('sort_by') : 'created_at';
        $dir = strtolower($r->get('sort_dir','desc')) === 'asc' ? 'asc' : 'desc';
        $q->orderBy("d.$by", $dir);

        $page = $q->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Fetched documents',
            'data'    => $page->items(),
            'meta'    => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ],
        ]);
    }

    /* ========================= Create (POST) ========================= */
    public function store(Request $r)
    {
        $data = $r->validate([
            'client_id'        => ['required','exists:clients,id'],
            'document_type_id' => ['required','exists:document_types,id'],
            'doc_name'         => ['required','string','max:160'],
            'issue_date'       => ['required','date'],
            'expiry_date'      => ['required','date','after_or_equal:issue_date'],
            'issuing_authority'=> ['required','string','max:160'],
            'file_url'         => ['required','string','max:255'],
            'slug'             => ['sometimes','string','max:140', 'unique:documents,slug'],
            'status'           => ['nullable', Rule::in(['active','inactive','archived'])],
        ]);

        $a = $this->actor($r);

        $slug = array_key_exists('slug', $data) && strlen((string)$data['slug'])
            ? $data['slug']
            : $this->generateUniqueSlug();

        $now = now();

        $id = DB::table('documents')->insertGetId([
            'client_id'        => $data['client_id'],
            'document_type_id' => $data['document_type_id'],
            'doc_name'         => $data['doc_name'],
            'issue_date'       => $data['issue_date'] ,
            'expiry_date'      => $data['expiry_date'] ,
            'issuing_authority'=> $data['issuing_authority'],
            'file_url'         => $data['file_url'],
            'created_by_id'    => $a['id'] ?: null,
            'created_by_role'  => $a['role'],
            'slug'             => $slug,
            'status'           => $data['status'] ?? 'active',
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        $row = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
            ->where('d.id', $id)
            ->first();

        // activity log
        $this->logActivity(
            $r,
            'store',
            'Documents',
            "Created document \"{$data['doc_name']}\"",
            'documents',
            (int) $id,
            array_keys($data),
            null,
            $row ? (array)$row : null
        );

        // notify admins
        $this->persistNotification([
            'title'     => 'Document created',
            'message'   => "“{$data['doc_name']}” was created.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'   => 'created',
                'document' => $row ? (array)$row : ['id'=>$id,'doc_name'=>$data['doc_name'],'slug'=>$slug],
                'actor'    => $a,
                'document_id' => $id,
            ],
            'type'      => 'document',
            'link_url'  => rtrim((string)config('app.url'), '/').'/documents/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document created',
            'data'    => $row
        ], 201);
    }

    /* ===================== Read (by ID, GET) ======================= */
    public function show($id)
    {
        $row = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
            ->where('d.id', $id)->first();

        if (!$row) return response()->json(['status'=>false,'message'=>'Not found'], 404);

        return response()->json(['status'=>true,'message'=>'Fetched','data'=>$row]);
    }

    /* ==================== Read (by slug, GET) ====================== */
    public function showBySlug($slug)
    {
        $row = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
            ->where('d.slug', $slug)->first();

        if (!$row) return response()->json(['status'=>false,'message'=>'Not found'], 404);

        return response()->json(['status'=>true,'message'=>'Fetched','data'=>$row]);
    }

    /* ================== Update (by ID, PUT/PATCH) ======================== */
    public function update(Request $r, $id)
    {
        $rowOld = DB::table('documents')->where('id', $id)->first();
        if (!$rowOld) {
            $this->logActivity($r, 'update', 'Documents', 'Document not found', 'documents', (int)$id);
            return response()->json(['status'=>false,'message'=>'Not found'], 404);
        }

        $data = $r->validate([
            'client_id'        => ['sometimes','required','exists:clients,id'],
            'document_type_id' => ['sometimes','required','exists:document_types,id'],
            'doc_name'         => ['sometimes','required','string','max:160'],
            'issue_date'       => ['sometimes','required','date'],
            'expiry_date'      => ['sometimes','required','date','after_or_equal:issue_date'],
            'issuing_authority'=> ['sometimes','required','string','max:160'],
            'file_url'         => ['sometimes','required','string','max:255'],
            'slug'             => ['sometimes','required','string','max:140', Rule::unique('documents','slug')->ignore($id)],
            'status'           => ['sometimes', Rule::in(['active','inactive','archived'])],
        ]);

        if (empty($data)) {
            $row = DB::table('documents')->where('id',$id)->first();
            $this->logActivity($r, 'update', 'Documents', 'No changes detected', 'documents', (int)$id);
            return response()->json(['status'=>true,'message'=>'No changes','data'=>$row]);
        }

        $data['updated_at'] = now();
        DB::table('documents')->where('id', $id)->update($data);

        $row = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
            ->where('d.id', $id)->first();

        // activity log
        $this->logActivity(
            $r,
            'update',
            'Documents',
            'Document updated',
            'documents',
            (int)$id,
            array_keys($data),
            (array)$rowOld,
            $row ? (array)$row : null
        );

        // notify admins
        $changed = array_keys($data);
        $this->persistNotification([
            'title'     => 'Document updated',
            'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Document updated.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'updated',
                'document'   => $row ? (array)$row : ['id'=>$id],
                'document_id'=> $id,
                'changed'    => $changed,
            ],
            'type'      => 'document',
            'link_url'  => rtrim((string)config('app.url'), '/').'/documents/'.$id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>true,'message'=>'Document updated','data'=>$row]);
    }

    /* ================== Update (by slug, PUT/PATCH) ====================== */
    public function updateBySlug(Request $r, $slug)
    {
        $row = DB::table('documents')->where('slug',$slug)->first();
        if (!$row) {
            $this->logActivity($r, 'update', 'Documents', 'Document not found', 'documents', null);
            return response()->json(['status'=>false,'message'=>'Not found'], 404);
        }
        $oldSnapshot = (array)$row;

        $data = $r->validate([
            'client_id'        => ['sometimes','required','exists:clients,id'],
            'document_type_id' => ['sometimes','required','exists:document_types,id'],
            'doc_name'         => ['sometimes','required','string','max:160'],
            'issue_date'       => ['sometimes','required','date'],
            'expiry_date'      => ['sometimes','required','date','after_or_equal:issue_date'],
            'issuing_authority'=> ['sometimes','required','string','max:160'],
            'file_url'         => ['sometimes','required','string','max:255'],
            'slug'             => ['sometimes','required','string','max:140', Rule::unique('documents','slug')->ignore($row->id)],
            'status'           => ['sometimes', Rule::in(['active','inactive','archived'])],
        ]);

        if (empty($data)) {
            $joined = DB::table('documents as d')
                ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
                ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
                ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
                ->where('d.id', $row->id)->first();

            $this->logActivity($r, 'update', 'Documents', 'No changes detected', 'documents', (int)$row->id);
            return response()->json(['status'=>true,'message'=>'No changes','data'=>$joined]);
        }

        $data['updated_at'] = now();
        DB::table('documents')->where('id', $row->id)->update($data);

        $fresh = DB::table('documents as d')
            ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
            ->leftJoin('document_types as t', 't.id', '=', 'd.document_type_id')
            ->select('d.*', DB::raw('c.name as client_name'), DB::raw('t.name as type_name'))
            ->where('d.id', $row->id)->first();

        // activity log
        $this->logActivity(
            $r,
            'update',
            'Documents',
            'Document updated',
            'documents',
            (int)$row->id,
            array_keys($data),
            $oldSnapshot,
            $fresh ? (array)$fresh : null
        );

        // notify admins
        $changed = array_keys($data);
        $this->persistNotification([
            'title'     => 'Document updated',
            'message'   => $changed ? ('Updated fields: '.implode(', ', $changed)) : 'Document updated.',
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'     => 'updated',
                'document'   => $fresh ? (array)$fresh : ['id'=>$row->id],
                'document_id'=> $row->id,
                'changed'    => $changed,
            ],
            'type'      => 'document',
            'link_url'  => rtrim((string)config('app.url'), '/').'/documents/'.$row->id,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>true,'message'=>'Document updated','data'=>$fresh]);
    }

    /* ================= Delete (by ID, DELETE) ========================= */
    public function destroy(Request $r, $id)
    {
        $row = DB::table('documents')->where('id', $id)->first();
        if (!$row) {
            $this->logActivity($r, 'destroy', 'Documents', 'Document not found', 'documents', (int)$id);
            return response()->json(['status'=>false,'message'=>'Not found'], 404);
        }

        DB::table('documents')->where('id',$id)->delete();

        // activity log
        $this->logActivity($r, 'destroy', 'Documents', "Deleted \"{$row->doc_name}\"", 'documents', (int)$id, null, (array)$row, null);

        // notify admins
        $this->persistNotification([
            'title'     => 'Document deleted',
            'message'   => "“{$row->doc_name}” was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'      => 'deleted',
                'document'    => (array)$row,
                'document_id' => (int)$id,
            ],
            'type'      => 'document',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>true,'message'=>'Document deleted']);
    }

    /* ================= Delete (by slug, DELETE) ======================= */
    public function destroyBySlug(Request $r, $slug)
    {
        $row = DB::table('documents')->where('slug', $slug)->first();
        if (!$row) {
            $this->logActivity($r, 'destroy', 'Documents', 'Document not found', 'documents', null);
            return response()->json(['status'=>false,'message'=>'Not found'], 404);
        }

        DB::table('documents')->where('id',$row->id)->delete();

        // activity log
        $this->logActivity($r, 'destroy', 'Documents', "Deleted \"{$row->doc_name}\"", 'documents', (int)$row->id, null, (array)$row, null);

        // notify admins
        $this->persistNotification([
            'title'     => 'Document deleted',
            'message'   => "“{$row->doc_name}” was deleted.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => [
                'action'      => 'deleted',
                'document'    => (array)$row,
                'document_id' => (int)$row->id,
            ],
            'type'      => 'document',
            'link_url'  => null,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json(['status'=>true,'message'=>'Document deleted']);
    }

    /** ================= File Upload (POST) ================== */
/** ================= File Upload (POST) ================== */
public function upload(Request $r)
{
    $folder = trim((string) $r->get('folder', 'documents'), "/ \t\n\r\0\x0B");
    if ($folder === '') $folder = 'documents';

    $r->validate([
        'file' => ['required','file','max:2000'], // 2MB
    ]);

    $file = $r->file('file');

    $dest = public_path('uploads/'.$folder);
    if (!is_dir($dest)) @mkdir($dest, 0775, true);
    if (!is_writable($dest)) {
        $this->logActivity($r, 'upload', 'Documents', 'Upload directory not writable', 'uploads', null, ['file']);
        return response()->json([
            'status'  => false,
            'message' => 'Upload directory is not writable: '.$dest,
        ], 500);
    }

    $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');
    try { $rand = bin2hex(random_bytes(6)); } catch (\Throwable $e) { $rand = Str::random(12); }
    $name = date('YmdHis').'-'.$rand.'.'.$ext;

    try {
        $file->move($dest, $name);
    } catch (\Throwable $e) {
        $this->logActivity($r, 'upload', 'Documents', 'Move failed: '.$e->getMessage(), 'uploads', null, ['file']);
        return response()->json([
            'status'  => false,
            'message' => 'Move failed: '.$e->getMessage(),
        ], 500);
    }

    $relPath = 'uploads/'.$folder.'/'.$name;

    // ✅ Only activity log (no notification)
    $this->logActivity(
        $r,
        'upload',
        'Documents',
        'File uploaded',
        'uploads',
        null,
        ['file'],
        null,
        ['path' => $relPath, 'name' => $name, 'ext' => $ext]
    );

    return response()->json([
        'status'  => true,
        'message' => 'Uploaded',
        'path'    => $relPath,
        'url'     => '/'.$relPath,
        'name'    => $name,
    ], 201);
}
}
