@php
  $repaymentRole = $repaymentRole ?? 'admin';
  $repaymentLoginUrl = $repaymentLoginUrl ?? '/';
  $repaymentBillsUrl = $repaymentBillsUrl ?? '/admin/accounting/client-bills';
@endphp

@push('styles')
<style>
.repayments-page{
  background:
    radial-gradient(circle at top left, rgba(14,165,233,.09), transparent 24%),
    radial-gradient(circle at bottom right, rgba(37,99,235,.08), transparent 20%),
    var(--bg-body);
  min-height:100vh;
  padding:24px;
  font-family:var(--font-sans);
}
.rp-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.rp-header h1{margin:0;font-size:28px;font-weight:800;color:var(--text-color)}
.rp-header p{margin:6px 0 0;color:#64748b;font-size:14px;max-width:820px}
.rp-actions{display:flex;gap:10px;flex-wrap:wrap}
.rp-toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
.rp-search{position:relative;flex:1;min-width:240px;max-width:420px}
.rp-search input{width:100%;height:44px;padding:0 14px 0 40px;border:1px solid #dbe5f0;border-radius:12px;background:#fff;color:#0f172a;font-size:14px}
.rp-search i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8}
.rp-btn,.rp-chip,.rp-input{height:44px;border:1px solid #dbe5f0;border-radius:12px;background:#fff;color:#0f172a;font-size:14px}
.rp-btn{padding:0 14px;font-weight:700;display:inline-flex;align-items:center;gap:8px;cursor:pointer;text-decoration:none}
.rp-btn.primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);border-color:#2563eb;color:#fff}
.rp-btn.soft{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.rp-chip{min-width:220px;padding:0 14px;display:inline-flex;align-items:center;justify-content:space-between;gap:10px;cursor:pointer}
.rp-tabs{display:inline-flex;align-items:center;gap:8px;padding:6px;border-radius:16px;background:#eaf1fb;border:1px solid #d8e6f7}
.rp-tab{min-width:110px;height:40px;border:0;border-radius:12px;background:transparent;color:#526070;font-size:13px;font-weight:800;cursor:pointer;transition:.2s}
.rp-tab.active{background:#fff;color:#1d4ed8;box-shadow:0 8px 18px rgba(37,99,235,.12)}
.rp-card-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:18px 18px 0}
.rp-card-title{display:flex;flex-direction:column;gap:4px}
.rp-card-title strong{font-size:18px;color:#0f172a}
.rp-card-title small{color:#64748b}
.rp-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:18px}
.rp-stat{background:rgba(255,255,255,.96);border:1px solid rgba(226,232,240,.9);border-radius:20px;box-shadow:0 16px 32px rgba(15,23,42,.07);padding:18px}
.rp-stat small{display:block;color:#64748b;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px}
.rp-stat strong{display:block;color:#0f172a;font-size:28px;line-height:1}
.rp-card{background:rgba(255,255,255,.96);border:1px solid rgba(226,232,240,.9);border-radius:22px;box-shadow:0 18px 36px rgba(15,23,42,.08);overflow:hidden}
.rp-table-wrap{overflow:auto}
.rp-table{width:100%;border-collapse:collapse}
.rp-table th,.rp-table td{padding:14px 16px;border-bottom:1px solid #eef2f7;text-align:left;font-size:14px;color:var(--text-color);vertical-align:top}
.rp-table th{background:#f8fafc;text-transform:uppercase;font-size:11px;letter-spacing:.5px;color:#64748b}
.rp-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid transparent}
.rp-badge.due{background:#fff7ed;color:#c2410c;border-color:#fdba74}
.rp-badge.paid{background:#dcfce7;color:#15803d;border-color:#86efac}
.rp-badge.pending{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
.rp-empty{text-align:center;padding:56px 20px;color:#94a3b8}
.rp-empty h3{margin:0 0 8px;font-size:18px;color:#475569}
.rp-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:18px;background:#f8fafc}
.rp-pages{display:flex;gap:6px;flex-wrap:wrap}
.rp-page-btn{min-width:38px;height:38px;border:1px solid #dbe5f0;border-radius:10px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer}
.rp-page-btn.active{background:#2563eb;border-color:#2563eb;color:#fff}
.rp-page-btn:disabled{opacity:.4;cursor:not-allowed}
.rp-inline-actions{display:flex;gap:8px;flex-wrap:wrap}
.rp-mini-btn{height:34px;padding:0 12px;border-radius:10px;border:1px solid #dbe5f0;background:#fff;color:#0f172a;font-size:12px;font-weight:800;display:inline-flex;align-items:center;gap:7px;cursor:pointer}
.rp-mini-btn.primary{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.rp-mini-btn.success{background:#ecfdf5;border-color:#bbf7d0;color:#047857}
.rp-mini-btn[disabled]{opacity:.45;cursor:not-allowed}
.tree-shell{border:1px solid #e2e8f0;border-radius:16px;background:#fff;max-height:420px;overflow:auto;padding:10px}
.tree-node{display:flex;align-items:flex-start;gap:10px;padding:8px 10px;border-radius:12px;cursor:pointer}
.tree-node:hover{background:#f8fafc}
.tree-node.active{background:#eff6ff;box-shadow:inset 0 0 0 1px #bfdbfe}
.tree-children{margin-left:18px;padding-left:12px;border-left:1px solid #e2e8f0}
.tree-meta{display:flex;flex-direction:column;gap:3px}
.tree-meta strong{font-size:14px;color:#0f172a}
.tree-meta small{color:#94a3b8;font-size:12px}
.rp-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.rp-detail-box,.rp-list-card{border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc}
.rp-detail-box small,.rp-list-card small{display:block;color:#64748b;margin-bottom:4px}
.rp-line{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #e5edf6}
.rp-line:last-child{border-bottom:0}
.rp-proof-list{display:flex;flex-direction:column;gap:10px;margin-top:12px}
.rp-proof-link{display:inline-flex;align-items:center;gap:8px;color:#1d4ed8;text-decoration:none;font-weight:700}
.rp-note{font-size:12px;color:#64748b}
.rp-accent{color:#1d4ed8}
@media (max-width: 900px){.rp-detail-grid{grid-template-columns:1fr}}
@media (max-width: 768px){.repayments-page{padding:16px}}
</style>
@endpush

<div class="repayments-page">
  <div class="rp-header">
    <div>
      <h1>Bill Repayments</h1>
      <p>Track published bill balances, separate due bills from fully paid bills, and add repayments only where a remaining balance still exists.</p>
    </div>
    <div class="rp-actions">
      <a href="{{ $repaymentBillsUrl }}" class="rp-btn soft">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        Bills
      </a>
    </div>
  </div>

  <div class="rp-toolbar">
    <div class="rp-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="billSearch" placeholder="Search by client or bill id...">
    </div>
    <button type="button" class="rp-chip" id="clientFilterBtn">
      <span id="clientFilterLabel">All Clients</span>
      <i class="fa-solid fa-sitemap"></i>
    </button>
    <button type="button" class="rp-btn" id="clearClientFilterBtn">
      <i class="fa-solid fa-xmark"></i>
      Clear Client
    </button>
    <button type="button" class="rp-btn" id="refreshBillsBtn">
      <i class="fa-solid fa-rotate"></i>
      Refresh
    </button>
  </div>

  <div class="rp-summary">
    <div class="rp-stat">
      <small>Visible Bills</small>
      <strong id="statBills">0</strong>
    </div>
    <div class="rp-stat">
      <small>Total Amount</small>
      <strong id="statTotal">Rs 0.00</strong>
    </div>
    <div class="rp-stat">
      <small>Total Paid</small>
      <strong id="statPaid">Rs 0.00</strong>
    </div>
    <div class="rp-stat">
      <small>Total Remaining</small>
      <strong id="statRemaining">Rs 0.00</strong>
    </div>
  </div>

  <div class="rp-card">
    <div class="rp-card-head">
      <div class="rp-card-title">
        <strong>Client Bill Ledger</strong>
        <small>All published bills, repayment balance, PDF export, and repayment history in one place.</small>
      </div>
      <div class="rp-tabs" id="bucketTabs">
        <button type="button" class="rp-tab active" data-bucket="due">Due Bills</button>
        <button type="button" class="rp-tab" data-bucket="paid">Paid Bills</button>
      </div>
    </div>
    <div class="rp-table-wrap">
      <table class="rp-table">
        <thead>
          <tr>
            <th>Bill</th>
            <th>Client</th>
            <th>Bill Date</th>
            <th>Due Date</th>
            <th>Total Amount</th>
            <th>Repaid Amount</th>
            <th>Due Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="billRows">
          <tr><td colspan="9" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
    <div class="rp-pagination">
      <div id="billPaginationInfo">Showing 0-0 of 0 bills</div>
      <div class="rp-pages" id="billPager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="clientTreeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Choose Client Tree</h5>
          <div class="text-muted small">Filter the due/paid bill list using one client branch.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="rp-search mb-3" style="max-width:none;">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="clientTreeSearch" placeholder="Search clients...">
        </div>
        <div class="tree-shell" id="clientTreeShell">
          <div class="text-center py-4 text-muted">Loading client tree…</div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="text-muted small" id="clientTreeSelectionLabel">All Clients</div>
        <div class="d-flex gap-2">
          <button type="button" class="rp-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="rp-btn primary" id="applyClientTreeBtn">Use Client</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="repaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="repaymentForm" autocomplete="off">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-1">Add Repayment</h5>
            <div class="text-muted small" id="repaymentModalSubtitle">Published bill repayments are approved immediately for admin/accountant users and submitted for approval by client users.</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="rp-detail-grid mb-3">
            <div class="rp-detail-box"><small>Bill</small><strong id="formBillTitle">—</strong></div>
            <div class="rp-detail-box"><small>Total</small><strong id="formBillTotal">Rs 0.00</strong></div>
            <div class="rp-detail-box"><small>Remaining</small><strong id="formBillRemaining">Rs 0.00</strong></div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Repayment Date <span class="text-danger">*</span></label>
              <input type="date" id="formRepaymentDate" class="rp-input" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
              <input type="number" min="0.01" step="0.01" id="formAmount" class="rp-input" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Proof Files</label>
              <input type="file" id="formAttachments" class="rp-input" multiple>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Note</label>
              <textarea id="formNote" class="form-control" rows="4" placeholder="Optional note, reference, or payment context"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="d-flex gap-2">
            <button type="button" class="rp-btn" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="rp-btn primary" id="saveRepaymentBtn">
              <i class="fa-solid fa-check"></i>
              Save Repayment
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="billRepaymentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Bill Repayments</h5>
          <div class="text-muted small" id="billRepaymentsSubtitle">Review all repayments linked to this bill.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="billRepaymentsBody">
        <div class="text-center text-muted py-4">Loading…</div>
      </div>
      <div class="modal-footer" id="billRepaymentsFooter"></div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const API_BASE = @json(url('/api'));
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const LOGIN_URL = @json($repaymentLoginUrl);
  const PORTAL_ROLE = @json($repaymentRole);
  const DIRECT_APPROVAL = PORTAL_ROLE === 'admin' || PORTAL_ROLE === 'accountant_user';
  if (!TOKEN) {
    setTimeout(() => { window.location.href = LOGIN_URL; }, 400);
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const clientTreeModal = new bootstrap.Modal(document.getElementById('clientTreeModal'));
  const repaymentModal = new bootstrap.Modal(document.getElementById('repaymentModal'));
  const billRepaymentsModal = new bootstrap.Modal(document.getElementById('billRepaymentsModal'));

  const state = {
    page: 1,
    total: 0,
    totalPages: 1,
    q: '',
    bucket: 'due',
    clientId: '',
    pendingTreeClientId: '',
    clients: [],
    clientTreeRoots: [],
    items: [],
    currentBill: null,
  };

  const els = {
    billSearch: document.getElementById('billSearch'),
    clientFilterBtn: document.getElementById('clientFilterBtn'),
    clientFilterLabel: document.getElementById('clientFilterLabel'),
    clearClientFilterBtn: document.getElementById('clearClientFilterBtn'),
    bucketTabs: document.getElementById('bucketTabs'),
    refreshBillsBtn: document.getElementById('refreshBillsBtn'),
    statBills: document.getElementById('statBills'),
    statTotal: document.getElementById('statTotal'),
    statPaid: document.getElementById('statPaid'),
    statRemaining: document.getElementById('statRemaining'),
    billRows: document.getElementById('billRows'),
    billPaginationInfo: document.getElementById('billPaginationInfo'),
    billPager: document.getElementById('billPager'),
    clientTreeSearch: document.getElementById('clientTreeSearch'),
    clientTreeShell: document.getElementById('clientTreeShell'),
    clientTreeSelectionLabel: document.getElementById('clientTreeSelectionLabel'),
    applyClientTreeBtn: document.getElementById('applyClientTreeBtn'),
    repaymentForm: document.getElementById('repaymentForm'),
    formBillTitle: document.getElementById('formBillTitle'),
    formBillTotal: document.getElementById('formBillTotal'),
    formBillRemaining: document.getElementById('formBillRemaining'),
    formRepaymentDate: document.getElementById('formRepaymentDate'),
    formAmount: document.getElementById('formAmount'),
    formAttachments: document.getElementById('formAttachments'),
    formNote: document.getElementById('formNote'),
    repaymentModalSubtitle: document.getElementById('repaymentModalSubtitle'),
    saveRepaymentBtn: document.getElementById('saveRepaymentBtn'),
    billRepaymentsSubtitle: document.getElementById('billRepaymentsSubtitle'),
    billRepaymentsBody: document.getElementById('billRepaymentsBody'),
    billRepaymentsFooter: document.getElementById('billRepaymentsFooter'),
  };

  const esc = (value = '') => String(value).replace(/[&<>"']/g, (m) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[m]));
  const fmtDate = (value) => {
    if (!value) return '—';
    const dt = new Date(value);
    return Number.isNaN(dt.getTime()) ? esc(value) : dt.toLocaleDateString('en-IN', { year:'numeric', month:'short', day:'numeric' });
  };
  const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const toast = (icon, title) => Swal.fire({ toast:true, position:'top-end', timer:1700, showConfirmButton:false, icon, title });

  function setBtnLoading(btn, on) {
    btn.disabled = !!on;
    btn.style.opacity = on ? '.75' : '1';
  }

  async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, { headers, ...opts });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
    return data;
  }

  function normalizeClients(rows) {
    return Array.isArray(rows) ? rows.map((row) => ({
      id: row.id,
      name: row.name || `Client #${row.id}`,
      parent_id: row.parent_id ?? null,
    })) : [];
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

  function labelForClient(clientId, fallback = 'All Clients') {
    const match = state.clients.find((client) => String(client.id) === String(clientId || ''));
    return match ? (match.name || `Client #${match.id}`) : fallback;
  }

  function updateClientLabels() {
    els.clientFilterLabel.textContent = state.clientId ? labelForClient(state.clientId) : 'All Clients';
  }

  function renderClientTree() {
    const query = String(els.clientTreeSearch.value || '').trim().toLowerCase();
    const activeId = Number(state.pendingTreeClientId || state.clientId || 0);

    const matchNode = (node) => {
      const selfMatch = !query || String(node.name || '').toLowerCase().includes(query);
      const childMatches = (node.children || []).map(matchNode).filter(Boolean);
      if (!selfMatch && !childMatches.length) return null;
      return { ...node, children: childMatches };
    };

    const filteredRoots = state.clientTreeRoots.map(matchNode).filter(Boolean);
    if (!filteredRoots.length) {
      els.clientTreeShell.innerHTML = '<div class="text-center py-4 text-muted">No clients match your search.</div>';
      return;
    }

    const renderNodes = (nodes, depth = 0) => nodes.map((node) => {
      const checked = activeId === Number(node.id) ? 'checked' : '';
      const active = activeId === Number(node.id) ? 'active' : '';
      return `
        <div class="tree-node ${active}">
          <input type="radio" name="client_tree_pick" value="${esc(node.id)}" ${checked}>
          <div class="tree-meta">
            <strong>${esc(node.name || `Client #${node.id}`)}</strong>
            <small>${depth === 0 ? 'Root client' : `Nested level ${depth}`}</small>
          </div>
        </div>
        ${node.children && node.children.length ? `<div class="tree-children">${renderNodes(node.children, depth + 1)}</div>` : ''}`;
    }).join('');

    els.clientTreeShell.innerHTML = renderNodes(filteredRoots);
    els.clientTreeSelectionLabel.textContent = activeId ? labelForClient(activeId, 'Choose Client') : 'All Clients';
  }

  async function loadClients() {
    const data = await fetchJSON(`${API_BASE}/clients/all`);
    state.clients = normalizeClients(data.data || []);
    state.clientTreeRoots = buildTree(state.clients);
    updateClientLabels();
    renderClientTree();
  }

  function updateSummary() {
    const totalBills = state.total;
    const totalAmount = state.items.reduce((sum, row) => sum + Number(row.total_amount || 0), 0);
    const totalPaid = state.items.reduce((sum, row) => sum + Number(row.paid_amount || 0), 0);
    const totalRemaining = state.items.reduce((sum, row) => sum + Number(row.remaining_amount || 0), 0);
    els.statBills.textContent = String(totalBills || 0);
    els.statTotal.textContent = money(totalAmount);
    els.statPaid.textContent = money(totalPaid);
    els.statRemaining.textContent = money(totalRemaining);
  }

  function renderPager() {
    const start = state.total ? ((state.page - 1) * 10) + 1 : 0;
    const end = Math.min(state.total, state.page * 10);
    els.billPaginationInfo.textContent = `Showing ${start}-${end} of ${state.total} bills`;
    const buttons = [];
    buttons.push(`<button class="rp-page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let page = Math.max(1, state.page - 2); page <= Math.min(state.totalPages, state.page + 2); page += 1) {
      buttons.push(`<button class="rp-page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>`);
    }
    buttons.push(`<button class="rp-page-btn" data-page="${state.page + 1}" ${state.page >= state.totalPages ? 'disabled' : ''}>Next</button>`);
    els.billPager.innerHTML = buttons.join('');
  }

  function statusBadge(row) {
    const key = Number(row.remaining_amount || 0) > 0.009 ? 'due' : 'paid';
    return `<span class="rp-badge ${key}">${key === 'due' ? 'Due' : 'Paid'}</span>`;
  }

  async function downloadBillPdf(id) {
    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}/pdf`, { headers });
    if (!res.ok) throw new Error(`Unable to download PDF (HTTP ${res.status})`);
    const blob = await res.blob();
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `client_bill_${id}.pdf`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);
  }

  function renderRows() {
    updateSummary();
    if (!state.items.length) {
      els.billRows.innerHTML = `<tr><td colspan="9"><div class="rp-empty"><h3>No ${state.bucket} bills found</h3><p>Try another client branch or switch tabs.</p></div></td></tr>`;
      renderPager();
      return;
    }

    els.billRows.innerHTML = state.items.map((row) => {
      const remaining = Number(row.remaining_amount || 0);
      const canRepay = remaining > 0.009;
      return `
        <tr>
          <td>
            <strong>Bill #${esc(row.id)}</strong>
            <div class="rp-note mt-1">${row.published_at ? `Published ${fmtDate(row.published_at)}` : 'Published bill'}</div>
          </td>
          <td>${esc(row.client_name || '—')}</td>
          <td>${fmtDate(row.bill_date)}</td>
          <td>${fmtDate(row.due_date)}</td>
          <td style="font-weight:800;">${esc(money(row.total_amount || 0))}</td>
          <td>${esc(money(row.paid_amount || 0))}</td>
          <td style="font-weight:800;color:${canRepay ? '#c2410c' : '#15803d'};">${esc(money(remaining))}</td>
          <td>
            ${statusBadge(row)}
            <div class="rp-note mt-1">${esc(row.repayment_count || 0)} repayment entr${Number(row.repayment_count || 0) === 1 ? 'y' : 'ies'}</div>
          </td>
          <td>
            <div class="rp-inline-actions">
              <button type="button" class="rp-mini-btn primary" data-action="view" data-id="${esc(row.id)}">
                <i class="fa-solid fa-eye"></i>
                View
              </button>
              <button type="button" class="rp-mini-btn" data-action="pdf" data-id="${esc(row.id)}">
                <i class="fa-solid fa-file-pdf rp-accent"></i>
                PDF
              </button>
              ${canRepay ? `<button type="button" class="rp-mini-btn success" data-action="repay" data-id="${esc(row.id)}">
                <i class="fa-solid fa-plus"></i>
                Repayment
              </button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
    renderPager();
  }

  async function loadBills() {
    const params = new URLSearchParams({ page: state.page, per_page: 10, view: 'bills', bucket: state.bucket });
    if (state.q) params.set('q', state.q);
    if (state.clientId) params.set('client_id', state.clientId);
    const data = await fetchJSON(`${API_BASE}/client-bill-repayments?${params.toString()}`);
    state.items = Array.isArray(data.data) ? data.data : [];
    state.total = Number(data.meta?.total || 0);
    state.totalPages = Number(data.meta?.total_pages || 1);
    renderRows();
  }

  function syncBucketTabs() {
    els.bucketTabs.querySelectorAll('.rp-tab').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.bucket === state.bucket);
    });
  }

  function openRepaymentModalForBill(billId) {
    const bill = state.items.find((item) => String(item.id) === String(billId));
    if (!bill) return;
    state.currentBill = bill;
    els.repaymentForm.reset();
    els.formRepaymentDate.value = new Date().toISOString().slice(0, 10);
    els.formBillTitle.textContent = `Bill #${bill.id} · ${bill.client_name || '—'}`;
    els.formBillTotal.textContent = money(bill.total_amount || 0);
    els.formBillRemaining.textContent = money(bill.remaining_amount || 0);
    els.formAmount.value = Number(bill.remaining_amount || 0).toFixed(2);
    els.repaymentModalSubtitle.textContent = DIRECT_APPROVAL
      ? 'This repayment will be approved immediately and reflected in bill balances right away.'
      : 'This repayment will be submitted for approval and will reduce due balance after approval.';
    repaymentModal.show();
  }

  async function openBillRepaymentsModal(billId) {
    billRepaymentsModal.show();
    els.billRepaymentsBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';

    const summaryResp = await fetchJSON(`${API_BASE}/client-bill-repayments?view=bills&client_bill_id=${encodeURIComponent(billId)}&per_page=1`);
    const summary = Array.isArray(summaryResp.data) && summaryResp.data.length ? summaryResp.data[0] : null;
    const data = await fetchJSON(`${API_BASE}/client-bill-repayments?client_bill_id=${encodeURIComponent(billId)}&per_page=200`);
    const rows = Array.isArray(data.data) ? data.data : [];

    if (summary) {
      els.billRepaymentsSubtitle.textContent = `Bill #${summary.id} · ${summary.client_name || '—'} · Remaining ${money(summary.remaining_amount || 0)}`;
    } else {
      els.billRepaymentsSubtitle.textContent = `Bill #${billId}`;
    }

    els.billRepaymentsBody.innerHTML = `
      <div class="rp-detail-grid mb-3">
        <div class="rp-detail-box"><small>Bill</small><strong>#${esc(summary?.id || billId)}</strong></div>
        <div class="rp-detail-box"><small>Total</small><strong>${esc(money(summary?.total_amount || 0))}</strong></div>
        <div class="rp-detail-box"><small>Paid</small><strong>${esc(money(summary?.paid_amount || 0))}</strong></div>
        <div class="rp-detail-box"><small>Due</small><strong>${esc(money(summary?.remaining_amount || 0))}</strong></div>
        <div class="rp-detail-box"><small>Bill Date</small><strong>${fmtDate(summary?.bill_date)}</strong></div>
        <div class="rp-detail-box"><small>Due Date</small><strong>${fmtDate(summary?.due_date)}</strong></div>
        <div class="rp-detail-box"><small>Published</small><strong>${fmtDate(summary?.published_at)}</strong></div>
      </div>
      <div class="rp-list-card">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
          <div>
            <small>Repayment History</small>
            <strong>${rows.length ? `${rows.length} repayment entr${rows.length === 1 ? 'y' : 'ies'}` : 'No repayments yet'}</strong>
          </div>
          ${Number(summary?.remaining_amount || 0) > 0.009 ? `<button type="button" class="rp-mini-btn success" id="billModalRepayBtn" data-id="${esc(summary?.id || billId)}"><i class="fa-solid fa-plus"></i>Repayment</button>` : ''}
        </div>
        <div class="mt-3">
          ${rows.length ? rows.map((row) => `
            <div class="rp-line">
              <div>
                <div style="font-weight:800;">${esc(money(row.amount || 0))}</div>
                <div class="rp-note mt-1">${fmtDate(row.repayment_date)} · ${esc(row.submitted_by_name || '—')}</div>
                <div class="rp-note mt-1">${row.note ? esc(row.note) : 'No note'}</div>
              </div>
              <div style="text-align:right;min-width:180px">
                <div>${Number(row.status === 'approved') ? '<span class="rp-badge paid">Approved</span>' : row.status === 'pending' ? '<span class="rp-badge pending">Pending</span>' : '<span class="rp-badge due">Rejected</span>'}</div>
                <div class="rp-proof-list" style="align-items:flex-end;">
                  ${Array.isArray(row.attachments) && row.attachments.length ? row.attachments.map((proof) => `<a class="rp-proof-link" href="${esc(proof.absolute_url || proof.relative_url || '#')}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-paperclip"></i>${esc(proof.original_name || 'Attachment')}</a>`).join('') : '<span class="rp-note">No proof file</span>'}
                </div>
              </div>
            </div>`).join('') : '<div class="rp-empty" style="padding:28px 12px;"><h3>No repayments yet</h3><p>Add the first repayment while this bill still has remaining balance.</p></div>'}
        </div>
      </div>`;

    els.billRepaymentsFooter.innerHTML = `
      <div class="d-flex gap-2 ms-auto">
        <button type="button" class="rp-btn" data-bs-dismiss="modal">Close</button>
        <button type="button" class="rp-btn" id="billModalPdfBtn" data-id="${esc(summary?.id || billId)}">
          <i class="fa-solid fa-file-pdf"></i>
          Download PDF
        </button>
      </div>`;

    const repayBtn = document.getElementById('billModalRepayBtn');
    if (repayBtn) {
      repayBtn.addEventListener('click', () => {
        billRepaymentsModal.hide();
        openRepaymentModalForBill(repayBtn.dataset.id);
      });
    }
    const pdfBtn = document.getElementById('billModalPdfBtn');
    if (pdfBtn) {
      pdfBtn.addEventListener('click', async () => {
        try {
          await downloadBillPdf(pdfBtn.dataset.id);
        } catch (error) {
          Swal.fire({ icon:'error', title:'Download failed', text:String(error.message || error) });
        }
      });
    }
  }

  async function submitRepayment(event) {
    event.preventDefault();
    if (!state.currentBill) {
      Swal.fire({ icon:'warning', title:'Bill required', text:'Choose a bill first.' });
      return;
    }

    const remaining = Number(state.currentBill.remaining_amount || 0);
    const amount = Number(els.formAmount.value || 0);
    if (!(amount > 0)) {
      Swal.fire({ icon:'warning', title:'Amount required', text:'Enter a valid repayment amount.' });
      return;
    }
    if (amount - remaining > 0.009) {
      Swal.fire({ icon:'warning', title:'Amount too high', text:`Repayment cannot exceed remaining balance of ${money(remaining)}.` });
      return;
    }

    const formData = new FormData();
    formData.append('client_bill_id', state.currentBill.id);
    formData.append('repayment_date', els.formRepaymentDate.value);
    formData.append('amount', String(amount));
    if (els.formNote.value.trim()) formData.append('note', els.formNote.value.trim());
    Array.from(els.formAttachments.files || []).forEach((file) => formData.append('attachments[]', file));

    setBtnLoading(els.saveRepaymentBtn, true);
    try {
      const res = await fetch(`${API_BASE}/client-bill-repayments`, {
        method: 'POST',
        headers,
        body: formData,
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const errors = data?.errors ? Object.entries(data.errors).map(([key, value]) => `${key}: ${[].concat(value).join(', ')}`).join('\n') : '';
        throw new Error(errors || data?.message || 'Unable to save repayment');
      }
      toast('success', DIRECT_APPROVAL ? 'Repayment saved' : 'Repayment submitted');
      repaymentModal.hide();
      await loadBills();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Save failed', text:String(error.message || error) });
    } finally {
      setBtnLoading(els.saveRepaymentBtn, false);
    }
  }

  let searchTimer;
  els.billSearch.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.q = els.billSearch.value.trim();
      state.page = 1;
      loadBills().catch((error) => {
        els.billRows.innerHTML = `<tr><td colspan="9" class="text-danger text-center py-4">${esc(error.message || error)}</td></tr>`;
      });
    }, 300);
  });

  els.clientFilterBtn.addEventListener('click', () => {
    state.pendingTreeClientId = state.clientId || '';
    renderClientTree();
    clientTreeModal.show();
  });

  els.clearClientFilterBtn.addEventListener('click', () => {
    state.clientId = '';
    state.page = 1;
    updateClientLabels();
    loadBills().catch(() => {});
  });

  els.bucketTabs.addEventListener('click', (event) => {
    const button = event.target.closest('.rp-tab[data-bucket]');
    if (!button) return;
    const nextBucket = button.dataset.bucket;
    if (!nextBucket || nextBucket === state.bucket) return;
    state.bucket = nextBucket;
    state.page = 1;
    syncBucketTabs();
    loadBills().catch(() => {});
  });

  els.refreshBillsBtn.addEventListener('click', () => {
    loadBills().catch(() => {});
  });

  els.clientTreeSearch.addEventListener('input', renderClientTree);
  els.clientTreeShell.addEventListener('click', (event) => {
    const node = event.target.closest('.tree-node');
    if (!node) return;
    const input = node.querySelector('input[name="client_tree_pick"]');
    if (!input) return;
    input.checked = true;
    state.pendingTreeClientId = input.value;
    renderClientTree();
  });
  els.clientTreeShell.addEventListener('change', (event) => {
    const input = event.target.closest('input[name="client_tree_pick"]');
    if (!input) return;
    state.pendingTreeClientId = input.value;
    renderClientTree();
  });

  els.applyClientTreeBtn.addEventListener('click', async () => {
    state.clientId = state.pendingTreeClientId || '';
    state.page = 1;
    updateClientLabels();
    await loadBills();
    clientTreeModal.hide();
  });

  els.repaymentForm.addEventListener('submit', submitRepayment);

  els.billRows.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const action = button.dataset.action;
    const id = button.dataset.id;
    if (action === 'view') {
      openBillRepaymentsModal(id).catch((error) => {
        els.billRepaymentsBody.innerHTML = `<div class="text-danger">${esc(error.message || error)}</div>`;
      });
      return;
    }
    if (action === 'pdf') {
      downloadBillPdf(id).catch((error) => {
        Swal.fire({ icon:'error', title:'Download failed', text:String(error.message || error) });
      });
      return;
    }
    if (action === 'repay') {
      openRepaymentModalForBill(id);
    }
  });

  els.billPager.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-page]');
    if (!button || button.disabled) return;
    const page = Number(button.dataset.page || 1);
    if (Number.isNaN(page) || page < 1 || page > state.totalPages || page === state.page) return;
    state.page = page;
    loadBills().catch(() => {});
  });

  Promise.all([loadClients(), loadBills()]).then(syncBucketTabs).catch((error) => {
    els.billRows.innerHTML = `<tr><td colspan="9" class="text-danger text-center py-4">${esc(error.message || error)}</td></tr>`;
  });
})();
</script>
@endpush
