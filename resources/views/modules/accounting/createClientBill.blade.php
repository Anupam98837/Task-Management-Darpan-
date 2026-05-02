@push('styles')
<style>
* { box-sizing: border-box; }

.bill-builder-page {
  background:
    radial-gradient(circle at top left, rgba(59,130,246,.08), transparent 22%),
    radial-gradient(circle at bottom right, rgba(16,185,129,.08), transparent 20%),
    var(--bg-body);
  min-height: 100vh;
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
}
.builder-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
.builder-header h1 { margin:0; font-size:28px; font-weight:800; color:var(--text-color); }
.builder-header p { margin:6px 0 0; color:#64748b; font-size:14px; max-width:760px; }
.builder-actions { display:flex; gap:10px; flex-wrap:wrap; }
.builder-grid { display:grid; grid-template-columns: minmax(0, 1.55fr) minmax(320px, .95fr); gap:20px; align-items:start; }
.panel-card {
  background: rgba(255,255,255,.94);
  border:1px solid rgba(226,232,240,.9);
  border-radius:22px;
  box-shadow:0 18px 36px rgba(15,23,42,.08);
  overflow:hidden;
}
.panel-inner { padding:20px; }
.section-title { font-size:16px; font-weight:800; color:#0f172a; margin:0 0 12px; }
.section-sub { font-size:13px; color:#64748b; margin:0 0 16px; }
.btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; padding:0 18px; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer; transition:all .2s; text-decoration:none; border:none; }
.btn-primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; box-shadow:0 10px 20px rgba(37,99,235,.2); }
.btn-secondary { background:#fff; border:1px solid #dbe5f0; color:#0f172a; }
.btn-soft { background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; }
.btn-danger-soft { background:#fff1f2; border:1px solid #fecdd3; color:#be123c; }
.form-label { display:block; font-size:13px; font-weight:700; color:#475569; margin-bottom:8px; }
.form-control, .form-select {
  width:100%; min-height:44px; padding:10px 14px; border:1px solid #dbe5f0; border-radius:12px;
  background:#fff; font-size:14px; color:#0f172a;
}
textarea.form-control { min-height: 96px; resize: vertical; }
.field-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:14px; }
.stack { display:flex; flex-direction:column; gap:18px; }
.tree-shell { border:1px solid #e2e8f0; border-radius:16px; background:#fff; max-height:320px; overflow:auto; padding:10px; }
.tree-node { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border-radius:12px; }
.tree-node:hover { background:#f8fafc; }
.tree-node.active { background:#eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
.tree-children { margin-left:18px; padding-left:12px; border-left:1px solid #e2e8f0; }
.tree-node input { margin-top:4px; }
.tree-meta { display:flex; flex-direction:column; gap:3px; }
.tree-meta strong { font-size:14px; color:#0f172a; }
.tree-meta small { color:#94a3b8; font-size:12px; }
.picker-head { display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:12px; }
.selected-client-banner {
  display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;
  padding:14px 16px; border-radius:16px; background:linear-gradient(135deg,#eff6ff,#f8fafc); border:1px solid #bfdbfe;
}
.selected-client-banner strong { display:block; color:#0f172a; font-size:16px; }
.selected-client-banner small { color:#64748b; }
.search-box { position:relative; flex:1; min-width:220px; }
.search-box input { width:100%; height:44px; padding:0 14px 0 40px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; }
.search-box i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.expense-table-wrap { border:1px solid #e2e8f0; border-radius:18px; overflow:hidden; background:#fff; }
.expense-table-scroll { max-height:320px; overflow:auto; }
.expense-table { width:100%; border-collapse:collapse; }
.expense-table th, .expense-table td { padding:12px 14px; border-bottom:1px solid #eef2f7; font-size:13px; vertical-align:top; text-align:left; }
.expense-table th { position:sticky; top:0; background:#f8fafc; text-transform:uppercase; letter-spacing:.4px; font-size:11px; color:#64748b; z-index:1; }
.expense-check { width:18px; height:18px; }
.pill { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:700; background:#f8fafc; border:1px solid #e2e8f0; color:#475569; }
.item-list { display:flex; flex-direction:column; gap:12px; }
.item-row { border:1px solid #e2e8f0; border-radius:16px; background:#fff; padding:14px; }
.item-row.source-expense { background:linear-gradient(180deg,#f8fbff,#ffffff); border-color:#cfe0ff; }
.item-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
.item-top strong { color:#0f172a; }
.item-top small { color:#64748b; display:block; margin-top:4px; }
.item-grid { display:grid; grid-template-columns: 1fr 1fr 180px auto; gap:12px; align-items:end; }
.summary-kpis { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; }
.kpi-box { border:1px solid #e2e8f0; border-radius:16px; padding:16px; background:#fff; }
.kpi-box .label { color:#64748b; font-size:11px; text-transform:uppercase; letter-spacing:.5px; font-weight:700; }
.kpi-box .value { margin-top:8px; font-size:22px; font-weight:800; color:#0f172a; }
.analysis-list { display:flex; flex-direction:column; gap:10px; }
.analysis-card { border:1px solid #e2e8f0; border-radius:16px; padding:14px; background:#fff; }
.analysis-card strong { color:#0f172a; }
.analysis-card small { color:#64748b; display:block; margin-top:4px; }
.total-bar {
  display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;
  padding:16px 18px; border-radius:18px; background:#0f172a; color:#fff;
}
.empty-note { padding:18px; border:1px dashed #cbd5e1; border-radius:16px; color:#94a3b8; text-align:center; background:#fff; }
.right-scroll { max-height: calc(100vh - 170px); overflow:auto; padding:20px; }
.left-scroll { padding:20px; }
.helper-text { color:#64748b; font-size:12px; margin-top:6px; }
.btn.is-loading, .btn[aria-busy="true"] { pointer-events:none; opacity:.85; position:relative; }
.btn.is-loading .btn-label { visibility:hidden; }
.btn.is-loading::after { content:""; position:absolute; inset:0; margin:auto; width:18px; height:18px; border-radius:50%; border:2.5px solid rgba(255,255,255,.75); border-top-color:transparent; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

@media (max-width: 1200px) {
  .builder-grid { grid-template-columns: 1fr; }
  .right-scroll { max-height:none; }
}
@media (max-width: 768px) {
  .bill-builder-page { padding:16px; }
  .field-grid, .summary-kpis, .item-grid { grid-template-columns: 1fr; }
  .left-scroll, .right-scroll { padding:16px; }
}
</style>
@endpush

@section('content')
<div class="bill-builder-page">
  <div class="builder-header">
    <div>
      <h1>Create Client Bill</h1>
      <p>Choose a client tree, pull in existing expenses, and build a bill while comparing it against job budgets, total expenses, and previous client bills.</p>
    </div>
    <div class="builder-actions">
      <a href="{{ url('/admin/accounting/client-bills') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Back To Bills
      </a>
      <button type="button" class="btn btn-soft" id="clearSelectedClientBtn">
        <i class="fa-solid fa-rotate-left"></i>
        Reset Client
      </button>
    </div>
  </div>

  <div class="builder-grid">
    <section class="panel-card">
      <div class="left-scroll stack">
        <div>
          <div class="picker-head">
            <div>
              <h2 class="section-title">1. Choose Client Tree</h2>
              <p class="section-sub">Selecting a parent client includes child client jobs and expenses in the analysis.</p>
            </div>
            <div class="search-box">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="clientTreeSearch" placeholder="Search clients...">
            </div>
          </div>

          <div class="selected-client-banner" id="selectedClientBanner">
            <div>
              <strong>No client selected</strong>
              <small>Pick a client or parent client tree to load budgets, expenses, and previous bills.</small>
            </div>
            <span class="pill">Waiting</span>
          </div>

          <div class="tree-shell mt-3" id="clientTreeShell">
            <div class="empty-note">Loading client tree…</div>
          </div>
        </div>

        <form id="billCreateForm" class="stack" autocomplete="off">
          <div>
            <h2 class="section-title">2. Bill Basics</h2>
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
                <h2 class="section-title">3. Pull Client Expenses</h2>
                <p class="section-sub">Tick expenses to add them into the bill automatically. You can still edit the amount afterward.</p>
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

          <div>
            <div class="picker-head">
              <div>
                <h2 class="section-title">4. Bill Items</h2>
                <p class="section-sub">Selected expenses appear here as bill items. You can also add manual bill heads for extra charges or adjustments.</p>
              </div>
              <button type="button" class="btn btn-secondary" id="addManualItemBtn">
                <i class="fa-solid fa-plus"></i>
                Add Manual Item
              </button>
            </div>

            <div class="item-list" id="billItemsList">
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
        <div>
          <h2 class="section-title">Client Analysis</h2>
          <p class="section-sub">Use this panel to compare billing against budgets, real expenses, and previous bills for the same client tree.</p>
        </div>

        <div class="summary-kpis">
          <div class="kpi-box">
            <div class="label">Total Budget</div>
            <div class="value" id="statBudget">Rs 0.00</div>
          </div>
          <div class="kpi-box">
            <div class="label">Total Expenses</div>
            <div class="value" id="statExpenses">Rs 0.00</div>
          </div>
          <div class="kpi-box">
            <div class="label">Published Bills</div>
            <div class="value" id="statPublishedBills">0</div>
          </div>
          <div class="kpi-box">
            <div class="label">Remaining Budget</div>
            <div class="value" id="statRemainingBudget">Rs 0.00</div>
          </div>
        </div>

        <div class="analysis-card">
          <strong>Scope Overview</strong>
          <small id="scopeOverview">Choose a client tree to see the analysis summary.</small>
        </div>

        <div>
          <h3 class="section-title" style="font-size:15px;">Recent Bills In This Client Tree</h3>
          <div class="analysis-list" id="previousBillsList">
            <div class="empty-note">No analysis yet.</div>
          </div>
        </div>
      </div>
    </aside>
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
  };

  const els = {
    clientTreeSearch: document.getElementById('clientTreeSearch'),
    clientTreeShell: document.getElementById('clientTreeShell'),
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
    statPublishedBills: document.getElementById('statPublishedBills'),
    statRemainingBudget: document.getElementById('statRemainingBudget'),
    scopeOverview: document.getElementById('scopeOverview'),
    previousBillsList: document.getElementById('previousBillsList'),
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
      const checked = Number(state.selectedClientId) === Number(node.id) ? 'checked' : '';
      const active = Number(state.selectedClientId) === Number(node.id) ? 'active' : '';
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
  }

  function updateBanner() {
    const html = state.selectedClientId ? `
      <div>
        <strong>${esc(state.selectedClientLabel || `Client #${state.selectedClientId}`)}</strong>
        <small>${state.selectedTreeIds.length} client records included in this billing tree.</small>
      </div>
      <span class="pill">${state.selectedTreeIds.length} in scope</span>` : `
      <div>
        <strong>No client selected</strong>
        <small>Pick a client or parent client tree to load budgets, expenses, and previous bills.</small>
      </div>
      <span class="pill">Waiting</span>`;
    els.selectedClientBanner.innerHTML = html;
  }

  function renderPreviousBills(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      els.previousBillsList.innerHTML = '<div class="empty-note">No previous bills found for this client tree.</div>';
      return;
    }
    els.previousBillsList.innerHTML = rows.map((bill) => `
      <div class="analysis-card">
        <strong>Bill #${esc(bill.id)}</strong>
        <small>${esc(bill.client_name || '—')} · ${bill.is_published ? 'Published' : 'Draft'} · ${fmtDate(bill.bill_date)}</small>
        <div style="margin-top:8px;font-weight:800;color:#0f172a;">${esc(money(bill.total_amount))}</div>
      </div>
    `).join('');
  }

  function updateSummary(stats = {}) {
    els.statBudget.textContent = money(stats.total_budget || 0);
    els.statExpenses.textContent = money(stats.total_expense_amount || 0);
    els.statPublishedBills.textContent = String(stats.published_bill_count || 0);
    els.statRemainingBudget.textContent = money(stats.remaining_budget || 0);
    els.scopeOverview.textContent = state.selectedClientId
      ? `${stats.tree_client_count || 0} clients, ${stats.jobs_count || 0} jobs, ${stats.expense_count || 0} expenses, ${stats.draft_bill_count || 0} draft bills.`
      : 'Choose a client tree to see the analysis summary.';
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
        <button type="button" class="btn btn-danger-soft remove-item-btn"><i class="fa-solid fa-trash"></i>Remove</button>
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
        <div>
          <button type="button" class="btn btn-secondary remove-item-btn"><i class="fa-solid fa-xmark"></i>Remove</button>
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
        <button type="button" class="btn btn-danger-soft remove-item-btn"><i class="fa-solid fa-trash"></i>Remove</button>
      </div>
      <div class="item-grid">
        <div>
          <label class="form-label">Bill Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control item-title" value="${esc(expense.expense_head_title || `Expense #${expense.id}`)}">
        </div>
        <div>
          <label class="form-label">Amount <span class="text-danger">*</span></label>
          <input type="number" min="0" step="0.01" class="form-control item-amount" value="${esc(expense.amount || 0)}">
        </div>
        <div>
          <button type="button" class="btn btn-secondary remove-item-btn"><i class="fa-solid fa-xmark"></i>Remove</button>
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
  els.clearSelectedClientBtn.addEventListener('click', () => {
    state.selectedClientId = null;
    state.selectedClientLabel = '';
    state.selectedTreeIds = [];
    state.expenses = [];
    state.expenseMap = new Map();
    state.selectedExpenseIds = new Set();
    renderClientTree();
    updateBanner();
    renderExpenses([]);
    renderPreviousBills([]);
    updateSummary({});
    els.billItemsList.innerHTML = '<div class="empty-note">No bill items yet. Select client expenses or add a manual item.</div>';
    updateBillTotal();
    setCookie(KNOWN_COOKIE, '');
  });
  els.addManualItemBtn.addEventListener('click', addManualItem);
  els.clientTreeShell.addEventListener('change', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) || target.name !== 'client_tree_pick') return;
    const clientId = Number(target.value || 0);
    const client = state.clients.find((row) => Number(row.id) === clientId);
    if (!client) return;
    try {
      await selectClient(client.id, client.name || `Client #${client.id}`);
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to load client analysis', text:String(error.message || error) });
    }
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
      window.location.href = '{{ url('/admin/accounting/client-bills') }}';
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
