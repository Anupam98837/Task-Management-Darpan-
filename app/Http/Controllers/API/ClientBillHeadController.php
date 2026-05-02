<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientBillHeadController extends Controller
{
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

    private function logActivity(Request $request, string $activity, string $note, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $actor = $this->actor($request);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $actor['id'] ?: 0,
                'performed_by_role' => $actor['role'] ?: null,
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,
                'module'            => 'Client Bill Heads',
                'table_name'        => 'client_bill_heads',
                'record_id'         => $recordId,
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('user_data_activity_log insert failed (ClientBillHead)', ['error' => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $sortBy = trim((string) $request->query('sort_by', 'created_at'));
        $sortDir = strtolower(trim((string) $request->query('sort_dir', 'desc'))) === 'asc' ? 'asc' : 'desc';
        $allowed = ['id', 'title', 'status', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowed, true)) $sortBy = 'created_at';

        $query = DB::table('client_bill_heads');
        if ($q !== '') {
            $query->where('title', 'LIKE', "%{$q}%");
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
        $total = (clone $query)->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        if ($totalPages === 0) $page = 1;
        if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;

        $rows = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Client bill heads fetched',
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

    public function allHeads(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $rows = DB::table('client_bill_heads')
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return response()->json(['status' => 'success', 'message' => 'Active bill heads retrieved', 'data' => $rows]);
    }

    public function show(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $row = DB::table('client_bill_heads')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Bill head not found'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $row]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $actor = $this->actor($request);
        $now = now();
        $id = DB::table('client_bill_heads')->insertGetId([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'active',
            'created_by' => $actor['id'] ?: null,
            'created_by_role' => $actor['role'] ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $row = DB::table('client_bill_heads')->where('id', $id)->first();
        $this->logActivity($request, 'store', 'Created client bill head "' . $data['title'] . '"', $id, null, $row ? (array) $row : null);

        return response()->json(['status' => 'success', 'message' => 'Client bill head created', 'data' => $row], 201);
    }

    public function update(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bill_heads')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Bill head not found'], 404);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::table('client_bill_heads')->where('id', $id)->update([
            'title' => $data['title'],
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        $row = DB::table('client_bill_heads')->where('id', $id)->first();
        $this->logActivity($request, 'update', 'Updated client bill head "' . $data['title'] . '"', $id, (array) $existing, $row ? (array) $row : null);

        return response()->json(['status' => 'success', 'message' => 'Client bill head updated', 'data' => $row]);
    }

    public function destroy(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bill_heads')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Bill head not found'], 404);
        }

        DB::table('client_bill_heads')->where('id', $id)->delete();
        $this->logActivity($request, 'destroy', 'Deleted client bill head "' . ($existing->title ?? ('#' . $id)) . '"', $id, (array) $existing, null);

        return response()->json(['status' => 'success', 'message' => 'Client bill head deleted']);
    }

    public function toggleStatus(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $existing = DB::table('client_bill_heads')->where('id', $id)->first();
        if (!$existing) {
            return response()->json(['status' => 'error', 'message' => 'Bill head not found'], 404);
        }

        $next = strtolower((string) ($existing->status ?? 'inactive')) === 'active' ? 'inactive' : 'active';
        DB::table('client_bill_heads')->where('id', $id)->update([
            'status' => $next,
            'updated_at' => now(),
        ]);

        $row = DB::table('client_bill_heads')->where('id', $id)->first();
        $this->logActivity($request, 'toggle', 'Toggled client bill head "' . ($existing->title ?? ('#' . $id)) . '" to ' . $next, $id, (array) $existing, $row ? (array) $row : null);

        return response()->json(['status' => 'success', 'message' => 'Status updated', 'data' => $row]);
    }
}
