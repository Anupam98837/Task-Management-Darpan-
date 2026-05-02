<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClientBillController extends Controller
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

    private function scopedClientIdsForActor(Request $request): ?array
    {
        $actor = $this->actor($request);
        return $this->scopeService->visibleClientIdsForActor($actor['role'] ?? null, (int) ($actor['id'] ?? 0));
    }

    private function ensureClientVisible(Request $request, int $clientId): void
    {
        $scopedClientIds = $this->scopedClientIdsForActor($request);
        if ($scopedClientIds !== null && !in_array($clientId, $scopedClientIds, true)) {
            throw new HttpResponseException(
                response()->json(['status' => 'error', 'message' => 'Selected client is outside your scope'], 403)
            );
        }
    }

    private function validateItems(array $items): array
    {
        return collect($items)
            ->map(function ($item, $index) {
                $headId = isset($item['client_bill_head_id']) && $item['client_bill_head_id'] !== '' ? (int) $item['client_bill_head_id'] : null;
                $headTitle = trim((string) ($item['bill_head_title'] ?? ''));
                $amount = round((float) ($item['amount'] ?? 0), 2);

                if ($headId) {
                    $head = DB::table('client_bill_heads')->where('id', $headId)->first();
                    if (!$head) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.$index.client_bill_head_id" => 'Bill head not found.',
                        ]);
                    }
                    $headTitle = (string) ($head->title ?? $headTitle);
                }

                if ($headTitle === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.$index.bill_head_title" => 'Bill head title is required.',
                    ]);
                }

                if ($amount < 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.$index.amount" => 'Amount must be zero or greater.',
                    ]);
                }

                return [
                    'client_bill_head_id' => $headId,
                    'bill_head_title' => $headTitle,
                    'amount' => $amount,
                    'ordering' => $index + 1,
                    'metadata' => !empty($item['metadata']) && is_array($item['metadata']) ? json_encode($item['metadata'], JSON_UNESCAPED_UNICODE) : null,
                ];
            })
            ->values()
            ->all();
    }

    private function syncItems(int $billId, array $items): float
    {
        DB::table('client_bill_items')->where('client_bill_id', $billId)->delete();
        if (empty($items)) {
            return 0;
        }

        $now = now();
        $total = 0;
        $rows = [];
        foreach ($items as $item) {
            $total += (float) $item['amount'];
            $rows[] = [
                'client_bill_id' => $billId,
                'client_bill_head_id' => $item['client_bill_head_id'],
                'bill_head_title' => $item['bill_head_title'],
                'amount' => $item['amount'],
                'ordering' => $item['ordering'],
                'metadata' => $item['metadata'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('client_bill_items')->insert($rows);

        return round($total, 2);
    }

    private function mapBill(object $row): object
    {
        $row->items = DB::table('client_bill_items')
            ->where('client_bill_id', $row->id)
            ->orderBy('ordering')
            ->get();
        $row->items_count = $row->items->count();
        return $row;
    }

    private function baseQuery()
    {
        return DB::table('client_bills as cb')
            ->join('clients as c', 'c.id', '=', 'cb.client_id')
            ->leftJoin('accountant_users as au', 'au.id', '=', 'cb.created_by')
            ->select(
                'cb.*',
                'c.name as client_name',
                'au.name as created_by_name'
            );
    }

    private function collectClientTreeIds(int $clientId): array
    {
        if ($clientId <= 0) {
            return [];
        }

        $seen = [];
        $queue = [$clientId];

        while (!empty($queue)) {
            $current = (int) array_shift($queue);
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

    public function index(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user', 'client_user'])) {
            return $resp;
        }

        $actor = $this->actor($request);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
        $q = trim((string) $request->query('q', ''));
        $clientId = (int) $request->query('client_id', 0);
        $publish = trim((string) $request->query('publish', ''));
        $sortBy = trim((string) $request->query('sort_by', 'bill_date'));
        $sortDir = strtolower(trim((string) $request->query('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'bill_date', 'due_date', 'total_amount', 'published_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'bill_date';

        $query = $this->baseQuery();

        $scopedClientIds = $this->scopedClientIdsForActor($request);
        if ($scopedClientIds !== null) {
            if (empty($scopedClientIds)) {
                return response()->json(['status' => 'success', 'message' => 'No scoped bills', 'data' => [], 'meta' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'total_pages' => 0, 'last_page' => 0]]);
            }
            $query->whereIn('cb.client_id', $scopedClientIds);
        }

        if ($q !== '') {
            $like = "%{$q}%";
            $query->where(function ($w) use ($like) {
                $w->where('c.name', 'LIKE', $like)
                    ->orWhere('cb.notes', 'LIKE', $like)
                    ->orWhereExists(function ($sub) use ($like) {
                        $sub->from('client_bill_items as cbi')
                            ->whereColumn('cbi.client_bill_id', 'cb.id')
                            ->where('cbi.bill_head_title', 'LIKE', $like);
                    });
            });
        }

        if ($clientId > 0) {
            $query->where('cb.client_id', $clientId);
        }

        if (($actor['role'] ?? null) === 'client_user') {
            $query->where('cb.is_published', true);
        } elseif ($publish === 'published') {
            $query->where('cb.is_published', true);
        } elseif ($publish === 'draft') {
            $query->where('cb.is_published', false);
        }

        $total = (clone $query)->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        if ($totalPages === 0) $page = 1;
        if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

        $rows = $query
            ->orderBy("cb.$sortBy", $sortDir)
            ->orderBy('cb.id', $sortDir)
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($row) {
                $row->items_count = DB::table('client_bill_items')->where('client_bill_id', $row->id)->count();
                return $row;
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Client bills fetched',
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'last_page' => $totalPages,
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user', 'client_user'])) {
            return $resp;
        }

        $row = $this->baseQuery()->where('cb.id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }

        $this->ensureClientVisible($request, (int) $row->client_id);
        if (($this->actor($request)['role'] ?? null) === 'client_user' && !(bool) $row->is_published) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $this->mapBill($row)]);
    }

    public function analysis(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $clientId = (int) $request->query('client_id', 0);
        if ($clientId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'client_id is required'], 422);
        }

        $this->ensureClientVisible($request, $clientId);

        $client = DB::table('clients')->where('id', $clientId)->first();
        if (!$client) {
            return response()->json(['status' => 'error', 'message' => 'Client not found'], 404);
        }

        $treeIds = $this->collectClientTreeIds($clientId);
        $jobsBase = DB::table('job_details as j')->whereIn('j.client_id', $treeIds);
        $expensesBase = DB::table('job_expenses as e')
            ->join('job_details as j', 'j.id', '=', 'e.job_id')
            ->leftJoin('expense_heads as eh', 'eh.id', '=', 'e.expense_head_id')
            ->whereIn('j.client_id', $treeIds);

        $expenses = (clone $expensesBase)
            ->select(
                'e.id',
                'e.job_id',
                'j.title as job_title',
                'j.client_id',
                'eh.title as expense_head_title',
                'e.expense_date',
                'e.amount',
                'e.currency',
                'e.note',
                'e.created_by',
                'e.created_at'
            )
            ->orderBy('e.expense_date', 'desc')
            ->orderBy('e.id', 'desc')
            ->limit(200)
            ->get();

        $previousBills = $this->baseQuery()
            ->whereIn('cb.client_id', $treeIds)
            ->orderBy('cb.bill_date', 'desc')
            ->orderBy('cb.id', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $row->items_count = DB::table('client_bill_items')->where('client_bill_id', $row->id)->count();
                return $row;
            });

        $stats = [
            'tree_client_count' => count($treeIds),
            'jobs_count' => (clone $jobsBase)->count(),
            'jobs_with_budget_count' => (clone $jobsBase)->whereNotNull('j.budget')->count(),
            'total_budget' => round((float) ((clone $jobsBase)->sum('j.budget') ?? 0), 2),
            'total_expense_amount' => round((float) ((clone $expensesBase)->sum('e.amount') ?? 0), 2),
            'expense_count' => (clone $expensesBase)->count(),
            'total_billed_amount' => round((float) (DB::table('client_bills')->whereIn('client_id', $treeIds)->sum('total_amount') ?? 0), 2),
            'published_billed_amount' => round((float) (DB::table('client_bills')->whereIn('client_id', $treeIds)->where('is_published', true)->sum('total_amount') ?? 0), 2),
            'draft_bill_count' => DB::table('client_bills')->whereIn('client_id', $treeIds)->where('is_published', false)->count(),
            'published_bill_count' => DB::table('client_bills')->whereIn('client_id', $treeIds)->where('is_published', true)->count(),
            'remaining_budget' => round(
                round((float) ((clone $jobsBase)->sum('j.budget') ?? 0), 2)
                - round((float) ((clone $expensesBase)->sum('e.amount') ?? 0), 2),
                2
            ),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Client bill analysis fetched',
            'data' => [
                'client' => $client,
                'tree_client_ids' => $treeIds,
                'stats' => $stats,
                'expenses' => $expenses,
                'previous_bills' => $previousBills,
            ],
        ]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.client_bill_head_id' => ['nullable', 'integer', 'exists:client_bill_heads,id'],
            'items.*.bill_head_title' => ['nullable', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
        ]);

        $this->ensureClientVisible($request, (int) $data['client_id']);
        $items = $this->validateItems($data['items']);
        $actor = $this->actor($request);
        $now = now();

        DB::beginTransaction();
        try {
            $billId = DB::table('client_bills')->insertGetId([
                'client_id' => $data['client_id'],
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'is_published' => false,
                'published_at' => null,
                'total_amount' => 0,
                'metadata' => !empty($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor['id'] ?: null,
                'created_by_role' => $actor['role'] ?: null,
                'updated_by' => $actor['id'] ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $total = $this->syncItems($billId, $items);
            DB::table('client_bills')->where('id', $billId)->update([
                'total_amount' => $total,
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ClientBillController@store] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to create client bill'], 500);
        }

        $row = $this->baseQuery()->where('cb.id', $billId)->first();
        return response()->json(['status' => 'success', 'message' => 'Client bill created', 'data' => $row ? $this->mapBill($row) : null], 201);
    }

    public function update(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bills')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }
        $this->ensureClientVisible($request, (int) $existing->client_id);
        if ((bool) $existing->is_published) {
            return response()->json(['status' => 'error', 'message' => 'Published bills cannot be edited'], 422);
        }

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.client_bill_head_id' => ['nullable', 'integer', 'exists:client_bill_heads,id'],
            'items.*.bill_head_title' => ['nullable', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
        ]);

        $this->ensureClientVisible($request, (int) $data['client_id']);
        $items = $this->validateItems($data['items']);
        $actor = $this->actor($request);

        DB::beginTransaction();
        try {
            DB::table('client_bills')->where('id', $id)->update([
                'client_id' => $data['client_id'],
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'metadata' => !empty($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : null,
                'notes' => $data['notes'] ?? null,
                'updated_by' => $actor['id'] ?: null,
                'updated_at' => now(),
            ]);

            $total = $this->syncItems($id, $items);
            DB::table('client_bills')->where('id', $id)->update([
                'total_amount' => $total,
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ClientBillController@update] failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update client bill'], 500);
        }

        $row = $this->baseQuery()->where('cb.id', $id)->first();
        return response()->json(['status' => 'success', 'message' => 'Client bill updated', 'data' => $row ? $this->mapBill($row) : null]);
    }

    public function publish(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bills')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }
        $this->ensureClientVisible($request, (int) $existing->client_id);
        if ((bool) $existing->is_published) {
            return response()->json(['status' => 'error', 'message' => 'Bill is already published'], 422);
        }

        DB::table('client_bills')->where('id', $id)->update([
            'is_published' => true,
            'published_at' => now(),
            'updated_at' => now(),
            'updated_by' => (int) ($this->actor($request)['id'] ?? 0) ?: null,
        ]);

        $row = $this->baseQuery()->where('cb.id', $id)->first();
        return response()->json(['status' => 'success', 'message' => 'Client bill published', 'data' => $row ? $this->mapBill($row) : null]);
    }

    public function destroy(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bills')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }
        $this->ensureClientVisible($request, (int) $existing->client_id);
        if ((bool) $existing->is_published) {
            return response()->json(['status' => 'error', 'message' => 'Published bills cannot be deleted'], 422);
        }

        DB::table('client_bills')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Client bill deleted']);
    }
}
