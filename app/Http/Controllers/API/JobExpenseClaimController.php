<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;


class JobExpenseClaimController extends Controller
{
  /**
     * GET /api/job-expense-claims/my
     * GET /api/job-expense-claims/admin
     * Unified handler - scope determined by route
     */
    public function index(Request $r)
    {
        if ($resp = $this->requireRole($r, ['admin', 'assignee'])) return $resp;

        // ✅ Get scope from route parameter (set by route defaults)
        $scope = $r->route()->parameter('scope');
        
        // ✅ Validate scope
        if (!in_array($scope, ['my', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid scope.',
            ], 400);
        }

        $actor = $this->actor($r);
        // ✅ Admin-only scope check
        if ($scope === 'admin' && !$this->hasRole($actor, 'admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin role required.',
            ], 403);
        }

        // ✅ Route to appropriate handler based on scope
        if ($scope === 'my') {
            return $this->handleMyClaims($r, $actor);
        } else {
            return $this->handleAdminList($r);
        }
    }

    /**
     * Handle "my claims" logic - EXACT same response as before
     */
    private function handleMyClaims(Request $r, $actor)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(1, (int)$r->query('per_page', 20)));

        $createdByAssigneeId = $this->resolveCreatedByAssigneeId($actor);
        if (!$createdByAssigneeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not mapped to assigned_people table (created_by FK).',
            ], 422);
        }

        $qb = DB::table('job_expense_claim_requests')
            ->where('created_by', (int)$createdByAssigneeId)
            ->orderBy('id', 'desc');

        $total = (clone $qb)->count();
        $totalPages = (int)ceil($total / $per);

        $rows = $qb->skip(($page - 1) * $per)->take($per)->get();

        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $per,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    /**
     * Handle "admin list" logic - EXACT same response as before
     */
    private function handleAdminList(Request $r)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(200, max(1, (int)$r->query('per_page', 30)));

        $qb = DB::table('job_expense_claim_requests')->orderBy('id','desc');

        if ($r->filled('status')) $qb->where('status', (string)$r->query('status'));
        if ($r->filled('job_id')) $qb->where('job_id', (int)$r->query('job_id'));
        if ($r->filled('expense_id')) $qb->where('expense_id', (int)$r->query('expense_id'));
        if ($r->filled('created_by')) $qb->where('created_by', (int)$r->query('created_by'));

        $total = (clone $qb)->count();
        $totalPages = (int)ceil($total / $per);

        $rows = $qb->skip(($page - 1) * $per)->take($per)->get();

        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $per,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    /**
     * ✅ FIXED: Helper to check if actor has a specific role
     * $actor is an ARRAY, not an object
     */
    private function hasRole(array $actor, string $role): bool
    {
        // $actor = ['role' => 'admin', 'type' => '...', 'id' => 123]
        return isset($actor['role']) && $actor['role'] === $role;
    }
    /**
     * POST /api/job-expense-claims/claim
     * payload: { expense_id, message?, job_id? }  (job_id optional; only used to cross-check)
     *
     * Assignee creates claim request to admin.
     */
    public function claim(Request $r)
    {
        // Only assignee should request claims (admin can be allowed if you want, but typically not needed)
        // if ($resp = $this->requireRole($r, ['assignee'])) return $resp;

        $data = $r->validate([
            'expense_id' => 'required|integer|exists:job_expenses,id',
            'job_id'     => 'sometimes|nullable|integer', // optional cross-check
            'message'    => 'sometimes|nullable|string|max:2000',
        ]);

        $actor = $this->actor($r);

        // Fetch expense + ensure not deleted (if soft delete exists)
        $expQ = DB::table('job_expenses as e')
            ->leftJoin('expense_heads as h', 'h.id', '=', 'e.expense_head_id')
            ->select(
                'e.id','e.job_id','e.created_by','e.amount','e.currency','e.expense_date',
                'h.title as expense_head'
            )
            ->where('e.id', (int)$data['expense_id']);

        if (Schema::hasColumn('job_expenses', 'deleted_at')) {
            $expQ->whereNull('e.deleted_at');
        }

        $expense = $expQ->first();
        if (!$expense) {
            return response()->json(['status'=>'error','message'=>'Expense not found'], 404);
        }

        $jobId = (int)($expense->job_id ?? 0);
        if ($jobId <= 0) {
            return response()->json(['status'=>'error','message'=>'Expense has no job_id'], 422);
        }

        // Optional: cross-check job_id if frontend sends it
        if (!empty($data['job_id']) && (int)$data['job_id'] !== $jobId) {
            return response()->json([
                'status'=>'error',
                'message'=>'Job mismatch: expense does not belong to selected job'
            ], 422);
        }

        // Assignee must be assigned to this job
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;

        // Assignee can claim ONLY their own expenses
        if ((int)($expense->created_by ?? 0) !== (int)($actor['id'] ?? 0)) {
            return response()->json([
                'status'=>'error',
                'message'=>'You can only claim expenses created by you.'
            ], 403);
        }

        // Validate job exists (and not deleted if soft delete exists)
        $jobQ = DB::table('job_details')->where('id', $jobId);
        if (Schema::hasColumn('job_details', 'deleted_at')) {
            $jobQ->whereNull('deleted_at');
        }
        $job = $jobQ->first();
        if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

        // Prevent duplicate active claim on same expense
        $already = DB::table('job_expense_claim_requests')
            ->where('expense_id', (int)$expense->id)
            ->whereIn('status', ['pending','partially paid','paid'])  // block any active/paid claim
            ->exists();

        if ($already) {
            return response()->json([
                'status'=>'error',
                'message'=>'A claim for this expense already exists.'
            ], 409);
        }

        // created_by must be assigned_people.id (FK in migration)
        $createdByAssigneeId = $this->resolveCreatedByAssigneeId($actor);
        if (!$createdByAssigneeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is not mapped to assigned_people table (created_by FK -> assigned_people.id). Please create/link an assigned_people record for this account email OR change the FK.'
            ], 422);
        }

        $jobTitle   = $job->title ?? ('Job #'.$jobId);
        $headTitle  = $expense->expense_head ?? 'Expense';
        $amount     = (float)($expense->amount ?? 0);
        $currency   = strtoupper((string)($expense->currency ?? 'INR'));

        $title = "Expense Claim - {$jobTitle} - {$headTitle} ({$currency} ".number_format($amount,2,'.','').")";

        // payment_breakdown format as you asked
        $breakdown = [
            'amount'      => $amount,
            'paid_by'     => null,
            'paid_at'     => null,
            'attachments' => [],
            'remaining'   => $amount,
        ];

        $now = now();

        $insert = [
            'title'             => $title,
            'created_by'        => (int)$createdByAssigneeId,
            'job_id'            => $jobId,
            'expense_id'        => (int)$expense->id,
            'message'           => $this->sanitizeHtml($data['message'] ?? null),
            'requested_at'      => $now,
            'status'            => 'pending',
            'payment_breakdown' => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        $id = DB::table('job_expense_claim_requests')->insertGetId($insert);
        $fresh = DB::table('job_expense_claim_requests')->where('id', $id)->first();

        // Notify admins
        $card = $this->jobCard($jobId);
        $this->persistNotification([
            'title' => 'Expense claim requested',
            'message' => mb_strimwidth(strip_tags($insert['message'] ?? 'Expense claim requested.'), 0, 240, '…'),
            'receivers' => $this->adminReceivers(),
            'metadata' => [
                'action' => 'expense_claim_requested',
                'job' => $card['job'] ?? null,
                'client' => $card['client'] ?? null,
                'job_id' => $jobId,
                'expense_id' => (int)$expense->id,
                'claim_request_id' => (int)$id,
            ],
            'type' => 'job',
            'link_url' => rtrim((string)config('app.url'), '/').'/jobs/'.$jobId.'#expenses',
            'priority' => 'normal',
            'status' => 'active',
        ]);

        $this->logActivity($r, 'store', 'Expense claim request created', 'job_expense_claim_requests', (int)$id, array_keys($insert), null, (array)$fresh);

        return response()->json([
            'status'  => 'success',
            'message' => 'Claim request created',
            'data'    => $fresh,
        ], 201);
    }

    /**
     * PATCH /api/job-expense-claims/admin/{id}
     * payload:
     *  - status: pending|paid|failed|partially paid
     *  - payment_breakdown: { amount, paid_by, paid_at, attachments, remaining }
     */
   
public function adminUpdate(Request $r, int $id)
{
    if ($resp = $this->requireRole($r, ['admin'])) return $resp;

    // ✅ Accept both JSON + multipart (for file uploads)
    $data = $r->validate([
        'status'            => ['required','string','max:20', Rule::in(['pending','paid','failed','partially paid'])],
        'payment_breakdown' => 'nullable', // keep raw (array or json string)
        'attachments_files' => 'sometimes',
        'attachments_files.*' => 'file|max:10240', // 10MB each
        // optionally restrict types:
        // 'attachments_files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
    ]);

    $status = strtolower(trim($data['status']));

    $row = DB::table('job_expense_claim_requests')->where('id', $id)->first();
    if (!$row) return response()->json(['status'=>'error','message'=>'Request not found'], 404);

    // =========================
    // Existing payment_breakdown (array)
    // =========================
    $existing = [];
    if (!empty($row->payment_breakdown)) {
        $decoded = json_decode($row->payment_breakdown, true);
        $existing = is_array($decoded) ? $decoded : [];
    }

    // =========================
    // Incoming payment_breakdown (array) — from JSON or multipart
    // IMPORTANT: do not default to [] (avoid turning NULL into "[]")
    // =========================
    $incomingRaw = $r->input('payment_breakdown', null);
    $incoming = [];

    if (is_string($incomingRaw)) {
        $tmp = json_decode($incomingRaw, true);
        $incoming = is_array($tmp) ? $tmp : [];
    } elseif (is_array($incomingRaw)) {
        $incoming = $incomingRaw;
    } else {
        $incoming = [];
    }

    // =========================
    // Merge strategy:
    // - if incoming is empty => keep existing as-is
    // - else incoming overrides existing
    // - replace history entirely if incoming has history (avoid index-merge corruption)
    // =========================
    $merged = $existing;

    if (!empty($incoming)) {
        foreach ($incoming as $k => $v) {
            if ($k === 'history' && is_array($v)) {
                $merged['history'] = $v; // replace whole history
            } else {
                $merged[$k] = $v;
            }
        }
    }

    // =========================
    // Save uploaded files into public/ (NO storage:link)
    // =========================
    $uploadedAttachments = [];
    if ($r->hasFile('attachments_files')) {
        $files = $r->file('attachments_files'); // array of UploadedFile
        $uploadedAttachments = $this->storeClaimAttachments($files, $id);
    }

    // Attach uploads to last history row when possible
    if (!empty($uploadedAttachments)) {
        if (!empty($merged['history']) && is_array($merged['history'])) {
            $lastIndex = count($merged['history']) - 1;
            if ($lastIndex >= 0 && is_array($merged['history'][$lastIndex])) {
                if (empty($merged['history'][$lastIndex]['attachments']) || !is_array($merged['history'][$lastIndex]['attachments'])) {
                    $merged['history'][$lastIndex]['attachments'] = [];
                }
                $merged['history'][$lastIndex]['attachments'] = array_values(array_merge(
                    $merged['history'][$lastIndex]['attachments'],
                    $uploadedAttachments
                ));
            } else {
                // history exists but last row not usable -> put top-level
                if (empty($merged['attachments']) || !is_array($merged['attachments'])) $merged['attachments'] = [];
                $merged['attachments'] = array_values(array_merge($merged['attachments'], $uploadedAttachments));
            }
        } else {
            // no history -> store top-level
            if (empty($merged['attachments']) || !is_array($merged['attachments'])) $merged['attachments'] = [];
            $merged['attachments'] = array_values(array_merge($merged['attachments'], $uploadedAttachments));
        }
    }

    // =========================
    // Auto fill paid_by / paid_by_name / paid_at when paid/partial
    // =========================
    $actor = $this->actor($r);
    $actorId = (int)($actor['id'] ?? 0);
    $actorName = $this->resolvePersonName($actorId);

    if (in_array($status, ['paid','partially paid'], true)) {
        // top-level
        if (empty($merged['paid_by'])) $merged['paid_by'] = $actorId;
        if (empty($merged['paid_at'])) $merged['paid_at'] = now()->toDateTimeString();
        if (empty($merged['paid_by_name']) && $actorName) $merged['paid_by_name'] = $actorName;

        // last history row
        if (!empty($merged['history']) && is_array($merged['history'])) {
            $lastIndex = count($merged['history']) - 1;
            if ($lastIndex >= 0 && is_array($merged['history'][$lastIndex])) {
                if (empty($merged['history'][$lastIndex]['paid_by'])) $merged['history'][$lastIndex]['paid_by'] = $actorId;
                if (empty($merged['history'][$lastIndex]['paid_at'])) $merged['history'][$lastIndex]['paid_at'] = now()->toDateTimeString();
                if (empty($merged['history'][$lastIndex]['paid_by_name']) && $actorName) {
                    $merged['history'][$lastIndex]['paid_by_name'] = $actorName;
                }
            }
        }
    }

    // Normalize attachments arrays
    if (isset($merged['attachments']) && !is_array($merged['attachments'])) $merged['attachments'] = [$merged['attachments']];

    // ✅ Keep NULL if nothing meaningful exists (don’t convert NULL => "[]")
    $hasMeaningfulPB = !empty($merged) && (count(array_filter(array_keys($merged), fn($k) => $merged[$k] !== null && $merged[$k] !== '' && $merged[$k] !== [])) > 0);

    $pbToSave = $hasMeaningfulPB ? json_encode($merged, JSON_UNESCAPED_UNICODE) : null;

    $update = [
        'status'            => $status,
        'payment_breakdown' => $pbToSave,
        'updated_at'        => now(),
    ];

    DB::table('job_expense_claim_requests')->where('id', $id)->update($update);

    $fresh = DB::table('job_expense_claim_requests')->where('id', $id)->first();
    $fresh = $this->decoratePaidByName($fresh);

    $this->logActivity($r, 'update', 'Expense claim request updated', 'job_expense_claim_requests', $id, array_keys($update), (array)$row, (array)$fresh);

    return response()->json([
        'status'  => 'success',
        'message' => 'Updated',
        'data'    => $fresh,
    ]);
}

/**
 * Save claim attachments directly in public/ (NO storage:link)
 * Returns array of attachment objects with absolute_url.
 */
private function storeClaimAttachments($files, int $claimId): array
{
    $files = is_array($files) ? $files : [$files];
    $out = [];

    $dir = 'job_claim_payments/claim_' . $claimId;
    $absDir = public_path($dir);

    if (!is_dir($absDir)) {
        @mkdir($absDir, 0755, true);
    }

    foreach ($files as $file) {
        if (!$file || !$file->isValid()) continue;

        $original = $file->getClientOriginalName() ?: 'attachment';
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');

        // safe base
        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $base);
        $base = trim($base, '._');
        if ($base === '') $base = 'attachment';

        $name = $base . '_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $ext;

        // move into public/
        $file->move($absDir, $name);

        $relative = '/' . trim($dir, '/') . '/' . $name;
        $absolute = rtrim((string)config('app.url'), '/') . $relative;

        $out[] = [
            'kind'          => 'file',
            'original_name' => $original,
            'stored_name'   => $name,
            'relative_url'  => $relative,
            'absolute_url'  => $absolute,
        ];
    }

    return $out;
}

private function resolvePersonName(int $id): ?string
{
    if ($id <= 0) return null;

    if (Schema::hasTable('admins')) {
        $name = DB::table('admins')->where('id', $id)->value('name');
        if ($name) return $name;
        $email = DB::table('admins')->where('id', $id)->value('email');
        if ($email) return $email;
    }

    if (Schema::hasTable('assigned_people')) {
        $name = DB::table('assigned_people')->where('id', $id)->value('name');
        if ($name) return $name;
        $email = DB::table('assigned_people')->where('id', $id)->value('email');
        if ($email) return $email;
    }

    // Optional: if you have users table
    if (Schema::hasTable('users')) {
        $name = DB::table('users')->where('id', $id)->value('name');
        if ($name) return $name;
        $email = DB::table('users')->where('id', $id)->value('email');
        if ($email) return $email;
    }

    return null;
}
private function decoratePaidByName($row)
{
    if (!$row) return $row;

    $pb = json_decode($row->payment_breakdown ?? '', true);
    if (!is_array($pb)) return $row;

    // top-level
    if (!empty($pb['paid_by']) && empty($pb['paid_by_name'])) {
        $pb['paid_by_name'] = $this->resolvePersonName((int)$pb['paid_by']);
    }

    // history rows
    if (!empty($pb['history']) && is_array($pb['history'])) {
        foreach ($pb['history'] as $i => $h) {
            if (!is_array($h)) continue;
            if (!empty($h['paid_by']) && empty($h['paid_by_name'])) {
                $pb['history'][$i]['paid_by_name'] = $this->resolvePersonName((int)$h['paid_by']);
            }
        }
    }

    $row->payment_breakdown = json_encode($pb, JSON_UNESCAPED_UNICODE);
    return $row;
}

    // =========================
    // Helpers (same style as your ExpenseController)
    // =========================

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function forbidIfNoAccess(Request $r, int $jobId)
    {
        if (!$this->userCanSeeJob($r, $jobId)) {
            return response()->json(['status'=>'error','message'=>'Forbidden'], 403);
        }
        return null;
    }

    private function userCanSeeJob(Request $r, int $jobId): bool
    {
        $a = $this->actor($r);
        if (($a['role'] ?? null) === 'admin') return true;

        if (($a['role'] ?? null) === 'assignee' && ($a['id'] ?? 0)) {
            return DB::table('job_assignees')
                ->where('job_id', $jobId)
                ->where('assigned_person_id', (int)$a['id'])
                ->where('status', 'active')
                ->exists();
        }
        return false;
    }

    private function resolveCreatedByAssigneeId(array $actor): ?int
    {
        // Migration: created_by FK -> assigned_people.id
        // So we must resolve a real assigned_people.id.

        $actorId = (int)($actor['id'] ?? 0);
        if ($actorId <= 0) return null;

        // If your tokenable_id is already assigned_people.id (best case)
        if (DB::table('assigned_people')->where('id', $actorId)->exists()) {
            return $actorId;
        }

        // Else resolve by email (admin/assignee -> assigned_people)
        $email =
            DB::table('admins')->where('id', $actorId)->value('email')
            ?: DB::table('assigned_people')->where('id', $actorId)->value('email');

        if (!$email) return null;

        $pid = DB::table('assigned_people')->where('email', $email)->value('id');
        return $pid ? (int)$pid : null;
    }

    private function logActivity(
        Request $request,
        string $activity,
        string $note,
        string $tableName,
        ?int $recordId = null,
        ?array $changed = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $a = $this->actor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $a['id'] ?: 0,
                'performed_by_role' => $a['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'JobExpenseClaimController',
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changed ? json_encode($changed, JSON_UNESCAPED_UNICODE) : null,
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

        DB::table('notifications')->insert([
            'title'      => $title,
            'message'    => $message,
            'receivers'  => json_encode($receivers, JSON_UNESCAPED_UNICODE),
            'metadata'   => !empty($payload['metadata']) ? json_encode($payload['metadata'], JSON_UNESCAPED_UNICODE) : null,
            'type'       => (string)($payload['type'] ?? 'general'),
            'link_url'   => $payload['link_url'] ?? null,
            'priority'   => in_array(($payload['priority'] ?? 'normal'), ['low','normal','high','urgent'], true) ? $payload['priority'] : 'normal',
            'status'     => in_array(($payload['status'] ?? 'active'), ['active','archived','deleted'], true) ? $payload['status'] : 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

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

    private function jobCard(int $jobId): ?array
    {
        $row = DB::table('job_details as j')
            ->leftJoin('clients as c','c.id','=','j.client_id')
            ->select('j.id','j.title','j.status','j.client_id','c.name as client_name')
            ->where('j.id',$jobId)->first();
        if (!$row) return null;
        return [
            'job'    => ['id'=>$row->id, 'title'=>$row->title, 'status'=>$row->status],
            'client' => ['id'=>$row->client_id, 'name'=>$row->client_name],
        ];
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) return null;
        if (class_exists(\Mews\Purifier\Facades\Purifier::class)) {
            return \Mews\Purifier\Facades\Purifier::clean($html, 'default');
        }
        $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><a><blockquote><code><pre><span><div>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        return $clean;
    }
    
}
