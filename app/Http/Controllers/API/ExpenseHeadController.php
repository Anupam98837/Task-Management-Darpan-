<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseHeadController extends Controller
{
    /**
     * Return actor info (id, role, type) pulled from middleware attributes or Auth facade.
     *
     * Priority:
     * 1) $request->attributes (middleware like CheckRole)
     * 2) Auth::user() fallback
     *
     * Always returns ['role'=>string|null, 'type'=>string|null, 'id'=>int|null]
     */
    private function actor(Request $request): array
    {
        // attempt to read middleware-provided attributes first
        $roleAttr = $request->attributes->get('auth_role', null);
        $typeAttr = $request->attributes->get('auth_tokenable_type', null);
        $idAttr   = $request->attributes->get('auth_tokenable_id', null);

        if ($idAttr !== null) {
            $id = (int) $idAttr;
            return [
                'role' => $roleAttr !== null ? (string) $roleAttr : null,
                'type' => $typeAttr !== null ? (string) $typeAttr : null,
                'id'   => $id > 0 ? $id : null,
            ];
        }

        // fallback to Auth facade (session / token)
        $user = Auth::user();
        if ($user) {
            return [
                'role' => $roleAttr ?? ($user->role ?? null),
                'type' => $typeAttr ?? get_class($user),
                'id'   => isset($user->id) ? (int) $user->id : null,
            ];
        }

        // anonymous / unauthenticated
        return [
            'role' => $roleAttr ?? null,
            'type' => $typeAttr ?? null,
            'id'   => null,
        ];
    }

    /**
     * Convenience: log info with actor context.
     */
    private function logWithActor(string $message, Request $request, array $extra = []): void
    {
        $a = $this->actor($request);
        Log::info($message, array_merge([
            'actor_id'   => $a['id'],
            'actor_role' => $a['role'],
            'actor_type' => $a['type'],
        ], $extra));
    }

    /**
     * Display a listing of expense heads with search/filter/sort handled server-side
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('expense_heads');

            // --- Search (accept 'q' or 'search') ---
            $search = $request->input('q', $request->input('search', null));
            if (!empty($search)) {
                $query->where('title', 'like', '%' . trim($search) . '%');
            }

            // --- Status filter ---
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // --- Created date range filter ---
            if ($request->filled('created_from')) {
                $query->whereDate('created_at', '>=', $request->input('created_from'));
            }
            if ($request->filled('created_to')) {
                $query->whereDate('created_at', '<=', $request->input('created_to'));
            }

            // --- Sorting ---
            $allowedSorts = ['id', 'title', 'status', 'created_at', 'updated_at'];
            $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'created_at';
            $sortDir = strtolower($request->input('sort_order', $request->input('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortDir);

            // --- Pagination ---
            $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
            $page = max((int) $request->input('page', 1), 1);

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'message' => 'Expense heads retrieved successfully.'
            ]);

        } catch (Throwable $e) {
            // optional: log exception with actor context
            $this->logWithActor('ExpenseHead index failed: ' . $e->getMessage(), $request ?? new Request(), ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense heads.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created expense head
     */
    public function store(Request $request)
    {
        $payload = $request->only(['title', 'status']);
        $payload['title'] = isset($payload['title']) ? trim($payload['title']) : null;

        $validator = Validator::make($payload, [
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $a = $this->actor($request);
            $userId = $a['id'] ?? null;
            $userRole = $a['role'] ?? null;

            $data = [
                'title' => $payload['title'],
                'status' => $payload['status'],
                'created_by' => $userId,
                'created_by_role' => $userRole,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $id = DB::table('expense_heads')->insertGetId($data);
            $expenseHead = DB::table('expense_heads')->where('id', $id)->first();

            // optional: log creation
            $this->logWithActor("Created expense_head id={$id}", $request, ['expense_head' => (array)$expenseHead]);

            return response()->json([
                'success' => true,
                'data' => $expenseHead,
                'message' => 'Expense head created successfully.'
            ], 201);

        } catch (Throwable $e) {
            $this->logWithActor('Failed to create expense head: ' . $e->getMessage(), $request, ['payload' => $payload]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense head.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified expense head
     */
    public function show($id)
    {
        try {
            $expenseHead = DB::table('expense_heads')->where('id', $id)->first();

            if (!$expenseHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense head not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $expenseHead,
                'message' => 'Expense head retrieved successfully.'
            ]);

        } catch (Throwable $e) {
            // we don't have a Request object here, create a minimal one for logging
            $this->logWithActor('Failed to show expense head: ' . $e->getMessage(), new Request(), ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expense head.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified expense head
     */
    public function update(Request $request, $id)
    {
        try {
            $expenseHead = DB::table('expense_heads')->where('id', $id)->first();

            if (!$expenseHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense head not found.'
                ], 404);
            }

            $payload = $request->only(['title', 'status']);
            $payload['title'] = isset($payload['title']) ? trim($payload['title']) : null;

            $validator = Validator::make($payload, [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                   ],
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'title' => $payload['title'],
                'status' => $payload['status'],
                'updated_at' => now(),
            ];

            DB::table('expense_heads')->where('id', $id)->update($data);
            $updatedExpenseHead = DB::table('expense_heads')->where('id', $id)->first();

            // optional: log update
            $this->logWithActor("Updated expense_head id={$id}", $request, ['updated_fields' => array_keys($data), 'expense_head' => (array)$updatedExpenseHead]);

            return response()->json([
                'success' => true,
                'data' => $updatedExpenseHead,
                'message' => 'Expense head updated successfully.'
            ]);

        } catch (Throwable $e) {
            $this->logWithActor('Failed to update expense head: ' . $e->getMessage(), $request, ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense head.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified expense head
     */
    public function destroy(Request $request, $id)
    {
        try {
            $expenseHead = DB::table('expense_heads')->where('id', $id)->first();

            if (!$expenseHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense head not found.'
                ], 404);
            }

            // Prevent delete if related expenses exist
            $hasChildren = DB::table('expenses')->where('head_id', $id)->exists();
            if ($hasChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: related expenses exist.'
                ], 400);
            }

            DB::table('expense_heads')->where('id', $id)->delete();

            // optional: log delete
            $this->logWithActor("Deleted expense_head id={$id}", $request, ['deleted_record' => (array)$expenseHead]);

            return response()->json([
                'success' => true,
                'message' => 'Expense head deleted successfully.'
            ]);

        } catch (Throwable $e) {
            $this->logWithActor('Failed to delete expense head: ' . $e->getMessage(), $request, ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense head.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle expense head status
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $expenseHead = DB::table('expense_heads')->where('id', $id)->first();

            if (!$expenseHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense head not found.'
                ], 404);
            }

            $newStatus = $expenseHead->status === 'active' ? 'inactive' : 'active';

            DB::table('expense_heads')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            $updated = DB::table('expense_heads')->where('id', $id)->first();

            // optional: log toggle
            $this->logWithActor("Toggled status for expense_head id={$id} to {$newStatus}", $request, ['expense_head' => (array)$updated]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'data' => $updated
            ]);

        } catch (Throwable $e) {
            $this->logWithActor('Failed to toggle status for expense head: ' . $e->getMessage(), $request, ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active expense heads (for dropdowns)
     */
    public function activeHeads()
    {
        try {
            $expenseHeads = DB::table('expense_heads')
                            ->where('status', 'active')
                            ->orderBy('title')
                            ->get();

            return response()->json([
                'success' => true,
                'data' => $expenseHeads,
                'message' => 'Active expense heads retrieved successfully.'
            ]);

        } catch (Throwable $e) {
            // minimal Request for logging
            $this->logWithActor('Failed to retrieve active expense heads: ' . $e->getMessage(), new Request());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active expense heads.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function allExpenseHeads()
{
    try {
        $items = DB::table('expense_heads')->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'total' => $items->count(),
            'message' => 'All expense heads retrieved successfully.'
        ]);

    } catch (Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve all expense heads.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
