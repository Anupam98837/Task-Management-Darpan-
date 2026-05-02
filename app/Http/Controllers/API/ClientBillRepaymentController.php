<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClientUserScopeService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientBillRepaymentController extends Controller
{
    public function __construct(private ClientUserScopeService $scopeService)
    {
    }

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'id' => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
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

    private function baseQuery()
    {
        return DB::table('client_bill_repayments as cbr')
            ->join('client_bills as cb', 'cb.id', '=', 'cbr.client_bill_id')
            ->join('clients as c', 'c.id', '=', 'cbr.client_id')
            ->select(
                'cbr.*',
                'cb.bill_date',
                'cb.due_date',
                'cb.total_amount as bill_total_amount',
                'cb.is_published as bill_is_published',
                'c.name as client_name'
            );
    }

    private function resolveActorName(?string $role, ?int $id): ?string
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0 || !$role) {
            return null;
        }

        return match ($role) {
            'admin' => DB::table('admins')->where('id', $id)->value('name'),
            'accountant_user' => DB::table('accountant_users')->where('id', $id)->value('name'),
            'client_user' => DB::table('client_users')->where('id', $id)->value('name'),
            'assignee' => DB::table('assigned_people')->where('id', $id)->value('name'),
            default => null,
        };
    }

    private function generateAttachmentUrls(string $publicPath): array
    {
        $absoluteUrl = Storage::disk('public')->url($publicPath);
        $relativeUrl = parse_url($absoluteUrl, PHP_URL_PATH) ?: '/f/' . $publicPath;
        if (!str_starts_with($relativeUrl, '/f/')) {
            $relativeUrl = str_replace('/storage/', '/f/', $relativeUrl);
        }
        $absoluteUrl = rtrim(config('app.url'), '/') . $relativeUrl;

        return [
            'absolute_url' => $absoluteUrl,
            'relative_url' => $relativeUrl,
        ];
    }

    private function normalizeAttachments($row)
    {
        if (!$row || empty($row->attachments_json)) {
            return $row;
        }

        $attachments = is_string($row->attachments_json)
            ? (json_decode($row->attachments_json, true) ?: [])
            : (is_array($row->attachments_json) ? $row->attachments_json : []);

        foreach ($attachments as &$attachment) {
            if (!empty($attachment['disk']) && !empty($attachment['disk_path'])) {
                $urlData = $this->generateAttachmentUrls($attachment['disk_path']);
                $attachment['absolute_url'] = $urlData['absolute_url'];
                $attachment['relative_url'] = $urlData['relative_url'];
            }
        }

        $row->attachments_json = json_encode($attachments, JSON_UNESCAPED_UNICODE);
        $row->attachments = $attachments;
        $row->attachments_count = (int) ($row->attachments_count ?? count($attachments));
        $row->has_attachments = (bool) ($row->has_attachments ?? ($row->attachments_count > 0));

        return $row;
    }

    private function mapRepayment(object $row): object
    {
        $row = $this->normalizeAttachments($row);
        $row->submitted_by_name = $this->resolveActorName($row->submitted_by_role ?? null, (int) ($row->submitted_by ?? 0));
        $row->approved_by_name = $this->resolveActorName($row->approved_by_role ?? null, (int) ($row->approved_by ?? 0));

        return $row;
    }

    private function storeAttachments(Request $request, int $billId): array
    {
        $files = $request->file('attachments');
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            $files = [$files];
        }
        if (!is_array($files)) {
            return [];
        }

        if (!Storage::disk('public')->exists('clientBillRepayments')) {
            Storage::disk('public')->makeDirectory('clientBillRepayments');
        }

        $attachments = [];
        foreach ($files as $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }
            if ($file->getError() !== UPLOAD_ERR_OK || !$file->isValid()) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $stored = 'billrepay_' . $billId . '_' . Str::uuid()->toString() . '.' . $ext;
            $ok = Storage::disk('public')->putFileAs('clientBillRepayments', $file, $stored);
            if (!$ok) {
                Log::warning('[ClientBillRepaymentController] attachment store failed', ['name' => $stored]);
                continue;
            }

            $publicPath = 'clientBillRepayments/' . $stored;
            $urlData = $this->generateAttachmentUrls($publicPath);
            $mime = $file->getClientMimeType() ?: $file->getMimeType() ?: 'application/octet-stream';

            $attachments[] = [
                'kind' => str_starts_with($mime, 'image/') ? 'image' : 'file',
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $stored,
                'mime' => $mime,
                'size' => (int) ($file->getSize() ?? 0),
                'relative_url' => $urlData['relative_url'],
                'absolute_url' => $urlData['absolute_url'],
                'disk' => 'public',
                'disk_path' => $publicPath,
                'uploaded_at' => now()->toIso8601String(),
            ];
        }

        return $attachments;
    }

    public function index(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user', 'client_user'])) {
            return $resp;
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
        $clientId = (int) $request->query('client_id', 0);
        $billId = (int) $request->query('client_bill_id', 0);
        $status = trim((string) $request->query('status', ''));
        $q = trim((string) $request->query('q', ''));

        $query = $this->baseQuery();

        $scopedClientIds = $this->scopedClientIdsForActor($request);
        if ($scopedClientIds !== null) {
            if (empty($scopedClientIds)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No repayments found',
                    'data' => [],
                    'meta' => ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'total_pages' => 0, 'last_page' => 0],
                ]);
            }
            $query->whereIn('cbr.client_id', $scopedClientIds);
        }

        if ($clientId > 0) {
            $this->ensureClientVisible($request, $clientId);
            $query->whereIn('cbr.client_id', $this->collectClientTreeIds($clientId));
        }

        if ($billId > 0) {
            $query->where('cbr.client_bill_id', $billId);
        }

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('cbr.status', $status);
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('c.name', 'LIKE', $like)
                    ->orWhere('cbr.note', 'LIKE', $like)
                    ->orWhere('cbr.approval_note', 'LIKE', $like);
            });
        }

        $total = (clone $query)->count();
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        if ($totalPages === 0) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $rows = $query
            ->orderBy('cbr.repayment_date', 'desc')
            ->orderBy('cbr.id', 'desc')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($row) => $this->mapRepayment($row));

        return response()->json([
            'status' => 'success',
            'message' => 'Bill repayments fetched',
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

        $row = $this->baseQuery()->where('cbr.id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Bill repayment not found'], 404);
        }

        $this->ensureClientVisible($request, (int) $row->client_id);

        return response()->json(['status' => 'success', 'data' => $this->mapRepayment($row)]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user', 'client_user'])) {
            return $resp;
        }

        $data = $request->validate([
            'client_bill_id' => ['required', 'integer', 'exists:client_bills,id'],
            'repayment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string'],
            'attachments.*' => ['sometimes', 'file', 'max:102400'],
        ]);

        $bill = DB::table('client_bills')->where('id', (int) $data['client_bill_id'])->first();
        if (!$bill) {
            return response()->json(['status' => 'error', 'message' => 'Client bill not found'], 404);
        }
        if (!(bool) $bill->is_published) {
            return response()->json(['status' => 'error', 'message' => 'Repayments can only be added to published bills'], 422);
        }

        $this->ensureClientVisible($request, (int) $bill->client_id);
        $actor = $this->actor($request);
        $directApproval = in_array($actor['role'] ?? null, ['admin', 'accountant_user'], true);
        $attachments = $this->storeAttachments($request, (int) $bill->id);
        $now = now();

        $repaymentId = DB::table('client_bill_repayments')->insertGetId([
            'client_bill_id' => (int) $bill->id,
            'client_id' => (int) $bill->client_id,
            'repayment_date' => $data['repayment_date'],
            'amount' => round((float) $data['amount'], 2),
            'note' => $data['note'] ?? null,
            'has_attachments' => !empty($attachments),
            'attachments_count' => count($attachments),
            'attachments_json' => $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null,
            'status' => $directApproval ? 'approved' : 'pending',
            'submitted_by' => $actor['id'] ?: null,
            'submitted_by_role' => $actor['role'] ?: null,
            'approved_by' => $directApproval ? ($actor['id'] ?: null) : null,
            'approved_by_role' => $directApproval ? ($actor['role'] ?: null) : null,
            'approved_at' => $directApproval ? $now : null,
            'approval_note' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $row = $this->baseQuery()->where('cbr.id', $repaymentId)->first();

        return response()->json([
            'status' => 'success',
            'message' => $directApproval ? 'Repayment added' : 'Repayment submitted for approval',
            'data' => $row ? $this->mapRepayment($row) : null,
        ], 201);
    }

    public function approve(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $data = $request->validate([
            'approval_note' => ['nullable', 'string'],
        ]);

        $row = $this->baseQuery()->where('cbr.id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Bill repayment not found'], 404);
        }

        $this->ensureClientVisible($request, (int) $row->client_id);

        $actor = $this->actor($request);
        DB::table('client_bill_repayments')->where('id', $id)->update([
            'status' => 'approved',
            'approved_by' => $actor['id'] ?: null,
            'approved_by_role' => $actor['role'] ?: null,
            'approved_at' => now(),
            'approval_note' => $data['approval_note'] ?? null,
            'updated_at' => now(),
        ]);

        $fresh = $this->baseQuery()->where('cbr.id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Repayment approved',
            'data' => $fresh ? $this->mapRepayment($fresh) : null,
        ]);
    }

    public function reject(Request $request, int $id)
    {
        if ($resp = $this->requireRole($request, ['admin', 'accountant_user'])) {
            return $resp;
        }

        $data = $request->validate([
            'approval_note' => ['nullable', 'string'],
        ]);

        $row = $this->baseQuery()->where('cbr.id', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Bill repayment not found'], 404);
        }

        $this->ensureClientVisible($request, (int) $row->client_id);

        $actor = $this->actor($request);
        DB::table('client_bill_repayments')->where('id', $id)->update([
            'status' => 'rejected',
            'approved_by' => $actor['id'] ?: null,
            'approved_by_role' => $actor['role'] ?: null,
            'approved_at' => now(),
            'approval_note' => $data['approval_note'] ?? null,
            'updated_at' => now(),
        ]);

        $fresh = $this->baseQuery()->where('cbr.id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Repayment rejected',
            'data' => $fresh ? $this->mapRepayment($fresh) : null,
        ]);
    }
}
