<?php
 
namespace App\Http\Controllers\API;
 
use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
// use Barryvdh\DomPDF\Facade\Pdf;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Services\JobNotifier; 
use App\Services\DynamicMail;  
use Illuminate\Support\Facades\Schema;
use App\Services\FCMService;

 
class JobDetailsController extends Controller
{
    public function __construct(private ClientUserScopeService $scopeService)
    {
    }

    /** =========================
     *   Enumerations
     * ========================= */
    private array $TYPES     = ['task','milestone','bug','feature','epic','other'];
    private array $PRIORITY  = ['lowest','low','normal','high','urgent'];
    private array $STATUS    = ['draft','planned','in_progress','on_hold','blocked','completed','cancelled','pending'];
 
    /** =========================
     *   Auth/Role Helpers
     * ========================= */
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
 
    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'], 'actor_type' => $a['type'], 'actor_id' => $a['id'],
        ], $extra));
    }
 
    /** =========================
     *   Activity Log (DB) Helper
     * ========================= */
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
                'activity'          => $activity,               // store | update | destroy | upload | reorder | suggest
                'module'            => 'JobDetails',
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
  
    /** =========================
     *   Helpers
     * ========================= */
    private function parseParentId($val): ?int
    {
        if ($val === null) return null;
        if (is_string($val)) {
            $s = strtolower(trim($val));
            if ($s === '' || $s === 'self' || $s === 'root' || $s === 'null') return null;
            if (ctype_digit($s)) return (int) $s ?: null;
        }
        if (is_numeric($val)) {
            $v = (int) $val;
            return $v > 0 ? $v : null;
        }
        return null;
    }
 
    private function nextOrdering(?int $parentId): int
    {
        $q = DB::table('job_details');
        $parentId ? $q->where('parent_id', $parentId) : $q->whereNull('parent_id');
        $max = $q->max('ordering');
        return is_null($max) ? 1 : ((int)$max + 1);
    }

    private function collectClientSubtreeIds(int $rootId): array
    {
        if ($rootId <= 0) {
            return [];
        }

        $seen = [];
        $queue = [$rootId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if ($current <= 0 || isset($seen[$current])) {
                continue;
            }

            $seen[$current] = true;

            $children = DB::table('clients')
                ->where('parent_id', $current)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($children as $childId) {
                if (!isset($seen[$childId])) {
                    $queue[] = $childId;
                }
            }
        }

        return array_map('intval', array_keys($seen));
    }
 
    private function findJob(int $id): ?object
    {
        return DB::table('job_details')->where('id', $id)->first();
    }
 
    private function computeEndFromDuration(?string $startAt, ?int $durationDays): ?string
    {
        if (!$startAt || !$durationDays) return null;
        return Carbon::parse($startAt)->addDays($durationDays)->toDateTimeString();
    }
 
    private function computeDurationFromRange(?string $startAt, ?string $endAt): ?int
    {
        if (!$startAt || !$endAt) return null;
        $s = Carbon::parse($startAt);
        $e = Carbon::parse($endAt);
        return max(0, $s->diffInDays($e));
    }
 
    /** ===== Timezone + Date Rules ===== */
    private function localTz(): string
    {
        return 'Asia/Kolkata'; // UI works in IST
    }
 
    private function parseLocal(?string $val): ?Carbon
    {
        if (!$val) return null;
        $dt = Carbon::parse($val, $this->localTz());                    // interpret as IST
        return $dt->clone()->timezone(config('app.timezone', 'UTC'));   // store in app TZ
    }
 
    private function isWeekend(Carbon $dt): bool
    {
        return in_array($dt->dayOfWeekIso, [6, 7], true); // Sat=6, Sun=7
    }
 
    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null) return null;
 
        // Prefer mews/purifier if available
        if (class_exists(\Mews\Purifier\Facades\Purifier::class)) {
            return \Mews\Purifier\Facades\Purifier::clean($html, 'default');
        }
 
        // Fallback: allow-list tags; strip event handlers + javascript:
        $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><a><h1><h2><h3><h4><blockquote><code><pre><span><div><img>';
        $clean = strip_tags($html, $allowed);
 
        // remove on* attributes and javascript: URLs
        $clean = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
 
        // allow only safe img attributes roughly (src, alt, width, height)
        $clean = preg_replace_callback('/<img[^>]*>/i', function ($m) {
            $tag = $m[0];
            // keep src|alt|width|height only
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
     * Validate + normalize planned_* fields:
     * - no past dates
     * - no weekends by default (unless allow_weekend=true)
     * - end >= start, deadline >= start
     * - derive end from duration or duration from range
     * Mutates $payload with normalized MySQL strings.
     */
  private function validatePlannedDates(array &$payload, Request $request): void
{
    $allowWeekend = (bool) $request->boolean('allow_weekend', false);

    $start    = $this->parseLocal($payload['planned_start_at']    ?? null);
    $end      = $this->parseLocal($payload['planned_end_at']      ?? null);
    $deadline = $this->parseLocal($payload['planned_deadline_at'] ?? null);
    $days     = $payload['planned_duration_days'] ?? null;

    if ($start && $days !== null && $end === null) {
        $end = $start->clone()->addDays((int)$days);
    } elseif ($start && $end && $days === null) {
        $days = max(0, $start->diffInDays($end));
    }

    $now = now();
    // compare past-ness against the start of today so "today" is acceptable
    $todayStart = $now->copy()->startOfDay();

    $errors = [];

    // Allow past start date when updating (PUT/PATCH request)
    $allowPastStartForUpdate = in_array(strtoupper($request->method()), ['PUT', 'PATCH'], true);

    $check = function (?Carbon $dt, string $field) use ($allowWeekend, $todayStart, &$errors, $allowPastStartForUpdate) {
        if (!$dt) return;

        // Treat as "past" only if strictly before the start of today.
        // This allows any date that is equal to today's date.
        if ($dt->lt($todayStart)) {
            if (!($field === 'planned_start_at' && $allowPastStartForUpdate)) {
                $errors[$field] = 'Past date/time is not allowed.';
            }
        }

        // Prevent weekends unless explicitly allowed
        if (!$allowWeekend && $this->isWeekend($dt)) {
            $errors[$field] = 'Weekends are disabled by default. Set "allow_weekend" to true to allow.';
        }
    };

    // Run checks
    $check($start, 'planned_start_at');
    $check($end, 'planned_end_at');
    $check($deadline, 'planned_deadline_at');

    // Logical order validation
    if ($start && $end && $end->lt($start)) {
        $errors['planned_end_at'] = 'Planned end must be after or equal to planned start.';
    }
    if ($start && $deadline && $deadline->lt($start)) {
        $errors['planned_deadline_at'] = 'Deadline must be after or equal to planned start.';
    }

    if ($errors) {
        throw ValidationException::withMessages($errors);
    }

    // Write back normalized values (preserve time if parseLocal created a DateTime)
    $payload['planned_start_at']      = $start ? $start->toDateTimeString() : null;
    $payload['planned_end_at']        = $end ? $end->toDateTimeString() : null;
    $payload['planned_deadline_at']   = $deadline ? $deadline->toDateTimeString() : null;
    $payload['planned_duration_days'] = $days !== null ? (int)$days : null;
}

    /** If role is 'user', restrict to jobs where this person is assigned (active). */
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
private function forbidIfNoAccess(Request $r, int $jobId)
{
    if (!$this->userCanSeeJob($r, $jobId)) {
        return response()->json(['status'=>'error','message'=>'Forbidden'], 403);
    }
    return null;
}


/** Try to resolve the actor's email to avoid emailing the sender. */
private function actorEmail(array $a): ?string
{
    if (!($a['id'] ?? 0)) return null;
    return DB::table('admins')->where('id',$a['id'])->value('email')
     ?: DB::table('assigned_people')->where('id',$a['id'])->value('email')
     ?: DB::table('client_users')->where('id',$a['id'])->value('email')
        ?: DB::table('users')->where('id',$a['id'])->value('email');
}

/** Fetch compact job + client info for emails. */
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
 /** =========================
     *   NOTIFICATION HELPERS (DB only)
     * ========================= */

   private function persistNotification(array $payload): void
{
    Log::info('[Notification] Starting persistNotification', [
        'payload_type' => $payload['type'] ?? 'unknown',
        'action' => $payload['metadata']['action'] ?? 'unknown'
    ]);

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

    // 1) DB store
    try {
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

        Log::info('[Notification] Saved to database', [
            'title' => $title,
            'receivers_count' => count($receivers)
        ]);
    } catch (\Throwable $e) {
        Log::error('[Notification] Failed to save to database', [
            'error' => $e->getMessage()
        ]);
    }

    // 2) FCM push
    try {
        Log::debug('[Notification] Getting FCM tokens');
        $tokens = $this->resolveFcmTokensForReceivers($receivers);

        Log::info('[Notification] FCM tokens found', [
            'tokens_count' => count($tokens)
        ]);

        if (!empty($tokens)) {
            /** @var \App\Services\FCMService $fcm */
            $fcm = app(FCMService::class);

            // Prepare FCM data with individual fields for easy access
            $data = [
                'type'      => (string)$type,
                'priority'  => (string)$priority,
                'link_url'  => $linkUrl ? (string)$linkUrl : '',
                'action'    => (string)($metadata['action'] ?? 'notification'),
                'job_id'    => (string)($metadata['job_id'] ?? ''),
            ];

            // Add job title if available (check multiple possible locations)
            if (isset($metadata['job_title']) && $metadata['job_title']) {
                $data['job_title'] = (string)$metadata['job_title'];
            } elseif (isset($metadata['job']['title']) && $metadata['job']['title']) {
                $data['job_title'] = (string)$metadata['job']['title'];
            } elseif (isset($metadata['job']) && is_string($metadata['job'])) {
                $data['job_title'] = (string)$metadata['job'];
            } else {
                $data['job_title'] = '';
            }

            // Add client name if available
            if (isset($metadata['client_name']) && $metadata['client_name']) {
                $data['client_name'] = (string)$metadata['client_name'];
            } elseif (isset($metadata['client']['name']) && $metadata['client']['name']) {
                $data['client_name'] = (string)$metadata['client']['name'];
            } else {
                $data['client_name'] = '';
            }

            // Add sender name for chat messages
            if (isset($metadata['sender_name']) && $metadata['sender_name']) {
                $data['sender_name'] = (string)$metadata['sender_name'];
            } elseif (isset($metadata['actor']['name']) && $metadata['actor']['name']) {
                $data['sender_name'] = (string)$metadata['actor']['name'];
            } elseif (isset($metadata['actor']['role']) && $metadata['actor']['role']) {
                $data['sender_name'] = ucfirst((string)$metadata['actor']['role']);
            } else {
                $data['sender_name'] = '';
            }

            // Add message ID for chat messages
            if (isset($metadata['message_id']) && $metadata['message_id']) {
                $data['message_id'] = (string)$metadata['message_id'];
            }

            // Add status if available
            if (isset($metadata['job']['status']) && $metadata['job']['status']) {
                $data['job_status'] = (string)$metadata['job']['status'];
            } elseif (isset($metadata['new_status']) && $metadata['new_status']) {
                $data['job_status'] = (string)$metadata['new_status'];
            } elseif (isset($metadata['old_status']) && $metadata['old_status']) {
                $data['job_status'] = (string)$metadata['old_status'];
            }

            // Add actor ID and role
            if (isset($metadata['actor']['id']) && $metadata['actor']['id']) {
                $data['actor_id'] = (string)$metadata['actor']['id'];
            }
            if (isset($metadata['actor']['role']) && $metadata['actor']['role']) {
                $data['actor_role'] = (string)$metadata['actor']['role'];
            }

            // Also include the full metadata as JSON for reference
            $data['metadata'] = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : '';

            Log::info('[Notification] Calling FCM service', [
                'tokens_count' => count($tokens),
                'title' => $title,
                'data_fields' => array_keys($data)
            ]);

            Log::debug('[Notification] FCM data details', [
                'job_title' => $data['job_title'] ?? 'not_set',
                'job_id' => $data['job_id'] ?? 'not_set',
                'action' => $data['action'] ?? 'not_set'
            ]);

            $fcm->sendToTokens($tokens, $title, $message, $data);
        } else {
            Log::info('[Notification] No FCM tokens found, skipping push');
        }
    } catch (\Throwable $e) {
        Log::error('[Notification] FCM push failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
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

    /** Active assignees for a job (role=assignee). */
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

    /** Dedupe receivers by (id,role). */
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
    /** =========================
     *   LIST (GET /job-details)
     * ========================= */
public function index(Request $request)
{
    if ($resp = $this->requireRole($request, ['admin','assignee','client_user'])) return $resp;

    $page     = max(1, (int) $request->query('page', 1));
    $perPage  = min(100, max(1, (int) $request->query('per_page', 10)));
    $q        = trim((string) $request->query('q', ''));
    $clientId = (int) $request->query('client_id', 0);
    $parentId = $this->parseParentId($request->query('parent_id', null));
    $type     = trim((string) $request->query('type', ''));
    $priority = trim((string) $request->query('priority', ''));
    $status   = trim((string) $request->query('status', ''));
    $sort     = strtolower(trim((string) $request->query('sort', 'desc')));
    $actor    = $this->actor($request);

    // base query with assignees_count + document details
    $qBuilder = DB::table('job_details as j')
        ->leftJoin('clients as c', 'c.id', '=', 'j.client_id')
        ->leftJoin('documents as d', 'd.id', '=', 'j.document_id')
        ->leftJoin(
            DB::raw("(SELECT job_id, COUNT(*) AS assignees_count
                      FROM job_assignees
                      WHERE status = 'active'
                      GROUP BY job_id) jacc"),
            'jacc.job_id', '=', 'j.id'
        )
        ->select(
            'j.*',
            'c.name as client_name',

            // keep if you want quick access; otherwise you can remove these two
            'd.doc_name as document_name',

            DB::raw('COALESCE(jacc.assignees_count, 0) as assignees_count'),

            // ✅ All document fields (aliased to avoid clashes)
            'd.id as document__id',
            'd.client_id as document__client_id',
            'd.document_type_id as document__document_type_id',
            'd.doc_name as document__doc_name',
            'd.issue_date as document__issue_date',
            'd.expiry_date as document__expiry_date',
            'd.issuing_authority as document__issuing_authority',
            'd.file_url as document__file_url',
            'd.stored_name as document__stored_name',
            'd.created_by_id as document__created_by_id',
            'd.created_by_role as document__created_by_role',
            'd.slug as document__slug',
            'd.status as document__status',
            'd.created_at as document__created_at',
            'd.updated_at as document__updated_at'
        );

    // 🔒 If role=assignee, show only jobs actively assigned to this person (no duplicates)
    if (($actor['role'] ?? null) === 'assignee' && ($actor['id'] ?? 0)) {
        $aid = (int) $actor['id'];
        $qBuilder->whereExists(function($q) use ($aid) {
            $q->from('job_assignees as me')
              ->whereColumn('me.job_id', 'j.id')
              ->where('me.status', 'active')
              ->where('me.assigned_person_id', $aid);
        });
    }
    if (($actor['role'] ?? null) === 'client_user' && ($actor['id'] ?? 0)) {
        $visibleClientIds = $this->scopeService->visibleClientIdsForUser((int) $actor['id']);
        if (empty($visibleClientIds)) {
            $qBuilder->whereRaw('1 = 0');
        } else {
            $qBuilder->whereIn('j.client_id', $visibleClientIds);
        }
    }

    if ($q !== '') {
        $like = "%{$q}%";
        $qBuilder->where(function ($w) use ($like) {
            $w->where('j.title', 'LIKE', $like)
              ->orWhere('j.description', 'LIKE', $like);
        });
    }
    if ($clientId > 0) {
        $clientIds = $this->collectClientSubtreeIds($clientId);
        $qBuilder->whereIn('j.client_id', !empty($clientIds) ? $clientIds : [$clientId]);
    }
    if (!is_null($parentId)) $qBuilder->where('j.parent_id', $parentId);
    if ($type !== '' && in_array($type, $this->TYPES, true)) $qBuilder->where('j.type', $type);
    if ($priority !== '' && in_array($priority, $this->PRIORITY, true)) $qBuilder->where('j.priority', $priority);
    if ($status !== '' && in_array($status, $this->STATUS, true)) $qBuilder->where('j.status', $status);

    // distinct to avoid any chance of double-counting
    $total = (clone $qBuilder)->distinct()->count('j.id');

    $items = $qBuilder
        ->orderBy('j.created_at', $sort === 'asc' ? 'asc' : 'desc')
        ->orderBy('j.id',         $sort === 'asc' ? 'asc' : 'desc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    // ✅ Build "document" key with full doc details (so it can be viewed)
    $items = $items->map(function ($row) {
        $docId = $row->document__id ?? null;

        $row->document = $docId ? [
            'id'               => $row->document__id,
            'client_id'        => $row->document__client_id,
            'document_type_id' => $row->document__document_type_id,
            'doc_name'         => $row->document__doc_name,
            'issue_date'       => $row->document__issue_date,
            'expiry_date'      => $row->document__expiry_date,
            'issuing_authority'=> $row->document__issuing_authority,
            'file_url'         => $row->document__file_url,
            'stored_name'      => $row->document__stored_name,
            'created_by_id'    => $row->document__created_by_id,
            'created_by_role'  => $row->document__created_by_role,
            'slug'             => $row->document__slug,
            'status'           => $row->document__status,
            'created_at'       => $row->document__created_at,
            'updated_at'       => $row->document__updated_at,
        ] : null;

        // remove alias fields from root payload
        unset(
            $row->document__id,
            $row->document__client_id,
            $row->document__document_type_id,
            $row->document__doc_name,
            $row->document__issue_date,
            $row->document__expiry_date,
            $row->document__issuing_authority,
            $row->document__file_url,
            $row->document__stored_name,
            $row->document__created_by_id,
            $row->document__created_by_role,
            $row->document__slug,
            $row->document__status,
            $row->document__created_at,
            $row->document__updated_at
        );

        return $row;
    });

    return response()->json([
        'status'  => 'success',
        'message' => 'Jobs fetched',
        'data'    => $items,
        'meta'    => [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ],
    ]);
}
/** =========================
 *   SHOW (GET /job-details/{id})
 * ========================= */
public function show(Request $request, int $id)
{
    if ($resp = $this->requireRole($request, ['admin','assignee','client_user'])) return $resp;
    if (($request->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($request, $id)) return $resp;
    }


    $job = DB::table('job_details as j')
        ->leftJoin('clients as c', 'c.id', '=', 'j.client_id')
        ->leftJoin('documents as d', 'd.id', '=', 'j.document_id')
        ->select('j.*', 'c.name as client_name', 'd.doc_name as document_name')
        ->where('j.id', $id)
        ->first();

    if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

    $media = DB::table('job_description_media')
        ->where('job_id', $id)
        ->orderBy('id', 'desc')
        ->get();

    // include current assignees of this job
    $assignees = DB::table('job_assignees as ja')
        ->join('assigned_people as ap', 'ap.id', '=', 'ja.assigned_person_id')
        ->select(
            'ap.id',
            'ap.name',
            'ap.email',
            'ap.status',
            'ja.status as map_status',
            'ja.assigned_at',
            'ja.unassigned_at',
            'ja.note'
        )
        ->where('ja.job_id', $id)
        ->orderBy('ap.name')
        ->get();

    return response()->json([
        'status'    => 'success',
        'message'   => 'Job fetched',
        'data'      => $job,
        'media'     => $media,
        'assignees' => $assignees,
    ]);
}

 
    /** =========================
     *   ENUMS (GET /job-details/enums)
     * ========================= */
    public function enums(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','assignee','client_user'])) return $resp;
 
        return response()->json([
            'status' => 'success',
            'data'   => [
                'types'    => $this->TYPES,
                'priority' => $this->PRIORITY,
                'status'   => $this->STATUS,
            ],
        ]);
    }
 
    /** =========================
     *   CREATE (POST /job-details)
     * ========================= */
    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;
        $this->logWithActor('[Job Store] start', $request);
 
        // Pre-normalize parent_id
        $parentId = $this->parseParentId($request->input('parent_id'));
 
        // Basic validate (parent_id checked manually for exists)
        $data = $request->validate([
            'title'                 => 'required|string|max:200',
            'description'           => 'sometimes|nullable|string',
            'type'                  => ['sometimes','nullable', Rule::in($this->TYPES)],
            'priority'              => ['sometimes','nullable', Rule::in($this->PRIORITY)],
            'status'                => ['sometimes','nullable', Rule::in($this->STATUS)],
            'client_id'             => ['sometimes','nullable','integer','exists:clients,id'],
            'document_id'           => ['sometimes','nullable','integer','exists:documents,id'],
            'planned_duration_days' => 'sometimes|nullable|integer|min:0',
            'planned_start_at'      => 'sometimes|nullable|date',
            'planned_end_at'        => 'sometimes|nullable|date',
            'planned_deadline_at'   => 'sometimes|nullable|date',
            'ordering'              => 'sometimes|nullable|integer|min:0',
            'metadata'              => 'sometimes|nullable|array',
            'allow_weekend'         => 'sometimes|boolean',
        ]);
  
        if (!is_null($parentId)) {
            $exists = DB::table('job_details')->where('id', $parentId)->exists();
            if (!$exists) {
                return response()->json(['status'=>'error','message'=>'Parent job not found'], 422);
            }
        }
 
        // Sanitize description
        if (array_key_exists('description', $data) && $data['description'] !== null) {
            $data['description'] = $this->sanitizeHtml($data['description']);
        }
 
        // Normalize + validate planned dates (IST → app TZ, weekend/past rules)
        $payload = [
            'planned_start_at'      => $data['planned_start_at']      ?? null,
            'planned_end_at'        => $data['planned_end_at']        ?? null,
            'planned_deadline_at'   => $data['planned_deadline_at']   ?? null,
            'planned_duration_days' => $data['planned_duration_days'] ?? null,
        ];
        $this->validatePlannedDates($payload, $request);
 
        // Ordering
        $ordering = array_key_exists('ordering', $data) && $data['ordering'] !== null
            ? (int)$data['ordering']
            : $this->nextOrdering($parentId);
 
        $now = now();
        $insert = [
            'client_id'             => $data['client_id']      ?? null,
            'document_id'           => $data['document_id']    ?? null,
            'parent_id'             => $parentId,
            'title'                 => $data['title'],
            'description'           => $data['description']    ?? null,
            'type'                  => $data['type']           ?? 'task',
            'priority'              => $data['priority']       ?? 'normal',
            'status'                => $data['status']         ?? 'planned',
            'planned_duration_days' => $payload['planned_duration_days'],
            'planned_start_at'      => $payload['planned_start_at'],
            'planned_end_at'        => $payload['planned_end_at'],
            'planned_deadline_at'   => $payload['planned_deadline_at'],
            'ordering'              => $ordering,
            'metadata'              => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null,
            'created_at'            => $now,
            'updated_at'            => $now,
        ];
 
        $id = DB::table('job_details')->insertGetId($insert);
        $fresh = $this->findJob($id);
 
        $this->logActivity($request, 'store', "Created job \"{$insert['title']}\"", 'job_details', $id, array_keys($insert), null, $fresh ? (array)$fresh : null);
        
                // NOTIFY: Job created -> admins only
        $card = $this->jobCard($id);
        $link = rtrim((string)config('app.url'), '/').'/jobs/'.$id;
        $this->persistNotification([
            'title'     => 'Job created',
            'message'   => "“{$insert['title']}” was created.",
            'receivers' => $this->adminReceivers(),
            'metadata'  => ['action'=>'created','job'=>$card['job']??null,'client'=>$card['client']??null,'job_id'=>$id],
            'type'      => 'job',
            'link_url'  => $link,
            'priority'  => 'normal',
            'status'    => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Job created successfully',
            'data'    => $fresh,
        ], 201);
    }
    /** =========================
*   STATUS CHANGE (PATCH /job-details/{id}/status)
*   payload: { status: "...", note?: "..." }
* ========================= */
public function changeStatus(Request $r, int $id)
{
    // Only admins (keeps it consistent with store/reorder/destroy)
    if ($resp = $this->requireRole($r, ['admin', 'assignee'])) return $resp;
 
    // validate input (note is optional!)
    $data = $r->validate([
        'status' => ['required', 'string', Rule::in($this->STATUS)],
        'note'   => ['sometimes','nullable','string','max:500'],
    ]);
 
    // job
    $job = $this->findJob($id);
    if (!$job) {
        return response()->json(['status'=>'error','message'=>'Job not found'], 404);
    }
 
    $oldStatus = (string)$job->status;
    $newStatus = (string)$data['status'];
    $note      = $data['note'] ?? null;   // ✅ safe; no "Undefined array key"
 
    // no-op
    if ($oldStatus === $newStatus) {
        return response()->json([
            'status'  => 'success',
            'message' => 'No changes detected',
            'data'    => $job,
        ]);
    }
 
    // persist
    $update = [
        'status'     => $newStatus,
        'updated_at' => now(),
    ];
    DB::table('job_details')->where('id', $id)->update($update);
 
    $fresh = $this->findJob($id);
 
    // activity log (same style as others)
    $this->logActivity(
        $r,
        'update',
        "Status {$oldStatus} → {$newStatus}".($note ? " | {$note}" : ''),
        'job_details',
        $id,
        ['status'],
        ['status' => $oldStatus],
        ['status' => $newStatus]
    );
 
    // simple notification (same primitives you already use)
    $card = $this->jobCard($id);
    $link = rtrim((string)config('app.url'), '/').'/jobs/'.$id;
 
    $receivers = $this->mergeReceivers(
        $this->adminReceivers(),
        $this->assigneeReceivers($id)
    );
 
    $this->persistNotification([
        'title'     => 'Job status updated',
        'message'   => "Status changed: {$oldStatus} → {$newStatus}".($note ? " | {$note}" : ''),
        'receivers' => $receivers,
        'metadata'  => [
            'action'     => 'status_changed',
            'job'        => $card['job'] ?? null,
            'client'     => $card['client'] ?? null,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'job_id'     => $id,
            'note'       => $note,
        ],
        'type'      => 'job',
        'link_url'  => $link,
        'priority'  => 'normal',
        'status'    => 'active',
    ]);
 
    return response()->json([
        'status'  => 'success',
        'message' => 'Status updated successfully',
        'data'    => $fresh,
    ]);
}
 
   /** =========================
     *   UPDATE (PUT/PATCH /job-details/{id})
     * ========================= */
    public function update(Request $request, int $id)
{
    if ($resp = $this->requireRole($request, ['admin','assignee'])) {
        Log::info('[JobUpdate] Unauthorized role attempt', ['id' => $id, 'actor' => $this->actor($request)]);
        return $resp;
    }

    Log::info('[JobUpdate] Start updating job', ['job_id' => $id, 'payload_raw' => $request->all()]);

    $job = $this->findJob($id);
    if (!$job) {
        Log::warning('[JobUpdate] Job not found', ['id' => $id]);
        return response()->json(['status'=>'error','message'=>'Job not found'], 404);
    }

    $wasStatus = $job->status;
    $parentId = $this->parseParentId($request->input('parent_id'));

    Log::debug('[JobUpdate] Parsed parent_id', ['input_parent_id' => $request->input('parent_id'), 'parsed_parent_id' => $parentId]);

    $data = $request->validate([
        'title'                 => 'sometimes|string|max:200',
        'description'           => 'sometimes|nullable|string',
        'type'                  => ['sometimes','nullable', Rule::in($this->TYPES)],
        'priority'              => ['sometimes','nullable', Rule::in($this->PRIORITY)],
        'status'                => ['sometimes','nullable', Rule::in($this->STATUS)],
        'client_id'             => ['sometimes','nullable','integer','exists:clients,id'],
        'document_id'           => ['sometimes','nullable','integer','exists:documents,id'],
        'planned_duration_days' => 'sometimes|nullable|integer|min:0',
        'planned_start_at'      => 'sometimes|nullable|date',
        'planned_end_at'        => 'sometimes|nullable|date',
        'planned_deadline_at'   => 'sometimes|nullable|date',
        'ordering'              => 'sometimes|nullable|integer|min:0',
        'metadata'              => 'sometimes|nullable|array',
        'parent_id'             => 'sometimes',
        'allow_weekend'         => 'sometimes|boolean',
    ]);

    Log::info('[JobUpdate] Validation complete', ['validated' => $data]);

    if (array_key_exists('parent_id', $request->all())) {
        if (!is_null($parentId)) {
            $exists = DB::table('job_details')->where('id', $parentId)->exists();
            if (!$exists) {
                Log::warning('[JobUpdate] Parent job not found', ['parent_id' => $parentId]);
                return response()->json(['status'=>'error','message'=>'Parent job not found'], 422);
            }
        }
    } else {
        $parentId = $job->parent_id;
    }

    if (array_key_exists('description', $data) && $data['description'] !== null) {
        $data['description'] = $this->sanitizeHtml($data['description']);
        Log::debug('[JobUpdate] Description sanitized', ['desc_length' => strlen($data['description'])]);
    }

    $payload = [
        'planned_start_at'      => $data['planned_start_at']      ?? $job->planned_start_at,
        'planned_end_at'        => $data['planned_end_at']        ?? $job->planned_end_at,
        'planned_deadline_at'   => $data['planned_deadline_at']   ?? $job->planned_deadline_at,
        'planned_duration_days' => $data['planned_duration_days'] ?? $job->planned_duration_days,
    ];

    Log::debug('[JobUpdate] Planned payload before validation', $payload);

    $this->validatePlannedDates($payload, $request);
    Log::info('[JobUpdate] Planned dates validated successfully');

    $update = [];
    foreach (['title','description','type','priority','status','client_id','document_id','ordering','metadata'] as $f) {
        if (array_key_exists($f, $data)) {
            $update[$f] = $f === 'metadata'
                ? ($data['metadata'] === null ? null : json_encode($data['metadata']))
                : $data[$f];
        }
    }

    if ($parentId !== $job->parent_id) {
        $update['parent_id'] = $parentId;
        if (!array_key_exists('ordering', $data) || $data['ordering'] === null) {
            $update['ordering'] = $this->nextOrdering($parentId);
        }
        Log::info('[JobUpdate] Parent changed', ['new_parent_id' => $parentId]);
    }

    $update = array_merge($update, $payload);
    Log::debug('[JobUpdate] Final update array', $update);

    if (empty($update)) {
        Log::info('[JobUpdate] No changes detected', ['job_id' => $id]);
        $this->logActivity($request, 'update', 'No changes detected', 'job_details', $id);
        return response()->json(['status'=>'success','message'=>'No changes detected','data'=>$job]);
    }

    $oldSnapshot = (array) $job;
    $update['updated_at'] = now();

    DB::table('job_details')->where('id', $id)->update($update);
    Log::info('[JobUpdate] Database updated successfully', ['job_id' => $id, 'updated_fields' => array_keys($update)]);

    $fresh = $this->findJob($id);
    $card = $this->jobCard($id);

    $changedKeys = array_keys($update);
    $summary = $changedKeys ? ('Updated fields: ' . implode(', ', $changedKeys)) : 'Updated';

    $assignees = $this->assigneeReceivers($id);
    $receivers = $assignees
        ? $this->mergeReceivers($this->adminReceivers(), $assignees)
        : $this->adminReceivers();

    $link = rtrim((string)config('app.url'), '/').'/jobs/'.$id;

    $notificationPayload = [
        'title'     => 'Job updated',
        'message'   => $summary,
        'receivers' => $receivers,
        'metadata'  => [
            'action'       => 'updated',
            'job'          => $card['job'] ?? null,
            'client'       => $card['client'] ?? null,
            'job_id'       => $id,
            'changed'      => $changedKeys,
            'old_status'   => $wasStatus,
            'new_status'   => $fresh->status,
        ],
        'type'      => 'job',
        'link_url'  => $link,
        'priority'  => 'normal',
        'status'    => 'active',
    ];

    Log::debug('[JobUpdate] Notification payload', $notificationPayload);

    $this->persistNotification($notificationPayload);

    $this->logActivity($request, 'update', 'Job updated', 'job_details', $id, array_keys($update), $oldSnapshot, $fresh ? (array)$fresh : null);
    Log::info('[JobUpdate] Activity logged', ['job_id' => $id]);

    Log::info('[JobUpdate] Completed successfully', [
        'job_id' => $id,
        'changed_keys' => $changedKeys,
        'new_status' => $fresh->status ?? null
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Job updated successfully',
        'data'    => $fresh,
    ]);
}


 
     /** =========================
     *   DELETE (DELETE /job-details/{id})
     * ========================= */
    public function destroy(Request $request, int $id)
{
    if ($resp = $this->requireRole($request, ['admin'])) return $resp;

    $job = $this->findJob($id);
    if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

    // 👇 fetch active assignees BEFORE deleting (cascade-safe)
    $assignees = DB::table('job_assignees')
        ->where('job_id', $id)
        ->where('status', 'active')
        ->pluck('assigned_person_id')
        ->all();
    $assigneeReceivers = array_map(fn($pid)=>['id'=>(int)$pid,'role'=>'assignee','read'=>0], $assignees);
    $receivers = $assigneeReceivers
        ? $this->mergeReceivers($this->adminReceivers(), $assigneeReceivers)
        : $this->adminReceivers();

    $snapshot = (array) $job;
    DB::table('job_details')->where('id', $id)->delete();

    $this->logActivity($request, 'destroy', "Deleted job \"{$job->title}\"", 'job_details', $id, null, $snapshot, null);

    $this->persistNotification([
        'title'     => 'Job deleted',
        'message'   => "“{$job->title}” was deleted.",
        'receivers' => $receivers,
        'metadata'  => ['action'=>'deleted','job'=>['id'=>$id,'title'=>$job->title],'client_id'=>$job->client_id,'job_id'=>$id],
        'type'      => 'job',
        'link_url'  => null,
        'priority'  => $assigneeReceivers ? 'high' : 'normal',
        'status'    => 'active',
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Job deleted successfully',
    ]);
}

    /** =========================
     *   PARENT TYPEAHEAD (GET /job-details/parents/suggest)
     *   ?q=...&client_id=...&exclude_id=...&limit=8
     * ========================= */
    public function suggestParents(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','assignee'])) return $resp;
 
        $q        = trim((string) $request->query('q', ''));
        $clientId = (int) $request->query('client_id', 0);
        $exclude  = (int) $request->query('exclude_id', 0);
        $limit    = min(20, max(1, (int) $request->query('limit', 8)));
 
        $qb = DB::table('job_details')
            ->select('id','title','parent_id','client_id')
            ->when($q !== '', fn($w) => $w->where('title', 'LIKE', "%{$q}%"))
            ->when($clientId > 0, fn($w) => $w->where('client_id', $clientId))
            ->when($exclude > 0, fn($w) => $w->where('id', '<>', $exclude))
            ->orderBy('title')
            ->limit($limit);
 
        $rows = $qb->get();
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Parent suggestions fetched',
            'data'    => $rows,
        ]);
    }
 
    /** =========================
     *   REORDER SIBLINGS (POST /job-details/reorder)
     *   payload: { parent_id: <id|null|'self'>, ordered_ids: [3,9,2,...] }
     * ========================= */
    public function reorder(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;
 
        $ordered = $request->validate([
            'parent_id'     => 'nullable',
            'ordered_ids'   => 'required|array|min:1',
            'ordered_ids.*' => 'integer|min:1',
        ]);
 
        $parentId = $this->parseParentId($ordered['parent_id'] ?? null);
        $ids = $ordered['ordered_ids'];
 
        // Ensure all belong to same parent
        $parentCheck = DB::table('job_details')->whereIn('id', $ids)->pluck('parent_id', 'id');
        foreach ($ids as $i) {
            $p = $parentCheck[$i] ?? null;
            if ((int)$p !== (int)$parentId && !($p === null && $parentId === null)) {
                return response()->json(['status'=>'error','message'=>'All jobs must share the same parent for reorder'], 422);
            }
        }
 
        DB::beginTransaction();
        try {
            $pos = 1;
            foreach ($ids as $jid) {
                DB::table('job_details')->where('id', $jid)->update([
                    'ordering'   => $pos++,
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Job Reorder] failed', ['error'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Reorder failed'], 500);
        }
 
        $this->logActivity($request, 'reorder', 'Sibling ordering updated', 'job_details', null, ['ordered_ids' => $ids]);
 
        return response()->json(['status'=>'success','message'=>'Reordered successfully']);
    }
 
    /** =========================
     *   UPLOAD DESCRIPTION IMAGE (job-bound)
     *   (POST /job-details/{id}/media)
     *   form-data: file=..., title (optional)
     * ========================= */
    public function uploadDescriptionMedia(Request $request, int $jobId)
    {
        if ($resp = $this->requireRole($request, ['admin','assignee'])) return $resp;
 
        $job = $this->findJob($jobId);
        if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);
 
        $validated = $request->validate([
            'file'  => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB
            'title' => 'sometimes|nullable|string|max:160',
        ]);
 
        $file = $validated['file'];
        $ext  = strtolower($file->getClientOriginalExtension());
        $name = 'job_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;
 
        $dest = public_path('uploads/jobDescriptionMedia');
        if (!is_dir($dest)) @mkdir($dest, 0775, true);
 
        $file->move($dest, $name);
 
        $relative = '/uploads/jobDescriptionMedia/'.$name;
        $absolute = rtrim(config('app.url'), '/').$relative;
 
        $mid = DB::table('job_description_media')->insertGetId([
            'title'        => $validated['title'] ?? null,
            'job_id'       => $jobId,
            'absolute_url' => $absolute,
            'relative_url' => $relative,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
 
        $this->logActivity($request, 'upload', 'Job description media uploaded', 'job_description_media', $mid, ['absolute_url','relative_url']);
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Media uploaded',
            'data'    => [
                'id'           => $mid,
                'absolute_url' => $absolute,
                'relative_url' => $relative,
            ],
        ], 201);
    }
 
    /** =========================
     *   DELETE MEDIA (DELETE /job-details/media/{media_id})
     * ========================= */
    public function deleteMedia(Request $request, int $mediaId)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;
 
        $row = DB::table('job_description_media')->where('id', $mediaId)->first();
        if (!$row) return response()->json(['status'=>'error','message'=>'Media not found'], 404);
 
        // Try unlink
        if (!empty($row->relative_url)) {
            $path = public_path(ltrim($row->relative_url, '/'));
            if (is_file($path)) @unlink($path);
        }
 
        DB::table('job_description_media')->where('id', $mediaId)->delete();
        $this->logActivity($request, 'destroy', 'Job description media deleted', 'job_description_media', $mediaId, null, (array)$row, null);
 
        return response()->json(['status'=>'success','message'=>'Media deleted']);
    }
 
    /** =========================
     *   MEDIA LIBRARY: list (GET /job-details/media)
     * ========================= */
    public function listMedia(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','assignee'])) return $resp;
 
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = min(100, max(1, (int) $request->query('per_page', 24)));
        $q        = trim((string) $request->query('q', ''));
        $clientId = (int) $request->query('client_id', 0);
 
        $qb = DB::table('job_description_media as m')
            ->leftJoin('job_details as j', 'j.id', '=', 'm.job_id')
            ->select('m.id','m.title','m.absolute_url','m.relative_url','m.job_id','j.title as job_title','j.client_id')
            ->orderBy('m.id','desc');
 
        if ($q !== '') {
            $like = "%{$q}%";
            $qb->where(function($w) use ($like){
                $w->where('m.title','LIKE',$like)->orWhere('m.absolute_url','LIKE',$like);
            });
        }
    if ($clientId > 0) {
    $qb->where(function($w) use ($clientId){
        $w->where('j.client_id', $clientId)
          ->orWhereNull('m.job_id');   // <= include library items (no job yet)
    });
}
 
        $total = (clone $qb)->count();
        $rows  = $qb->skip(($page-1)*$perPage)->take($perPage)->get();
 
        return response()->json([
            'status'=>'success',
            'message'=>'Media fetched',
            'data'=>$rows,
            'meta'=>[
                'page'=>$page,'per_page'=>$perPage,'total'=>$total,
                'total_pages'=>(int)ceil($total/$perPage),
            ],
        ]);
    }
 
    /** =========================
     *   MEDIA LIBRARY: upload loose (POST /job-details/media)
     * ========================= */
    public function uploadLooseMedia(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin','assignee'])) return $resp;
 
        $validated = $request->validate([
            'file'  => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'title' => 'sometimes|nullable|string|max:160',
        ]);
 
        $file = $validated['file'];
        $ext  = strtolower($file->getClientOriginalExtension());
        $name = 'joblib_'.Str::uuid()->toString().'.'.$ext;
 
        $dest = public_path('uploads/jobDescriptionMedia');
        if (!is_dir($dest)) @mkdir($dest, 0775, true);
        $file->move($dest, $name);
 
        $relative = '/uploads/jobDescriptionMedia/'.$name;
        $absolute = rtrim(config('app.url'), '/').$relative;
 
        $mid = DB::table('job_description_media')->insertGetId([
            'title'        => $validated['title'] ?? null,
            'job_id'       => null,
            'absolute_url' => $absolute,
            'relative_url' => $relative,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
 
        $this->logActivity($request, 'upload', 'Loose media uploaded', 'job_description_media', $mid, ['absolute_url','relative_url']);
 
        return response()->json([
            'status'=>'success',
            'message'=>'Media uploaded',
            'data'=>['id'=>$mid,'absolute_url'=>$absolute,'relative_url'=>$relative],
        ], 201);
    }
 
    /** =========================
     *   MEDIA LIBRARY: attach to job (PATCH /job-details/media/{id}/attach)
     * ========================= */
    public function attachMedia(Request $request, int $mediaId)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;
 
        $payload = $request->validate(['job_id'=>'required|integer|min:1|exists:job_details,id']);
        $media = DB::table('job_description_media')->where('id',$mediaId)->first();
        if (!$media) return response()->json(['status'=>'error','message'=>'Media not found'], 404);
        
        DB::table('job_description_media')->where('id',$mediaId)->update([
            'job_id'     => (int)$payload['job_id'],
            'updated_at' => now(),
        ]);
 
        $this->logActivity(
            $request,
            'update',
            'Media attached to job',
            'job_description_media',
            $mediaId,
            ['job_id'],
            (array)$media,
            array_merge((array)$media, ['job_id'=>(int)$payload['job_id']])
        );
 
        return response()->json(['status'=>'success','message'=>'Attached']);
    }


    /** GET /api/job-details/{job}/assignees */
public function listAssignees(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;
    if (($r->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }


    $rows = DB::table('job_assignees as ja')
        ->join('assigned_people as ap', 'ap.id','=','ja.assigned_person_id')
        ->select('ap.id','ap.name','ap.email','ap.status',
                 'ja.status as map_status','ja.assigned_at','ja.unassigned_at','ja.note')
        ->where('ja.job_id',$jobId)->orderBy('ap.name')->get();

    return response()->json(['status'=>'success','data'=>$rows]);
}

/** POST /api/job-details/{job}/assign
 * payload: { assigned_person_ids: [1,2,...], note?: string }
 */
public function assignPeople(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin'])) return $resp;

    $payload = $r->validate([
        'assigned_person_ids'   => 'required|array|min:1',
        'assigned_person_ids.*' => 'integer|min:1|exists:assigned_people,id',
        'note'                  => 'sometimes|nullable|string|max:255',
    ]);

    $actor = $this->actor($r);
    $job = DB::table('job_details')->where('id',$jobId)->first();
    if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'],404);

    $now = now(); $clientId = $job->client_id ?: null;
    $ids = array_values(array_unique(array_map('intval', $payload['assigned_person_ids'])));

    DB::beginTransaction();
    try{
        foreach($ids as $pid){
            $existing = DB::table('job_assignees')
                ->where('job_id',$jobId)->where('assigned_person_id',$pid)->first();

            if ($existing){
                DB::table('job_assignees')->where('id',$existing->id)->update([
                    'status'           => 'active',
                    'unassigned_at'    => null,
                    'assigned_at'      => $existing->assigned_at ?? $now,
                    'client_id'        => $clientId,
                    'note'             => $payload['note'] ?? $existing->note,
                    'assigned_by_type' => $actor['type'],
                    'assigned_by_id'   => $actor['id'],
                    'assigned_by_role' => $actor['role'],
                    'updated_at'       => $now,
                ]);
            } else {
                DB::table('job_assignees')->insert([
                    'job_id'            => $jobId,
                    'assigned_person_id'=> $pid,
                    'client_id'         => $clientId,
                    'status'            => 'active',
                    'assigned_at'       => $now,
                    'unassigned_at'     => null,
                    'note'              => $payload['note'] ?? null,
                    'assigned_by_type'  => $actor['type'],
                    'assigned_by_id'    => $actor['id'],
                    'assigned_by_role'  => $actor['role'],
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }
        DB::commit();

    } catch (\Throwable $e){
        DB::rollBack();
        return response()->json(['status'=>'error','message'=>'Assign failed'],500);
    }
      // NOTIFY: job assigned -> admins + newly assigned people
$card = $this->jobCard($jobId);
$link = rtrim((string)config('app.url'), '/').'/jobs/'.$jobId;

// Get assignee names for the admin message
$assigneeNames = DB::table('assigned_people')
    ->whereIn('id', $ids)
    ->pluck('name')
    ->toArray();

$assigneeNamesString = implode(', ', $assigneeNames);
$jobTitle = $card['job']['title'] ?? "Job #{$jobId}";

// Create separate receiver groups
$adminReceivers = $this->adminReceivers();
$assigneeReceivers = array_map(fn($pid) => ['id'=>(int)$pid,'role'=>'assignee','read'=>0], $ids);

// Send notification to admins
if (!empty($adminReceivers)) {
    $this->persistNotification([
        'title'     => 'New Assignment Created',
        'message'   => "A new assignment for \"{$jobTitle}\" has been assigned to {$assigneeNamesString}.",
        'receivers' => $adminReceivers,
        'metadata'  => [
            'action'       => 'assigned',
            'job'          => $card['job'] ?? null,
            'client'       => $card['client'] ?? null,
            'note'         => $payload['note'] ?? null,
            'job_id'       => $jobId,
            'assigned_ids' => $ids,
        ],
        'type'      => 'job',
        'link_url'  => $link,
        'priority'  => 'high',
        'status'    => 'active',
    ]);
}

// Send notification to assignees
if (!empty($assigneeReceivers)) {
    $this->persistNotification([
        'title'     => 'New Assignment Assigned to You',
        'message'   => "You have been assigned to a new job: \"{$jobTitle}\".",
        'receivers' => $assigneeReceivers,
        'metadata'  => [
            'action'       => 'assigned_to_me',
            'job'          => $card['job'] ?? null,
            'client'       => $card['client'] ?? null,
            'note'         => $payload['note'] ?? null,
            'job_id'       => $jobId,
            'assigned_ids' => $ids,
        ],
        'type'      => 'job',
        'link_url'  => $link,
        'priority'  => 'high',
        'status'    => 'active',
    ]);
}
    // --- NOTIFY: new assignment ---
$actor  = $this->actor($r);
$ownerT = (string) $actor['type'];
$ownerI = (int)    $actor['id'];
$card   = $this->jobCard($jobId);

// Build recipients = (newly assigned) + (admins & client), then de-dupe & exclude sender
$newAssignees = DB::table('assigned_people')
    ->whereIn('id', $ids)
    ->whereNotNull('email')
    ->select('email','name')
    ->get()
    ->map(fn($x) => ['email'=>$x->email,'name'=>$x->name])
    ->all();

$adminsClient = JobNotifier::recipients($jobId, [
    'admins'  => true,
    'client'  => true,
    'exclude' => strtolower((string)($this->actorEmail($actor) ?? '')),
]);

$bucket = [];
$push = function ($rcp) use (&$bucket) {
    $em = strtolower(trim((string)$rcp['email']));
    if ($em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $bucket[$em] = ['email'=>$rcp['email'], 'name'=>$rcp['name'] ?? null];
    }
};
foreach (array_merge($newAssignees, $adminsClient) as $rcp) $push($rcp);
$to = array_values($bucket);

if (!empty($to) && $card) {
    $data = [
        'action'       => 'assigned',
        'action_label' => 'New assignment',
        'job'          => $card['job'],
        'client'       => $card['client'],
        'actor'        => $actor,
        'note'         => $payload['note'] ?? null,
    ];
    JobNotifier::notify($ownerT, $ownerI, $to, $data);
}

    

    $this->logActivity($r, 'update', 'People assigned to job', 'job_assignees', $jobId, ['assigned_person_ids'=>$ids]);
    return response()->json(['status'=>'success','message'=>'Assigned successfully']);
}

/** PATCH /api/job-details/{job}/unassign
 * payload: { assigned_person_ids: [1,2,...] }
 */
public function unassignPeople(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin'])) return $resp;

    $payload = $r->validate([
        'assigned_person_ids'   => 'required|array|min:1',
        'assigned_person_ids.*' => 'integer|min:1|exists:assigned_people,id',
    ]);

    // Get job and assignee details BEFORE unassigning
    $job = DB::table('job_details')->where('id', $jobId)->first();
    if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

    $assigneeNames = DB::table('assigned_people')
        ->whereIn('id', $payload['assigned_person_ids'])
        ->pluck('name')
        ->toArray();

    $now = now();
    DB::table('job_assignees')
        ->where('job_id', $jobId)
        ->whereIn('assigned_person_id', $payload['assigned_person_ids'])
        ->update(['status'=>'unassigned','unassigned_at'=>$now,'updated_at'=>$now]);

    // NOTIFY: Person unassigned -> admins only
    $assigneeNamesString = implode(', ', $assigneeNames);
    $jobTitle = $job->title ?? "Job #{$jobId}";
    
    $this->persistNotification([
        'title'     => 'Person Unassigned from Job',
        'message'   => "\"{$assigneeNamesString}\" has been unassigned from job \"{$jobTitle}\".",
        'receivers' => $this->adminReceivers(),
        'metadata'  => [
            'action'       => 'unassigned',
            'job'          => ['id' => $jobId, 'title' => $jobTitle],
            'assignee_names' => $assigneeNames,
            'assigned_person_ids' => $payload['assigned_person_ids'],
            'job_id'       => $jobId,
        ],
        'type'      => 'job',
        'link_url'  => rtrim((string)config('app.url'), '/').'/jobs/'.$jobId,
        'priority'  => 'normal',
        'status'    => 'active',
    ]);

    $this->logActivity($r, 'update', 'People unassigned from job', 'job_assignees', $jobId, ['assigned_person_ids'=>$payload['assigned_person_ids']]);
    return response()->json(['status'=>'success','message'=>'Unassigned successfully']);
}
/** POST /api/job-details/assign-jobs-to-person/{person}
 * payload: { job_ids: [..], note?: string }
 */
public function assignJobsToPerson(Request $r, int $personId)
{
    if ($resp = $this->requireRole($r, ['admin'])) return $resp;

    $payload = $r->validate([
        'job_ids'   => 'required|array|min:1',
        'job_ids.*' => 'integer|min:1|exists:job_details,id',
        'note'      => 'sometimes|nullable|string|max:255',
    ]);

    $actor = $this->actor($r); $now = now();
    DB::beginTransaction();
    try{
        $jobs = DB::table('job_details')->whereIn('id',$payload['job_ids'])->get(['id','client_id']);
        foreach($jobs as $job){
            $existing = DB::table('job_assignees')
                ->where('job_id',$job->id)->where('assigned_person_id',$personId)->first();

            if ($existing){
                DB::table('job_assignees')->where('id',$existing->id)->update([
                    'status'=>'active','unassigned_at'=>null,'assigned_at'=>$existing->assigned_at ?? $now,
                    'client_id'=>$job->client_id,'note'=>$payload['note'] ?? $existing->note,
                    'assigned_by_type'=>$actor['type'],'assigned_by_id'=>$actor['id'],'assigned_by_role'=>$actor['role'],
                    'updated_at'=>$now,
                ]);
            } else {
                DB::table('job_assignees')->insert([
                    'job_id'=>$job->id,'assigned_person_id'=>$personId,'client_id'=>$job->client_id,
                    'status'=>'active','assigned_at'=>$now,'note'=>$payload['note'] ?? null,
                    'assigned_by_type'=>$actor['type'],'assigned_by_id'=>$actor['id'],'assigned_by_role'=>$actor['role'],
                    'created_at'=>$now,'updated_at'=>$now,
                ]);
            }
            // Notify per job (admins + this assignee)
                $card = ['job'=>['id'=>$job->id,'title'=>$job->title],'client'=>['id'=>$job->client_id,'name'=>null]];
                $link = rtrim((string)config('app.url'), '/').'/jobs/'.$job->id;
                $this->persistNotification([
                    'title'     => 'Assigned to a job',
                    'message'   => "New assignment on “".($job->title ?? "Job #{$job->id}")."”.",
                    'receivers' => $this->mergeReceivers(
                        $this->adminReceivers(),
                        [['id'=>(int)$personId,'role'=>'assignee','read'=>0]]
                    ),
                    'metadata'  => [
                        'action'       => 'assigned',
                        'job'          => $card['job'],
                        'client'       => $card['client'],
                        'note'         => $payload['note'] ?? null,
                        'job_id'       => $job->id,
                        'assigned_ids' => [$personId],
                    ],
                    'type'      => 'job',
                    'link_url'  => $link,
                    'priority'  => 'high',
                    'status'    => 'active',
                ]);
        }
        DB::commit();
    } catch (\Throwable $e){
        DB::rollBack();
        return response()->json(['status'=>'error','message'=>'Assign failed'],500);
    }

    $this->logActivity($r, 'update', 'Jobs assigned to person', 'job_assignees', null, ['person_id'=>$personId,'job_ids'=>$payload['job_ids']]);
    return response()->json(['status'=>'success','message'=>'Assigned successfully']);
}


/** GET /api/job-details/{job}/messages?page=&per_page= */
public function listMessages(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;
    if (($r->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }


    $page = max(1,(int)$r->query('page',1));
    $per  = min(100,max(1,(int)$r->query('per_page',20)));

    $q = DB::table('job_messages')->where('job_id',$jobId)->orderBy('id','desc');
    $total = (clone $q)->count();
$rows = $q->skip(($page-1)*$per)->take($per)->get()->map(function($row) use ($r) {
    // 1) Normalize attachment URLs (kept from your code)
    if (!empty($row->attachments_json)) {
        $att = json_decode($row->attachments_json, true) ?: [];
        foreach ($att as &$a) {
            if (!empty($a['disk']) && !empty($a['disk_path'])) {
                $a['absolute_url'] = Storage::disk($a['disk'])->url($a['disk_path']);
                $a['relative_url'] = parse_url($a['absolute_url'], PHP_URL_PATH) ?? $a['relative_url'] ?? null;
            } else {
                if (!empty($a['absolute_url']) && str_contains($a['absolute_url'], '/storage/')) {
                    $a['absolute_url'] = str_replace('/storage/', '/f/', $a['absolute_url']);
                }
                if (!empty($a['relative_url']) && str_starts_with($a['relative_url'], '/storage/')) {
                    $a['relative_url'] = str_replace('/storage/', '/f/', $a['relative_url']);
                }
            }
        }
        $row->attachments_json = json_encode($att, JSON_UNESCAPED_UNICODE);
    }

    // 2) NEW: attach sender contact
    $contact = $this->resolveSenderContact($row->sender_role ?? null, (int)($row->sender_id ?? 0));
    $row->sender_name  = $contact['name']  ?? null;
    $row->sender_email = $contact['email'] ?? null;
    $row->sender_phone = $contact['phone'] ?? null;
    $actor = $this->actor($r);
$actorEmail = $this->actorEmail($actor);

$row->my_email = $actorEmail; // ✅ send actor (viewer) email

    return $row;
});



    return response()->json([
        'status'=>'success','data'=>$rows,
        'meta'=>['page'=>$page,'per_page'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)]
    ]);
}
/** Resolve sender contact (name/email/phone) from role + id. */
/** Resolve sender contact (name/email/phone) from role + id.
 *  Automatically detects phone column if name varies.
 */
private function resolveSenderContact(?string $role, ?int $id): ?array
{
    $role = strtolower((string)$role);
    $id   = (int)($id ?? 0);
    if ($id <= 0 || $role === '') return null;

    if ($role === 'admin') {
        $tbl = 'admins';
    } elseif ($role === 'assignee') {
        $tbl = 'assigned_people';
    } else {
        $tbl = 'users';
    }

    // Try all possible phone column names
    $possiblePhones = ['phone', 'mobile', 'phone_no', 'phone_number', 'contact_number', 'contact_no', 'mobile_no'];

    $select = ['name', 'email'];
    foreach ($possiblePhones as $p) {
        // Only add column if it exists in the table
        if (Schema::hasColumn($tbl, $p)) {
            $select[] = "$tbl.$p as phone";
            break;
        }
    }

    $row = DB::table($tbl)
        ->select($select)
        ->where('id', $id)
        ->first();

    if (!$row) return null;

    return [
        'name'  => $row->name  ?? null,
        'email' => $row->email ?? null,
        'phone' => $row->phone ?? null,
    ];
}


/** POST /api/job-details/{job}/messages  (multipart)
 * fields: message_html (optional), attachments[] (0..N any file)
 */
/** POST /api/job-details/{job}/messages  (multipart)
 * fields: message_html (optional), attachments[] (0..N any file)
 */
public function postMessage(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;
    if (!DB::table('job_details')->where('id',$jobId)->exists())
        return response()->json(['status'=>'error','message'=>'Job not found'],404);

    // 🔒 user must be assigned
    if (($r->attributes->get('auth_role') ?? null) === 'assignee') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }

    $data = $r->validate([
        'message_html'  => 'sometimes|nullable|string',
        'attachments.*' => 'sometimes|file|max:102400', // 100 MB each
    ]);

    $actor = $this->actor($r);

    // Ensure public disk is usable
    if (!Storage::disk('public')->exists('jobMessages')) {
        Storage::disk('public')->makeDirectory('jobMessages');
    }

    // Collect files (support both "attachments" and "attachments[]")
    $files = $r->file('attachments');
    if ($files instanceof \Illuminate\Http\UploadedFile) {
        $files = [$files];
    } elseif (!is_array($files)) {
        $files = [];
    }

    $att = [];
    foreach ($files as $f) {
        if (!$f instanceof \Illuminate\Http\UploadedFile) continue;

        // Hard guard against PHP upload errors
        if ($f->getError() !== UPLOAD_ERR_OK || !$f->isValid()) {
            if (in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Attachment exceeds server upload limit (check upload_max_filesize / post_max_size).'
                ], 422);
            }
            continue;
        }

        $ext    = strtolower($f->getClientOriginalExtension() ?: 'bin');
        $stored = 'jobmsg_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;

        // Stream copy to storage/app/public/jobMessages/{file}
        $ok = Storage::disk('public')->putFileAs('jobMessages', $f, $stored);
        if (!$ok) {
            Log::warning('jobmsg store failed', ['name'=>$stored]);
            continue;
        }

        $publicPath = 'jobMessages/'.$stored;
        
        // ✅ FIX: Generate URL using the helper method for consistency
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

    $html  = $this->sanitizeHtml($data['message_html'] ?? null);
    $plain = $html ? trim(preg_replace('/\s+/', ' ', strip_tags($html))) : null;

    if (!$html && empty($att)) {
        return response()->json(['status'=>'error','message'=>'Nothing to post'], 422);
    }

    $now = now();
    $id = DB::table('job_messages')->insertGetId([
        'job_id'            => $jobId,
        'sender_type'       => $actor['type'],
        'sender_id'         => $actor['id'],
        'sender_role'       => $actor['role'],
        'message_html'      => $html,
        'message_text'      => $plain,
        'has_attachments'   => count($att) > 0,
        'attachments_count' => count($att),
        'attachments_json'  => $att ? json_encode($att, JSON_UNESCAPED_UNICODE) : null,
        'created_at'        => $now,
        'updated_at'        => $now,
    ]);

    // ✅ FIX: Fetch the message and normalize URLs before returning
    $row = DB::table('job_messages')->where('id',$id)->first();
    $row = $this->normalizeMessageAttachmentUrls($row);

    $card = $this->jobCard($jobId);
    // attach sender contact before returning
    $contact = $this->resolveSenderContact($actor['role'] ?? null, (int)($actor['id'] ?? 0));
    $row->sender_name  = $contact['name']  ?? null;
    $row->sender_email = $contact['email'] ?? null;
    $row->sender_phone = $contact['phone'] ?? null;
    $row->my_email = $this->actorEmail($actor);

    // NOTIFY: Message posted -> notify the "other side"
    $senderId = (int)($actor['id'] ?? 0);
    $senderRole = (string)($actor['role'] ?? '');

    if ($senderRole === 'admin') {
        $receivers = $this->assigneeReceivers($jobId, [$senderId]);
    } elseif ($senderRole === 'assignee') {
        $receivers = $this->adminReceivers([$senderId]);
    } else {
        $receivers = $this->adminReceivers([$senderId]);
    }

    if (!$receivers) $receivers = $this->adminReceivers([$senderId]);

    $link = rtrim((string)config('app.url'), '/').'/jobs/'.$jobId.'#messages';

    // Get job details for the notification message
    $job = DB::table('job_details')->where('id', $jobId)->first();
    $jobTitle = $job ? $job->title : "Job #{$jobId}";
    
    // Get sender name for the message
    $senderName = $this->resolveSenderContact($actor['role'], $actor['id']);
    $senderDisplayName = $senderName['name'] ?? ($actor['role'] === 'admin' ? 'Admin' : 'User');
    
    // Create notification message with job title
    $messagePreview = $plain ? mb_strimwidth($plain, 0, 120, '…') : 'New attachments posted.';
    $notificationMessage = "{$senderDisplayName} in \"{$jobTitle}\": {$messagePreview}";

    $this->persistNotification([
        'title'     => 'New chat message',
        'message'   => $notificationMessage,
        'receivers' => $receivers,
        'metadata'  => [
            'action'       => 'message',
            'job'          => $card['job'] ?? null,
            'client'       => $card['client'] ?? null,
            'actor'        => $actor,
            'attachments'  => $att,
            'job_id'       => $jobId,
            'job_title'    => $jobTitle, // Added job title separately
            'message_id'   => $row->id ?? null,
            'sender_name'  => $senderDisplayName,
        ],
        'type'      => 'job_message',
        'link_url'  => $link,
        'priority'  => 'normal',
        'status'    => 'active',
    ]);

    // --- NOTIFY: new message via email ---
    $ownerT = (string) $actor['type'];
    $ownerI = (int)    $actor['id'];

    $to = JobNotifier::recipients($jobId, [
        'exclude' => strtolower((string)($this->actorEmail($actor) ?? '')),
    ]);

    if (!empty($to) && $card) {
        $data = [
            'action'       => 'message',
            'action_label' => 'New message',
            'job'          => $card['job'],
            'client'       => $card['client'],
            'actor'        => $actor,
            'note'         => $plain,
            'attachments'  => $att,
        ];
        JobNotifier::notify($ownerT, $ownerI, $to, $data);
    }

    $this->logActivity($r, 'store', 'Job message posted', 'job_messages', $id, ['job_id'=>$jobId]);
    return response()->json(['status'=>'success','message'=>'Message posted','data'=>$row], 201);
}

/**
 * ✅ NEW HELPER: Generate consistent attachment URLs
 */
private function generateAttachmentUrls(string $publicPath): array
{
    // Use Storage facade to generate the public URL
    $absoluteUrl = Storage::disk('public')->url($publicPath);
    
    // Extract relative path from absolute URL
    $relativeUrl = parse_url($absoluteUrl, PHP_URL_PATH) ?: '/f/'.$publicPath;
    
    // Ensure /f/ prefix (your custom route)
    if (!str_starts_with($relativeUrl, '/f/')) {
        $relativeUrl = str_replace('/storage/', '/f/', $relativeUrl);
    }
    
    // Rebuild absolute URL with correct domain
    $absoluteUrl = rtrim(config('app.url'), '/') . $relativeUrl;
    
    return [
        'absolute_url' => $absoluteUrl,
        'relative_url' => $relativeUrl,
    ];
}

/**
 * ✅ NEW HELPER: Normalize attachment URLs in a message object
 */
private function normalizeMessageAttachmentUrls($message)
{
    if (!$message || empty($message->attachments_json)) {
        return $message;
    }

    $attachments = json_decode($message->attachments_json, true) ?: [];
    
    foreach ($attachments as &$att) {
        if (!empty($att['disk']) && !empty($att['disk_path'])) {
            // Regenerate URLs using the helper
            $urlData = $this->generateAttachmentUrls($att['disk_path']);
            $att['absolute_url'] = $urlData['absolute_url'];
            $att['relative_url'] = $urlData['relative_url'];
        } else {
            // Fallback for old-style attachments
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

/** Optional inline image upload for editor
 * POST /api/job-details/{job}/messages/upload  (file=image)
 */
public function uploadMessageImage(Request $r, int $jobId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;
    if (!DB::table('job_details')->where('id',$jobId)->exists())
        return response()->json(['status'=>'error','message'=>'Job not found'],404);

    if (($r->attributes->get('auth_role') ?? null) === 'assignee') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }

    $r->validate(['file'=>'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120']);

    if (!Storage::disk('public')->exists('jobMessages')) {
        Storage::disk('public')->makeDirectory('jobMessages');
    }

    /** @var \Illuminate\Http\UploadedFile $f */
    $f = $r->file('file');
    if (!$f || $f->getError() !== UPLOAD_ERR_OK || !$f->isValid()) {
        return response()->json(['status'=>'error','message'=>'Invalid image upload'], 422);
    }

    $ext    = strtolower($f->getClientOriginalExtension() ?: 'png');
    $stored = 'jobmsgimg_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;

    $ok = Storage::disk('public')->putFileAs('jobMessages', $f, $stored);
    if (!$ok) {
        Log::warning('jobmsgimg store failed', ['name'=>$stored]);
        return response()->json(['status'=>'error','message'=>'Upload failed'], 500);
    }

    $publicPath = 'jobMessages/'.$stored;
$abs = Storage::url($publicPath);
$rel = parse_url($abs, PHP_URL_PATH) ?: '/f/'.$publicPath;
return response()->json([
    'status'       => 'success',
    'url'          => $abs,   // editor will insert this
    'relative_url' => $rel,
], 201);

}



/** DELETE /api/job-details/messages/{messageId} */
public function deleteMessage(Request $r, int $messageId)
{
    if ($resp = $this->requireRole($r, ['admin'])) return $resp;

    $row = DB::table('job_messages')->where('id',$messageId)->first();
    if (!$row) return response()->json(['status'=>'error','message'=>'Not found'],404);

    // Try to delete stored files (support both old public/uploads and new storage/public)
    if (!empty($row->attachments_json)){
        $files = json_decode($row->attachments_json, true) ?: [];
        foreach($files as $f){
            // New format: disk + disk_path
            if (!empty($f['disk']) && !empty($f['disk_path'])) {
                try { Storage::disk($f['disk'])->delete($f['disk_path']); } catch (\Throwable $e) { /* ignore */ }
                continue;
            }
            // Back-compat: relative_url in /uploads/...
            if (!empty($f['relative_url'])) {
                $p = public_path(ltrim($f['relative_url'],'/'));
                if (is_file($p)) @unlink($p);
            }
        }
    }

    DB::table('job_messages')->where('id',$messageId)->delete();

    $this->logActivity($r, 'destroy', 'Job message deleted', 'job_messages', $messageId);
    return response()->json(['status'=>'success','message'=>'Deleted']);
}
/** =========================
 *   EDIT MESSAGE (PATCH /job-details/messages/{messageId})
 *   WhatsApp-style editing: messages can be edited within 24 hours
 * ========================= */
public function editMessage(Request $r, int $messageId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee'])) return $resp;

    $message = DB::table('job_messages')->where('id', $messageId)->first();
    if (!$message) {
        return response()->json(['status'=>'error','message'=>'Message not found'], 404);
    }

    // Check if user can edit this message - ONLY SENDER CAN EDIT
    $editCheck = $this->checkMessageEditPermission($r, $message);
    if ($editCheck['error']) {
        return response()->json([
            'status'=>'error',
            'message'=>$editCheck['message']
        ], $editCheck['code']);
    }

    $actor = $this->actor($r);
    
    $data = $r->validate([
        'message_html' => 'sometimes|nullable|string|max:50000',
        'remove_attachments' => 'sometimes|array',
        'remove_attachments.*' => 'integer|min:0',
        'attachments.*' => 'sometimes|file|max:102400', // 100MB
    ]);

    // Validate that message won't be empty after edit
    $validationResult = $this->validateMessageEdit($message, $data, $r);
    if (!$validationResult['valid']) {
        return response()->json([
            'status'=>'error',
            'message'=>$validationResult['message']
        ], 422);
    }

    DB::beginTransaction();
    try {
        // Store original state for activity log
        $oldSnapshot = [
            'message_html' => $message->message_html,
            'message_text' => $message->message_text,
            'attachments' => $message->attachments_json ? json_decode($message->attachments_json, true) : [],
            'attachments_count' => $message->attachments_count,
        ];

        // Process message content
        $updateData = $this->prepareMessageUpdate($message, $data);
        
        // Handle attachment removal
        $finalAttachments = $this->processAttachmentRemovals(
            $message,
            $data['remove_attachments'] ?? []
        );

        // Handle new attachments
        $newAttachments = $this->processNewAttachments($r, $message->job_id);
        
        // Merge attachments
        $allAttachments = array_merge($finalAttachments, $newAttachments);
        
        // Update attachment metadata
        $updateData['attachments_json'] = !empty($allAttachments) 
            ? json_encode($allAttachments, JSON_UNESCAPED_UNICODE) 
            : null;
        $updateData['attachments_count'] = count($allAttachments);
        $updateData['has_attachments'] = !empty($allAttachments);
        $updateData['updated_at'] = now();
        // Apply update
        DB::table('job_messages')->where('id', $messageId)->update($updateData);
        
        DB::commit();

        // Fetch fresh data
        $freshMessage = DB::table('job_messages')->where('id', $messageId)->first();

        // Log activity
        $this->logMessageEditActivity($r, $messageId, $oldSnapshot, $freshMessage);

        // Send notifications
        $this->notifyMessageEdit($message, $actor, $oldSnapshot, $freshMessage);

        return response()->json([
            'status' => 'success',
            'message' => 'Message updated successfully',
            'data' => $freshMessage,
            'changes' => [
                'content_updated' => array_key_exists('message_html', $data),
                'attachments_removed' => count($data['remove_attachments'] ?? []),
                'attachments_added' => count($newAttachments),
                'total_attachments' => count($allAttachments),
            ]
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Message edit failed', [
            'message_id' => $messageId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status'=>'error',
            'message'=>'Failed to update message'
        ], 500);
    }
}

/** =========================
 *   HELPER: Check Message Edit Permission
 *   ONLY THE SENDER CAN EDIT THEIR MESSAGES (regardless of role)
 * ========================= */
private function checkMessageEditPermission(Request $r, $message): array
{
    $actor = $this->actor($r);
    
    // Check if current user is the message sender
    $isSender = ($actor['type'] === $message->sender_type && 
                 $actor['id'] == $message->sender_id); // Use == for loose comparison

    if (!$isSender) {
        return [
            'error' => true,
            'message' => 'You can only edit your own messages',
            'code' => 403
        ];
    }

    // Check 24-hour time limit
    $messageTime = Carbon::parse($message->created_at);
    $now = now();
    $hoursSinceCreation = $messageTime->diffInHours($now);
    
    if ($hoursSinceCreation > 24) {
        return [
            'error' => true,
            'message' => 'Messages can only be edited within 24 hours of posting',
            'code' => 422,
            'meta' => [
                'hours_since_creation' => $hoursSinceCreation,
                'edit_window_expired' => true,
                'created_at' => $messageTime->toISOString(),
            ]
        ];
    }

    return [
        'error' => false,
        'hours_remaining' => 24 - $hoursSinceCreation,
        'can_edit_until' => $messageTime->addHours(24)->toISOString(),
    ];
}

/** =========================
 *   HELPER: Validate Message Edit
 * ========================= */
private function validateMessageEdit($message, array $data, Request $r): array
{
    // Check if any changes are provided
    if (!array_key_exists('message_html', $data) && 
        !array_key_exists('remove_attachments', $data) && 
        !$r->hasFile('attachments')) {
        return [
            'valid' => false,
            'message' => 'No changes provided'
        ];
    }

    $oldAttachments = $message->attachments_json 
        ? json_decode($message->attachments_json, true) 
        : [];

    // Calculate resulting state
    $willHaveMessage = true;
    if (array_key_exists('message_html', $data)) {
        $newHtml = $this->sanitizeHtml($data['message_html']);
        $willHaveMessage = !empty(trim(strip_tags($newHtml ?? '')));
    } else {
        $willHaveMessage = !empty(trim(strip_tags($message->message_html ?? '')));
    }

    $remainingAttachments = count($oldAttachments);
    if (array_key_exists('remove_attachments', $data) && !empty($data['remove_attachments'])) {
        $remainingAttachments -= count($data['remove_attachments']);
    }
    
    $willHaveNewAttachments = $r->hasFile('attachments');
    $willHaveAttachments = ($remainingAttachments > 0) || $willHaveNewAttachments;

    // Message must have either text or attachments
    if (!$willHaveMessage && !$willHaveAttachments) {
        return [
            'valid' => false,
            'message' => 'Message cannot be empty. Please provide text or at least one attachment.'
        ];
    }

    return ['valid' => true];
}

/** =========================
 *   HELPER: Prepare Message Update Data
 * ========================= */
private function prepareMessageUpdate($message, array $data): array
{
    $updateData = [];

    if (array_key_exists('message_html', $data)) {
        $html = $this->sanitizeHtml($data['message_html']);
        $plain = $html ? trim(preg_replace('/\s+/', ' ', strip_tags($html))) : null;
        
        $updateData['message_html'] = $html;
        $updateData['message_text'] = $plain;
    }

    return $updateData;
}

/** =========================
 *   HELPER: Process Attachment Removals
 * ========================= */
private function processAttachmentRemovals($message, array $removeIndices): array
{
    $oldAttachments = $message->attachments_json 
        ? json_decode($message->attachments_json, true) 
        : [];

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

/** =========================
 *   HELPER: Delete Attachment File
 * ========================= */
private function deleteAttachmentFile(array $attachment): void
{
    try {
        // New format: disk + disk_path
        if (!empty($attachment['disk']) && !empty($attachment['disk_path'])) {
            Storage::disk($attachment['disk'])->delete($attachment['disk_path']);
            return;
        }
        
        // Fallback: relative_url
        if (!empty($attachment['relative_url'])) {
            $path = public_path(ltrim($attachment['relative_url'], '/'));
            if (is_file($path)) {
                @unlink($path);
            }
        }
    } catch (\Throwable $e) {
        Log::warning('Failed to delete attachment file', [
            'attachment' => $attachment,
            'error' => $e->getMessage()
        ]);
    }
}

/** =========================
 *   HELPER: Process New Attachments
 * ========================= */
private function processNewAttachments(Request $r, int $jobId): array
{
    $files = $r->file('attachments');
    if (!$files) {
        return [];
    }

    if ($files instanceof \Illuminate\Http\UploadedFile) {
        $files = [$files];
    } elseif (!is_array($files)) {
        return [];
    }

    // Ensure storage directory exists
    if (!Storage::disk('public')->exists('jobMessages')) {
        Storage::disk('public')->makeDirectory('jobMessages');
    }

    $newAttachments = [];
    foreach ($files as $f) {
        if (!$f instanceof \Illuminate\Http\UploadedFile) continue;

        // Validate upload
        if ($f->getError() !== UPLOAD_ERR_OK || !$f->isValid()) {
            if (in_array($f->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                throw new \Exception('Attachment exceeds server upload limit');
            }
            continue;
        }

        $ext = strtolower($f->getClientOriginalExtension() ?: 'bin');
        $stored = 'jobmsg_'.$jobId.'_'.Str::uuid()->toString().'.'.$ext;

        // Store file
        $ok = Storage::disk('public')->putFileAs('jobMessages', $f, $stored);
        if (!$ok) {
            Log::warning('Failed to store attachment during edit', ['name'=>$stored]);
            continue;
        }

        $publicPath = 'jobMessages/'.$stored;
  $url = Storage::url($publicPath);                          // -> /f/jobMessages/...
$rel = parse_url($url, PHP_URL_PATH) ?: '/f/'.$publicPath;
$mime = $f->getClientMimeType() ?: $f->getMimeType() ?: 'application/octet-stream';

$newAttachments[] = [
  'kind'          => str_starts_with($mime,'image/') ? 'image' : 'file',
  'original_name' => $f->getClientOriginalName(),
  'stored_name'   => $stored,
  'mime'          => $mime,
  'size'          => (int)($f->getSize() ?? 0),
  'relative_url'  => $rel,
  'absolute_url'  => $url,
  'disk'          => 'public',
  'disk_path'     => $publicPath,
  'uploaded_at'   => now()->toIso8601String(),
];

    }

    return $newAttachments;
}

/** =========================
 *   HELPER: Log Message Edit Activity
 * ========================= */
private function logMessageEditActivity(Request $r, int $messageId, array $old, $new): void
{
    $changedFields = [];
    $oldValues = [];
    $newValues = [];

    if ($old['message_html'] !== $new->message_html) {
        $changedFields[] = 'message_html';
        $changedFields[] = 'message_text';
        $oldValues['message_html'] = $old['message_html'];
        $oldValues['message_text'] = $old['message_text'];
        $newValues['message_html'] = $new->message_html;
        $newValues['message_text'] = $new->message_text;
    }

    if ($old['attachments_count'] !== $new->attachments_count) {
        $changedFields[] = 'attachments';
        $oldValues['attachments_count'] = $old['attachments_count'];
        $oldValues['attachments'] = $old['attachments'];
        $newValues['attachments_count'] = $new->attachments_count;
        $newValues['attachments'] = $new->attachments_json 
            ? json_decode($new->attachments_json, true) 
            : [];
    }

    $this->logActivity(
        $r,
        'update',
        'Job message edited (WhatsApp-style)',
        'job_messages',
        $messageId,
        $changedFields,
        $oldValues,
        $newValues
    );
}

/** =========================
 *   HELPER: Notify Message Edit
 * ========================= */
private function notifyMessageEdit($message, array $actor, array $old, $new): void
{
    $card = $this->jobCard($message->job_id);
    if (!$card) return;

    $senderId = (int)($actor['id'] ?? 0);
    $senderRole = (string)($actor['role'] ?? '');

    // Determine receivers (opposite party)
    if ($senderRole === 'admin') {
        $receivers = $this->assigneeReceivers($message->job_id, [$senderId]);
    } elseif ($senderRole === 'assignee') {
        $receivers = $this->adminReceivers([$senderId]);
    } else {
        $receivers = $this->adminReceivers([$senderId]);
    }

    if (!$receivers) $receivers = $this->adminReceivers([$senderId]);

    $link = rtrim((string)config('app.url'), '/').'/jobs/'.$message->job_id.'#messages';

    // Build change summary
    $changeSummary = [];
    if ($old['message_html'] !== $new->message_html) {
        $changeSummary[] = 'content';
    }
    if ($old['attachments_count'] !== $new->attachments_count) {
        $diff = $new->attachments_count - $old['attachments_count'];
        if ($diff > 0) {
            $changeSummary[] = "+{$diff} attachment(s)";
        } else {
            $changeSummary[] = "{$diff} attachment(s)";
        }
    }

    $changeText = !empty($changeSummary) 
        ? implode(', ', $changeSummary) 
        : 'updated';

    $notificationMsg = "A message was edited";
    if ($new->message_text) {
        $notificationMsg .= ' - ' . mb_strimwidth($new->message_text, 0, 200, '…');
    }

    $this->persistNotification([
        'title' => 'Message edited',
        'message' => $notificationMsg,
        'receivers' => $receivers,
        'metadata' => [
            'action' => 'message_edited',
            'job' => $card['job'] ?? null,
            'client' => $card['client'] ?? null,
            'actor' => $actor,
            'job_id' => $message->job_id,
            'message_id' => $message->id,
            'changes' => $changeSummary,
        ],
        'type' => 'job',
        'link_url' => $link,
        'priority' => 'low',
        'status' => 'active',
    ]);
}

/** =========================
 *   GET: Check if message can be edited
 *   GET /job-details/messages/{messageId}/can-edit
 * ========================= */
public function canEditMessage(Request $r, int $messageId)
{
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;

    $message = DB::table('job_messages')->where('id', $messageId)->first();
    if (!$message) {
        return response()->json(['status'=>'error','message'=>'Message not found'], 404);
    }

    $editCheck = $this->checkMessageEditPermission($r, $message);
    
    return response()->json([
        'status' => 'success',
        'can_edit' => !$editCheck['error'],
        'reason' => $editCheck['error'] ? $editCheck['message'] : null,
        'hours_remaining' => $editCheck['hours_remaining'] ?? null,
        'can_edit_until' => $editCheck['can_edit_until'] ?? null,
    ]);
}
   /**
     * Export job messages as Excel (CSV), Word (.doc via HTML), or PDF.
     * GET /api/job-details/{jobId}/export-chats?format=excel|pdf|word&rolewise=1
     */
public function exportChats(Request $r, int $jobId)
{
    // Roles allowed
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;

    // If actor is not admin, ensure they can see this job
    if (($r->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }

    // validate inputs
    $format = strtolower(trim((string) $r->query('format', 'excel')));
    if (!in_array($format, ['excel','pdf','word'], true)) {
        return response()->json(['status'=>'error','message'=>'Invalid format. Use excel, pdf or word'], 422);
    }
    $rolewise = (bool) $r->boolean('rolewise', true); // split per role if true
    $includeAttachments = (bool) $r->boolean('include_attachments', true);

    $job = DB::table('job_details')->where('id',$jobId)->first();
    if (!$job) return response()->json(['status'=>'error','message'=>'Job not found'], 404);

    // fetch messages ordered asc for readable export
    $msgs = DB::table('job_messages')
        ->where('job_id', $jobId)
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function($m){
            return (object)[
                'id' => $m->id,
                'sender_type' => $m->sender_type,
                'sender_id' => $m->sender_id,
                'sender_role' => $m->sender_role,
                'created_at' => $m->created_at,
                'message_text' => $m->message_text,
                'message_html' => $m->message_html,
                'has_attachments' => (bool)$m->has_attachments,
                'attachments' => $m->attachments_json ? json_decode($m->attachments_json, true) : [],
            ];
        });

    // ------------------- Batch-resolve sender names (Option B) -------------------
    // Build id sets grouped by sender_type
    $idsByType = [];
    foreach ($msgs as $m) {
        $type = $m->sender_type ?: 'unknown';
        $id   = isset($m->sender_id) ? intval($m->sender_id) : 0;
        if ($id <= 0) continue;
        $idsByType[$type][$id] = $id;
    }

    // Container for resolved names: e.g. 'assigned_person:123' => 'Jane Doe'
    $senderNameMap = [];

    // assigned_people / assignee
    $apKeys = array_merge(
        $idsByType['assigned_person'] ?? [],
        $idsByType['assignee'] ?? []
    );
    if (!empty($apKeys)) {
        $apIds = array_values(array_unique($apKeys));
        $rows = DB::table('assigned_people')->whereIn('id', $apIds)->select('id','name','email')->get();
        foreach ($rows as $r) {
            $name = trim($r->name ?: $r->email ?: ("Person #{$r->id}"));
            $senderNameMap['assigned_person:'.$r->id] = $name;
            $senderNameMap['assignee:'.$r->id] = $name;
        }
    }

    // users
    if (!empty($idsByType['user'] ?? [])) {
        $uIds = array_values(array_unique($idsByType['user']));
        $rows = DB::table('users')->whereIn('id', $uIds)->select('id','name','email')->get();
        foreach ($rows as $r) {
            $senderNameMap['user:'.$r->id] = trim($r->name ?: $r->email ?: ("User #{$r->id}"));
        }
    }

    // admins
    if (!empty($idsByType['admin'] ?? [])) {
        $aIds = array_values(array_unique($idsByType['admin']));
        $rows = DB::table('admins')->whereIn('id', $aIds)->select('id','name','email')->get();
        foreach ($rows as $r) {
            $senderNameMap['admin:'.$r->id] = trim($r->name ?: $r->email ?: ("Admin #{$r->id}"));
        }
    }

    // clients
    if (!empty($idsByType['client'] ?? [])) {
        $cIds = array_values(array_unique($idsByType['client']));
        $rows = DB::table('clients')->whereIn('id', $cIds)->select('id','name','email')->get();
        foreach ($rows as $r) {
            $senderNameMap['client:'.$r->id] = trim($r->name ?: $r->email ?: ("Client #{$r->id}"));
        }
    }

    // Attach sender_name to each message (preserve existing sender_name if present)
    $msgs = $msgs->map(function($m) use ($senderNameMap) {
        if (!empty($m->sender_name)) return $m;

        $type = $m->sender_type ?: 'unknown';
        $id   = isset($m->sender_id) ? intval($m->sender_id) : 0;
        $key  = $type . ':' . $id;

        if (!empty($senderNameMap[$key])) {
            $m->sender_name = $senderNameMap[$key];
        } else {
            // fallback: role or id
            $m->sender_name = $m->sender_role ? ucfirst($m->sender_role) : ($id ? ('#'.$id) : 'Unknown');
        }
        return $m;
    });
    // ---------------------------------------------------------------------------

    if ($format === 'excel') {
        return $this->exportMessagesExcel($job, $msgs, $rolewise, $includeAttachments);
    } elseif ($format === 'word') {
        return $this->exportMessagesWord($job, $msgs, $rolewise, $includeAttachments);
    } else {
        return $this->exportMessagesPdf($job, $msgs, $rolewise, $includeAttachments);
    }
}

    /**
     * CSV export (Excel-friendly) — streaming
     */
    private function exportMessagesExcel($job, Collection $messages, bool $rolewise = true, bool $includeAttachments = true)
    {
        if ($messages->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No messages found for this job.'], 404);
        }

        $groups = $rolewise ? $messages->groupBy(fn($m) => ($m->sender_role ?: 'unknown'))
                            : collect(['all' => $messages]);

        $fileName = 'job_' . $job->id . '_messages_' . date('Ymd_His') . '.csv';

        $callback = function() use ($groups, $includeAttachments) {
            $out = fopen('php://output', 'w');

            // Optional BOM for Excel (uncomment if you need it)
            // fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            foreach ($groups as $role => $rows) {
                fputcsv($out, ["Role: {$role}"]);
$headers = ['ID', 'Sender Role', 'Sender Name', 'Sender Type', 'Sender ID', 'DateTime', 'Message', 'Has Attachments'];
                if ($includeAttachments) $headers[] = 'Attachments (JSON)';
                fputcsv($out, $headers);

                foreach ($rows as $m) {
                    $text = $m->message_text ?? trim(strip_tags($m->message_html ?? ''));
                    $text = preg_replace("/\r\n|\r|\n/", "\n", (string)$text);
                    $text = trim($text);

                    $row = [
                        $m->id,
                        $m->sender_role,
                            $m->sender_name ?? '',      // <- new

                        $m->sender_type,
                        $m->sender_id,
                        (string) Carbon::parse($m->created_at)->toDateTimeString(),
                        $text,
                        $m->has_attachments ? 'yes' : 'no',
                    ];

                    if ($includeAttachments) {
                        $row[] = $m->attachments ? json_encode($m->attachments, JSON_UNESCAPED_UNICODE) : '';
                    }

                    fputcsv($out, $row);
                }

                // blank line between groups
                fputcsv($out, []);
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
    private function exportMessagesWord($job, Collection $messages, bool $rolewise = true, bool $includeAttachments = true)
    {
        if ($messages->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No messages found for this job.'], 404);
        }

        $groups = $rolewise ? $messages->groupBy(fn($m) => ($m->sender_role ?: 'unknown'))
                            : collect(['all' => $messages]);

        $data = [
            'job' => $job,
            'groups' => $groups,
            'include_attachments' => $includeAttachments,
            'generated_at' => now()->toDateTimeString(),
        ];

        // Render the blade (create view resources/views/exports/job_messages_word_html.blade.php)
        $html = view('exports.job_messages_word_html', $data)->render();

        $fileName = 'job_' . $job->id . '_messages_' . date('Ymd_His') . '.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * PDF export using Browser Print (HTML with print styles)
     */
    private function exportMessagesPdf($job, Collection $messages, bool $rolewise = true, bool $includeAttachments = true)
    {
        if ($messages->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No messages found for this job.'], 404);
        }

        $groups = $rolewise ? $messages->groupBy(fn($m)=>($m->sender_role ?: 'unknown')) : collect(['all' => $messages]);

        $data = [
            'job' => $job,
            'groups' => $groups,
            'include_attachments' => $includeAttachments,
            'generated_at' => now()->toDateTimeString(),
        ];

        // Render HTML with print-friendly styles
        $html = view('exports.job_messages_pdf', $data)->render();

        $fileName = 'job_'.$job->id.'_messages_'.date('Ymd_His').'.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Compatibility helper (kept from earlier)
     */
    private function sanitizeSheetTitle(string $t): string
    {
        return preg_replace('/[:\\\\\/\?\*\[\]]+/', '_', (string)$t);
    }
    /**
 * Sanitize strings for safe file names or job report titles.
 * Removes special characters that are invalid in file systems.
 * Example: "Job: Report / Q1 * Summary?" → "Job_Report_Q1_Summary"
 */
private function sanitizeSheetTitle2(string $t): string
{
    // Remove disallowed characters (Windows, Linux, macOS safe set)
    $clean = preg_replace('/[\\\\\/:\*\?"<>\|\r\n\t]+/', '_', $t);

    // Replace multiple underscores/spaces with a single underscore
    $clean = preg_replace('/[\s_]+/', '_', trim($clean));

    // Truncate to a safe length (e.g., 100 chars)
    if (strlen($clean) > 100) {
        $clean = substr($clean, 0, 100);
    }

    // Ensure non-empty string
    return $clean !== '' ? $clean : 'Untitled_Report';
}

   /**
 * GET /api/job-details/{jobId}/export-report?format=pdf|word|json
 * query flags:
 *   include_messages=1
 *   include_media=1
 *   include_activity=1
 *   include_assignees=1
 *   include_children=1
 */
public function exportJobReport(Request $r, int $jobId)
{
    // role guard
    if ($resp = $this->requireRole($r, ['admin','assignee','client_user'])) return $resp;

    // non-admin roles may only export jobs they can see
    if (($r->attributes->get('auth_role') ?? null) !== 'admin') {
        if ($resp = $this->forbidIfNoAccess($r, $jobId)) return $resp;
    }

    $format = strtolower(trim((string)$r->query('format', 'pdf')));
    if (!in_array($format, ['pdf','word','json'], true)) {
        return response()->json(['status'=>'error','message'=>'Invalid format. Use pdf, word or json'], 422);
    }

    // options
    $opts = [
        'include_messages' => (bool) $r->boolean('include_messages', true),
        'include_media'    => (bool) $r->boolean('include_media', true),
        'include_activity' => (bool) $r->boolean('include_activity', true),
        'include_assignees' => (bool) $r->boolean('include_assignees', true),
        'include_children' => (bool) $r->boolean('include_children', true),
        'rolewise'         => (bool) $r->boolean('rolewise', false),
    ];

    // assemble data with better error handling
    try {
        Log::info('[exportJobReport] Starting report assembly', ['job_id' => $jobId, 'format' => $format]);
        
        $report = $this->assembleJobReport($jobId, $opts);
        if (!$report) {
            Log::warning('[exportJobReport] Job not found', ['job_id' => $jobId]);
            return response()->json(['status'=>'error','message'=>'Job not found'], 404);
        }
        
        Log::info('[exportJobReport] Report assembled successfully', [
            'job_id' => $jobId, 
            'sections' => array_keys($report)
        ]);
    } catch (\Throwable $e) {
        Log::error('[exportJobReport] assemble failed', [
            'error' => $e->getMessage(), 
            'job_id' => $jobId,
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        return response()->json(['status'=>'error','message'=>'Failed to assemble report: ' . $e->getMessage()], 500);
    }

    $generatedAt = now()->format('Ymd_His');
    $fileBase = $this->sanitizeSheetTitle2('job_'.$jobId.'_report_'.now()->format('Ymd_His'));

    if ($format === 'json') {
        return response()->json([
            'status'=>'success',
            'generated_at'=>now()->toDateTimeString(),
            'data'=>$report
        ]);
    }

    $viewData = [
        'report' => $report, 
        'generated_at' => now()->toDateTimeString()
    ];

    try {
        if ($format === 'word') {
            // Check if view exists
            if (!view()->exists('exports.job_report_word_html')) {
                throw new \Exception('Word export template not found');
            }
            
            // render an HTML that MS Word can open and download as .doc
            $html = view('exports.job_report_word_html', $viewData)->render();
            $fileName = $fileBase . '.doc';
            
            Log::info('[exportJobReport] Word document generated', [
                'job_id' => $jobId, 
                'file_size' => strlen($html)
            ]);
            
            return response($html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        // default: pdf - now using browser print HTML
        if (!view()->exists('exports.job_report_pdf')) {
            throw new \Exception('Print export template not found');
        }
        
        $html = view('exports.job_report_pdf', $viewData)->render();
        $fileName = $fileBase . '.html';
        
        Log::info('[exportJobReport] Print HTML document generated', ['job_id' => $jobId]);
        
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
        ]);

    } catch (\Throwable $e) {
        Log::error('[exportJobReport] Document generation failed', [
            'error' => $e->getMessage(),
            'job_id' => $jobId,
            'format' => $format,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status'=>'error',
            'message'=>'Failed to generate document: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Assemble a job report payload (structured array) with optional sections.
 * Returns null if job missing.
 */
private function assembleJobReport(int $jobId, array $opts = []): ?array
{
    $opts = array_merge([
        'include_messages' => true,
        'include_media'    => true,
        'include_activity' => true,
        'include_assignees' => true,
        'include_children' => true,
        'rolewise'         => false,
    ], $opts);

    Log::info('[assembleJobReport] Starting', ['job_id' => $jobId, 'opts' => $opts]);

    try {
        // core job + client + document
        $job = DB::table('job_details as j')
            ->leftJoin('clients as c','c.id','=','j.client_id')
            ->leftJoin('documents as d','d.id','=','j.document_id')
            ->select('j.*','c.name as client_name','c.email as client_email','d.doc_name as document_name')
            ->where('j.id',$jobId)
            ->first();

        if (!$job) {
            Log::warning('[assembleJobReport] Job not found', ['job_id' => $jobId]);
            return null;
        }

        Log::info('[assembleJobReport] Core job data fetched', ['job_id' => $jobId, 'title' => $job->title]);

        $report = [
            'job' => (array)$job,
            'client' => [
                'id' => $job->client_id,
                'name' => $job->client_name ?? null,
                'email' => $job->client_email ?? null,
            ],
            'document' => $job->document_id ? ['id'=>$job->document_id,'name'=>$job->document_name] : null,
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'requested_by' => $this->actor(request()) ?? null,
            ],
        ];
        if (!empty($report['job']['description'])) {
    try {
        $html = $report['job']['description'];
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $appUrl = rtrim(config('app.url') ?? '', '/');
        $inlineMaxBytes = 1_200_000; // ~1.2MB
        $inlineLocalImages = true;

        foreach ($doc->getElementsByTagName('img') as $img) {
            $origSrc = $img->getAttribute('src') ?? '';

            // style + remove fixed dimensions
            $existing = $img->getAttribute('style') ?? '';
            $extra = 'max-width:100%;height:auto;display:block;';
            $img->setAttribute('style', trim($existing . ';' . $extra, ';'));
            if ($img->hasAttribute('width')) $img->removeAttribute('width');
            if ($img->hasAttribute('height')) $img->removeAttribute('height');
            if ($img->hasAttribute('srcset')) $img->removeAttribute('srcset');
            if ($img->hasAttribute('sizes')) $img->removeAttribute('sizes');

            $src = trim($origSrc);
            if ($src === '') {
                Log::debug('[assembleJobReport] img has empty src', ['job_id'=>$jobId]);
                continue;
            }

            // normalize protocol-relative //host/path
            if (str_starts_with($src, '//')) {
                $src = (parse_url($appUrl, PHP_URL_SCHEME) ?: 'https') . ':' . $src;
            }

            // if already absolute or data:, keep
            if (preg_match('#^(https?:|data:)#i', $src)) {
                $abs = $src;
            } else {
                // turn relative into absolute using app url; keep leading slash
                $clean = '/' . ltrim($src, '/');
                $abs = $appUrl . $clean;
            }

            $inlined = false;

            if ($inlineLocalImages) {
                // try multiple local path heuristics
                $candidates = [];
                // if /f/ mapping (your custom route), try storage/app/public/<rest>
                if (str_contains($abs, $appUrl . '/f/')) {
                    $relative = substr($abs, strlen($appUrl . '/f/'));
                    $candidates[] = storage_path('app/public/' . $relative);
                    $candidates[] = public_path('storage/' . $relative);
                    $candidates[] = public_path($relative);
                }
                // if /storage/ path
                if (str_contains($abs, $appUrl . '/storage/')) {
                    $relative = substr($abs, strlen($appUrl . '/storage/'));
                    $candidates[] = storage_path('app/public/' . $relative);
                    $candidates[] = public_path('storage/' . $relative);
                }
                // final fallback: strip domain and try as public path
                $uriPath = parse_url($abs, PHP_URL_PATH);
                if ($uriPath) {
                    $candidates[] = public_path(ltrim($uriPath, '/'));
                    $candidates[] = storage_path('app/public/' . ltrim($uriPath, '/'));
                }

                // remove duplicates
                $candidates = array_unique($candidates);

                // attempt to find a readable file
                foreach ($candidates as $local) {
                    if (!$local) continue;
                    if (is_file($local) && is_readable($local)) {
                        $size = filesize($local);
                        Log::debug('[assembleJobReport] img candidate found', ['job_id'=>$jobId,'orig'=>$origSrc,'local'=>$local,'size'=>$size]);
                        if ($size <= $inlineMaxBytes) {
                            $data = @file_get_contents($local);
                            if ($data !== false) {
                                $mime = @mime_content_type($local) ?: 'application/octet-stream';
                                $img->setAttribute('src', 'data:' . $mime . ';base64,' . base64_encode($data));
                                $inlined = true;
                            }
                        } else {
                            // too big to inline, use absolute URL so Word can try to fetch
                            $img->setAttribute('src', $abs);
                            $inlined = false;
                        }
                        break;
                    } else {
                        Log::debug('[assembleJobReport] img candidate missing', ['job_id'=>$jobId,'candidate'=>$local,'orig'=>$origSrc]);
                    }
                }

                // if no local file matched, set absolute URL
                if (!$inlined && !$img->getAttribute('src')) {
                    $img->setAttribute('src', $abs);
                }
            } else {
                $img->setAttribute('src', $abs);
            }
        }

        // extract back body html
        $body = $doc->getElementsByTagName('body')->item(0);
        $newHtml = '';
        foreach ($body->childNodes as $child) {
            $newHtml .= $doc->saveHTML($child);
        }
        $report['job']['description'] = $newHtml;
        libxml_clear_errors();
    } catch (\Throwable $e) {
        Log::warning('[assembleJobReport] normalize imgs failed', ['job_id'=>$jobId,'err'=>$e->getMessage()]);
    }
}

        // assignees
        if ($opts['include_assignees']) {
            try {
                Log::debug('[assembleJobReport] Fetching assignees', ['job_id' => $jobId]);

                // NOTE: avoid selecting ap.phone directly (some installs may not have that column).
                // select core fields + mapping fields from job_assignees
                $rows = DB::table('job_assignees as ja')
                    ->join('assigned_people as ap','ap.id','=','ja.assigned_person_id')
                    ->where('ja.job_id',$jobId)
                    ->select(
                        'ap.id as id',
                        'ap.name as name',
                        'ap.email as email',
                        // 'ap.phone' omitted intentionally for schema safety
                        'ja.status as map_status',
                        'ja.assigned_at',
                        'ja.unassigned_at',
                        'ja.note'
                    )
                    ->orderBy('ap.name')
                    ->get();

                // map rows to arrays and copy phone if present (defensive)
                $assignees = $rows->map(function($r){
                    $arr = (array)$r;
                    // keep phone if present on object (defensive)
                    if (property_exists($r, 'phone')) {
                        $arr['phone'] = $r->phone;
                    } else {
                        $arr['phone'] = $arr['phone'] ?? null;
                    }
                    return $arr;
                })->all();

                $report['assignees'] = $assignees;
                $report['assignees_count'] = is_array($assignees) ? count($assignees) : 0;
                // also attach to top-level job representation so blades using job.assignees_count find it
                $report['job']['assignees_count'] = $report['assignees_count'];

                Log::debug('[assembleJobReport] Assignees fetched', [
                    'job_id' => $jobId,
                    'count' => $report['assignees_count']
                ]);
            } catch (\Throwable $e) {
                Log::error('[assembleJobReport] Failed to fetch assignees', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                $report['assignees'] = [];
                $report['assignees_count'] = 0;
                $report['job']['assignees_count'] = 0;
            }
        } else {
            // ensure keys exist even when assignees are not included
            $report['assignees'] = $report['assignees'] ?? [];
            $report['assignees_count'] = $report['assignees_count'] ?? 0;
            $report['job']['assignees_count'] = $report['job']['assignees_count'] ?? 0;
        }

        // messages summary
        if ($opts['include_messages']) {
            try {
                Log::debug('[assembleJobReport] Fetching messages', ['job_id' => $jobId]);
                $q = DB::table('job_messages')->where('job_id',$jobId)->orderBy('created_at','asc');
                $rows = $q->get();
                $messages = $rows->map(function($m){
                    return [
                        'id' => $m->id,
                        'sender_role' => $m->sender_role,
                        'sender_id' => $m->sender_id,
                        'created_at' => $m->created_at,
                        'text' => $m->message_text,
                        'html' => $m->message_html,
                        'has_attachments' => (bool)$m->has_attachments,
                        'attachments' => $m->attachments_json ? @json_decode($m->attachments_json, true) ?? [] : [],
                    ];
                })->all();

                if ($opts['rolewise']) {
                    $grouped = [];
                    foreach ($messages as $msg) {
                        $role = $msg['sender_role'] ?: 'unknown';
                        $grouped[$role][] = $msg;
                    }
                    $report['messages'] = $grouped;
                } else {
                    $report['messages'] = $messages;
                }

                $report['messages_summary'] = [
                    'count' => count($messages),
                    'first' => $rows->first()?->created_at ?? null,
                    'last'  => $rows->last()?->created_at ?? null,
                ];
                Log::debug('[assembleJobReport] Messages fetched', ['job_id' => $jobId, 'count' => count($messages)]);
            } catch (\Throwable $e) {
                Log::error('[assembleJobReport] Failed to fetch messages', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                $report['messages'] = [];
                $report['messages_summary'] = ['count' => 0, 'first' => null, 'last' => null];
            }
        }

        // media
        if ($opts['include_media']) {
            try {
                Log::debug('[assembleJobReport] Fetching media', ['job_id' => $jobId]);
                $media = DB::table('job_description_media')
                    ->where('job_id',$jobId)
                    ->orderBy('id','desc')
                    ->get()
                    ->map(fn($m)=>(array)$m)
                    ->all();
                $report['media'] = $media;
                Log::debug('[assembleJobReport] Media fetched', ['job_id' => $jobId, 'count' => count($media)]);
            } catch (\Throwable $e) {
                Log::error('[assembleJobReport] Failed to fetch media', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                $report['media'] = [];
            }
        }

        // children quick summary
        if ($opts['include_children']) {
            try {
                Log::debug('[assembleJobReport] Fetching children', ['job_id' => $jobId]);
                $children = DB::table('job_details')
                    ->where('parent_id',$jobId)
                    ->orderBy('ordering')
                    ->get()
                    ->map(fn($c)=>[
                        'id'=>$c->id,
                        'title'=>$c->title,
                        'status'=>$c->status,
                        'priority'=>$c->priority,
                        'assignees_count'=>$c->assignees_count ?? 0
                    ])->all();
                $report['children'] = $children;
                $report['children_summary'] = ['count'=>count($children)];
                Log::debug('[assembleJobReport] Children fetched', ['job_id' => $jobId, 'count' => count($children)]);
            } catch (\Throwable $e) {
                Log::error('[assembleJobReport] Failed to fetch children', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                $report['children'] = [];
                $report['children_summary'] = ['count' => 0];
            }
        }

        // activity log (recent N)
        if ($opts['include_activity']) {
            try {
                Log::debug('[assembleJobReport] Fetching activity log', ['job_id' => $jobId]);
                $activity = DB::table('user_data_activity_log')
                    ->where('table_name','job_details')
                    ->where(function($w) use ($jobId){
                        $w->where('record_id',$jobId)
                          ->orWhere('metadata','like','%"job_id":'.$jobId.'%')
                          ->orWhere('metadata','like','%\"job_id\":'.$jobId.'%');
                    })
                    ->orderBy('id','desc')
                    ->limit(200)
                    ->get()
                    ->map(function($r){
                        return [
                            'id'=>$r->id,
                            'performed_by'=>$r->performed_by,
                            'performed_by_role'=>$r->performed_by_role,
                            'activity'=>$r->activity,
                            'module'=>$r->module,
                            'record_id'=>$r->record_id,
                            'changed_fields'=> $r->changed_fields ? @json_decode($r->changed_fields,true) ?? null : null,
                            'log_note'=>$r->log_note,
                            'created_at'=>$r->created_at
                        ];
                    })->all();
                $report['activity'] = $activity;
                Log::debug('[assembleJobReport] Activity log fetched', ['job_id' => $jobId, 'count' => count($activity)]);
            } catch (\Throwable $e) {
                Log::error('[assembleJobReport] Failed to fetch activity log', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage()
                ]);
                $report['activity'] = [];
            }
        }

        // simple KPIs / aggregates
        try {
            $report['kpis'] = [
                'open_children' => count(array_filter($report['children'] ?? [], fn($c)=>($c['status'] ?? '') !== 'completed')),
                'attachments_total' => $this->countJobAttachments($jobId),
                'assignees' => $report['assignees_count'] ?? count($report['assignees'] ?? []),
            ];
            Log::debug('[assembleJobReport] KPIs calculated', ['job_id' => $jobId, 'kpis' => $report['kpis']]);
        } catch (\Throwable $e) {
            Log::error('[assembleJobReport] Failed to calculate KPIs', [
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ]);
            $report['kpis'] = [
                'open_children' => 0,
                'attachments_total' => 0,
                'assignees' => $report['assignees_count'] ?? 0,
            ];
        }

        Log::info('[assembleJobReport] Completed successfully', [
            'job_id' => $jobId,
            'sections' => array_keys($report)
        ]);

        return $report;

    } catch (\Throwable $e) {
        Log::error('[assembleJobReport] Unexpected error', [
            'job_id' => $jobId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e; // Re-throw to be caught by the main method
    }
}

/**
 * Resolve FCM tokens for a list of receivers.
 * Supports either:
 *  - fcm_tokens table (recommended), or
 *  - fcm_token column in role tables (admins / assigned_people / users)
 */
/**
 * Resolve FCM tokens for a list of receivers based on your actual table structure.
 * Your system uses separate tables: fcm_tokens_admin and fcm_tokens_assignee
 */
private function resolveFcmTokensForReceivers(array $receivers): array
{
    Log::debug('[FCM] Resolving tokens for receivers', [
        'receivers_count' => count($receivers),
        'receivers_sample' => array_slice($receivers, 0, 3)
    ]);

    $tokens = [];

    // First, check which FCM tables exist
    $hasAdminTable = Schema::hasTable('fcm_tokens_admin') || Schema::hasTable('fcm_token_admin');
    $hasAssigneeTable = Schema::hasTable('fcm_tokens_assignee') || Schema::hasTable('fcm_token_assignee');

    // Group receivers by role
    $adminIds = [];
    $assigneeIds = [];
    
    foreach ($receivers as $r) {
        $id = (int)($r['id'] ?? 0);
        $role = strtolower((string)($r['role'] ?? ''));
        
        if ($id <= 0 || $role === '') continue;
        
        if ($role === 'admin') {
            $adminIds[] = $id;
        } elseif ($role === 'assignee') {
            $assigneeIds[] = $id;
        }
    }

    // Remove duplicates
    $adminIds = array_values(array_unique($adminIds));
    $assigneeIds = array_values(array_unique($assigneeIds));

    // Fetch admin tokens
    if (!empty($adminIds) && $hasAdminTable) {
        // Try both table name variations
        $adminTable = Schema::hasTable('fcm_tokens_admin') ? 'fcm_tokens_admin' : 'fcm_token_admin';
        
        $rows = DB::table($adminTable)
            ->whereIn('user_id', $adminIds)
            ->where('is_active', true)
            ->whereNotNull('fcm_admin')  // Your FCM controller uses this column name
            ->pluck('fcm_admin')
            ->all();

        Log::debug('[FCM] Admin tokens fetched', [
            'table' => $adminTable,
            'user_ids' => $adminIds,
            'tokens_found' => count($rows)
        ]);

        foreach ($rows as $t) {
            $t = trim((string)$t);
            if ($t !== '') $tokens[$t] = true;
        }
    }

    // Fetch assignee tokens
    if (!empty($assigneeIds) && $hasAssigneeTable) {
        // Try both table name variations
        $assigneeTable = Schema::hasTable('fcm_tokens_assignee') ? 'fcm_tokens_assignee' : 'fcm_token_assignee';
        
        $rows = DB::table($assigneeTable)
            ->whereIn('user_id', $assigneeIds)
            ->where('is_active', true)
            ->whereNotNull('fcm_assignee')  // Your FCM controller uses this column name
            ->pluck('fcm_assignee')
            ->all();

        Log::debug('[FCM] Assignee tokens fetched', [
            'table' => $assigneeTable,
            'user_ids' => $assigneeIds,
            'tokens_found' => count($rows)
        ]);

        foreach ($rows as $t) {
            $t = trim((string)$t);
            if ($t !== '') $tokens[$t] = true;
        }
    }

    Log::info('[FCM] Tokens resolved', [
        'unique_tokens' => count($tokens),
        'admin_receivers' => count($adminIds),
        'assignee_receivers' => count($assigneeIds)
    ]);

    return array_keys($tokens);
}

/**
 * Check which FCM tables exist and return the correct table/column names
 */
private function getFcmTableConfig(string $role): ?array
{
    $role = strtolower($role);
    
    if ($role === 'admin') {
        if (Schema::hasTable('fcm_tokens_admin')) {
            return ['table' => 'fcm_tokens_admin', 'column' => 'fcm_admin'];
        } elseif (Schema::hasTable('fcm_token_admin')) {
            return ['table' => 'fcm_token_admin', 'column' => 'fcm_admin'];
        }
    } elseif ($role === 'assignee') {
        if (Schema::hasTable('fcm_tokens_assignee')) {
            return ['table' => 'fcm_tokens_assignee', 'column' => 'fcm_assignee'];
        } elseif (Schema::hasTable('fcm_token_assignee')) {
            return ['table' => 'fcm_token_assignee', 'column' => 'fcm_assignee'];
        }
    }
    
    return null;
}

/**
 * Alternative simplified version of resolveFcmTokensForReceivers
 */
private function resolveFcmTokensForReceiversAlt(array $receivers): array
{
    $tokens = [];
    
    foreach ($receivers as $r) {
        $id = (int)($r['id'] ?? 0);
        $role = strtolower((string)($r['role'] ?? ''));
        
        if ($id <= 0 || $role === '') continue;
        
        $config = $this->getFcmTableConfig($role);
        if (!$config) continue;
        
        $rows = DB::table($config['table'])
            ->where('user_id', $id)
            ->where('is_active', true)
            ->whereNotNull($config['column'])
            ->pluck($config['column'])
            ->all();
        
        foreach ($rows as $t) {
            $t = trim((string)$t);
            if ($t !== '') $tokens[$t] = true;
        }
    }
    
    return array_keys($tokens);
}


/** Count total attachments across messages for a job (small helper) */
private function countJobAttachments(int $jobId): int
{
    try {
        $rows = DB::table('job_messages')
            ->where('job_id',$jobId)
            ->whereNotNull('attachments_json')
            ->pluck('attachments_json')
            ->all();
        
        $cnt = 0;
        foreach ($rows as $j) {
            $arr = @json_decode($j, true) ?: [];
            $cnt += count($arr);
        }
        return $cnt;
    } catch (\Throwable $e) {
        Log::error('[countJobAttachments] Failed', [
            'job_id' => $jobId,
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}
}
