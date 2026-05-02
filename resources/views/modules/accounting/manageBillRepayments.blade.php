@php
  $repaymentRole = $repaymentRole ?? 'admin';
  $repaymentLoginUrl = $repaymentLoginUrl ?? '/';
  $repaymentBillsUrl = $repaymentBillsUrl ?? '/admin/accounting/client-bills';
@endphp

@push('styles')
<style>
.repayments-page {
  background:
    radial-gradient(circle at top left, rgba(14,165,233,.1), transparent 24%),
    radial-gradient(circle at bottom right, rgba(37,99,235,.08), transparent 20%),
    var(--bg-body);
  min-height:100vh;
  padding:24px;
  font-family:var(--font-sans);
}
.rp-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:22px; }
.rp-header h1 { margin:0; font-size:28px; font-weight:800; color:var(--text-color); }
.rp-header p { margin:6px 0 0; color:#64748b; font-size:14px; max-width:760px; }
.rp-actions { display:flex; gap:10px; flex-wrap:wrap; }
.rp-toolbar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
.rp-search { position:relative; flex:1; min-width:240px; max-width:420px; }
.rp-search input {
  width:100%; height:44px; padding:0 14px 0 40px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; color:#0f172a; font-size:14px;
}
.rp-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.rp-chip, .rp-select, .rp-btn, .rp-input {
  height:44px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; color:#0f172a; font-size:14px;
}
.rp-chip {
  min-width:220px; padding:0 14px; display:inline-flex; align-items:center; justify-content:space-between; gap:10px; cursor:pointer;
}
.rp-select, .rp-input { padding:0 14px; }
.rp-input { width:100%; }
.rp-btn {
  padding:0 14px; font-weight:700; display:inline-flex; align-items:center; gap:8px; cursor:pointer; text-decoration:none;
}
.rp-btn.primary { background:linear-gradient(135deg,#2563eb,#1d4ed8); border-color:#2563eb; color:#fff; }
.rp-btn.soft { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
.rp-card {
  background:rgba(255,255,255,.96);
  border:1px solid rgba(226,232,240,.9);
  border-radius:22px;
  box-shadow:0 18px 36px rgba(15,23,42,.08);
  overflow:hidden;
}
.rp-table-wrap { overflow:auto; }
.rp-table { width:100%; border-collapse:collapse; }
.rp-table th, .rp-table td { padding:14px 16px; border-bottom:1px solid #eef2f7; text-align:left; font-size:14px; color:var(--text-color); vertical-align:top; }
.rp-table th { background:#f8fafc; text-transform:uppercase; font-size:11px; letter-spacing:.5px; color:#64748b; }
.rp-badge {
  display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:700; border:1px solid transparent;
}
.rp-badge.pending { background:#fff7ed; color:#c2410c; border-color:#fdba74; }
.rp-badge.approved { background:#dcfce7; color:#15803d; border-color:#86efac; }
.rp-badge.rejected { background:#fee2e2; color:#b91c1c; border-color:#fca5a5; }
.rp-empty { text-align:center; padding:56px 20px; color:#94a3b8; }
.rp-empty h3 { margin:0 0 8px; font-size:18px; color:#475569; }
.rp-pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:18px; background:#f8fafc; }
.rp-pages { display:flex; gap:6px; flex-wrap:wrap; }
.rp-page-btn {
  min-width:38px; height:38px; border:1px solid #dbe5f0; border-radius:10px; background:#fff; color:#0f172a; font-weight:700; cursor:pointer;
}
.rp-page-btn.active { background:#2563eb; border-color:#2563eb; color:#fff; }
.rp-page-btn:disabled { opacity:.4; cursor:not-allowed; }
.rp-icon-btn {
  width:34px; height:34px; border-radius:10px; border:1px solid #dbe5f0; background:#fff; color:#1e293b; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.rp-icon-btn:hover { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.rp-inline-actions { display:flex; gap:8px; flex-wrap:wrap; }
.rp-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.rp-detail-box, .rp-line-card {
  border:1px solid #e2e8f0; border-radius:14px; padding:14px; background:#f8fafc;
}
.rp-detail-box small, .rp-line-card small { display:block; color:#64748b; margin-bottom:4px; }
.rp-proof-list { display:flex; flex-direction:column; gap:10px; margin-top:14px; }
.rp-proof-link { display:inline-flex; align-items:center; gap:8px; color:#1d4ed8; text-decoration:none; font-weight:700; }
.tree-shell { border:1px solid #e2e8f0; border-radius:16px; background:#fff; max-height:420px; overflow:auto; padding:10px; }
.tree-node { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border-radius:12px; }
.tree-node:hover { background:#f8fafc; }
.tree-node.active { background:#eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
.tree-children { margin-left:18px; padding-left:12px; border-left:1px solid #e2e8f0; }
.tree-meta { display:flex; flex-direction:column; gap:3px; }
.tree-meta strong { font-size:14px; color:#0f172a; }
.tree-meta small { color:#94a3b8; font-size:12px; }
.picker-note {
  display:inline-flex; align-items:center; gap:8px; min-height:44px; padding:0 14px; border-radius:12px; border:1px dashed #bfdbfe; background:#f8fbff; color:#1d4ed8; font-weight:700;
}
@media (max-width: 768px) {
  .repayments-page { padding:16px; }
  .rp-detail-grid { grid-template-columns:1fr; }
}
</style>
@endpush

<div class="repayments-page">
  <div class="rp-header">
    <div>
      <h1>Repayments</h1>
      <p>Track bill repayments, approve client-submitted receipts, and keep every published bill connected to its repayment history.</p>
    </div>
    <div class="rp-actions">
      <a href="{{ $repaymentBillsUrl }}" class="rp-btn soft">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        Bills
      </a>
      <button type="button" class="rp-btn primary" id="newRepaymentBtn">
        <i class="fa-solid fa-plus"></i>
        {{ $repaymentRole === 'client_user' ? 'Submit Repayment' : 'New Repayment' }}
      </button>
    </div>
  </div>

  <div class="rp-toolbar">
    <div class="rp-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="repaymentSearch" placeholder="Search by client, note, or approval note...">
    </div>
    <button type="button" class="rp-chip" id="clientFilterBtn">
      <span id="clientFilterLabel">All Clients</span>
      <i class="fa-solid fa-sitemap"></i>
    </button>
    <button type="button" class="rp-btn" id="clearClientFilterBtn">
      <i class="fa-solid fa-xmark"></i>
      Clear Client
    </button>
    <select id="statusFilter" class="rp-select">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
    </select>
    <button type="button" class="rp-btn" id="refreshRepaymentsBtn">
      <i class="fa-solid fa-rotate"></i>
      Refresh
    </button>
  </div>

  <div class="rp-card">
    <div class="rp-table-wrap">
      <table class="rp-table">
        <thead>
          <tr>
            <th>Repayment</th>
            <th>Bill</th>
            <th>Client</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Submitted By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="repaymentRows">
          <tr><td colspan="8" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
    <div class="rp-pagination">
      <div id="repaymentPaginationInfo">Showing 0-0 of 0 repayments</div>
      <div class="rp-pages" id="repaymentPager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="clientTreeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Choose Client Tree</h5>
          <div class="text-muted small">Use one client branch for the current filter or repayment form.</div>
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
            <h5 class="modal-title mb-1">{{ $repaymentRole === 'client_user' ? 'Submit Repayment' : 'New Repayment' }}</h5>
            <div class="text-muted small">{{ $repaymentRole === 'client_user' ? 'Client submissions stay pending until an admin or accountant approves them.' : 'Admin and accountant repayments are saved as approved immediately.' }}</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Client Tree <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <button type="button" class="rp-chip" id="formClientPickerBtn">
                <span id="formClientLabel">Choose Client</span>
                <i class="fa-solid fa-sitemap"></i>
              </button>
              <span class="picker-note" id="formClientNote">Published bills load after you choose a client.</span>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Published Bill <span class="text-danger">*</span></label>
              <select id="formBillId" class="rp-input" required>
                <option value="">Choose a published bill</option>
              </select>
            </div>
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
          <div class="text-muted small">{{ $repaymentRole === 'client_user' ? 'Your repayment will appear as pending until it is approved.' : 'This repayment will appear inside future billing analysis right away.' }}</div>
          <div class="d-flex gap-2">
            <button type="button" class="rp-btn" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="rp-btn primary" id="saveRepaymentBtn">
              <i class="fa-solid fa-check"></i>
              {{ $repaymentRole === 'client_user' ? 'Submit Repayment' : 'Save Repayment' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="repaymentDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Repayment Details</h5>
          <div class="text-muted small">Review proof files, approval state, and bill reference details.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="repaymentDetailBody">
        <div class="text-center text-muted py-4">Loading…</div>
      </div>
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
  const CAN_APPROVE = PORTAL_ROLE === 'admin' || PORTAL_ROLE === 'accountant_user';
  if (!TOKEN) {
    setTimeout(() => { window.location.href = LOGIN_URL; }, 400);
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const treeModal = new bootstrap.Modal(document.getElementById('clientTreeModal'));
  const repaymentModal = new bootstrap.Modal(document.getElementById('repaymentModal'));
  const repaymentDetailModal = new bootstrap.Modal(document.getElementById('repaymentDetailModal'));

  const state = {
    page: 1,
    total: 0,
    totalPages: 1,
    q: '',
    status: '',
    clientId: '',
    pendingTreeClientId: '',
    treeTarget: 'filter',
    clients: [],
    clientTreeRoots: [],
    items: [],
    formClientId: '',
    formBills: [],
  };

  const els = {
    repaymentSearch: document.getElementById('repaymentSearch'),
    clientFilterBtn: document.getElementById('clientFilterBtn'),
    clientFilterLabel: document.getElementById('clientFilterLabel'),
    clearClientFilterBtn: document.getElementById('clearClientFilterBtn'),
    statusFilter: document.getElementById('statusFilter'),
    refreshRepaymentsBtn: document.getElementById('refreshRepaymentsBtn'),
    repaymentRows: document.getElementById('repaymentRows'),
    repaymentPaginationInfo: document.getElementById('repaymentPaginationInfo'),
    repaymentPager: document.getElementById('repaymentPager'),
    clientTreeSearch: document.getElementById('clientTreeSearch'),
    clientTreeShell: document.getElementById('clientTreeShell'),
    clientTreeSelectionLabel: document.getElementById('clientTreeSelectionLabel'),
    applyClientTreeBtn: document.getElementById('applyClientTreeBtn'),
    newRepaymentBtn: document.getElementById('newRepaymentBtn'),
    repaymentForm: document.getElementById('repaymentForm'),
    formClientPickerBtn: document.getElementById('formClientPickerBtn'),
    formClientLabel: document.getElementById('formClientLabel'),
    formClientNote: document.getElementById('formClientNote'),
    formBillId: document.getElementById('formBillId'),
    formRepaymentDate: document.getElementById('formRepaymentDate'),
    formAmount: document.getElementById('formAmount'),
    formAttachments: document.getElementById('formAttachments'),
    formNote: document.getElementById('formNote'),
    saveRepaymentBtn: document.getElementById('saveRepaymentBtn'),
    repaymentDetailBody: document.getElementById('repaymentDetailBody'),
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
    els.formClientLabel.textContent = state.formClientId ? labelForClient(state.formClientId, 'Choose Client') : 'Choose Client';
    els.formClientNote.textContent = state.formClientId
      ? `${labelForClient(state.formClientId)} selected`
      : 'Published bills load after you choose a client.';
  }

  function renderClientTree() {
    const query = String(els.clientTreeSearch.value || '').trim().toLowerCase();
    const activeId = Number(state.pendingTreeClientId || (state.treeTarget === 'form' ? state.formClientId : state.clientId) || 0);

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
    const selectedLabel = activeId ? labelForClient(activeId, 'Choose Client') : 'All Clients';
    els.clientTreeSelectionLabel.textContent = selectedLabel;
  }

  async function loadClients() {
    const data = await fetchJSON(`${API_BASE}/clients/all`);
    state.clients = normalizeClients(data.data || []);
    state.clientTreeRoots = buildTree(state.clients);
    updateClientLabels();
    renderClientTree();
  }

  function renderPager() {
    const start = state.total ? ((state.page - 1) * 10) + 1 : 0;
    const end = Math.min(state.total, state.page * 10);
    els.repaymentPaginationInfo.textContent = `Showing ${start}-${end} of ${state.total} repayments`;
    const buttons = [];
    buttons.push(`<button class="rp-page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let page = Math.max(1, state.page - 2); page <= Math.min(state.totalPages, state.page + 2); page += 1) {
      buttons.push(`<button class="rp-page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>`);
    }
    buttons.push(`<button class="rp-page-btn" data-page="${state.page + 1}" ${state.page >= state.totalPages ? 'disabled' : ''}>Next</button>`);
    els.repaymentPager.innerHTML = buttons.join('');
  }

  function statusBadge(status) {
    const key = ['approved', 'rejected'].includes(String(status)) ? String(status) : 'pending';
    return `<span class="rp-badge ${key}">${esc(key.charAt(0).toUpperCase() + key.slice(1))}</span>`;
  }

  function renderRows() {
    if (!state.items.length) {
      els.repaymentRows.innerHTML = '<tr><td colspan="8"><div class="rp-empty"><h3>No repayments found</h3><p>Try another client tree or add a new repayment.</p></div></td></tr>';
      renderPager();
      return;
    }

    els.repaymentRows.innerHTML = state.items.map((row) => `
      <tr>
        <td>
          <strong>Repayment #${esc(row.id)}</strong>
          <div style="font-size:12px;color:#94a3b8;margin-top:4px;">${Number(row.attachments_count || 0)} proof file(s)</div>
        </td>
        <td>
          <strong>Bill #${esc(row.client_bill_id)}</strong>
          <div style="font-size:12px;color:#94a3b8;margin-top:4px;">${esc(money(row.bill_total_amount || 0))}</div>
        </td>
        <td>${esc(row.client_name || '—')}</td>
        <td>${fmtDate(row.repayment_date)}</td>
        <td style="font-weight:800;">${esc(money(row.amount))}</td>
        <td>${statusBadge(row.status)}</td>
        <td>
          ${esc(row.submitted_by_name || '—')}
          <div style="font-size:12px;color:#94a3b8;margin-top:4px;">${esc(String(row.submitted_by_role || '—').replaceAll('_', ' '))}</div>
        </td>
        <td>
          <div class="rp-inline-actions">
            <button type="button" class="rp-icon-btn" data-action="view" data-id="${esc(row.id)}" title="View"><i class="fa-solid fa-eye"></i></button>
            ${CAN_APPROVE && row.status === 'pending' ? `<button type="button" class="rp-icon-btn" data-action="approve" data-id="${esc(row.id)}" title="Approve"><i class="fa-solid fa-check"></i></button>` : ''}
            ${CAN_APPROVE && row.status === 'pending' ? `<button type="button" class="rp-icon-btn" data-action="reject" data-id="${esc(row.id)}" title="Reject"><i class="fa-solid fa-xmark"></i></button>` : ''}
          </div>
        </td>
      </tr>
    `).join('');
    renderPager();
  }

  async function loadRepayments() {
    const params = new URLSearchParams({ page: state.page, per_page: 10 });
    if (state.q) params.set('q', state.q);
    if (state.status) params.set('status', state.status);
    if (state.clientId) params.set('client_id', state.clientId);
    const data = await fetchJSON(`${API_BASE}/client-bill-repayments?${params.toString()}`);
    state.items = Array.isArray(data.data) ? data.data : [];
    state.total = Number(data.meta?.total || 0);
    state.totalPages = Number(data.meta?.total_pages || 1);
    renderRows();
  }

  async function loadFormBills(clientId) {
    if (!clientId) {
      state.formBills = [];
      els.formBillId.innerHTML = '<option value="">Choose a published bill</option>';
      return;
    }
    const data = await fetchJSON(`${API_BASE}/client-bills?publish=published&client_id=${encodeURIComponent(clientId)}&per_page=100`);
    state.formBills = Array.isArray(data.data) ? data.data : [];
    els.formBillId.innerHTML = '<option value="">Choose a published bill</option>' + state.formBills.map((bill) => (
      `<option value="${esc(bill.id)}">Bill #${esc(bill.id)} · ${esc(bill.client_name || '—')} · ${esc(money(bill.total_amount || 0))}</option>`
    )).join('');
  }

  function resetForm() {
    els.repaymentForm.reset();
    els.formRepaymentDate.value = new Date().toISOString().slice(0, 10);
    state.formClientId = state.clientId || '';
    state.pendingTreeClientId = '';
    updateClientLabels();
  }

  async function openCreateModal() {
    resetForm();
    if (state.formClientId) {
      await loadFormBills(state.formClientId);
    } else {
      await loadFormBills('');
    }
    repaymentModal.show();
  }

  async function openDetail(id) {
    repaymentDetailModal.show();
    els.repaymentDetailBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
    const data = await fetchJSON(`${API_BASE}/client-bill-repayments/${encodeURIComponent(id)}`);
    const row = data.data || {};
    const proofs = Array.isArray(row.attachments) ? row.attachments : [];
    els.repaymentDetailBody.innerHTML = `
      <div class="rp-detail-grid">
        <div class="rp-detail-box"><small>Repayment</small><strong>#${esc(row.id || '—')}</strong></div>
        <div class="rp-detail-box"><small>Bill</small><strong>#${esc(row.client_bill_id || '—')}</strong></div>
        <div class="rp-detail-box"><small>Client</small><strong>${esc(row.client_name || '—')}</strong></div>
        <div class="rp-detail-box"><small>Status</small><strong>${esc(String(row.status || 'pending').toUpperCase())}</strong></div>
        <div class="rp-detail-box"><small>Repayment Date</small><strong>${fmtDate(row.repayment_date)}</strong></div>
        <div class="rp-detail-box"><small>Amount</small><strong>${esc(money(row.amount || 0))}</strong></div>
      </div>
      <div class="rp-line-card mt-3">
        <small>Submitted By</small>
        <strong>${esc(row.submitted_by_name || '—')}</strong>
        <div class="text-muted small mt-1">${esc(String(row.submitted_by_role || '—').replaceAll('_', ' '))}</div>
      </div>
      <div class="rp-line-card mt-3">
        <small>Approval</small>
        <strong>${esc(row.approved_by_name || 'Pending')}</strong>
        <div class="text-muted small mt-1">${row.approved_at ? `Updated ${fmtDate(row.approved_at)}` : 'No approval action yet.'}</div>
        <div class="mt-2">${row.approval_note ? esc(row.approval_note) : '<span class="text-muted small">No approval note.</span>'}</div>
      </div>
      <div class="rp-line-card mt-3">
        <small>Note</small>
        <div>${row.note ? esc(row.note) : '<span class="text-muted small">No note added.</span>'}</div>
      </div>
      <div class="rp-line-card mt-3">
        <small>Proof Files</small>
        <div class="rp-proof-list">
          ${proofs.length ? proofs.map((proof) => `<a class="rp-proof-link" href="${esc(proof.absolute_url || proof.relative_url || '#')}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-paperclip"></i>${esc(proof.original_name || 'Attachment')}</a>`).join('') : '<span class="text-muted small">No proof files uploaded.</span>'}
        </div>
      </div>
    `;
  }

  async function takeApprovalAction(id, action) {
    const result = await Swal.fire({
      title: action === 'approve' ? 'Approve repayment?' : 'Reject repayment?',
      input: 'textarea',
      inputLabel: 'Optional note',
      inputPlaceholder: action === 'approve' ? 'Approval note' : 'Reason for rejection',
      showCancelButton: true,
      confirmButtonText: action === 'approve' ? 'Approve' : 'Reject',
      icon: action === 'approve' ? 'question' : 'warning',
    });
    if (!result.isConfirmed) return;

    await fetchJSON(`${API_BASE}/client-bill-repayments/${encodeURIComponent(id)}/${action}`, {
      method: 'PATCH',
      headers: { ...headers, 'Content-Type': 'application/json' },
      body: JSON.stringify({ approval_note: result.value || null }),
    });
    toast('success', action === 'approve' ? 'Repayment approved' : 'Repayment rejected');
    await loadRepayments();
  }

  async function submitRepayment(event) {
    event.preventDefault();
    if (!state.formClientId) {
      Swal.fire({ icon:'warning', title:'Client required', text:'Choose a client tree first.' });
      return;
    }
    if (!els.formBillId.value) {
      Swal.fire({ icon:'warning', title:'Bill required', text:'Choose a published bill for this repayment.' });
      return;
    }

    const formData = new FormData();
    formData.append('client_bill_id', els.formBillId.value);
    formData.append('repayment_date', els.formRepaymentDate.value);
    formData.append('amount', els.formAmount.value);
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
      toast('success', PORTAL_ROLE === 'client_user' ? 'Repayment submitted' : 'Repayment saved');
      repaymentModal.hide();
      await loadRepayments();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Save failed', text:String(error.message || error) });
    } finally {
      setBtnLoading(els.saveRepaymentBtn, false);
    }
  }

  let searchTimer;
  els.repaymentSearch.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.q = els.repaymentSearch.value.trim();
      state.page = 1;
      loadRepayments().catch((error) => {
        els.repaymentRows.innerHTML = `<tr><td colspan="8" class="text-danger text-center py-4">${esc(error.message || error)}</td></tr>`;
      });
    }, 300);
  });

  els.clientFilterBtn.addEventListener('click', () => {
    state.treeTarget = 'filter';
    state.pendingTreeClientId = state.clientId || '';
    renderClientTree();
    treeModal.show();
  });

  els.formClientPickerBtn.addEventListener('click', () => {
    state.treeTarget = 'form';
    state.pendingTreeClientId = state.formClientId || '';
    renderClientTree();
    treeModal.show();
  });

  els.clearClientFilterBtn.addEventListener('click', () => {
    state.clientId = '';
    state.page = 1;
    updateClientLabels();
    loadRepayments().catch(() => {});
  });

  els.statusFilter.addEventListener('change', () => {
    state.status = els.statusFilter.value;
    state.page = 1;
    loadRepayments().catch(() => {});
  });

  els.refreshRepaymentsBtn.addEventListener('click', () => {
    loadRepayments().catch(() => {});
  });

  els.clientTreeSearch.addEventListener('input', renderClientTree);
  els.clientTreeShell.addEventListener('change', (event) => {
    const input = event.target.closest('input[name="client_tree_pick"]');
    if (!input) return;
    state.pendingTreeClientId = input.value;
    renderClientTree();
  });

  els.applyClientTreeBtn.addEventListener('click', async () => {
    if (state.treeTarget === 'form') {
      state.formClientId = state.pendingTreeClientId || '';
      updateClientLabels();
      await loadFormBills(state.formClientId);
    } else {
      state.clientId = state.pendingTreeClientId || '';
      state.page = 1;
      updateClientLabels();
      await loadRepayments();
    }
    treeModal.hide();
  });

  els.newRepaymentBtn.addEventListener('click', () => {
    openCreateModal().catch((error) => {
      Swal.fire({ icon:'error', title:'Unable to open form', text:String(error.message || error) });
    });
  });

  els.repaymentForm.addEventListener('submit', submitRepayment);

  els.repaymentRows.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const action = button.dataset.action;
    const id = button.dataset.id;
    if (action === 'view') {
      openDetail(id).catch((error) => {
        els.repaymentDetailBody.innerHTML = `<div class="text-danger">${esc(error.message || error)}</div>`;
      });
      return;
    }
    if (action === 'approve' || action === 'reject') {
      takeApprovalAction(id, action).catch((error) => {
        Swal.fire({ icon:'error', title:'Action failed', text:String(error.message || error) });
      });
    }
  });

  els.repaymentPager.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-page]');
    if (!button || button.disabled) return;
    const page = Number(button.dataset.page || 1);
    if (Number.isNaN(page) || page < 1 || page > state.totalPages || page === state.page) return;
    state.page = page;
    loadRepayments().catch(() => {});
  });

  Promise.all([loadClients(), loadRepayments()]).catch((error) => {
    els.repaymentRows.innerHTML = `<tr><td colspan="8" class="text-danger text-center py-4">${esc(error.message || error)}</td></tr>`;
  });
})();
</script>
@endpush
