<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ExpenseController extends Controller
{
    public function __construct(private ClientUserScopeService $scopeService)
    {
    }

    // =========================
    // Public endpoints
    // =========================

       /** GET /api/job-details/{job}/expenses?page=&per_page= */
    /** GET /api/job-details/{job}/expenses?page=&per_page= */
/** GET /api/job-details/{job}/expenses?page=&per_page= */
public function listExpenses(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;
    if (($r->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }

    $page = max(1, (int)$r->query('page', 1));
    $per  = min(100, max(1, (int)$r->query('per_page', 20)));

    // actor once
    $actor = $this->actor($r);
    $actorEmail = $this->actorEmail($actor); // may be null

    $qb = DB::table('job_expenses as e')
        ->leftJoin('expense_heads as h','h.id','=','e.expense_head_id')
        ->select('e.*','h.title as expense_head')
        ->where('e.job_id', $jobId)
        ->orderBy('e.expense_date','desc');

    // If the caller is an assignee, only show expenses created by that assignee
    if (isset($actor['role']) && $actor['role'] === 'assignee' && !empty($actor['id'])) {
        // Use created_by id comparison (reliable) rather than email comparison
        $qb->where('e.created_by', (int) $actor['id']);
    }

    // total before pagination
    $total = (clone $qb)->count();

    // compute pagination bounds
    $totalPages = (int)ceil($total / $per);
    $from = ($total === 0) ? null : (($page - 1) * $per) + 1;
    $to   = ($total === 0) ? null : min($total, $page * $per);

    $rows = $qb->skip(($page - 1) * $per)->take($per)->get()->map(function($row) use ($r) {
        // normalize attachments_json into array and re-encode string
        $attachments = [];
        if (!empty($row->attachments_json)) {
            if (is_string($row->attachments_json)) {
                $decoded = json_decode($row->attachments_json, true);
                $attachments = is_array($decoded) ? $decoded : [];
            } elseif (is_array($row->attachments_json)) {
                $attachments = $row->attachments_json;
            }
        }

        // update urls if stored as disk+path
        foreach ($attachments as &$a) {
            if (!empty($a['disk']) && !empty($a['disk_path'])) {
                try {
                    $urlData = $this->generateAttachmentUrls($a['disk_path']);
                    $a['absolute_url'] = $urlData['absolute_url'];
                    $a['relative_url'] = $urlData['relative_url'];
                } catch (\Throwable $e) {
                    // keep existing urls if generation fails
                    $a['absolute_url'] = $a['absolute_url'] ?? null;
                }
            } else {
                if (!empty($a['absolute_url']) && str_contains($a['absolute_url'], '/storage/')) {
                    $a['absolute_url'] = str_replace('/storage/', '/f/', $a['absolute_url']);
                }
                if (!empty($a['relative_url']) && str_starts_with($a['relative_url'], '/storage/')) {
                    $a['relative_url'] = str_replace('/storage/', '/f/', $a['relative_url']);
                }
            }
        }

        // ensure attachments_json is a JSON string or null
        $row->attachments_json = !empty($attachments) ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null;

        // ensure boolean / int fields are set predictably
        $row->attachments_count = (int)($row->attachments_count ?? count($attachments));
        $row->has_attachments   = (bool)($row->has_attachments ?? ($row->attachments_count > 0));

        // who created the expense (best-effort)
        $contact = $this->resolveSenderContact(null, (int)($row->created_by ?? 0));
        $row->creator_name  = $contact['name']  ?? null;
        $row->creator_email = $contact['email'] ?? null;
        $row->creator_phone = $contact['phone'] ?? null;

        return $row;
    });

    // after $rows computed (where $rows is a Collection)
    $personTotals = $this->computePersonTotals($rows);

    // build pagination links (next / prev) using current request URL
    $baseUrl = $r->url(); // path without query
    $query = $r->query();
    // next page url
    $nextPageUrl = null;
    if ($page < $totalPages) {
        $nextQuery = array_merge($query, ['page' => $page + 1, 'per_page' => $per]);
        $nextPageUrl = $baseUrl . '?' . http_build_query($nextQuery);
    }
    // prev page url
    $prevPageUrl = null;
    if ($page > 1 && $totalPages > 0) {
        $prevQuery = array_merge($query, ['page' => max(1, $page - 1), 'per_page' => $per]);
        $prevPageUrl = $baseUrl . '?' . http_build_query($prevQuery);
    }

    return response()->json([
        'status' => 'success',
        'data' => $rows,
        'actor_email' => $actorEmail,
        'totals' => $personTotals,
        'meta' => [
            'page' => $page,
            'per_page' => $per,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'next_page_url' => $nextPageUrl,
            'prev_page_url' => $prevPageUrl,
        ],
        'links' => [
            'next' => $nextPageUrl,
            'prev' => $prevPageUrl,
        ],
    ]);
}


    /**
     * POST /api/job-details/{job}/expenses  (multipart)
     * fields:
     *   expense_head_id (required), expense_date (required), amount (required),
     *   currency (optional), note (optional), attachments[] (0..N)
     */
    public function postExpense(Request $r, int $jobId)
    {
        if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;
        if (!DB::table('job_details')->where('id',$jobId)->exists())
            return response()->json(['status'=>'error','message'=>'Job not found'],404);

        if (($r->attributes->get('auth_role') ?? null) === 'assignee') {
            if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
        }

        $data = $r->validate([
            'expense_head_id' => 'required|integer|exists:expense_heads,id',
            'expense_date'    => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'currency'        => 'sometimes|nullable|string|size:3',
            'note'            => 'sometimes|nullable|string',
            'attachments.*'   => 'sometimes|file|max:102400',
        ]);

        $actor = $this->actor($r);

        // ensure dir
        if (!Storage::disk('public')->exists('jobExpenses')) {
            Storage::disk('public')->makeDirectory('jobExpenses');
        }

        // collect attachments using the helper; it now uses generateAttachmentUrls internally
        $files = $r->file('attachments');
        if ($files instanceof \Illuminate\Http\UploadedFile) $files = [$files];
        if (!is_array($files)) $files = [];

        $att = [];
        foreach ($files as $f) {
            if (!$f instanceof \Illuminate\Http\UploadedFile) continue;
            if ($f->getError() !== UPLOAD_ERR_OK || !$f->isValid()) {
                if (in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                    return response()->json(['status'=>'error','message'=>'Attachment exceeds server upload limit.'],422);
                }
                continue;
            }

            $ext    = strtolower($f->getClientOriginalExtension() ?: 'bin');
            $stored = 'jobexp_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;
            $ok = Storage::disk('public')->putFileAs('jobExpenses', $f, $stored);
            if (!$ok) { Log::warning('jobexpense store failed', ['name'=>$stored]); continue; }

            $publicPath = 'jobExpenses/'.$stored;
            $urlData = $this->generateAttachmentUrls($publicPath);
            $mime = $f->getClientMimeType() ?: $f->getMimeType() ?: 'application/octet-stream';

            $att[] = [
                'kind'          => str_starts_with($mime,'image/') ? 'image' : 'file',
                'original_name' => $f->getClientOriginalName(),
                'stored_name'   => $stored,
                'mime'          => $mime,
                'size'          => (int)($f->getSize() ?? 0),
                'relative_url'  => $urlData['relative_url'],
                'absolute_url'  => $urlData['absolute_url'],
                'disk'          => 'public',
                'disk_path'     => $publicPath,
                'uploaded_at'   => now()->toIso8601String(),
            ];
        }

        $note = $this->sanitizeHtml($data['note'] ?? null);

        $now = now();
        $insert = [
            'job_id'           => $jobId,
            'expense_head_id'  => $data['expense_head_id'],
            'expense_date'     => Carbon::parse($data['expense_date'])->toDateString(),
            'amount'           => (float)$data['amount'],
            'currency'         => strtoupper($data['currency'] ?? 'INR'),
            'note'             => $note,
            'has_attachments'  => count($att) > 0,
            'attachments_count'=> count($att),
            'attachments_json' => $att ? json_encode($att, JSON_UNESCAPED_UNICODE) : null,
            'created_by'       => $actor['id'] ?: null,
            'updated_by'       => $actor['id'] ?: null,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        $id = DB::table('job_expenses')->insertGetId($insert);
        $fresh = DB::table('job_expenses')->where('id',$id)->first();

        // normalize returned fresh record
        if ($fresh) {
            if (!empty($fresh->attachments_json)) {
                $fresh = $this->normalizeMessageAttachmentUrls($fresh);
            }
            // ensure numeric / boolean fields are explicit
            $fresh->attachments_count = (int)($fresh->attachments_count ?? ($att ? count($att) : 0));
            $fresh->has_attachments   = (bool)($fresh->has_attachments ?? ($fresh->attachments_count > 0));
        }

        $contact = $this->resolveSenderContact(null, (int)($fresh->created_by ?? 0));
        $fresh->creator_name  = $contact['name']  ?? null;
        $fresh->creator_email = $contact['email'] ?? null;
        $fresh->creator_phone = $contact['phone'] ?? null;

        $actor = $this->actor($r);
$actorEmail = $this->actorEmail($actor);


        // notify admins + assignees
        $card = $this->jobCard($jobId);
        $link = rtrim((string)config('app.url'), '/').'/jobs/'.$jobId.'#expenses';

        $receivers = $this->mergeReceivers($this->adminReceivers(), $this->assigneeReceivers($jobId));
        $this->persistNotification([
            'title' => 'Expense added',
            'message' => mb_strimwidth(($note ? strip_tags($note) : "Expense of {$insert['amount']} {$insert['currency']} added."), 0, 240, '…'),
            'receivers' => $receivers,
            'metadata' => ['action'=>'expense_created','job'=>$card['job']??null,'client'=>$card['client']??null,'job_id'=>$jobId,'expense_id'=>$id],
            'type'=>'job','link_url'=>$link,'priority'=>'normal','status'=>'active'
        ]);

        $this->logActivity($r, 'store', 'Job expense created', 'job_expenses', $id, array_keys($insert), null, (array)$fresh);

        return response()->json(['status'=>'success','message'=>'Expense created','data'=>$fresh], 201);
    }

    /** PATCH /api/job-details/expenses/{expenseId} */
    public function updateExpense(Request $r, int $expenseId)
    {
        if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;

        $exp = DB::table('job_expenses')->where('id',$expenseId)->first();
        if (!$exp) return response()->json(['status'=>'error','message'=>'Expense not found'],404);

        // only creator can edit
        $actor = $this->actor($r);
        $isCreator = ((int)$exp->created_by === (int)$actor['id']);
        if (!$isCreator) {
            return response()->json(['status'=>'error','message'=>'You can only edit expenses you created'],403);
        }

        // 24-hour edit window
        $created = Carbon::parse($exp->created_at);
        if ($created->diffInHours(now()) > 24) {
            return response()->json(['status'=>'error','message'=>'Expenses can only be edited within 24 hours of creation'],422);
        }

        $data = $r->validate([
            'expense_head_id' => 'sometimes|integer|exists:expense_heads,id',
            'expense_date'    => 'sometimes|date',
            'amount'          => 'sometimes|numeric|min:0',
            'currency'        => 'sometimes|nullable|string|size:3',
            'note'            => 'sometimes|nullable|string',
            'remove_attachments' => 'sometimes|array',
            'remove_attachments.*' => 'integer|min:0',
            'attachments.*'    => 'sometimes|file|max:102400',
        ]);

        // compute old attachments (ensure array)
        $oldAttachments = $exp->attachments_json ? (is_string($exp->attachments_json) ? (json_decode($exp->attachments_json, true) ?: []) : $exp->attachments_json) : [];

        // validate not empty: either note or attachments must remain
        $willHaveNote = true;
        if (array_key_exists('note', $data)) {
            $newHtml = $this->sanitizeHtml($data['note']);
            $willHaveNote = !empty(trim(strip_tags($newHtml ?? '')));
        } else {
            $willHaveNote = !empty(trim(strip_tags($exp->note ?? '')));
        }
        $remainingAttachments = count($oldAttachments);
        if (array_key_exists('remove_attachments', $data) && !empty($data['remove_attachments'])) {
            $remainingAttachments -= count($data['remove_attachments']);
        }
        $willHaveNewAttachments = $r->hasFile('attachments');
        $willHaveAttachments = ($remainingAttachments > 0) || $willHaveNewAttachments;
        if (!$willHaveNote && !$willHaveAttachments) {
            return response()->json(['status'=>'error','message'=>'Expense must have a note or at least one attachment'],422);
        }

        DB::beginTransaction();
        try {
            $oldSnapshot = (array)$exp;
            $update = [];

            if (array_key_exists('expense_head_id',$data)) $update['expense_head_id'] = $data['expense_head_id'];
            if (array_key_exists('expense_date',$data)) $update['expense_date'] = Carbon::parse($data['expense_date'])->toDateString();
            if (array_key_exists('amount',$data)) $update['amount'] = (float)$data['amount'];
            if (array_key_exists('currency',$data)) $update['currency'] = strtoupper($data['currency'] ?? $exp->currency);
            if (array_key_exists('note',$data)) $update['note'] = $this->sanitizeHtml($data['note']);

            // remove attachments (deletes files)
            $finalAttachments = $this->processAttachmentRemovals($exp, $data['remove_attachments'] ?? []);

            // add new attachments (this helper now returns normalized attachments with urls)
            $newAttachments = $this->processNewAttachments($r, $exp->job_id);
            $allAttachments = array_merge($finalAttachments, $newAttachments);

            $update['attachments_json'] = !empty($allAttachments) ? json_encode($allAttachments, JSON_UNESCAPED_UNICODE) : null;
            $update['attachments_count'] = count($allAttachments);
            $update['has_attachments'] = !empty($allAttachments);

            $update['updated_by'] = $actor['id'] ?: null;
            $update['updated_at'] = now();

            DB::table('job_expenses')->where('id',$expenseId)->update($update);
            DB::commit();

            $fresh = DB::table('job_expenses')->where('id',$expenseId)->first();
            if ($fresh) {
                if (!empty($fresh->attachments_json)) $fresh = $this->normalizeMessageAttachmentUrls($fresh);
                $fresh->attachments_count = (int)($fresh->attachments_count ?? count($allAttachments));
                $fresh->has_attachments   = (bool)($fresh->has_attachments ?? ($fresh->attachments_count > 0));
            }

            $contact = $this->resolveSenderContact(null, (int)($fresh->created_by ?? 0));
            $fresh->creator_name  = $contact['name']  ?? null;
            $fresh->creator_email = $contact['email'] ?? null;
            $fresh->creator_phone = $contact['phone'] ?? null;
            $actor = $this->actor($r);
$actorEmail = $this->actorEmail($actor);

            // notify & log
            $this->logActivity($r, 'update', 'Job expense updated', 'job_expenses', $expenseId, array_keys($update), $oldSnapshot, (array)$fresh);

            $card = $this->jobCard($exp->job_id);
            $receivers = $this->mergeReceivers($this->adminReceivers(), $this->assigneeReceivers($exp->job_id));
            $this->persistNotification([
                'title'=>'Expense updated',
                'message'=> mb_strimwidth(strip_tags($update['note'] ?? $fresh->note ?? "Expense updated"),0,240,'…'),
                'receivers'=>$receivers,
                'metadata'=>['action'=>'expense_updated','job'=>$card['job']??null,'job_id'=>$exp->job_id,'expense_id'=>$expenseId],
                'type'=>'job','link_url'=>rtrim((string)config('app.url'),'/').'/jobs/'.$exp->job_id.'#expenses','priority'=>'normal','status'=>'active'
            ]);

            return response()->json(['status'=>'success','message'=>'Expense updated','data'=>$fresh]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Expense update failed', ['expense_id'=>$expenseId,'error'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to update expense'],500);
        }
    }


    /** DELETE /api/job-details/expenses/{expenseId} */
    public function deleteExpense(Request $r, int $expenseId)
    {
        if ($resp = $this->requireRole($r, ['admin'])) return $resp;

        $row = DB::table('job_expenses')->where('id',$expenseId)->first();
        if (!$row) return response()->json(['status'=>'error','message'=>'Not found'],404);

        // delete files
        if (!empty($row->attachments_json)) {
            $files = json_decode($row->attachments_json, true) ?: [];
            foreach ($files as $f) {
                if (!empty($f['disk']) && !empty($f['disk_path'])) {
                    try { Storage::disk($f['disk'])->delete($f['disk_path']); } catch (\Throwable $e) {}
                    continue;
                }
                if (!empty($f['relative_url'])) {
                    $p = public_path(ltrim($f['relative_url'],'/'));
                    if (is_file($p)) @unlink($p);
                }
            }
        }

        DB::table('job_expenses')->where('id',$expenseId)->delete();

        $this->logActivity($r, 'destroy', 'Job expense deleted', 'job_expenses', $expenseId, null, (array)$row, null);

        $this->persistNotification([
            'title'=>'Expense deleted',
            'message'=> "An expense was deleted.",
            'receivers'=>$this->adminReceivers(),
            'metadata'=>['action'=>'expense_deleted','expense_id'=>$expenseId,'job_id'=>$row->job_id ?? null],
            'type'=>'job','link_url'=>null,'priority'=>'normal','status'=>'active'
        ]);

        return response()->json(['status'=>'success','message'=>'Deleted']);
    }


    // =========================
    // Helpers (lightweight copies of JobDetails helpers)
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
        if (($a['role'] ?? null) === 'client_user' && ($a['id'] ?? 0)) {
            return $this->scopeService->userCanSeeJob((int) $a['id'], $jobId);
        }
        return false;
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
                'module'            => 'ExpenseController',
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
            Log::error('user_data_activity_log insert failed', ['error' => $e->getMessage()]);
        }
    }

    private function persistNotification(array $payload): void
    {
        app(\App\Services\NotificationDispatchService::class)->dispatch($payload);
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

    private function assigneeReceivers(int $jobId, array $excludeIds = []): array
    {
        $exclude = array_flip(array_map('intval', $excludeIds));
        $rows = DB::table('job_assignees as ja')
            ->join('assigned_people as ap', 'ap.id','=','ja.assigned_person_id')
            ->where('ja.job_id',$jobId)->where('ja.status','active')
            ->select('ap.id')->get();
        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r->id;
            if (!isset($exclude[$id])) $out[] = ['id'=>$id, 'role'=>'assignee', 'read'=>0];
        }
        return $out;
    }

    private function mergeReceivers(array ...$lists): array
    {
        $seen = [];
        $out = [];
        foreach ($lists as $list) {
            foreach ($list as $rcp) {
                $k = ((int)($rcp['id'] ?? 0)).'|'.(string)($rcp['role'] ?? 'unknown');
                if (!isset($seen[$k])) { $seen[$k] = true; $out[] = $rcp + ['read'=>0]; }
            }
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

    private function actorEmail(array $a): ?string
    {
        if (!($a['id'] ?? 0)) return null;
        return DB::table('admins')->where('id',$a['id'])->value('email')
         ?: DB::table('assigned_people')->where('id',$a['id'])->value('email')
            ?: DB::table('users')->where('id',$a['id'])->value('email');
    }

    private function resolveSenderContact(?string $role, ?int $id): ?array
    {
        $id   = (int)($id ?? 0);
        if ($id <= 0) return null;

        // try admins -> assigned_people -> users
        $row = DB::table('admins')->select('name','email')->where('id',$id)->first()
            ?? DB::table('assigned_people')->select('name','email')->where('id',$id)->first()
            ?? DB::table('users')->select('name','email')->where('id',$id)->first();

        if (!$row) return null;
        return ['name'=>$row->name ?? null, 'email'=>$row->email ?? null, 'phone'=>null];
    }

    /**
     * Generate consistent attachment URLs (public disk)
     */
    private function generateAttachmentUrls(string $publicPath): array
    {
        $absoluteUrl = Storage::disk('public')->url($publicPath);
        $relativeUrl = parse_url($absoluteUrl, PHP_URL_PATH) ?: '/f/'.$publicPath;
        if (!str_starts_with($relativeUrl, '/f/')) {
            $relativeUrl = str_replace('/storage/', '/f/', $relativeUrl);
        }
        $absoluteUrl = rtrim(config('app.url'), '/') . $relativeUrl;
        return [
            'absolute_url' => $absoluteUrl,
            'relative_url' => $relativeUrl,
        ];
    }

    private function normalizeMessageAttachmentUrls($message)
    {
        if (!$message || empty($message->attachments_json)) {
            return $message;
        }

        $attachments = json_decode($message->attachments_json, true) ?: [];

        foreach ($attachments as &$att) {
            if (!empty($att['disk']) && !empty($att['disk_path'])) {
                $urlData = $this->generateAttachmentUrls($att['disk_path']);
                $att['absolute_url'] = $urlData['absolute_url'];
                $att['relative_url'] = $urlData['relative_url'];
            } else {
                if (!empty($att['absolute_url']) && str_contains($att['absolute_url'], '/storage/')) {
                    $att['absolute_url'] = str_replace('/storage/', '/f/', $att['absolute_url']);
                }
                if (!empty($att['relative_url']) && str_starts_with($att['relative_url'], '/storage/')) {
                    $att['relative_url'] = str_replace('/storage/', '/f/', $att['relative_url']);
                }
            }
        }

        $message->attachments_json = json_encode($attachments, JSON_UNESCAPED_UNICODE);
        return $message;
    }

    private function processAttachmentRemovals($message, array $removeIndices): array
    {
        $oldAttachments = is_string($message->attachments_json) ? (json_decode($message->attachments_json, true) ?: []) : ($message->attachments_json ?: []);

        if (empty($removeIndices)) {
            return $oldAttachments;
        }

        $finalAttachments = [];
        foreach ($oldAttachments as $index => $attachment) {
            if (!in_array($index, $removeIndices, true)) {
                $finalAttachments[] = $attachment;
            } else {
                // Delete file from storage
                $this->deleteAttachmentFile($attachment);
            }
        }

        return $finalAttachments;
    }

     private function processNewAttachments(Request $r, int $jobId): array
    {
        $files = $r->file('attachments');
        if (!$files) return [];

        if ($files instanceof \Illuminate\Http\UploadedFile) $files = [$files];
        if (!is_array($files)) return [];

        if (!Storage::disk('public')->exists('jobExpenses')) {
            Storage::disk('public')->makeDirectory('jobExpenses');
        }

        $newAttachments = [];
        foreach ($files as $f) {
            if (!$f instanceof \Illuminate\Http\UploadedFile) continue;
            if ($f->getError() !== UPLOAD_ERR_OK || !$f->isValid()) {
                if (in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                    throw new \Exception('Attachment exceeds server upload limit');
                }
                continue;
            }

            $ext = strtolower($f->getClientOriginalExtension() ?: 'bin');
            $stored = 'jobexp_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;
            $ok = Storage::disk('public')->putFileAs('jobExpenses', $f, $stored);
            if (!$ok) { Log::warning('Failed to store expense attachment', ['name'=>$stored]); continue; }

            $publicPath = 'jobExpenses/'.$stored;
            // use generateAttachmentUrls to keep consistency (absolute + relative)
            $urlData = $this->generateAttachmentUrls($publicPath);
            $mime = $f->getClientMimeType() ?: $f->getMimeType() ?: 'application/octet-stream';

            $newAttachments[] = [
              'kind' => str_starts_with($mime,'image/') ? 'image' : 'file',
              'original_name' => $f->getClientOriginalName(),
              'stored_name' => $stored,
              'mime' => $mime,
              'size' => (int)($f->getSize() ?? 0),
              'relative_url' => $urlData['relative_url'],
              'absolute_url' => $urlData['absolute_url'],
              'disk' => 'public',
              'disk_path' => $publicPath,
              'uploaded_at' => now()->toIso8601String(),
            ];
        }

        return $newAttachments;
    }

    private function deleteAttachmentFile(array $attachment): void
    {
        try {
            if (!empty($attachment['disk']) && !empty($attachment['disk_path'])) {
                Storage::disk($attachment['disk'])->delete($attachment['disk_path']);
                return;
            }
            if (!empty($attachment['relative_url'])) {
                $path = public_path(ltrim($attachment['relative_url'], '/'));
                if (is_file($path)) @unlink($path);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete attachment file', [
                'attachment' => $attachment,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) return null;
        if (class_exists(\Mews\Purifier\Facades\Purifier::class)) {
            return \Mews\Purifier\Facades\Purifier::clean($html, 'default');
        }
        $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><a><h1><h2><h3><h4><blockquote><code><pre><span><div><img>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        $clean = preg_replace_callback('/<img[^>]*>/i', function ($m) {
            $tag = $m[0];
            preg_match('/src\s*=\s*("|\')(.*?)\1/i', $tag, $src);
            preg_match('/alt\s*=\s*("|\')(.*?)\1/i', $tag, $alt);
            preg_match('/width\s*=\s*("|\')(.*?)\1/i', $tag, $w);
            preg_match('/height\s*=\s*("|\')(.*?)\1/i', $tag, $h);
            $attrs = [];
            if (!empty($src[2]))    $attrs[] = 'src="'.htmlspecialchars($src[2], ENT_QUOTES, 'UTF-8').'"';
            if (!empty($alt[2]))    $attrs[] = 'alt="'.htmlspecialchars($alt[2], ENT_QUOTES, 'UTF-8').'"';
            if (!empty($w[2]))      $attrs[] = 'width="'.htmlspecialchars($w[2], ENT_QUOTES, 'UTF-8').'"';
            if (!empty($h[2]))      $attrs[] = 'height="'.htmlspecialchars($h[2], ENT_QUOTES, 'UTF-8').'"';
            return '<img '.implode(' ', $attrs).' />';
        }, $clean);
        return $clean;
    }
        /**
     * GET /api/job-details/{jobId}/expenses/export?format=excel|pdf|word
     * query flags:
     *   include_attachments=1 (default 1)
     *   rolewise=1 (group by creator role; default 0)
     */
    public function exportExpenses(Request $r, int $jobId)
    {
        // role guard
        if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;
        if (($r->attributes->get('auth_role') ?? null) === 'assignee') {
            if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
        }

        $format = strtolower(trim((string)$r->query('format', 'excel')));
        if (!in_array($format, ['excel','pdf','word'], true)) {
            return response()->json(['status'=>'error','message'=>'Invalid format. Use excel, pdf or word'], 422);
        }

        $includeAttachments = (bool) $r->boolean('include_attachments', true);
        $rolewise = (bool) $r->boolean('rolewise', false);

        $job = DB::table('job_details')->where('id', $jobId)->first();
        if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

        // fetch expenses ordered asc for readable export
        $rows = DB::table('job_expenses')
            ->where('job_id', $jobId)
            ->orderBy('expense_date', 'asc')
            ->get()
            ->map(function($e) {
                // normalize attachments into array
                $attachments = [];
                if (!empty($e->attachments_json)) {
                    if (is_string($e->attachments_json)) {
                        $decoded = @json_decode($e->attachments_json, true);
                        $attachments = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($e->attachments_json)) {
                        $attachments = $e->attachments_json;
                    }
                }
                // ensure urls are present
                foreach ($attachments as &$a) {
                    if (!empty($a['disk']) && !empty($a['disk_path'])) {
                        try {
                            $urlData = $this->generateAttachmentUrls($a['disk_path']);
                            $a['absolute_url'] = $urlData['absolute_url'];
                            $a['relative_url'] = $urlData['relative_url'];
                        } catch (\Throwable $ex) { /* continue */ }
                    } else {
                        if (!empty($a['absolute_url']) && str_contains($a['absolute_url'], '/storage/')) {
                            $a['absolute_url'] = str_replace('/storage/', '/f/', $a['absolute_url']);
                        }
                        if (!empty($a['relative_url']) && str_starts_with($a['relative_url'], '/storage/')) {
                            $a['relative_url'] = str_replace('/storage/', '/f/', $a['relative_url']);
                        }
                    }
                }

                // attach creator contact + role
                $creatorId = (int)($e->created_by ?? 0);
                $contact = $this->resolveSenderContact(null, $creatorId) ?? [];
                $role = $this->resolveSenderRole($creatorId);

                return (object)[
                    'id' => $e->id,
                    'job_id' => $e->job_id,
                    'expense_head_id' => $e->expense_head_id,
                    'expense_head' => $e->expense_head ?? null,
                    'expense_date' => $e->expense_date,
                    'amount' => (float) $e->amount,
                    'currency' => $e->currency ?? null,
                    'note' => $e->note,
                    'has_attachments' => (bool) ($e->has_attachments ?? (!empty($attachments))),
                    'attachments' => $attachments,
                    'attachments_count' => (int)($e->attachments_count ?? count($attachments)),
                    'created_by' => $creatorId,
                    'creator_name' => $contact['name'] ?? null,
                    'creator_email' => $contact['email'] ?? null,
                    'creator_phone' => $contact['phone'] ?? null,
                    'creator_role' => $role,
                    'created_at' => $e->created_at,
                    'updated_at' => $e->updated_at,
                ];
            });

        if ($format === 'excel') {
            return $this->exportExpensesExcel($job, $rows, $rolewise, $includeAttachments);
        } elseif ($format === 'word') {
            return $this->exportExpensesWord($job, $rows, $rolewise, $includeAttachments);
        } else {
            return $this->exportExpensesPdf($job, $rows, $rolewise, $includeAttachments);
        }
    }

    /**
     * CSV export (Excel-friendly) — streaming
     */
   /**
 * CSV export (Excel-friendly) — streaming
 */
private function exportExpensesExcel($job, $expenses, bool $rolewise = false, bool $includeAttachments = true)
{
    if ($expenses->isEmpty()) {
        return response()->json(['status' => 'error', 'message' => 'No expenses found for this job.'], 404);
    }

    $groups = $rolewise ? $expenses->groupBy(fn($e) => ($e->creator_role ?: 'unknown')) : collect(['all' => $expenses]);

    // compute person totals from the flat $expenses collection
    $personTotals = $this->computePersonTotals($expenses);

    $fileName = 'job_' . $job->id . '_expenses_' . date('Ymd_His') . '.csv';

    $callback = function() use ($groups, $includeAttachments, $personTotals) {
        $out = fopen('php://output', 'w');

        // Optional BOM for Excel (uncomment if needed)
        // fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($groups as $group => $rows) {
            fputcsv($out, ["Group: {$group}"]);
            $headers = ['ID','Expense Date','Expense Head','Amount','Currency','Note','Creator Role','Creator Name','Creator Email','Has Attachments'];
            if ($includeAttachments) $headers[] = 'Attachments (JSON)';
            fputcsv($out, $headers);

            foreach ($rows as $r) {
                $note = $r->note ?? '';
                // normalize newlines
                $note = preg_replace("/\r\n|\r|\n/", "\n", (string)$note);
                $row = [
                    $r->id,
                    (string) Carbon::parse($r->expense_date)->toDateString(),
                    $r->expense_head,
                    (string) $r->amount,
                    $r->currency,
                    $note,
                    $r->creator_role,
                    $r->creator_name,
                    $r->creator_email,
                    $r->has_attachments ? 'yes' : 'no',
                ];
                if ($includeAttachments) {
                    $row[] = $r->attachments ? json_encode($r->attachments, JSON_UNESCAPED_UNICODE) : '';
                }
                fputcsv($out, $row);
            }

            // blank line between groups
            fputcsv($out, []);
        }

        // Append Grand totals block
        fputcsv($out, []); // spacer
        fputcsv($out, ['Grand totals by person']);
        fputcsv($out, ['Person', 'Currency', 'Amount']);

        foreach ($personTotals as $person => $cmap) {
            foreach ($cmap as $cur => $amt) {
                // write raw numeric (no thousands sep) so Excel can parse it reliably
                fputcsv($out, [$person, $cur, number_format($amt, 2, '.', '')]);
            }
        }

        fclose($out);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}

    /**
     * Word export via HTML (no external lib). Word opens HTML files saved with .doc
     */
   /**
 * Word export via HTML (no external lib). Word opens HTML files saved with .doc
 */
private function exportExpensesWord($job, $expenses, bool $rolewise = false, bool $includeAttachments = true)
{
    if ($expenses->isEmpty()) {
        return response()->json(['status' => 'error', 'message' => 'No expenses found for this job.'], 404);
    }

    // group by role (or single 'all' group)
    $groups = $rolewise ? $expenses->groupBy(fn($e) => ($e->creator_role ?: 'unknown')) : collect(['all' => $expenses]);

    // compute person totals (uses your existing helper)
    $personTotals = $this->computePersonTotals($expenses);

    $data = [
        'job' => $job,
        'groups' => $groups,
        'include_attachments' => $includeAttachments,
        'generated_at' => now()->toDateTimeString(),
        'personTotals' => $personTotals,
    ];

    // Render the blade you created: resources/views/exports/export_html_word_blade.php
    $html = view('exports.export_html_word_blade', $data)->render();

    // Prepend UTF-8 BOM so Word reliably detects encoding (fixes â€” etc)
    $content = "\xEF\xBB\xBF" . $html;

    $fileName = 'job_' . $job->id . '_expenses_' . date('Ymd_His') . '.doc';

    return response($content, 200, [
        'Content-Type' => 'application/msword; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
    ]);
}

   /**
 * PDF export using Browser Print (HTML with print styles)
 * (If you prefer server-side PDF, replace this with dompdf/snappy flow.)
 */
private function exportExpensesPdf($job, $expenses, bool $rolewise = false, bool $includeAttachments = true)
{
    if ($expenses->isEmpty()) {
        return response()->json(['status' => 'error', 'message' => 'No expenses found for this job.'], 404);
    }

    $groups = $rolewise ? $expenses->groupBy(fn($e) => ($e->creator_role ?: 'unknown')) : collect(['all' => $expenses]);

    // compute person totals and pass to view
    $personTotals = $this->computePersonTotals($expenses);

    $data = [
        'job' => $job,
        'groups' => $groups,
        'include_attachments' => $includeAttachments,
        'generated_at' => now()->toDateTimeString(),
        'personTotals' => $personTotals,
    ];

    // Make sure this blade exists: resources/views/exports/expenses_pdf.blade.php
    $html = view('exports.expenses_pdf', $data)->render();

    $fileName = 'job_' . $job->id . '_expenses_' . date('Ymd_His') . '.html';

    return response($html, 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
        'Content-Disposition' => "inline; filename=\"{$fileName}\"",
    ]);
}

    /**
     * Best-effort resolver that returns a role string for a given id.
     * (admin | assignee | user | unknown)
     */
    private function resolveSenderRole(?int $id): string
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) return 'unknown';

        if (DB::table('admins')->where('id', $id)->exists()) return 'admin';
        if (DB::table('assigned_people')->where('id', $id)->exists()) return 'assignee';
        if (DB::table('users')->where('id', $id)->exists()) return 'user';
        return 'unknown';
    }
    /**
 * Return grand totals grouped by person name -> currency -> amount
 *
 * Input: Collection/array of expense objects with ->creator_name, ->created_by, ->currency, ->amount
 * Output: [
 *   "Sampriti" => ["INR" => 1234.50, "USD" => 12.34],
 *   "admin"    => ["INR" => 999.00],
 *   ...
 * ]
 */
private function computePersonTotals($expenses): array
{
    $totals = [];

    // Accept both array and Laravel Collection
    foreach ($expenses as $e) {
        // prefer resolved creator_name, fallback to id placeholder
        $name = isset($e->creator_name) && $e->creator_name
                ? (string)$e->creator_name
                : ('#'.((int)($e->created_by ?? 0)));

        $currency = !empty($e->currency) ? strtoupper((string)$e->currency) : 'INR';
        $amount = (float)($e->amount ?? 0);

        if (!isset($totals[$name])) $totals[$name] = [];
        if (!isset($totals[$name][$currency])) $totals[$name][$currency] = 0.0;

        $totals[$name][$currency] += $amount;
    }

    // Optional: sort totals by descending grand-sum
    uasort($totals, function($a, $b) {
        return (array_sum($b) <=> array_sum($a));
    });

    return $totals;
}

}
