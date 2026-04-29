<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    private const TYPES = ['task', 'milestone', 'bug', 'feature', 'epic', 'other'];
    private const PRIORITY = ['lowest', 'low', 'normal', 'high', 'urgent'];
    private const STATUS = ['draft', 'planned', 'in_progress', 'on_hold', 'blocked', 'completed', 'cancelled'];

    private function requireRole(Request $r, array $allowed)
    {
        $role = $r->attributes->get('auth_role');
        if (!$role || !in_array($role, $allowed, true)) {
            return response()->json(['status'=>'error','message'=>'Unauthorized Access'], 403);
        }
        return null;
    }

    private function actor(Request $request)
    {
        // Try auth_id first (for admin), then auth_tokenable_id (for assignee)
        $id = $request->attributes->get('auth_id') 
              ?? $request->attributes->get('auth_tokenable_id');
              
        return [
            'id' => $id,
            'role' => $request->attributes->get('auth_role'),
            'tokenable_type' => $request->attributes->get('auth_tokenable_type'),
        ];
    }

    public function __invoke(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        try {
            $recentLimit = min(50, max(1, (int)$request->query('recent_limit', 10)));
            $period      = $request->query('period', '30d'); // today|7d|30d|60d|90d

            // --- KPIs ---
            $totalClients        = DB::table('clients')->count();
            $totalAssignedPeople = DB::table('assigned_people')->count();
            $totalJobsCreated    = DB::table('job_details')->count();

            // Completed jobs: status = 'completed'
            $totalJobsCompleted  = DB::table('job_details')->where('status', 'completed')->count();

            // In Progress jobs: status = 'in_progress'
            $totalJobsInProgress = DB::table('job_details')->where('status', 'in_progress')->count();

            // Pending jobs: all other statuses (exclude completed and in_progress)
            $totalJobsPending    = DB::table('job_details')->where('status', 'pending')->count();

            $totalAssignedJobs = DB::table('job_details as j')
                ->whereExists(function($q){
                    $q->from('job_assignees as ja')
                      ->whereColumn('ja.job_id','j.id')
                      ->where('ja.status','active');
                })->count();

            $totalUnassignedJobs = DB::table('job_details as j')
                ->whereNotExists(function($q){
                    $q->from('job_assignees as ja')
                      ->whereColumn('ja.job_id','j.id')
                      ->where('ja.status','active');
                })->count();

            $quickLinks = [
                'total_clients'             => $totalClients,
                'total_assigned_people'     => $totalAssignedPeople,
                'total_jobs_created'        => $totalJobsCreated,
                'total_jobs_completed'      => $totalJobsCompleted,
                'total_jobs_in_progress'    => $totalJobsInProgress,
                'total_jobs_pending'        => $totalJobsPending,
                'total_assigned_jobs'       => $totalAssignedJobs,
                'total_unassigned_jobs'     => $totalUnassignedJobs,
            ];

            // --- Recent activity (summary) ---
            $recentActivity = DB::table('user_data_activity_log')
                ->select('activity','module','table_name','record_id','performed_by','performed_by_role','log_note','created_at')
                ->orderBy('created_at','desc')->orderBy('id','desc')
                ->limit($recentLimit)
                ->get();

            // --- High priority ---
            $hpBase = DB::table('job_details as j')
                ->leftJoin('clients as c', 'c.id', '=', 'j.client_id')
                ->leftJoin('job_assignees as ja', function ($join) {
                    $join->on('ja.job_id', '=', 'j.id')->where('ja.status', '=', 'active');
                })
                ->whereIn('j.priority', ['urgent','high']);

            $highPriorityStatusDistribution = (clone $hpBase)
                ->select('j.status', DB::raw('COUNT(DISTINCT j.id) as count'))
                ->groupBy('j.status')
                ->orderBy('count','desc')
                ->get();

            $highPriorityOpen = (clone $hpBase)
                ->whereNotIn('j.status',['completed','cancelled'])
                ->select(
                    'j.id','j.title','j.priority','j.status','j.client_id',
                    'j.planned_deadline_at','j.created_at',
                    DB::raw('COALESCE(c.name, "") as client_name'),
                    DB::raw('COUNT(DISTINCT ja.assigned_person_id) as assignees_count')
                )
                ->groupBy('j.id','j.title','j.priority','j.status','j.client_id',
                          'j.planned_deadline_at','j.created_at','c.name')
                ->orderByRaw("FIELD(j.priority,'urgent','high')")
                ->orderBy('j.planned_deadline_at','asc')
                ->orderBy('j.created_at','desc')
                ->limit(20)
                ->get();

            // --- Windows ---
            $windows = [
                'today' => [Carbon::today(), Carbon::now()],
                '7d'    => [Carbon::now()->copy()->subDays(6)->startOfDay(), Carbon::now()],
                '30d'   => [Carbon::now()->copy()->subDays(29)->startOfDay(), Carbon::now()],
                '60d'   => [Carbon::now()->copy()->subDays(59)->startOfDay(), Carbon::now()],
                '90d'   => [Carbon::now()->copy()->subDays(89)->startOfDay(), Carbon::now()],
            ];
            if (!isset($windows[$period])) $period = '30d';
            [$start, $end] = $windows[$period];

            // --- Jobs Created: day-wise counts within window (zero-filled) ---
            $dailyCreatedRows = DB::table('job_details')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as created')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy(fn($r) => Carbon::parse($r->d)->toDateString());

            $cursor = $start->copy()->startOfDay();
            $createdDaily = [];
            while ($cursor->lte($end)) {
                $key = $cursor->toDateString();
                $createdDaily[$key] = (int)($dailyCreatedRows[$key]->created ?? 0);
                $cursor->addDay();
            }

            // --- Assigned vs Unassigned snapshot ---
            $assignedVsUnassigned = [
                'assigned'   => (int) $totalAssignedJobs,
                'unassigned' => (int) $totalUnassignedJobs,
            ];

            return response()->json([
                'status'  => 'success',
                'message' => 'Dashboard data',
                'data'    => [
                    'quick_links'     => $quickLinks,
                    'recent_activity' => $recentActivity,
                    'high_priority'   => [
                        'status_distribution' => $highPriorityStatusDistribution,
                        'open' => $highPriorityOpen,
                    ],
                    'charts' => [
                        'period'        => $period,
                        'date_range'    => ['start'=>$start->toDateString(), 'end'=>$end->toDateString()],
                        'created_daily' => $createdDaily,
                        'assigned_vs_unassigned' => $assignedVsUnassigned,
                    ],
                    'generated_at' => Carbon::now()->toDateTimeString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[AdminDashboard] failed', ['error'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed to fetch dashboard'], 500);
        }
    }

    public function recentActivity(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin'])) return $resp;

        $limit  = min(100, max(10, (int)$request->query('limit', 30)));
        $cursor = $request->query('cursor');

        $q = DB::table('user_data_activity_log')
            ->select('id','activity','module','table_name','record_id','performed_by','performed_by_role','log_note','created_at')
            ->orderBy('created_at','desc')->orderBy('id','desc');

        if ($cursor) {
            [$cAt, $cId] = explode('|', $cursor) + [null, null];
            if ($cAt && $cId) {
                $q->where(function ($w) use ($cAt, $cId) {
                    $w->where('created_at', '<', $cAt)
                      ->orWhere(function ($w2) use ($cAt, $cId) {
                          $w2->where('created_at', '=', $cAt)->where('id', '<', (int)$cId);
                      });
                });
            }
        }

        $rows = $q->limit($limit)->get();
        $next = null;
        if ($rows->count() === $limit) {
            $last = $rows->last();
            $next = $last->created_at.'|'.$last->id;
        }

        return response()->json(['status'=>'success','data'=>$rows,'next_cursor'=>$next]);
    }
/**
     * MY JOBS (GET /api/assignedpeople/my-jobs)
     * For assignees to view their assigned jobs
     */
    public function myJobs(Request $request)
    {
        $actor = $this->actor($request);
        
        // Debug logging
        Log::info('[myJobs] Actor info', [
            'actor' => $actor,
            'all_attributes' => $request->attributes->all(),
            'auth_header' => $request->header('Authorization')
        ]);
        
        // Check for assignee role
        if ($actor['role'] !== 'assignee') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access. Expected role: assignee, Got: ' . ($actor['role'] ?? 'none'),
                'debug' => [
                    'actor' => $actor,
                    'attributes' => $request->attributes->all()
                ]
            ], 403);
        }

        $assignedPersonId = $actor['id'];
        
        if (!$assignedPersonId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assigned person ID not found',
                'debug' => [
                    'actor' => $actor,
                    'attributes' => $request->attributes->all(),
                    'auth_id' => $request->attributes->get('auth_id'),
                    'auth_role' => $request->attributes->get('auth_role')
                ]
            ], 401);
        }

        try {
            // Pagination & filters
            $page     = max(1, (int)$request->query('page', 1));
            $perPage  = min(100, max(1, (int)$request->query('per_page', 10)));
            $q        = trim((string)$request->query('q', ''));
            $clientId = (int)$request->query('client_id', 0);
            $type     = trim((string)$request->query('type', ''));
            $priority = trim((string)$request->query('priority', ''));
            $status   = trim((string)$request->query('status', ''));
            $sort     = strtolower(trim((string)$request->query('sort', 'desc')));

            // Base query: jobs where this person is actively assigned
            $qb = DB::table('job_details as j')
                ->join('job_assignees as ja', function($j1) use ($assignedPersonId) {
                    $j1->on('ja.job_id', '=', 'j.id')
                       ->where('ja.assigned_person_id', '=', $assignedPersonId)
                       ->where('ja.status', '=', 'active');
                })
                ->leftJoin('clients as c', 'c.id', '=', 'j.client_id')
                ->leftJoin('documents as d', 'd.id', '=', 'j.document_id')
                ->select(
                    'j.*',
                    'c.name as client_name',
                    'd.doc_name as document_name',
                    'ja.assigned_at'
                );

            // Apply filters
            if ($q !== '') {
                $like = "%{$q}%";
                $qb->where(function($w) use ($like) {
                    $w->where('j.title', 'LIKE', $like)
                      ->orWhere('j.description', 'LIKE', $like);
                });
            }
            if ($clientId > 0) {
                $qb->where('j.client_id', $clientId);
            }
            if ($type !== '' && in_array($type, self::TYPES, true)) {
                $qb->where('j.type', $type);
            }
            if ($priority !== '' && in_array($priority, self::PRIORITY, true)) {
                $qb->where('j.priority', $priority);
            }
            if ($status !== '' && in_array($status, self::STATUS, true)) {
                $qb->where('j.status', $status);
            }

            // Count total
            $total = (clone $qb)->count();

            // Get paginated items
            $items = $qb->orderBy('j.created_at', $sort === 'asc' ? 'asc' : 'desc')
                        ->orderBy('j.id', $sort === 'asc' ? 'asc' : 'desc')
                        ->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

            // Calculate stats
            $today = now()->toDateString();
            $statsQb = DB::table('job_details as j')
                ->join('job_assignees as ja', function($j1) use ($assignedPersonId) {
                    $j1->on('ja.job_id', '=', 'j.id')
                       ->where('ja.assigned_person_id', '=', $assignedPersonId)
                       ->where('ja.status', '=', 'active');
                });

            $stats = [
                'assigned'  => $total,
                'due_today' => (clone $statsQb)->whereDate('j.planned_deadline_at', $today)->count(),
                'overdue'   => (clone $statsQb)
                    ->where('j.planned_deadline_at', '<', now())
                    ->whereNotIn('j.status', ['completed', 'cancelled'])
                    ->count(),
                'completed' => (clone $statsQb)->where('j.status', 'completed')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'My jobs fetched',
                'data' => $items,
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $perPage),
                    'stats' => $stats,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('[AssigneeDashboard] myJobs failed', [
                'error' => $e->getMessage(),
                'assigned_person_id' => $assignedPersonId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch jobs: ' . $e->getMessage()
            ], 500);
        }
    }

   /**
 * MY COMPLETION STATS (GET /api/assignedpeople/my-completion-stats)
 * For assignees to view their day-wise completion statistics
 */
public function myCompletionStats(Request $request)
{
    $actor = $this->actor($request);
    
    // Check for assignee role
    if ($actor['role'] !== 'assignee') {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized Access',
        ], 403);
    }

    $assignedPersonId = $actor['id'];
    
    if (!$assignedPersonId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Assigned person ID not found',
        ], 401);
    }

    try {
        $period = $request->query('period', '30d'); // 7d|30d|60d|90d
        
        // Define time windows
        $windows = [
            '7d'  => [Carbon::now()->copy()->subDays(6)->startOfDay(), Carbon::now()],
            '30d' => [Carbon::now()->copy()->subDays(29)->startOfDay(), Carbon::now()],
            '60d' => [Carbon::now()->copy()->subDays(59)->startOfDay(), Carbon::now()],
            '90d' => [Carbon::now()->copy()->subDays(89)->startOfDay(), Carbon::now()],
        ];
        
        if (!isset($windows[$period])) $period = '30d';
        [$start, $end] = $windows[$period];

        // Fetch day-wise completion counts
        // Using the date when the job status was changed to 'completed'
        // We'll use updated_at as proxy since we don't have a dedicated completion_date field
        $dailyCompletedRows = DB::table('job_details as j')
            ->join('job_assignees as ja', function($join) use ($assignedPersonId) {
                $join->on('ja.job_id', '=', 'j.id')
                     ->where('ja.assigned_person_id', '=', $assignedPersonId)
                     ->where('ja.status', '=', 'active');
            })
            ->selectRaw('DATE(j.updated_at) as d, COUNT(*) as completed')
            ->where('j.status', 'completed')
            ->whereBetween('j.updated_at', [$start, $end])
            ->groupBy(DB::raw('DATE(j.updated_at)'))
            ->orderBy(DB::raw('DATE(j.updated_at)'))
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->d)->toDateString());

        // Zero-fill for all days in range
        $cursor = $start->copy()->startOfDay();
        $completedDaily = [];
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $completedDaily[] = [
                'date' => $key,
                'completed' => (int)($dailyCompletedRows[$key]->completed ?? 0),
            ];
            $cursor->addDay();
        }

        // Summary stats for the period
        $totalCompleted = DB::table('job_details as j')
            ->join('job_assignees as ja', function($join) use ($assignedPersonId) {
                $join->on('ja.job_id', '=', 'j.id')
                     ->where('ja.assigned_person_id', '=', $assignedPersonId)
                     ->where('ja.status', '=', 'active');
            })
            ->where('j.status', 'completed')
            ->whereBetween('j.updated_at', [$start, $end])
            ->count();

        $avgPerDay = count($completedDaily) > 0 
            ? round($totalCompleted / count($completedDaily), 2) 
            : 0;

        // Job distribution stats (for pie chart)
        $baseStatsQuery = DB::table('job_details as j')
            ->join('job_assignees as ja', function($join) use ($assignedPersonId) {
                $join->on('ja.job_id', '=', 'j.id')
                     ->where('ja.assigned_person_id', '=', $assignedPersonId)
                     ->where('ja.status', '=', 'active');
            });

        $completedCount = (clone $baseStatsQuery)
            ->where('j.status', 'completed')
            ->count();

        $overdueCount = (clone $baseStatsQuery)
            ->where('j.planned_deadline_at', '<', Carbon::now())
            ->whereNotIn('j.status', ['completed', 'cancelled'])
            ->count();

        // === FIXED: count only rows where status = 'in_progress' ===
        $inProgressCount = (clone $baseStatsQuery)
            ->where('j.status', 'in_progress')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Completion stats fetched',
            'data' => [
                'period' => $period,
                'date_range' => [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ],
                'daily_completed' => $completedDaily,
                'summary' => [
                    'total_completed' => $totalCompleted,
                    'average_per_day' => $avgPerDay,
                    'days_count' => count($completedDaily),
                ],
                'distribution' => [
                    'completed' => $completedCount,
                    'overdue' => $overdueCount,
                    'in_progress' => $inProgressCount,
                ],
            ],
        ]);

    } catch (\Throwable $e) {
        Log::error('[AssigneeDashboard] myCompletionStats failed', [
            'error' => $e->getMessage(),
            'assigned_person_id' => $assignedPersonId,
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch completion stats',
        ], 500);
    }
}
 /**
 * STATUS STATS (GET /api/assignedpeople/status-stats)
 * For assignees to view their job status distribution for pie chart
 */
public function statusStats(Request $request)
{
    $actor = $this->actor($request);
    
    // Check for assignee role
    if ($actor['role'] !== 'assignee') {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized Access',
        ], 403);
    }

    $assignedPersonId = $actor['id'];
    
    if (!$assignedPersonId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Assigned person ID not found',
        ], 401);
    }

    try {
        // Base query for this assignee's active jobs
        $baseQuery = DB::table('job_details as j')
            ->join('job_assignees as ja', function($join) use ($assignedPersonId) {
                $join->on('ja.job_id', '=', 'j.id')
                     ->where('ja.assigned_person_id', '=', $assignedPersonId)
                     ->where('ja.status', '=', 'active');
            });

        // Total assigned jobs (all active assignments)
        $assigned = (clone $baseQuery)->count();

        // Overdue jobs (deadline passed but not completed/cancelled)
        $overdue = (clone $baseQuery)
            ->where('j.planned_deadline_at', '<', Carbon::now())
            ->whereNotIn('j.status', ['completed', 'cancelled'])
            ->count();

        // Completed jobs
        $completed = (clone $baseQuery)
            ->where('j.status', 'completed')
            ->count();

        // === FIXED: In Progress jobs counted only when status = 'in_progress' ===
        $inProgress = (clone $baseQuery)
            ->where('j.status', 'in_progress')
            ->count();

        // On Hold jobs
        $onHold = (clone $baseQuery)
            ->where('j.status', 'on_hold')
            ->count();

        // Planned jobs (not yet started)
        $planned = (clone $baseQuery)
            ->where('j.status', 'planned')
            ->count();

        // Additional status breakdown
        $statusBreakdown = (clone $baseQuery)
            ->select('j.status', DB::raw('COUNT(*) as count'))
            ->groupBy('j.status')
            ->orderBy('count', 'desc')
            ->get()
            ->keyBy('status')
            ->map(fn($item) => (int)$item->count);

        return response()->json([
            'status' => 'success',
            'message' => 'Status stats fetched',
            'data' => [
                'assigned' => $assigned,
                'overdue' => $overdue,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'on_hold' => $onHold,
                'planned' => $planned,
                'status_breakdown' => $statusBreakdown,
                'generated_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);

    } catch (\Throwable $e) {
        Log::error('[AssigneeDashboard] statusStats failed', [
            'error' => $e->getMessage(),
            'assigned_person_id' => $assignedPersonId,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch status stats: ' . $e->getMessage(),
        ], 500);
    }
}
}