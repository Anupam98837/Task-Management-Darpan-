@push('styles')
<style>
* { box-sizing: border-box; }

.billing-page { background: var(--bg-body); min-height: 100vh; padding: 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif; }
.page-header { margin-bottom: 28px; }
.page-header h1 { font-size: 28px; font-weight: 700; color: var(--text-color); margin: 0 0 6px; }
.page-header p { color: #64748b; font-size: 14px; margin: 0; }
.toolbar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
.search-box { position: relative; flex: 1; min-width: 260px; max-width: 420px; }
.search-box input { width: 100%; height: 44px; padding: 0 16px 0 42px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 14px; background: var(--surface); color: var(--text-color); }
.search-box svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); pointer-events:none; }
.select-box, .form-control, .form-select { height: 44px; padding: 0 14px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 14px; background: var(--surface); color: var(--text-color); }
.form-control, .form-select { width: 100%; }
textarea.form-control { min-height: 100px; padding: 10px 14px; resize: vertical; }
.btn { display:inline-flex; align-items:center; gap:8px; height:44px; padding:0 18px; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; border:none; text-decoration:none; }
.btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color:#fff; box-shadow:0 2px 8px rgba(59,130,246,.2); }
.btn-secondary { background: var(--surface); color: var(--text-color); border:1px solid #e2e8f0; }
.btn-danger-soft { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }
.btn-linkish { background:transparent; border:none; color:#2563eb; padding:0; font-weight:700; }
.data-card { background:var(--surface); border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden; }
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; color:var(--text-color); }
thead { background: var(--light-color); }
thead th { padding: 14px 18px; text-align:left; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
tbody tr { border-bottom:1px solid #f1f5f9; background:var(--surface); }
tbody td { padding:16px 18px; font-size:14px; color:var(--text-color); vertical-align:middle; }
.muted-small { font-size:12px; color:#94a3b8; }
.badge { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:700; }
.badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
.badge.published { background:#dcfce7; color:#15803d; }
.badge.draft { background:#fef3c7; color:#b45309; }
.actions-cell { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.btn-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border:1px solid #e2e8f0; border-radius:8px; background:var(--surface); color:var(--text-color); cursor:pointer; padding:0; }
.btn-icon:hover { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.pagination { display:flex; align-items:center; justify-content:space-between; padding:18px 20px; background:var(--light-color); border-top:1px solid #f1f5f9; gap:12px; flex-wrap:wrap; }
.pagination-controls { display:flex; gap:6px; flex-wrap:wrap; }
.page-btn { min-width:38px; height:38px; padding:0 12px; border:1px solid #e2e8f0; border-radius:8px; background:var(--surface); color:var(--text-color); font-size:14px; font-weight:600; cursor:pointer; }
.page-btn.active { background:var(--primary-color); color:#fff; border-color:var(--primary-color); }
.page-btn:disabled { opacity:.4; cursor:not-allowed; }
.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state h3 { font-size:18px; font-weight:600; color:#475569; margin:0 0 8px; }
.modal-content { border-radius:16px; border:none; box-shadow:0 20px 40px rgba(0,0,0,.15); }
.modal-header { padding:24px 28px; border-bottom:1px solid #f1f5f9; background:var(--surface); }
.modal-body { padding:24px 28px; }
.modal-footer { padding:18px 28px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; }
#billModal .modal-dialog,
#billDetailModal .modal-dialog { margin: 1rem auto; }
#billModal .modal-content,
#billDetailModal .modal-content {
  display: flex;
  flex-direction: column;
  max-height: calc(100dvh - 2rem);
}
#billForm {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  min-height: 0;
}
#billModal .modal-body,
#billDetailModal .modal-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
}
#billModal .modal-footer,
#billDetailModal .modal-footer { flex-shrink: 0; }
.section-card { border:1px solid #e2e8f0; border-radius:14px; padding:18px; background:#fff; }
.section-card + .section-card { margin-top:16px; }
.section-title { font-size:14px; font-weight:700; color:#0f172a; margin:0 0 12px; }
.item-list { display:flex; flex-direction:column; gap:12px; }
.item-row { border:1px solid #e2e8f0; border-radius:14px; padding:14px; background:#f8fafc; }
.item-grid { display:grid; grid-template-columns: 1.1fr 1fr .8fr auto; gap:12px; align-items:end; }
.summary-strip { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:14px; margin-bottom:20px; }
.summary-card { background:linear-gradient(180deg,#ffffff,#f8fbff); border:1px solid #e2e8f0; border-radius:16px; padding:16px; }
.summary-card .label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:.4px; }
.summary-card .value { font-size:24px; font-weight:800; color:#0f172a; margin-top:8px; }
.detail-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:14px; }
.detail-box { border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; background:#f8fafc; }
.detail-box small { display:block; color:#64748b; margin-bottom:4px; }
.detail-items { display:flex; flex-direction:column; gap:10px; margin-top:16px; }
.detail-item { display:flex; justify-content:space-between; gap:10px; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; background:#fff; }
.detail-total { display:flex; justify-content:flex-end; margin-top:16px; font-size:18px; font-weight:800; color:#0f172a; }
.table-link { background:none; border:none; color:#1d4ed8; padding:0; font-weight:700; text-align:left; cursor:pointer; }
.notes-box { white-space:pre-wrap; color:var(--text-color); }
.btn.is-loading, .btn[aria-busy="true"] { pointer-events:none; opacity:.8; position:relative; }
.btn.is-loading .btn-label { visibility:hidden; }
.btn.is-loading::after { content:""; position:absolute; inset:0; margin:auto; width:18px; height:18px; border-radius:50%; border:2px solid rgba(255,255,255,.7); border-top-color:transparent; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

@media (max-width: 991px) {
  .summary-strip { grid-template-columns: repeat(2, minmax(0,1fr)); }
  .item-grid { grid-template-columns: 1fr; }
  .detail-grid { grid-template-columns: 1fr; }
}

@media (max-width: 575px) {
  .billing-page { padding: 16px; }
  .summary-strip { grid-template-columns: 1fr; }
  #billModal .modal-body,
  #billDetailModal .modal-body { padding: 18px 16px; }
  #billModal .modal-header,
  #billDetailModal .modal-header,
  #billModal .modal-footer,
  #billDetailModal .modal-footer { padding-left: 16px; padding-right: 16px; }
}
</style>
@endpush

<div class="billing-page">
  <div class="page-header">
    <h1>Client Bills</h1>
    <p>Create draft client bills, filter by client, and publish finalized bills when they are ready.</p>
  </div>

  <div class="summary-strip">
    <div class="summary-card">
      <div class="label">Total Bills</div>
      <div class="value" id="statTotalBills">0</div>
    </div>
    <div class="summary-card">
      <div class="label">Published</div>
      <div class="value" id="statPublishedBills">0</div>
    </div>
    <div class="summary-card">
      <div class="label">Draft</div>
      <div class="value" id="statDraftBills">0</div>
    </div>
    <div class="summary-card">
      <div class="label">Visible Amount</div>
      <div class="value" id="statVisibleAmount">Rs 0.00</div>
    </div>
  </div>

  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search bills by client, note, or bill head...">
    </div>

    <select id="clientFilter" class="select-box" style="min-width:220px">
      <option value="">All Clients</option>
    </select>

    <select id="publishFilter" class="select-box" style="min-width:170px">
      <option value="">All Status</option>
      <option value="draft">Draft</option>
      <option value="published">Published</option>
    </select>

    <button id="refreshBtn" class="btn btn-secondary" type="button">
      <i class="fa-solid fa-rotate"></i>
      Refresh
    </button>

    <button id="addBillBtn" class="btn btn-primary" type="button">
      <i class="fa-solid fa-plus"></i>
      Open Bill Builder
    </button>
  </div>

  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Bill</th>
            <th>Client</th>
            <th>Bill Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Total</th>
            <th>Items</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="rows">
          <tr><td colspan="8" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div id="paginationInfo">Showing 0-0 of 0 bills</div>
      <div class="pagination-controls" id="pager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="billModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="billModalTitle">Create Client Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="billForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="bill_id">

          <div class="section-card">
            <h6 class="section-title">Bill Basics</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select id="bill_client_id" class="form-select" required></select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                <input id="bill_date" type="date" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input id="bill_due_date" type="date" class="form-control">
              </div>
              <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea id="bill_notes" class="form-control" placeholder="Optional internal note or billing context"></textarea>
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
              <h6 class="section-title mb-0">Bill Heads and Amounts</h6>
              <button id="addItemBtn" type="button" class="btn btn-secondary">
                <i class="fa-solid fa-plus"></i>
                Add Bill Head
              </button>
            </div>
            <div class="item-list mt-3" id="itemList"></div>
            <div class="detail-total mt-3">Total: <span id="formTotal" class="ms-2">Rs 0.00</span></div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="muted-small" id="billFormHint">Bills are created as drafts first. Publish them from the list once details are final.</div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveBillBtn"><span class="btn-label">Save Bill</span></button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="billDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Client Bill Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="billDetailBody">
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
  const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!token) {
    Swal.fire('Auth Required', 'Session expired. Please login again.', 'warning').then(() => location.href = '/');
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
  const PER_PAGE = 10;

  const billModal = new bootstrap.Modal(document.getElementById('billModal'));
  const billDetailModal = new bootstrap.Modal(document.getElementById('billDetailModal'));

  const state = {
    page: 1,
    total: 0,
    totalPages: 1,
    q: '',
    clientId: '',
    publish: '',
    items: [],
    clients: [],
    billHeads: [],
    editingBill: null,
  };

  const els = {
    rows: document.getElementById('rows'),
    pager: document.getElementById('pager'),
    paginationInfo: document.getElementById('paginationInfo'),
    searchInput: document.getElementById('searchInput'),
    clientFilter: document.getElementById('clientFilter'),
    publishFilter: document.getElementById('publishFilter'),
    refreshBtn: document.getElementById('refreshBtn'),
    addBillBtn: document.getElementById('addBillBtn'),
    statTotalBills: document.getElementById('statTotalBills'),
    statPublishedBills: document.getElementById('statPublishedBills'),
    statDraftBills: document.getElementById('statDraftBills'),
    statVisibleAmount: document.getElementById('statVisibleAmount'),
    billModalTitle: document.getElementById('billModalTitle'),
    billForm: document.getElementById('billForm'),
    billId: document.getElementById('bill_id'),
    billClientId: document.getElementById('bill_client_id'),
    billDate: document.getElementById('bill_date'),
    billDueDate: document.getElementById('bill_due_date'),
    billNotes: document.getElementById('bill_notes'),
    addItemBtn: document.getElementById('addItemBtn'),
    itemList: document.getElementById('itemList'),
    formTotal: document.getElementById('formTotal'),
    saveBillBtn: document.getElementById('saveBillBtn'),
    billDetailBody: document.getElementById('billDetailBody'),
  };

  const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const esc = (value = '') => String(value).replace(/[&<>"']/g, (m) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[m]));
  const fmtDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return esc(value);
    return d.toLocaleDateString('en-IN', { year:'numeric', month:'short', day:'numeric' });
  };
  const statusBadge = (row) => row.is_published ? '<span class="badge published">Published</span>' : '<span class="badge draft">Draft</span>';
  const toast = (icon, title) => Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:1800, icon, title });

  function setBtnLoading(btn, on) {
    btn.classList.toggle('is-loading', !!on);
    btn.setAttribute('aria-busy', on ? 'true' : 'false');
    btn.disabled = !!on;
  }

  function buildClientOptions(selected = '') {
    const options = ['<option value="">Select client</option>'];
    state.clients.forEach((client) => {
      const isSelected = String(selected) === String(client.id) ? 'selected' : '';
      options.push(`<option value="${esc(client.id)}" ${isSelected}>${esc(client.label)}</option>`);
    });
    return options.join('');
  }

  function normalizeClients(rows) {
    if (!Array.isArray(rows)) return [];
    const flat = [];

    const hasNested = rows.some((row) => Array.isArray(row?.children) && row.children.length);
    if (hasNested) {
      const walk = (nodes, depth) => {
        nodes.forEach((node) => {
          flat.push({
            id: node.id,
            name: node.name || `Client #${node.id}`,
            label: `${'— '.repeat(depth)}${node.name || `Client #${node.id}`}`,
          });
          if (Array.isArray(node.children) && node.children.length) {
            walk(node.children, depth + 1);
          }
        });
      };
      walk(rows, 0);
      return flat;
    }

    const byParent = new Map();
    rows.forEach((row) => {
      const key = row.parent_id == null ? 'root' : String(row.parent_id);
      if (!byParent.has(key)) byParent.set(key, []);
      byParent.get(key).push(row);
    });
    byParent.forEach((group) => group.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''))));

    const walkFlat = (parentId, depth) => {
      const key = parentId == null ? 'root' : String(parentId);
      (byParent.get(key) || []).forEach((node) => {
        flat.push({
          id: node.id,
          name: node.name || `Client #${node.id}`,
          label: `${'— '.repeat(depth)}${node.name || `Client #${node.id}`}`,
        });
        walkFlat(node.id, depth + 1);
      });
    };
    walkFlat(null, 0);
    return flat;
  }

  async function fetchClients() {
    const res = await fetch(`${API_BASE}/clients/all`, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to load clients');
    state.clients = normalizeClients(data.data || []);
    const filterValue = els.clientFilter.value;
    els.clientFilter.innerHTML = '<option value="">All Clients</option>' + state.clients.map((client) => (
      `<option value="${esc(client.id)}" ${String(filterValue) === String(client.id) ? 'selected' : ''}>${esc(client.label)}</option>`
    )).join('');
    els.billClientId.innerHTML = buildClientOptions(els.billClientId.value);
  }

  async function fetchBillHeads() {
    const res = await fetch(`${API_BASE}/client-bill-heads/all`, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to load bill heads');
    state.billHeads = Array.isArray(data.data) ? data.data : [];
  }

  function renderStats() {
    const publishedCount = state.items.filter((item) => item.is_published).length;
    const draftCount = state.items.length - publishedCount;
    const visibleAmount = state.items.reduce((sum, item) => sum + Number(item.total_amount || 0), 0);
    els.statTotalBills.textContent = String(state.total || 0);
    els.statPublishedBills.textContent = String(publishedCount);
    els.statDraftBills.textContent = String(draftCount);
    els.statVisibleAmount.textContent = money(visibleAmount);
  }

  function renderRows() {
    if (!state.items.length) {
      els.rows.innerHTML = `
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <h3>No client bills found</h3>
              <p>Adjust filters or create a new draft bill.</p>
            </div>
          </td>
        </tr>`;
      renderStats();
      return;
    }

    els.rows.innerHTML = state.items.map((row) => `
      <tr data-id="${esc(row.id)}">
        <td>
          <button type="button" class="table-link" data-action="view" data-id="${esc(row.id)}">Bill #${esc(row.id)}</button>
          <div class="muted-small">${row.published_at ? `Published ${esc(fmtDate(row.published_at))}` : 'Draft bill'}</div>
        </td>
        <td>${esc(row.client_name || '—')}</td>
        <td>${esc(fmtDate(row.bill_date))}</td>
        <td>${esc(fmtDate(row.due_date))}</td>
        <td>${statusBadge(row)}</td>
        <td style="font-weight:700">${esc(money(row.total_amount))}</td>
        <td>${esc(row.items_count || 0)}</td>
        <td>
          <div class="actions-cell">
            <button class="btn-icon" type="button" data-action="view" data-id="${esc(row.id)}" title="View"><i class="fa-solid fa-eye"></i></button>
            ${row.is_published ? '' : `<button class="btn-icon" type="button" data-action="edit" data-id="${esc(row.id)}" title="Edit"><i class="fa-solid fa-pen"></i></button>`}
            ${row.is_published ? '' : `<button class="btn-icon" type="button" data-action="publish" data-id="${esc(row.id)}" title="Publish"><i class="fa-solid fa-paper-plane"></i></button>`}
            ${row.is_published ? '' : `<button class="btn-icon" type="button" data-action="delete" data-id="${esc(row.id)}" title="Delete"><i class="fa-solid fa-trash"></i></button>`}
          </div>
        </td>
      </tr>`).join('');
    renderStats();
  }

  function renderPager() {
    const totalPages = Math.max(1, state.totalPages || 1);
    const start = state.total ? ((state.page - 1) * PER_PAGE) + 1 : 0;
    const end = Math.min(state.total, state.page * PER_PAGE);
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} bills`;

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let page = Math.max(1, state.page - 2); page <= Math.min(totalPages, state.page + 2); page += 1) {
      buttons.push(`<button class="page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>`);
    }
    buttons.push(`<button class="page-btn" data-page="${state.page + 1}" ${state.page >= totalPages ? 'disabled' : ''}>Next</button>`);
    els.pager.innerHTML = buttons.join('');
  }

  async function fetchBills() {
    const params = new URLSearchParams({
      page: state.page,
      per_page: PER_PAGE,
      sort_by: 'bill_date',
      sort_dir: 'desc',
    });
    if (state.q) params.set('q', state.q);
    if (state.clientId) params.set('client_id', state.clientId);
    if (state.publish) params.set('publish', state.publish);

    const res = await fetch(`${API_BASE}/client-bills?${params.toString()}`, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to load client bills');

    state.items = Array.isArray(data.data) ? data.data : [];
    state.total = Number(data?.meta?.total || 0);
    state.totalPages = Number(data?.meta?.total_pages || 1);
    renderRows();
    renderPager();
  }

  function updateFormTotal() {
    const total = Array.from(els.itemList.querySelectorAll('.item-amount')).reduce((sum, input) => sum + Number(input.value || 0), 0);
    els.formTotal.textContent = money(total);
  }

  function billHeadOptions(selectedId = '') {
    const options = ['<option value="">Custom title</option>'];
    state.billHeads.forEach((head) => {
      options.push(`<option value="${esc(head.id)}" ${String(selectedId) === String(head.id) ? 'selected' : ''}>${esc(head.title)}</option>`);
    });
    return options.join('');
  }

  function addItemRow(item = {}) {
    const wrapper = document.createElement('div');
    wrapper.className = 'item-row';
    wrapper.innerHTML = `
      <div class="item-grid">
        <div>
          <label class="form-label">Bill Head</label>
          <select class="form-select item-head-select">${billHeadOptions(item.client_bill_head_id || '')}</select>
        </div>
        <div>
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control item-title" value="${esc(item.bill_head_title || '')}" placeholder="Enter bill head title">
        </div>
        <div>
          <label class="form-label">Amount <span class="text-danger">*</span></label>
          <input type="number" min="0" step="0.01" class="form-control item-amount" value="${esc(item.amount || '')}" placeholder="0.00">
        </div>
        <div>
          <button type="button" class="btn btn-danger-soft remove-item-btn">
            <i class="fa-solid fa-trash"></i>
            Remove
          </button>
        </div>
      </div>`;

    const headSelect = wrapper.querySelector('.item-head-select');
    const titleInput = wrapper.querySelector('.item-title');
    const amountInput = wrapper.querySelector('.item-amount');

    headSelect.addEventListener('change', () => {
      const selected = state.billHeads.find((head) => String(head.id) === String(headSelect.value));
      if (selected) titleInput.value = selected.title || '';
    });
    amountInput.addEventListener('input', updateFormTotal);
    wrapper.querySelector('.remove-item-btn').addEventListener('click', () => {
      wrapper.remove();
      if (!els.itemList.children.length) addItemRow();
      updateFormTotal();
    });

    els.itemList.appendChild(wrapper);
    updateFormTotal();
  }

  function resetForm() {
    els.billForm.reset();
    els.billId.value = '';
    els.billModalTitle.textContent = 'Create Client Bill';
    els.billClientId.innerHTML = buildClientOptions('');
    els.billDate.value = new Date().toISOString().slice(0, 10);
    els.itemList.innerHTML = '';
    addItemRow();
    state.editingBill = null;
    updateFormTotal();
  }

  async function openEditModal(id) {
    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}`, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to load bill');

    const bill = data.data || {};
    if (bill.is_published) throw new Error('Published bills cannot be edited');

    resetForm();
    state.editingBill = bill;
    els.billModalTitle.textContent = `Edit Client Bill #${bill.id}`;
    els.billId.value = bill.id || '';
    els.billClientId.innerHTML = buildClientOptions(bill.client_id || '');
    els.billClientId.value = String(bill.client_id || '');
    els.billDate.value = bill.bill_date ? String(bill.bill_date).slice(0, 10) : '';
    els.billDueDate.value = bill.due_date ? String(bill.due_date).slice(0, 10) : '';
    els.billNotes.value = bill.notes || '';
    els.itemList.innerHTML = '';
    (Array.isArray(bill.items) && bill.items.length ? bill.items : [{}]).forEach(addItemRow);
    updateFormTotal();
    billModal.show();
  }

  async function openDetailModal(id) {
    els.billDetailBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
    billDetailModal.show();

    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}`, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      els.billDetailBody.innerHTML = `<div class="text-danger">Unable to load bill details.</div>`;
      return;
    }

    const bill = data.data || {};
    const items = Array.isArray(bill.items) ? bill.items : [];
    els.billDetailBody.innerHTML = `
      <div class="detail-grid">
        <div class="detail-box"><small>Bill ID</small><strong>#${esc(bill.id || '—')}</strong></div>
        <div class="detail-box"><small>Client</small><strong>${esc(bill.client_name || '—')}</strong></div>
        <div class="detail-box"><small>Bill Date</small><strong>${esc(fmtDate(bill.bill_date))}</strong></div>
        <div class="detail-box"><small>Due Date</small><strong>${esc(fmtDate(bill.due_date))}</strong></div>
        <div class="detail-box"><small>Status</small><strong>${bill.is_published ? 'Published' : 'Draft'}</strong></div>
        <div class="detail-box"><small>Published Date</small><strong>${esc(fmtDate(bill.published_at))}</strong></div>
      </div>
      <div class="section-card mt-3">
        <h6 class="section-title">Notes</h6>
        <div class="notes-box">${bill.notes ? esc(bill.notes) : '<span class="muted-small">No notes added.</span>'}</div>
      </div>
      <div class="section-card mt-3">
        <h6 class="section-title">Bill Items</h6>
        <div class="detail-items">
          ${items.length ? items.map((item) => `
            <div class="detail-item">
              <div>
                <strong>${esc(item.bill_head_title || 'Untitled')}</strong>
                <div class="muted-small">${item.client_bill_head_id ? `Head #${esc(item.client_bill_head_id)}` : 'Custom line item'}</div>
              </div>
              <div style="font-weight:700">${esc(money(item.amount))}</div>
            </div>`).join('') : '<div class="muted-small">No bill items.</div>'}
        </div>
        <div class="detail-total">Total: <span class="ms-2">${esc(money(bill.total_amount))}</span></div>
      </div>`;
  }

  async function saveBill(event) {
    event.preventDefault();

    const clientId = Number(els.billClientId.value || 0);
    if (!clientId) {
      Swal.fire({ icon:'warning', title:'Client required', text:'Select a client before saving the bill.' });
      return;
    }
    if (!els.billDate.value) {
      Swal.fire({ icon:'warning', title:'Bill date required', text:'Add the bill date before saving.' });
      return;
    }

    const items = Array.from(els.itemList.querySelectorAll('.item-row')).map((row) => {
      const client_bill_head_id = row.querySelector('.item-head-select').value || null;
      const bill_head_title = row.querySelector('.item-title').value.trim();
      const amount = row.querySelector('.item-amount').value;
      return {
        client_bill_head_id: client_bill_head_id ? Number(client_bill_head_id) : null,
        bill_head_title,
        amount: amount === '' ? null : Number(amount),
      };
    });

    const badItem = items.find((item) => !item.bill_head_title || item.amount === null || Number.isNaN(item.amount));
    if (badItem) {
      Swal.fire({ icon:'warning', title:'Bill items incomplete', text:'Each bill item needs a title and amount.' });
      return;
    }

    const payload = {
      client_id: clientId,
      bill_date: els.billDate.value,
      due_date: els.billDueDate.value || null,
      notes: els.billNotes.value.trim() || null,
      items,
    };

    const billId = els.billId.value.trim();
    const isEdit = !!billId;
    const url = isEdit ? `${API_BASE}/client-bills/${encodeURIComponent(billId)}` : `${API_BASE}/client-bills`;
    const method = isEdit ? 'PUT' : 'POST';

    setBtnLoading(els.saveBillBtn, true);
    try {
      const res = await fetch(url, {
        method,
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const errs = data?.errors ? Object.entries(data.errors).map(([key, value]) => `${key}: ${[].concat(value).join(', ')}`).join('\n') : '';
        throw new Error(errs ? `${data?.message || 'Unable to save bill'}\n${errs}` : (data?.message || 'Unable to save bill'));
      }
      toast('success', isEdit ? 'Client bill updated' : 'Client bill created');
      billModal.hide();
      await fetchBills();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Save failed', text:String(error.message || error) });
    } finally {
      setBtnLoading(els.saveBillBtn, false);
    }
  }

  async function publishBill(id) {
    const confirm = await Swal.fire({
      title: 'Publish this bill?',
      text: 'After publishing, this bill can no longer be edited or deleted.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Publish',
    });
    if (!confirm.isConfirmed) return;

    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}/publish`, { method:'PATCH', headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to publish bill');
    toast('success', 'Client bill published');
    await fetchBills();
  }

  async function deleteBill(id) {
    const confirm = await Swal.fire({
      title: 'Delete this draft bill?',
      text: 'This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'Delete',
    });
    if (!confirm.isConfirmed) return;

    const res = await fetch(`${API_BASE}/client-bills/${encodeURIComponent(id)}`, { method:'DELETE', headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Failed to delete bill');
    toast('success', 'Draft bill deleted');
    await fetchBills();
  }

  els.addBillBtn.addEventListener('click', () => {
    const clientId = els.clientFilter.value ? `?client_id=${encodeURIComponent(els.clientFilter.value)}` : '';
    window.location.href = `/admin/accounting/client-bills/create${clientId}`;
  });

  els.addItemBtn.addEventListener('click', () => addItemRow());
  els.billForm.addEventListener('submit', saveBill);
  els.refreshBtn.addEventListener('click', () => fetchBills().catch((error) => Swal.fire({ icon:'error', title:'Refresh failed', text:String(error.message || error) })));

  let searchTimer;
  els.searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.q = els.searchInput.value.trim();
      state.page = 1;
      fetchBills().catch((error) => Swal.fire({ icon:'error', title:'Search failed', text:String(error.message || error) }));
    }, 300);
  });

  els.clientFilter.addEventListener('change', () => {
    state.clientId = els.clientFilter.value;
    state.page = 1;
    fetchBills().catch((error) => Swal.fire({ icon:'error', title:'Filter failed', text:String(error.message || error) }));
  });

  els.publishFilter.addEventListener('change', () => {
    state.publish = els.publishFilter.value;
    state.page = 1;
    fetchBills().catch((error) => Swal.fire({ icon:'error', title:'Filter failed', text:String(error.message || error) }));
  });

  els.pager.addEventListener('click', (event) => {
    const btn = event.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;
    const page = Number(btn.dataset.page || 1);
    if (!Number.isNaN(page) && page >= 1 && page <= state.totalPages && page !== state.page) {
      state.page = page;
      fetchBills().catch((error) => Swal.fire({ icon:'error', title:'Pagination failed', text:String(error.message || error) }));
    }
  });

  els.rows.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-action]');
    if (!trigger) return;
    const action = trigger.dataset.action;
    const id = trigger.dataset.id;
    if (!id) return;

    try {
      if (action === 'view') await openDetailModal(id);
      if (action === 'edit') await openEditModal(id);
      if (action === 'publish') await publishBill(id);
      if (action === 'delete') await deleteBill(id);
    } catch (error) {
      Swal.fire({ icon:'error', title:'Action failed', text:String(error.message || error) });
    }
  });

  async function init() {
    try {
      await Promise.all([fetchClients(), fetchBillHeads()]);
      resetForm();
      await fetchBills();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to load billing page', text:String(error.message || error) });
      els.rows.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Unable to load client bills.</td></tr>`;
    }
  }

  init();
})();
</script>
@endpush
