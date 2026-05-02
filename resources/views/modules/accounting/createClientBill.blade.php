@php
  $billingBackUrl = $billingBackUrl ?? '/admin/accounting/client-bills';
@endphp

@push('styles')
<style>
* { box-sizing: border-box; }

.bill-builder-page {
  background:
    radial-gradient(circle at top right, rgba(35,119,252,.1), transparent 22%),
    radial-gradient(circle at bottom left, rgba(106,169,255,.08), transparent 20%),
    var(--bg-body);
  min-height: 100vh;
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
}
.builder-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
.builder-header h1 { margin:0; font-size:28px; font-weight:800; color:var(--text-color); }
.builder-header p { margin:6px 0 0; color:#64748b; font-size:14px; max-width:760px; }
.builder-back { display:inline-flex; align-items:center; gap:8px; color:#1d4ed8; font-size:13px; font-weight:700; text-decoration:none; margin-top:10px; }
.builder-actions { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.builder-grid { display:grid; grid-template-columns: minmax(0, 1.55fr) minmax(320px, .95fr); gap:20px; align-items:stretch; }
.panel-card {
  background: linear-gradient(180deg, rgba(255,255,255,.98), #fbfdff);
  border:1px solid var(--border-color);
  border-radius:24px;
  box-shadow:0 22px 42px rgba(15,23,42,.08);
  overflow:hidden;
  display:flex;
  flex-direction:column;
  height:100%;
}
.left-scroll, .right-scroll { padding:22px; height:100%; }
.right-scroll { overflow:auto; }
.stack { display:flex; flex-direction:column; gap:18px; }
.section-title { font-size:16px; font-weight:800; color:#0f172a; margin:0 0 12px; }
.section-sub { font-size:13px; color:#64748b; margin:0 0 16px; }
.btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:0 16px; border-radius:12px; font-size:13px; font-weight:800; cursor:pointer; transition:all .2s; text-decoration:none; border:none; }
.btn-primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; box-shadow:0 10px 20px rgba(37,99,235,.2); }
.btn-secondary { background:linear-gradient(180deg,#fff,#f8fbff); border:1px solid #dbe5f0; color:#0f172a; }
.btn-soft { background:linear-gradient(180deg,#f5f9ff,#eff6ff); border:1px solid #bfdbfe; color:#1d4ed8; }
.btn-danger-soft { background:#fff1f2; border:1px solid #fecdd3; color:#be123c; }
.btn.is-loading, .btn[aria-busy="true"] { pointer-events:none; opacity:.85; position:relative; }
.btn.is-loading .btn-label { visibility:hidden; }
.btn.is-loading::after { content:""; position:absolute; inset:0; margin:auto; width:18px; height:18px; border-radius:50%; border:2.5px solid rgba(255,255,255,.75); border-top-color:transparent; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

.form-label { display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px; }
.form-control, .form-select {
  width:100%;
  min-height:44px;
  padding:10px 14px;
  border:1px solid #dbe5f0;
  border-radius:12px;
  background:#fff;
  font-size:14px;
  color:#0f172a;
}
textarea.form-control { min-height: 96px; resize: vertical; }
.field-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:14px; }
.helper-text { color:#64748b; font-size:12px; margin-top:6px; }
.picker-head { display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:12px; }
.selected-client-banner {
  display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;
  padding:16px 18px; border-radius:18px; background:linear-gradient(135deg,#eff6ff,#f8fbff); border:1px solid #bfdbfe;
}
.selected-client-banner strong { display:block; color:#0f172a; font-size:16px; }
.selected-client-banner small { color:#64748b; }
.selected-client-banner .client-tag {
  display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
  background:#fff; border:1px solid #bfdbfe; color:#1d4ed8; font-size:12px; font-weight:800;
}
.pill { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:700; background:#f8fafc; border:1px solid #e2e8f0; color:#475569; }
.subsection-card { border:1px solid #dbeafe; border-radius:20px; padding:18px; background:linear-gradient(180deg,#ffffff,#fbfdff); }
.expense-table-wrap { border:1px solid #dbeafe; border-radius:20px; overflow:hidden; background:linear-gradient(180deg,#ffffff,#f8fbff); }
.expense-table-scroll { max-height:320px; overflow:auto; }
.expense-table { width:100%; border-collapse:collapse; }
.expense-table th, .expense-table td { padding:12px 14px; border-bottom:1px solid #eef2f7; font-size:13px; vertical-align:top; text-align:left; }
.expense-table th { position:sticky; top:0; background:#f8fafc; text-transform:uppercase; letter-spacing:.4px; font-size:11px; color:#64748b; z-index:1; }
.expense-check { width:18px; height:18px; }
.item-list { display:flex; flex-direction:column; gap:12px; }
.item-row { border:1px solid #e2e8f0; border-radius:18px; background:linear-gradient(180deg,#ffffff,#fbfdff); padding:14px; }
.item-row.source-expense { background:linear-gradient(180deg,#eff6ff,#ffffff); border-color:#bfdbfe; }
.item-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
.item-top strong { color:#0f172a; }
.item-top small { color:#64748b; display:block; margin-top:4px; }
.item-grid { display:grid; grid-template-columns: 1fr 1fr 180px; gap:12px; align-items:end; }
.item-grid.expense-grid { grid-template-columns: 1fr 180px; }
.item-close {
  width:34px; height:34px; border-radius:999px; border:1px solid #fecdd3; background:#fff1f2; color:#be123c;
  display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.item-close:hover { background:#ffe4e6; }
.total-bar {
  display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;
  padding:16px 18px; border-radius:18px; background:#0f172a; color:#fff;
}
.empty-note { padding:18px; border:1px dashed #cbd5e1; border-radius:16px; color:#94a3b8; text-align:center; background:#fff; }

.analysis-topbar {
  display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;
  padding:16px; border-radius:18px; background:linear-gradient(135deg,#0f3b93,#2563eb 58%,#38bdf8 100%); color:#fff;
}
.analysis-topbar h2, .analysis-topbar p { color:#fff; margin:0; }
.analysis-topbar p { opacity:.84; font-size:13px; max-width:320px; margin-top:6px; }
.btn-chip {
  height:36px; padding:0 14px; border-radius:999px; border:1px solid rgba(255,255,255,.28); background:rgba(255,255,255,.14);
  color:#fff; font-size:13px; font-weight:700; cursor:pointer;
}
.btn-chip.light { background:#fff; color:#1d4ed8; border-color:#bfdbfe; }
.summary-kpis { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; }
.kpi-box { border:1px solid rgba(191,219,254,.9); border-radius:18px; padding:16px; background:linear-gradient(145deg,#ffffff,#eef6ff); box-shadow:0 12px 24px rgba(37,99,235,.08); }
.kpi-box.blue { background:linear-gradient(145deg,#eff6ff,#dbeafe); }
.kpi-box.cyan { background:linear-gradient(145deg,#ecfeff,#cffafe); }
.kpi-box.indigo { background:linear-gradient(145deg,#eef2ff,#dbeafe); }
.kpi-box.sky { background:linear-gradient(145deg,#f0f9ff,#dbeafe); }
.kpi-box .label { color:#1e40af; font-size:11px; text-transform:uppercase; letter-spacing:.5px; font-weight:800; }
.kpi-box .value { margin-top:8px; font-size:24px; font-weight:800; color:#0f172a; }
.visual-card { border:1px solid #dbeafe; border-radius:20px; padding:16px; background:linear-gradient(180deg,#eff6ff,#ffffff); }
.visual-head { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
.visual-head strong { color:#0f172a; }
.visual-legend { display:flex; gap:12px; flex-wrap:wrap; color:#475569; font-size:12px; }
.visual-legend span::before { content:''; display:inline-block; width:10px; height:10px; border-radius:999px; margin-right:6px; vertical-align:-1px; }
.visual-legend .budget::before { background:#2563eb; }
.visual-legend .expenses::before { background:#06b6d4; }
.visual-legend .billed::before { background:#7c3aed; }
.visual-bars { display:flex; flex-direction:column; gap:12px; }
.visual-row { display:grid; grid-template-columns: 82px 1fr 88px; gap:10px; align-items:center; }
.visual-row label { font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.45px; }
.visual-track { height:14px; border-radius:999px; background:#dbeafe; overflow:hidden; }
.visual-fill { height:100%; border-radius:999px; min-width:4px; }
.visual-fill.budget { background:linear-gradient(90deg,#1d4ed8,#3b82f6); }
.visual-fill.expenses { background:linear-gradient(90deg,#0891b2,#22d3ee); }
.visual-fill.billed { background:linear-gradient(90deg,#6d28d9,#8b5cf6); }
.visual-row strong { text-align:right; font-size:12px; color:#0f172a; }
.analysis-list { display:flex; flex-direction:column; gap:10px; }
.analysis-card { border:1px solid #dbeafe; border-radius:18px; padding:14px; background:linear-gradient(180deg,#ffffff,#f8fbff); }
.analysis-card strong { color:#0f172a; }
.analysis-card small { color:#64748b; display:block; margin-top:4px; }
.bill-tabbar { display:flex; gap:8px; flex-wrap:wrap; margin:12px 0 14px; }
.bill-tab {
  height:36px; padding:0 14px; border-radius:999px; border:1px solid #dbeafe; background:#eff6ff; color:#1d4ed8;
  font-size:13px; font-weight:800; cursor:pointer;
}
.bill-tab.active { background:linear-gradient(135deg,#1d4ed8,#2563eb); color:#fff; border-color:#1d4ed8; }
.bill-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
.bill-card-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
.bill-action {
  display:inline-flex; align-items:center; gap:6px; height:34px; padding:0 12px; border-radius:999px;
  border:1px solid #dbeafe; background:#fff; color:#1d4ed8; font-size:12px; font-weight:800; cursor:pointer;
}
.bill-action.publish { background:#eff6ff; border-color:#bfdbfe; }
.bill-status {
  display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:800;
  background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; margin-top:8px;
}
.bill-status.draft { background:#fff7ed; color:#c2410c; border-color:#fdba74; }

.picker-modal {
  position:fixed; inset:0; z-index:1050; display:none; align-items:center; justify-content:center;
  padding:20px; background:rgba(15,23,42,.52);
}
.picker-modal.show { display:flex; }
.picker-modal-card {
  width:min(860px, 100%); max-height:min(82vh, 760px); display:flex; flex-direction:column;
  background:#fff; border-radius:24px; overflow:hidden; box-shadow:0 30px 60px rgba(15,23,42,.28);
}
.picker-modal-head, .picker-modal-foot {
  padding:18px 20px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.picker-modal-foot { border-bottom:none; border-top:1px solid #e2e8f0; }
.picker-modal-head h3 { margin:0; font-size:18px; font-weight:800; color:#0f172a; }
.picker-modal-head p { margin:6px 0 0; font-size:13px; color:#64748b; }
.picker-modal-body { padding:18px 20px 20px; overflow:auto; }
.search-box { position:relative; margin-bottom:14px; }
.search-box input { width:100%; height:44px; padding:0 14px 0 40px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; }
.search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.tree-shell { border:1px solid #e2e8f0; border-radius:16px; background:#fff; max-height:420px; overflow:auto; padding:10px; }
.tree-node { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border-radius:12px; }
.tree-node:hover { background:#f8fafc; }
.tree-node.active { background:#eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
.tree-children { margin-left:18px; padding-left:12px; border-left:1px solid #e2e8f0; }
.tree-node input { margin-top:4px; }
.tree-meta { display:flex; flex-direction:column; gap:3px; }
.tree-meta strong { font-size:14px; color:#0f172a; }
.tree-meta small { color:#94a3b8; font-size:12px; }

@media (max-width: 1200px) {
  .builder-grid { grid-template-columns: 1fr; }
  .right-scroll { max-height:none; }
}
@media (max-width: 768px) {
  .bill-builder-page { padding:16px; }
  .field-grid, .summary-kpis, .item-grid, .item-grid.expense-grid, .visual-row { grid-template-columns: 1fr; }
  .left-scroll, .right-scroll, .picker-modal-head, .picker-modal-body, .picker-modal-foot { padding:16px; }
}
</style>
@endpush

@section('content')
<div class="bill-builder-page">
  <div class="builder-header">
    <div>
      <h1>New Client Bill</h1>
      <p>Build a client bill with expense-based items, compare it against the selected client tree, and review earlier bills without leaving this page.</p>
      <a href="{{ $billingBackUrl }}" class="builder-back">
        <i class="fa-solid fa-arrow-left"></i>
        Back To Bills
      </a>
    </div>
    <div class="builder-actions">
      <button type="button" class="btn btn-soft" id="openClientPickerBtn">
        <i class="fa-solid fa-sitemap"></i>
        Choose Client
      </button>
      <button type="button" class="btn btn-secondary" id="clearSelectedClientBtn">
        <i class="fa-solid fa-rotate-left"></i>
        Clear
      </button>
    </div>
  </div>

  <div class="builder-grid">
    <section class="panel-card">
      <div class="left-scroll stack">
        <div class="selected-client-banner" id="selectedClientBanner">
          <div>
            <strong>No client selected</strong>
            <small>Choose a client tree from the right panel to load budgets, expenses, and earlier bills.</small>
          </div>
          <span class="client-tag">Waiting</span>
        </div>

        <form id="billCreateForm" class="stack" autocomplete="off">
          <div>
            <h2 class="section-title">Bill Basics</h2>
            <div class="field-grid">
              <div>
                <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="bill_date" required>
              </div>
              <div>
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date">
              </div>
              <div>
                <label class="form-label">Bill Status</label>
                <div class="form-control" style="display:flex;align-items:center;background:#f8fafc;">Draft first, publish later</div>
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" id="bill_notes" placeholder="Optional billing note, summary, or internal context"></textarea>
            </div>
          </div>

          <div>
            <div class="picker-head">
              <div>
                <h2 class="section-title">Bill Items</h2>
                <p class="section-sub">Pick client expenses below or add custom bill heads manually. Checked expenses create bill rows automatically.</p>
              </div>
              <button type="button" class="btn btn-secondary" id="addManualItemBtn">
                <i class="fa-solid fa-plus"></i>
                New Manual Item
              </button>
            </div>

            <div class="subsection-card">
              <div class="picker-head" style="margin-bottom:14px;">
                <div>
                  <h3 class="section-title" style="font-size:15px;margin-bottom:6px;">Client Expenses</h3>
                  <p class="section-sub" style="margin:0;">Select any expense to pull its title and amount directly into the bill.</p>
                </div>
                <span class="pill" id="expenseSelectionCount">0 selected</span>
              </div>
              <div class="expense-table-wrap">
                <div class="expense-table-scroll">
                  <table class="expense-table">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Expense Head</th>
                        <th>Job</th>
                        <th>Date</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody id="expenseRows">
                      <tr><td colspan="5" class="text-center py-4">Choose a client to load expenses.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="item-list mt-3" id="billItemsList">
              <div class="empty-note">No bill items yet. Select client expenses or add a manual item.</div>
            </div>
          </div>

          <div class="total-bar">
            <div>
              <div style="font-size:12px;opacity:.78;text-transform:uppercase;letter-spacing:.5px;">Bill Total</div>
              <div id="billGrandTotal" style="font-size:28px;font-weight:800;">Rs 0.00</div>
            </div>
            <button type="submit" class="btn btn-primary" id="saveBillBtn">
              <span class="btn-label"><i class="fa-solid fa-file-circle-plus"></i>Create Draft Bill</span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <aside class="panel-card">
      <div class="right-scroll stack">
        <div class="analysis-topbar">
          <div>
            <h2>Client Analysis</h2>
            <p>Pick a client tree here, then review budget, expense, and billing history while you prepare the next draft.</p>
          </div>
        </div>

        <div class="summary-kpis">
          <div class="kpi-box blue">
            <div class="label">Total Budget</div>
            <div class="value" id="statBudget">Rs 0.00</div>
          </div>
          <div class="kpi-box cyan">
            <div class="label">Total Expenses</div>
            <div class="value" id="statExpenses">Rs 0.00</div>
          </div>
          <div class="kpi-box indigo">
            <div class="label">Approved Repayments</div>
            <div class="value" id="statApprovedRepayments">Rs 0.00</div>
          </div>
          <div class="kpi-box sky">
            <div class="label">Draft Bills</div>
            <div class="value" id="statDraftBills">0</div>
          </div>
        </div>

        <div class="visual-card">
          <div class="visual-head">
            <strong>Budget vs Billing View</strong>
            <div class="visual-legend">
              <span class="budget">Budget</span>
              <span class="expenses">Expenses</span>
              <span class="billed">Billed</span>
            </div>
          </div>
          <div class="visual-bars">
            <div class="visual-row">
              <label>Budget</label>
              <div class="visual-track"><div class="visual-fill budget" id="barBudget" style="width:0%;"></div></div>
              <strong id="barBudgetText">Rs 0.00</strong>
            </div>
            <div class="visual-row">
              <label>Expenses</label>
              <div class="visual-track"><div class="visual-fill expenses" id="barExpenses" style="width:0%;"></div></div>
              <strong id="barExpensesText">Rs 0.00</strong>
            </div>
            <div class="visual-row">
              <label>Billed</label>
              <div class="visual-track"><div class="visual-fill billed" id="barBilled" style="width:0%;"></div></div>
              <strong id="barBilledText">Rs 0.00</strong>
            </div>
          </div>
        </div>

        <div>
          <h3 class="section-title" style="font-size:15px;">Bills For This Client</h3>
          <div class="analysis-list" id="previousBillsList">
            <div class="empty-note">Choose a client to review its earlier bills.</div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>

<div class="picker-modal" id="clientPickerModal" aria-hidden="true">
  <div class="picker-modal-card" role="dialog" aria-modal="true" aria-labelledby="clientPickerTitle">
    <div class="picker-modal-head">
      <div>
        <h3 id="clientPickerTitle">Choose Client Tree</h3>
        <p>Select a client or parent client. Child client jobs and expenses stay included in the analysis.</p>
      </div>
      <button type="button" class="item-close" id="closeClientPickerBtn" aria-label="Close client picker">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="picker-modal-body">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="clientTreeSearch" placeholder="Search clients...">
      </div>
      <div class="tree-shell" id="clientTreeShell">
        <div class="empty-note">Loading client tree…</div>
      </div>
    </div>
    <div class="picker-modal-foot">
      <span class="pill" id="pickerSelectionLabel">No client selected</span>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="button" class="btn btn-secondary" id="cancelClientPickerBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="applyClientPickerBtn">
          <span class="btn-label">Use Selected Client</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="picker-modal" id="billDetailModal" aria-hidden="true">
  <div class="picker-modal-card" role="dialog" aria-modal="true" aria-labelledby="billDetailTitle" style="width:min(760px, 100%);">
    <div class="picker-modal-head">
      <div>
        <h3 id="billDetailTitle">Client Bill Details</h3>
        <p>Review every bill item, amount, status, and due date for the selected bill.</p>
      </div>
      <button type="button" class="item-close" id="closeBillDetailBtn" aria-label="Close bill details">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="picker-modal-body" id="billDetailBody">
      <div class="empty-note">Select a bill to view its details.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const API_BASE = @json(url('/api'));
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    Swal.fire('Auth Required', 'Session expired. Please login again.', 'warning').then(() => location.href = '/');
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const KNOWN_COOKIE = 'billing_client_id';
  const billHeads = [];
  const state = {
    clients: [],
    treeRoots: [],
    selectedClientId: null,
    selectedClientLabel: '',
    selectedTreeIds: [],
    expenses: [],
    expenseMap: new Map(),
    selectedExpenseIds: new Set(),
    manualRowCounter: 0,
    pickerClientId: null,
    analysisBills: [],
    activeBillTab: 'published',
  };

  const els = {
    clientPickerModal: document.getElementById('clientPickerModal'),
    billDetailModal: document.getElementById('billDetailModal'),
    clientTreeSearch: document.getElementById('clientTreeSearch'),
    clientTreeShell: document.getElementById('clientTreeShell'),
    pickerSelectionLabel: document.getElementById('pickerSelectionLabel'),
    openClientPickerBtn: document.getElementById('openClientPickerBtn'),
    closeClientPickerBtn: document.getElementById('closeClientPickerBtn'),
    cancelClientPickerBtn: document.getElementById('cancelClientPickerBtn'),
    applyClientPickerBtn: document.getElementById('applyClientPickerBtn'),
    selectedClientBanner: document.getElementById('selectedClientBanner'),
    clearSelectedClientBtn: document.getElementById('clearSelectedClientBtn'),
    billDate: document.getElementById('bill_date'),
    dueDate: document.getElementById('due_date'),
    billNotes: document.getElementById('bill_notes'),
    expenseRows: document.getElementById('expenseRows'),
    expenseSelectionCount: document.getElementById('expenseSelectionCount'),
    billItemsList: document.getElementById('billItemsList'),
    addManualItemBtn: document.getElementById('addManualItemBtn'),
    billGrandTotal: document.getElementById('billGrandTotal'),
    saveBillBtn: document.getElementById('saveBillBtn'),
    billCreateForm: document.getElementById('billCreateForm'),
    statBudget: document.getElementById('statBudget'),
    statExpenses: document.getElementById('statExpenses'),
    statApprovedRepayments: document.getElementById('statApprovedRepayments'),
    statDraftBills: document.getElementById('statDraftBills'),
    previousBillsList: document.getElementById('previousBillsList'),
    billDetailBody: document.getElementById('billDetailBody'),
    closeBillDetailBtn: document.getElementById('closeBillDetailBtn'),
    barBudget: document.getElementById('barBudget'),
    barExpenses: document.getElementById('barExpenses'),
    barBilled: document.getElementById('barBilled'),
    barBudgetText: document.getElementById('barBudgetText'),
    barExpensesText: document.getElementById('barExpensesText'),
    barBilledText: document.getElementById('barBilledText'),
  };

  const esc = (value = '') => String(value).replace(/[&<>"']/g, (m) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[m]));
  const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const fmtDate = (value) => {
    if (!value) return '—';
    const dt = new Date(value);
    return Number.isNaN(dt.getTime()) ? esc(value) : dt.toLocaleDateString('en-IN', { year:'numeric', month:'short', day:'numeric' });
  };
  const setCookie = (name, value) => {
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${60 * 60 * 24 * 30}; SameSite=Lax`;
  };
  const getCookie = (name) => {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : '';
  };
  function setBtnLoading(btn, isLoading) {
    btn.classList.toggle('is-loading', !!isLoading);
    btn.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    btn.disabled = !!isLoading;
  }
  function openPicker() {
    els.clientPickerModal.classList.add('show');
    els.clientPickerModal.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => els.clientTreeSearch.focus());
  }
  function closePicker() {
    els.clientPickerModal.classList.remove('show');
    els.clientPickerModal.setAttribute('aria-hidden', 'true');
  }
  function openBillDetails() {
    els.billDetailModal.classList.add('show');
    els.billDetailModal.setAttribute('aria-hidden', 'false');
  }
  function closeBillDetails() {
    els.billDetailModal.classList.remove('show');
    els.billDetailModal.setAttribute('aria-hidden', 'true');
  }
  function resetBuilderState() {
    state.selectedClientId = null;
    state.selectedClientLabel = '';
    state.selectedTreeIds = [];
    state.expenses = [];
    state.expenseMap = new Map();
    state.selectedExpenseIds = new Set();
    state.pickerClientId = null;
    state.analysisBills = [];
    state.activeBillTab = 'published';
    updateBanner();
    renderClientTree();
    renderExpenses([]);
    renderPreviousBills([]);
    updateSummary({});
    els.billItemsList.innerHTML = '<div class="empty-note">No bill items yet. Select client expenses or add a manual item.</div>';
    updateBillTotal();
    setCookie(KNOWN_COOKIE, '');
  }

  async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, { headers, ...opts });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
    return data;
  }

  function billHeadOptions(selectedId = '') {
    const options = ['<option value="">Custom bill title</option>'];
    billHeads.forEach((head) => {
      options.push(`<option value="${esc(head.id)}" ${String(selectedId) === String(head.id) ? 'selected' : ''}>${esc(head.title)}</option>`);
    });
    return options.join('');
  }

  function buildTree(flatRows) {
    const byParent = new Map();
    flatRows.forEach((row) => {
      const key = row.parent_id == null ? 'root' : String(row.parent_id);
      if (!byParent.has(key)) byParent.set(key, []);
      byParent.get(key).push(row);
    });
    byParent.forEach((rows) => rows.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''))));
    const walk = (parentId = null) => {
      const key = parentId == null ? 'root' : String(parentId);
      return (byParent.get(key) || []).map((row) => ({ ...row, children: walk(row.id) }));
    };
    return walk(null);
  }

  function renderClientTree() {
    const query = String(els.clientTreeSearch.value || '').trim().toLowerCase();
    const activeId = Number(state.pickerClientId || state.selectedClientId || 0);

    const matchNode = (node) => {
      const selfMatch = !query || String(node.name || '').toLowerCase().includes(query);
      const childMatches = (node.children || []).map(matchNode).filter(Boolean);
      if (!selfMatch && !childMatches.length) return null;
      return { ...node, children: childMatches };
    };

    const filteredRoots = state.treeRoots.map(matchNode).filter(Boolean);
    if (!filteredRoots.length) {
      els.clientTreeShell.innerHTML = '<div class="empty-note">No clients match your search.</div>';
      return;
    }

    const renderNodes = (nodes, depth = 0) => nodes.map((node) => {
      const checked = activeId === Number(node.id) ? 'checked' : '';
      const active = activeId === Number(node.id) ? 'active' : '';
      return `
        <div class="tree-node ${active}" data-client-id="${esc(node.id)}">
          <input type="radio" name="client_tree_pick" value="${esc(node.id)}" ${checked}>
          <div class="tree-meta">
            <strong>${esc(node.name || `Client #${node.id}`)}</strong>
            <small>${depth === 0 ? 'Root client' : `Nested level ${depth}`}</small>
          </div>
        </div>
        ${node.children && node.children.length ? `<div class="tree-children">${renderNodes(node.children, depth + 1)}</div>` : ''}`;
    }).join('');

    els.clientTreeShell.innerHTML = renderNodes(filteredRoots);
    updatePickerLabel();
  }

  function updatePickerLabel() {
    const selectedId = Number(state.pickerClientId || state.selectedClientId || 0);
    if (!selectedId) {
      els.pickerSelectionLabel.textContent = 'No client selected';
      return;
    }
    const client = state.clients.find((row) => Number(row.id) === selectedId);
    els.pickerSelectionLabel.textContent = client ? client.name || `Client #${client.id}` : `Client #${selectedId}`;
  }

  function updateBanner() {
    const html = state.selectedClientId ? `
      <div>
        <strong>${esc(state.selectedClientLabel || `Client #${state.selectedClientId}`)}</strong>
        <small>${state.selectedTreeIds.length} client records included in this billing tree.</small>
      </div>
      <span class="client-tag"><i class="fa-solid fa-sitemap"></i>${state.selectedTreeIds.length} in scope</span>` : `
      <div>
        <strong>No client selected</strong>
        <small>Choose a client tree from the right panel to load budgets, expenses, and earlier bills.</small>
      </div>
      <span class="client-tag">Waiting</span>`;
    els.selectedClientBanner.innerHTML = html;
  }

  function renderPreviousBills(rows) {
    state.analysisBills = Array.isArray(rows) ? rows : [];
    const published = state.analysisBills.filter((bill) => !!bill.is_published);
    const drafts = state.analysisBills.filter((bill) => !bill.is_published);
    if (state.activeBillTab === 'published' && !published.length && drafts.length) {
      state.activeBillTab = 'draft';
    } else if (state.activeBillTab === 'draft' && !drafts.length && published.length) {
      state.activeBillTab = 'published';
    }
    const visibleBills = state.activeBillTab === 'draft' ? drafts : published;
    const publishedActive = state.activeBillTab === 'published' ? 'active' : '';
    const draftActive = state.activeBillTab === 'draft' ? 'active' : '';

    if (!state.analysisBills.length) {
      els.previousBillsList.innerHTML = '<div class="empty-note">No bills found for the selected client.</div>';
      return;
    }
    els.previousBillsList.innerHTML = `
      <div class="bill-tabbar">
        <button type="button" class="bill-tab ${publishedActive}" data-bill-tab="published">Published (${published.length})</button>
        <button type="button" class="bill-tab ${draftActive}" data-bill-tab="draft">Draft (${drafts.length})</button>
      </div>
      ${visibleBills.length ? visibleBills.map((bill) => `
        <div class="analysis-card">
          <div class="bill-card-top">
            <div>
              <strong>Bill #${esc(bill.id)}</strong>
              <small>${esc(bill.client_name || '—')} · ${fmtDate(bill.bill_date)} · Due ${fmtDate(bill.due_date)}</small>
            </div>
            <div class="bill-status ${bill.is_published ? '' : 'draft'}">${bill.is_published ? 'Published' : 'Draft'}</div>
          </div>
          <div style="margin-top:10px;font-weight:800;color:#0f172a;">${esc(money(bill.total_amount))}</div>
          <small style="margin-top:6px;">Approved repayments ${esc(money(bill.approved_repayment_amount || 0))}${Number(bill.pending_repayment_count || 0) ? ` · ${esc(bill.pending_repayment_count)} pending` : ''}</small>
          <div class="bill-card-actions">
            <button type="button" class="bill-action" data-bill-view="${esc(bill.id)}"><i class="fa-solid fa-eye"></i>View</button>
            ${bill.is_published ? `<button type="button" class="bill-action" data-bill-pdf="${esc(bill.id)}"><i class="fa-solid fa-file-pdf"></i>PDF</button>` : ''}
            ${bill.is_published ? '' : `<button type="button" class="bill-action publish" data-bill-publish="${esc(bill.id)}"><i class="fa-solid fa-paper-plane"></i>Publish</button>`}
          </div>
        </div>
      `).join('') : '<div class="empty-note">No bills found in this tab.</div>'}
    `;
  }

  async function openBillDetailModal(id) {
    openBillDetails();
    els.billDetailBody.innerHTML = '<div class="empty-note">Loading bill details…</div>';
    const data = await fetchJSON(`${API_BASE}/client-bills/${encodeURIComponent(id)}`);
    const bill = data.data || {};
    const items = Array.isArray(bill.items) ? bill.items : [];
    const repayments = Array.isArray(bill.repayments) ? bill.repayments : [];
    els.billDetailBody.innerHTML = `
      <div class="detail-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <div class="analysis-card"><small>Bill ID</small><strong>#${esc(bill.id || '—')}</strong></div>
        <div class="analysis-card"><small>Client</small><strong>${esc(bill.client_name || '—')}</strong></div>
        <div class="analysis-card"><small>Bill Date</small><strong>${fmtDate(bill.bill_date)}</strong></div>
        <div class="analysis-card"><small>Due Date</small><strong>${fmtDate(bill.due_date)}</strong></div>
        <div class="analysis-card"><small>Status</small><strong>${bill.is_published ? 'Published' : 'Draft'}</strong></div>
        <div class="analysis-card"><small>Published Date</small><strong>${fmtDate(bill.published_at)}</strong></div>
      </div>
      <div class="analysis-card" style="margin-top:14px;">
        <strong>Notes</strong>
        <small style="margin-top:8px;white-space:pre-wrap;">${bill.notes ? esc(bill.notes) : 'No notes added.'}</small>
      </div>
      <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;">
        ${items.length ? items.map((item) => `
          <div class="analysis-card" style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
            <div>
              <strong>${esc(item.bill_head_title || 'Untitled')}</strong>
              <small>${item.client_bill_head_id ? `Head #${esc(item.client_bill_head_id)}` : 'Custom line item'}</small>
            </div>
            <strong>${esc(money(item.amount))}</strong>
          </div>
        `).join('') : '<div class="empty-note">No bill items found.</div>'}
      </div>
      <div class="analysis-card" style="margin-top:14px;">
        <strong>Repayments</strong>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px;">
          ${repayments.length ? repayments.map((repayment) => `
            <div class="analysis-card" style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;background:#fff;">
              <div>
                <strong>${fmtDate(repayment.repayment_date)}</strong>
                <small>${esc(String(repayment.status || 'pending').replaceAll('_', ' '))}${repayment.submitted_by_name ? ` · ${esc(repayment.submitted_by_name)}` : ''}</small>
              </div>
              <strong>${esc(money(repayment.amount || 0))}</strong>
            </div>
          `).join('') : '<div class="empty-note">No repayments recorded yet.</div>'}
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;margin-top:16px;font-size:18px;font-weight:800;color:#0f172a;">Total: ${esc(money(bill.total_amount))}</div>
    `;
  }

  async function downloadBillPdf(id) {
    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}/pdf`, { headers });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data?.message || 'Failed to download bill PDF');
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `client_bill_${id}.pdf`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  async function publishBillFromAnalysis(id) {
    const confirm = await Swal.fire({
      title: 'Publish this bill?',
      text: 'After publishing, this bill can no longer be edited.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Publish',
    });
    if (!confirm.isConfirmed) return;
    await fetchJSON(`${API_BASE}/client-bills/${encodeURIComponent(id)}/publish`, { method: 'PATCH' });
    await Swal.fire({ icon:'success', title:'Bill published', timer:1400, showConfirmButton:false });
    if (state.selectedClientId) {
      await loadAnalysis(state.selectedClientId);
    }
  }

  function updateSummary(stats = {}) {
    const totalBudget = Number(stats.total_budget || 0);
    const totalExpenses = Number(stats.total_expense_amount || 0);
    const totalBilled = Number(stats.total_billed_amount || 0);
    const approvedRepayments = Number(stats.approved_repayment_amount || 0);
    const draftCount = Number(stats.draft_bill_count || 0);
    const maxValue = Math.max(totalBudget, totalExpenses, totalBilled, 1);

    els.statBudget.textContent = money(totalBudget);
    els.statExpenses.textContent = money(totalExpenses);
    els.statApprovedRepayments.textContent = money(approvedRepayments);
    els.statDraftBills.textContent = String(draftCount);

    els.barBudget.style.width = `${Math.max((totalBudget / maxValue) * 100, totalBudget > 0 ? 6 : 0)}%`;
    els.barExpenses.style.width = `${Math.max((totalExpenses / maxValue) * 100, totalExpenses > 0 ? 6 : 0)}%`;
    els.barBilled.style.width = `${Math.max((totalBilled / maxValue) * 100, totalBilled > 0 ? 6 : 0)}%`;
    els.barBudgetText.textContent = money(totalBudget);
    els.barExpensesText.textContent = money(totalExpenses);
    els.barBilledText.textContent = money(totalBilled);
  }

  function ensureItemsAreaVisible() {
    if (els.billItemsList.querySelector('.empty-note')) {
      els.billItemsList.innerHTML = '';
    }
  }

  function updateBillTotal() {
    const total = Array.from(els.billItemsList.querySelectorAll('.item-amount')).reduce((sum, input) => sum + Number(input.value || 0), 0);
    els.billGrandTotal.textContent = money(total);
  }

  function updateExpenseSelectionCount() {
    els.expenseSelectionCount.textContent = `${state.selectedExpenseIds.size} selected`;
  }

  function removeItemRow(key) {
    const row = els.billItemsList.querySelector(`[data-item-key="${CSS.escape(key)}"]`);
    if (row) row.remove();
    if (!els.billItemsList.children.length) {
      els.billItemsList.innerHTML = '<div class="empty-note">No bill items yet. Select client expenses or add a manual item.</div>';
    }
    updateBillTotal();
  }

  function addManualItem() {
    ensureItemsAreaVisible();
    state.manualRowCounter += 1;
    const key = `manual-${state.manualRowCounter}`;
    const row = document.createElement('div');
    row.className = 'item-row';
    row.dataset.itemKey = key;
    row.innerHTML = `
      <div class="item-top">
        <div>
          <strong>Manual Bill Item</strong>
          <small>Use this for fees, adjustments, or charges not tied to a specific expense.</small>
        </div>
        <button type="button" class="item-close remove-item-btn" aria-label="Remove bill item"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="item-grid">
        <div>
          <label class="form-label">Saved Bill Head</label>
          <select class="form-select item-head-select">
            ${billHeadOptions('')}
          </select>
          <div class="helper-text">Pick a reusable bill head or leave it custom.</div>
        </div>
        <div>
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control item-title" placeholder="Enter bill head or item title">
        </div>
        <div>
          <label class="form-label">Amount <span class="text-danger">*</span></label>
          <input type="number" min="0" step="0.01" class="form-control item-amount" placeholder="0.00">
        </div>
      </div>`;
    row.querySelectorAll('.remove-item-btn').forEach((btn) => btn.addEventListener('click', () => removeItemRow(key)));
    row.querySelector('.item-head-select').addEventListener('change', (event) => {
      const selected = billHeads.find((head) => String(head.id) === String(event.target.value || ''));
      if (selected) {
        row.querySelector('.item-title').value = selected.title || '';
      }
    });
    row.querySelector('.item-amount').addEventListener('input', updateBillTotal);
    els.billItemsList.appendChild(row);
  }

  function addExpenseItem(expense) {
    const key = `expense-${expense.id}`;
    if (els.billItemsList.querySelector(`[data-item-key="${CSS.escape(key)}"]`)) {
      return;
    }

    ensureItemsAreaVisible();
    const row = document.createElement('div');
    row.className = 'item-row source-expense';
    row.dataset.itemKey = key;
    row.dataset.source = 'expense';
    row.dataset.expenseId = expense.id;
    row.dataset.jobId = expense.job_id;
    row.innerHTML = `
      <div class="item-top">
        <div>
          <strong>${esc(expense.expense_head_title || 'Expense item')}</strong>
          <small>Linked expense #${esc(expense.id)} · ${esc(expense.job_title || `Job #${expense.job_id}`)} · ${fmtDate(expense.expense_date)}</small>
        </div>
        <button type="button" class="item-close remove-item-btn" aria-label="Remove expense item"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="item-grid expense-grid">
        <div>
          <label class="form-label">Bill Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control item-title" value="${esc(expense.expense_head_title || `Expense #${expense.id}`)}">
        </div>
        <div>
          <label class="form-label">Amount <span class="text-danger">*</span></label>
          <input type="number" min="0" step="0.01" class="form-control item-amount" value="${esc(expense.amount || 0)}">
        </div>
      </div>`;

    row.querySelectorAll('.remove-item-btn').forEach((btn) => btn.addEventListener('click', () => {
      state.selectedExpenseIds.delete(Number(expense.id));
      const checkbox = els.expenseRows.querySelector(`input[data-expense-id="${expense.id}"]`);
      if (checkbox) checkbox.checked = false;
      removeItemRow(key);
      updateExpenseSelectionCount();
    }));
    row.querySelector('.item-amount').addEventListener('input', updateBillTotal);
    els.billItemsList.appendChild(row);
    updateBillTotal();
  }

  function renderExpenses(rows) {
    if (!state.selectedClientId) {
      els.expenseRows.innerHTML = '<tr><td colspan="5" class="text-center py-4">Choose a client to load expenses.</td></tr>';
      updateExpenseSelectionCount();
      return;
    }
    if (!Array.isArray(rows) || !rows.length) {
      els.expenseRows.innerHTML = '<tr><td colspan="5" class="text-center py-4">No expenses found for the selected client tree.</td></tr>';
      updateExpenseSelectionCount();
      return;
    }
    els.expenseRows.innerHTML = rows.map((expense) => `
      <tr>
        <td><input class="expense-check" type="checkbox" data-expense-id="${esc(expense.id)}" ${state.selectedExpenseIds.has(Number(expense.id)) ? 'checked' : ''}></td>
        <td>
          <strong>${esc(expense.expense_head_title || 'Expense')}</strong>
          <div class="helper-text">Expense #${esc(expense.id)}</div>
        </td>
        <td>${esc(expense.job_title || `Job #${expense.job_id}`)}</td>
        <td>${fmtDate(expense.expense_date)}</td>
        <td style="font-weight:700;">${esc(money(expense.amount))}</td>
      </tr>
    `).join('');
    updateExpenseSelectionCount();
  }

  async function loadAnalysis(clientId) {
    if (!clientId) return;
    const data = await fetchJSON(`${API_BASE}/client-bills/analysis?client_id=${encodeURIComponent(clientId)}`);
    const payload = data.data || {};
    state.selectedTreeIds = Array.isArray(payload.tree_client_ids) ? payload.tree_client_ids : [];
    state.expenses = Array.isArray(payload.expenses) ? payload.expenses : [];
    state.expenseMap = new Map(state.expenses.map((expense) => [Number(expense.id), expense]));
    renderExpenses(state.expenses);
    renderPreviousBills(payload.previous_bills || []);
    updateSummary(payload.stats || {});
    updateBanner();
  }

  async function selectClient(clientId, clientLabel) {
    state.selectedClientId = Number(clientId);
    state.selectedClientLabel = clientLabel || `Client #${clientId}`;
    state.selectedExpenseIds = new Set();
    state.pickerClientId = Number(clientId);
    els.billItemsList.innerHTML = '<div class="empty-note">No bill items yet. Select client expenses or add a manual item.</div>';
    updateBillTotal();
    setCookie(KNOWN_COOKIE, String(clientId));
    renderClientTree();
    updateBanner();
    els.expenseRows.innerHTML = '<tr><td colspan="5" class="text-center py-4">Loading expenses and analysis…</td></tr>';
    await loadAnalysis(clientId);
  }

  async function loadInitialData() {
    const [clientsRes] = await Promise.all([
      fetchJSON(`${API_BASE}/clients/all`),
      fetchJSON(`${API_BASE}/client-bill-heads/all`).then((res) => {
        billHeads.splice(0, billHeads.length, ...(Array.isArray(res.data) ? res.data : []));
      }).catch(() => {}),
    ]);
    state.clients = Array.isArray(clientsRes.data) ? clientsRes.data : [];
    state.treeRoots = buildTree(state.clients);
    renderClientTree();

    const queryId = Number(new URLSearchParams(window.location.search).get('client_id') || 0);
    const cookieId = Number(getCookie(KNOWN_COOKIE) || 0);
    const initialId = queryId || cookieId;
    if (initialId > 0) {
      const match = state.clients.find((client) => Number(client.id) === initialId);
      if (match) {
        await selectClient(match.id, match.name || `Client #${match.id}`);
      }
    }
  }

  function collectPayload() {
    const rows = Array.from(els.billItemsList.querySelectorAll('.item-row'));
    const items = [];
    for (const row of rows) {
      const title = row.querySelector('.item-title')?.value?.trim() || '';
      const amountRaw = row.querySelector('.item-amount')?.value ?? '';
      const amount = amountRaw === '' ? null : Number(amountRaw);
      if (!title || amount === null || Number.isNaN(amount)) {
        throw new Error('Every bill item needs a title and amount.');
      }
      const meta = row.dataset.source === 'expense'
        ? {
            source: 'expense',
            expense_id: Number(row.dataset.expenseId || 0) || null,
            job_id: Number(row.dataset.jobId || 0) || null,
          }
        : { source: 'manual' };
      const manualHeadId = row.querySelector('.item-head-select')?.value || null;
      items.push({
        client_bill_head_id: manualHeadId ? Number(manualHeadId) : null,
        bill_head_title: title,
        amount,
        metadata: meta,
      });
    }
    if (!items.length) {
      throw new Error('Select at least one expense or add a manual bill item.');
    }
    return {
      client_id: state.selectedClientId,
      bill_date: els.billDate.value,
      due_date: els.dueDate.value || null,
      notes: els.billNotes.value.trim() || null,
      items,
    };
  }

  els.clientTreeSearch.addEventListener('input', renderClientTree);
  els.openClientPickerBtn.addEventListener('click', () => {
    state.pickerClientId = state.selectedClientId;
    renderClientTree();
    openPicker();
  });
  [els.closeClientPickerBtn, els.cancelClientPickerBtn].forEach((btn) => btn.addEventListener('click', closePicker));
  els.clientPickerModal.addEventListener('click', (event) => {
    if (event.target === els.clientPickerModal) closePicker();
  });
  els.billDetailModal.addEventListener('click', (event) => {
    if (event.target === els.billDetailModal) closeBillDetails();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && els.clientPickerModal.classList.contains('show')) {
      closePicker();
    }
    if (event.key === 'Escape' && els.billDetailModal.classList.contains('show')) {
      closeBillDetails();
    }
  });
  els.closeBillDetailBtn.addEventListener('click', closeBillDetails);
  els.applyClientPickerBtn.addEventListener('click', async () => {
    const clientId = Number(state.pickerClientId || 0);
    if (!clientId) {
      Swal.fire({ icon:'warning', title:'Client required', text:'Select a client tree before continuing.' });
      return;
    }
    const client = state.clients.find((row) => Number(row.id) === clientId);
    if (!client) return;
    closePicker();
    try {
      await selectClient(client.id, client.name || `Client #${client.id}`);
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to load client analysis', text:String(error.message || error) });
    }
  });
  els.clearSelectedClientBtn.addEventListener('click', resetBuilderState);
  els.addManualItemBtn.addEventListener('click', addManualItem);
  els.clientTreeShell.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) || target.name !== 'client_tree_pick') return;
    state.pickerClientId = Number(target.value || 0);
    renderClientTree();
  });
  els.expenseRows.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) || !target.classList.contains('expense-check')) return;
    const expenseId = Number(target.dataset.expenseId || 0);
    const expense = state.expenseMap.get(expenseId);
    if (!expense) return;
    if (target.checked) {
      state.selectedExpenseIds.add(expenseId);
      addExpenseItem(expense);
    } else {
      state.selectedExpenseIds.delete(expenseId);
      removeItemRow(`expense-${expenseId}`);
    }
    updateExpenseSelectionCount();
  });
  els.previousBillsList.addEventListener('click', async (event) => {
    const tabBtn = event.target.closest('[data-bill-tab]');
    if (tabBtn) {
      state.activeBillTab = tabBtn.dataset.billTab === 'draft' ? 'draft' : 'published';
      renderPreviousBills(state.analysisBills);
      return;
    }
    const viewBtn = event.target.closest('[data-bill-view]');
    if (viewBtn) {
      try {
        await openBillDetailModal(viewBtn.dataset.billView);
      } catch (error) {
        Swal.fire({ icon:'error', title:'Unable to load bill', text:String(error.message || error) });
      }
      return;
    }
    const pdfBtn = event.target.closest('[data-bill-pdf]');
    if (pdfBtn) {
      try {
        await downloadBillPdf(pdfBtn.dataset.billPdf);
      } catch (error) {
        Swal.fire({ icon:'error', title:'Download failed', text:String(error.message || error) });
      }
      return;
    }
    const publishBtn = event.target.closest('[data-bill-publish]');
    if (publishBtn) {
      try {
        await publishBillFromAnalysis(publishBtn.dataset.billPublish);
      } catch (error) {
        Swal.fire({ icon:'error', title:'Unable to publish bill', text:String(error.message || error) });
      }
    }
  });

  els.billCreateForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!state.selectedClientId) {
      Swal.fire({ icon:'warning', title:'Client required', text:'Choose a client tree before creating the bill.' });
      return;
    }
    if (!els.billDate.value) {
      Swal.fire({ icon:'warning', title:'Bill date required', text:'Enter the bill date before creating the bill.' });
      return;
    }

    let payload;
    try {
      payload = collectPayload();
    } catch (error) {
      Swal.fire({ icon:'warning', title:'Bill items incomplete', text:String(error.message || error) });
      return;
    }

    setBtnLoading(els.saveBillBtn, true);
    try {
      const res = await fetch(`${API_BASE}/client-bills`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const errs = data?.errors ? Object.entries(data.errors).map(([key, value]) => `${key}: ${[].concat(value).join(', ')}`).join('\n') : '';
        throw new Error(errs ? `${data?.message || 'Create failed'}\n${errs}` : (data?.message || 'Create failed'));
      }
      await Swal.fire({ icon:'success', title:'Draft bill created', text:'The new client bill has been saved as a draft.' });
      window.location.href = @json($billingBackUrl);
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to create bill', text:String(error.message || error) });
    } finally {
      setBtnLoading(els.saveBillBtn, false);
    }
  });

  els.billDate.value = new Date().toISOString().slice(0, 10);
  updateBanner();
  updateSummary({});
  renderPreviousBills([]);
  updateBillTotal();
  loadInitialData().catch((error) => {
    Swal.fire({ icon:'error', title:'Unable to load bill builder', text:String(error.message || error) });
    els.clientTreeShell.innerHTML = '<div class="empty-note">Unable to load clients right now.</div>';
  });
})();
</script>
@endpush
