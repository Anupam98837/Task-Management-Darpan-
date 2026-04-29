<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientUserDashboardController extends Controller
{
    public function __construct(private ClientUserScopeService $scopeService)
    {
    }

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $request, array $allowed)
    {
        $actor = $this->actor($request);
        if (!$actor['role'] || !in_array($actor['role'], $allowed, true)) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        return null;
    }

    public function __invoke(Request $request)
    {
        if ($resp = $this->requireRole($request, ['client_user'])) {
            return $resp;
        }

        $actor = $this->actor($request);
        $clientIds = $this->scopeService->visibleClientIdsForUser($actor['id']);

        try {
            if (empty($clientIds)) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Dashboard data',
                    'data'    => [
                        'quick_links' => [
                            'scoped_clients' => 0,
                            'visible_jobs'   => 0,
                            'due_today'      => 0,
                            'overdue'        => 0,
                            'completed'      => 0,
                        ],
                        'recent_jobs' => [],
                    ],
                ]);
            }

            $jobsBase = DB::table('job_details as j')->whereIn('j.client_id', $clientIds);

            $recentJobs = DB::table('job_details as j')
                ->leftJoin('clients as c', 'c.id', '=', 'j.client_id')
                ->leftJoin(
                    DB::raw("(SELECT job_id, COUNT(*) AS assignees_count
                              FROM job_assignees
                              WHERE status = 'active'
                              GROUP BY job_id) jacc"),
                    'jacc.job_id',
                    '=',
                    'j.id'
                )
                ->whereIn('j.client_id', $clientIds)
                ->orderBy('j.created_at', 'desc')
                ->orderBy('j.id', 'desc')
                ->limit(10)
                ->get([
                    'j.id',
                    'j.title',
                    'j.status',
                    'j.priority',
                    'j.type',
                    'j.client_id',
                    'j.planned_deadline_at',
                    'j.created_at',
                    'c.name as client_name',
                    DB::raw('COALESCE(jacc.assignees_count, 0) as assignees_count'),
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Dashboard data',
                'data'    => [
                    'quick_links' => [
                        'scoped_clients' => count($clientIds),
                        'visible_jobs'   => (clone $jobsBase)->count(),
                        'due_today'      => (clone $jobsBase)->whereDate('planned_deadline_at', now()->toDateString())->count(),
                        'overdue'        => (clone $jobsBase)
                            ->where('planned_deadline_at', '<', now())
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->count(),
                        'completed'      => (clone $jobsBase)->where('status', 'completed')->count(),
                    ],
                    'recent_jobs' => $recentJobs,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[ClientUserDashboard] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch dashboard'], 500);
        }
    }
}
