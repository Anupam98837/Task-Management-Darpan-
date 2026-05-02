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
        if ($resp = $this->requireRole($request, ['client_user', 'accountant_user'])) {
            return $resp;
        }

        $actor = $this->actor($request);
        $clientIds = $this->scopeService->visibleClientIdsForActor($actor['role'] ?? null, $actor['id']) ?? [];

        try {
            if (empty($clientIds)) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Dashboard data',
                    'data'    => [
                        'quick_links' => [
                            'scoped_clients' => 0,
                            'visible_jobs'   => 0,
                        'visible_documents' => 0,
                        'due_today'      => 0,
                        'overdue'        => 0,
                        'completed'      => 0,
                        'published_bills' => 0,
                        ],
                        'recent_jobs' => [],
                        'recent_documents' => [],
                        'recent_bills' => [],
                        'all_published_bills' => [],
                    ],
                ]);
            }

            $jobsBase = DB::table('job_details as j')->whereIn('j.client_id', $clientIds);
            $documentsBase = DB::table('documents as d')->whereIn('d.client_id', $clientIds);

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

            $recentDocuments = DB::table('documents as d')
                ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
                ->leftJoin('document_types as dt', 'dt.id', '=', 'd.document_type_id')
                ->whereIn('d.client_id', $clientIds)
                ->orderBy('d.created_at', 'desc')
                ->orderBy('d.id', 'desc')
                ->limit(10)
                ->get([
                    'd.id',
                    'd.doc_name',
                    'd.file_url',
                    'd.status',
                    'd.issue_date',
                    'd.expiry_date',
                    'd.issuing_authority',
                    'd.client_id',
                    'd.created_at',
                    'c.name as client_name',
                    'dt.name as document_type_name',
                ]);

            $recentBills = DB::table('client_bills as cb')
                ->leftJoin('clients as c', 'c.id', '=', 'cb.client_id')
                ->whereIn('cb.client_id', $clientIds)
                ->where('cb.is_published', true)
                ->orderBy('cb.published_at', 'desc')
                ->orderBy('cb.id', 'desc')
                ->limit(10)
                ->get([
                    'cb.id',
                    'cb.client_id',
                    'cb.bill_date',
                    'cb.due_date',
                    'cb.total_amount',
                    'cb.published_at',
                    'c.name as client_name',
                ]);

            $allPublishedBills = DB::table('client_bills as cb')
                ->leftJoin('clients as c', 'c.id', '=', 'cb.client_id')
                ->whereIn('cb.client_id', $clientIds)
                ->where('cb.is_published', true)
                ->orderBy('cb.published_at', 'desc')
                ->orderBy('cb.id', 'desc')
                ->limit(100)
                ->get([
                    'cb.id',
                    'cb.client_id',
                    'cb.bill_date',
                    'cb.due_date',
                    'cb.total_amount',
                    'cb.published_at',
                    'c.name as client_name',
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Dashboard data',
                'data'    => [
                    'quick_links' => [
                        'scoped_clients' => count($clientIds),
                        'visible_jobs'   => (clone $jobsBase)->count(),
                        'visible_documents' => (clone $documentsBase)->count(),
                        'due_today'      => (clone $jobsBase)->whereDate('planned_deadline_at', now()->toDateString())->count(),
                        'overdue'        => (clone $jobsBase)
                            ->where('planned_deadline_at', '<', now())
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->count(),
                        'completed'      => (clone $jobsBase)->where('status', 'completed')->count(),
                        'published_bills' => DB::table('client_bills')->whereIn('client_id', $clientIds)->where('is_published', true)->count(),
                    ],
                    'recent_jobs' => $recentJobs,
                    'recent_documents' => $recentDocuments,
                    'recent_bills' => $recentBills,
                    'all_published_bills' => $allPublishedBills,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[ClientUserDashboard] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch dashboard'], 500);
        }
    }

    /**
     * View-only listing of every document visible to the authenticated
     * client user (scoped to the clients assigned to them).
     *
     * Supports optional ?search= and ?client_id= filters and a simple
     * limit/offset for client-side pagination.
     */
    public function documents(Request $request)
    {
        if ($resp = $this->requireRole($request, ['client_user', 'accountant_user'])) {
            return $resp;
        }

        $actor     = $this->actor($request);
        $clientIds = $this->scopeService->visibleClientIdsForActor($actor['role'] ?? null, $actor['id']) ?? [];

        if (empty($clientIds)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Documents list',
                'data'    => [],
                'meta'    => ['total' => 0, 'limit' => 0, 'offset' => 0],
            ]);
        }

        $limit  = (int) min(200, max(10, (int) $request->query('limit', 100)));
        $offset = (int) max(0, (int) $request->query('offset', 0));
        $search = trim((string) $request->query('search', ''));
        $clientFilter = (int) $request->query('client_id', 0);

        try {
            $base = DB::table('documents as d')
                ->leftJoin('clients as c', 'c.id', '=', 'd.client_id')
                ->leftJoin('document_types as dt', 'dt.id', '=', 'd.document_type_id')
                ->whereIn('d.client_id', $clientIds);

            if ($clientFilter > 0 && in_array($clientFilter, $clientIds, true)) {
                $base->where('d.client_id', $clientFilter);
            }

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('d.doc_name', 'like', "%{$search}%")
                      ->orWhere('d.issuing_authority', 'like', "%{$search}%")
                      ->orWhere('c.name', 'like', "%{$search}%")
                      ->orWhere('dt.name', 'like', "%{$search}%");
                });
            }

            $total = (clone $base)->count();

            $rows = $base
                ->orderBy('d.created_at', 'desc')
                ->orderBy('d.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get([
                    'd.id',
                    'd.doc_name',
                    'd.file_url',
                    'd.status',
                    'd.issue_date',
                    'd.expiry_date',
                    'd.issuing_authority',
                    'd.client_id',
                    'd.created_at',
                    'c.name as client_name',
                    'dt.name as document_type_name',
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Documents list',
                'data'    => $rows,
                'meta'    => [
                    'total'  => $total,
                    'limit'  => $limit,
                    'offset' => $offset,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[ClientUserDashboard.documents] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch documents'], 500);
        }
    }
}
