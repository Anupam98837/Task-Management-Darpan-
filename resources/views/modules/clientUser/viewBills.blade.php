@push('styles')
<style>
.client-bills-page {
  background:
    radial-gradient(circle at top left, rgba(14,165,233,.10), transparent 24%),
    radial-gradient(circle at bottom right, rgba(37,99,235,.08), transparent 22%),
    var(--bg-body);
  min-height: 100vh;
  padding: 24px;
  font-family: var(--font-sans);
}
.cb-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
.cb-header h1 { margin:0; font-size:28px; font-weight:800; color:var(--text-color); }
.cb-header p { margin:6px 0 0; color:#64748b; font-size:14px; }
.cb-toolbar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
.cb-search { position:relative; flex:1; min-width:240px; max-width:420px; }
.cb-search input, .cb-filter {
  width:100%; height:44px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; color:#0f172a; font-size:14px;
}
.cb-search input { padding:0 14px 0 40px; }
.cb-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.cb-filter { min-width:220px; padding:0 14px; }
.cb-btn {
  height:44px; padding:0 14px; border-radius:12px; border:1px solid #dbe5f0; background:#fff; color:#0f172a;
  font-size:14px; font-weight:700; display:inline-flex; align-items:center; gap:8px; cursor:pointer;
}
.cb-card {
  background:rgba(255,255,255,.95);
  border:1px solid rgba(226,232,240,.92);
  border-radius:22px;
  box-shadow:0 18px 36px rgba(15,23,42,.08);
  overflow:hidden;
}
.cb-table-wrap { overflow:auto; }
.cb-table { width:100%; border-collapse:collapse; }
.cb-table th, .cb-table td { padding:15px 18px; border-bottom:1px solid #eef2f7; text-align:left; font-size:14px; color:var(--text-color); vertical-align:top; }
.cb-table th { background:#f8fafc; text-transform:uppercase; font-size:11px; letter-spacing:.5px; color:#64748b; }
.bill-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#dcfce7; color:#15803d; font-size:12px; font-weight:700; }
.bill-link { background:none; border:none; padding:0; color:#2563eb; font-weight:700; cursor:pointer; text-align:left; }
.cb-pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:18px; background:#f8fafc; }
.cb-pages { display:flex; gap:6px; flex-wrap:wrap; }
.cb-page-btn {
  min-width:38px; height:38px; border:1px solid #dbe5f0; border-radius:10px; background:#fff; color:#0f172a; font-weight:700; cursor:pointer;
}
.cb-page-btn.active { background:#2563eb; border-color:#2563eb; color:#fff; }
.cb-page-btn:disabled { opacity:.4; cursor:not-allowed; }
.cb-icon-btn {
  width:34px; height:34px; border-radius:10px; border:1px solid #dbe5f0; background:#fff; color:#1e293b;
  display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.cb-icon-btn:hover { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.cb-inline-actions { display:flex; gap:8px; flex-wrap:wrap; }
.empty-state { text-align:center; padding:56px 20px; color:#94a3b8; }
.empty-state h3 { margin:0 0 8px; font-size:18px; color:#475569; }
.detail-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; }
.detail-box { border:1px solid #e2e8f0; border-radius:14px; padding:14px; background:#f8fafc; }
.detail-box small { display:block; color:#64748b; margin-bottom:4px; }
.detail-items { display:flex; flex-direction:column; gap:10px; margin-top:16px; }
.detail-item { display:flex; justify-content:space-between; gap:12px; border:1px solid #e2e8f0; border-radius:14px; padding:12px 14px; background:#fff; }
.detail-total { display:flex; justify-content:flex-end; margin-top:16px; font-weight:800; font-size:18px; color:#0f172a; }
.tree-shell { border:1px solid #e2e8f0; border-radius:16px; background:#fff; max-height:420px; overflow:auto; padding:10px; }
.tree-node { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border-radius:12px; }
.tree-node:hover { background:#f8fafc; }
.tree-node.active { background:#eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
.tree-children { margin-left:18px; padding-left:12px; border-left:1px solid #e2e8f0; }
.tree-meta { display:flex; flex-direction:column; gap:3px; }
.tree-meta strong { font-size:14px; color:#0f172a; }
.tree-meta small { color:#94a3b8; font-size:12px; }
.proof-links { display:flex; flex-direction:column; gap:6px; margin-top:8px; }
.proof-link { color:#1d4ed8; font-size:12px; font-weight:700; text-decoration:none; }
.proof-link:hover { text-decoration:underline; }
@media (max-width: 768px) {
  .client-bills-page { padding:16px; }
  .detail-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="client-bills-page">
  <div class="cb-header">
    <div>
      <h1>Published Bills</h1>
      <p>View published client bills that fall inside your assigned client scope.</p>
    </div>
  </div>

  <div class="cb-toolbar">
    <div class="cb-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="billSearch" placeholder="Search bills by client or bill head...">
    </div>
    <button type="button" class="cb-btn cb-filter" id="clientFilterBtn" style="justify-content:space-between;">
      <span id="clientFilterLabel">All Clients</span>
      <i class="fa-solid fa-sitemap"></i>
    </button>
    <button type="button" class="cb-btn" id="clearClientFilterBtn">
      <i class="fa-solid fa-xmark"></i>
      Clear Client
    </button>
    <button type="button" class="cb-btn" id="exportBillsBtn">
      <i class="fa-solid fa-file-export"></i>
      Export
    </button>
  </div>

  <div class="cb-card">
    <div class="cb-table-wrap">
      <table class="cb-table">
        <thead>
          <tr>
            <th>Bill</th>
            <th>Client</th>
            <th>Bill Date</th>
            <th>Due Date</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Due</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="billRows">
          <tr><td colspan="9" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
    <div class="cb-pagination">
      <div id="paginationInfo">Showing 0-0 of 0 bills</div>
      <div class="cb-pages" id="pager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="clientTreeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Choose Client Tree</h5>
          <div class="text-muted small">Filter published bills by one client branch.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="cb-search mb-3" style="max-width:none;">
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
          <button type="button" class="cb-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="cb-btn" id="applyClientTreeBtn">Use Client</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="billDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bill Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="billDetailBody">
        <div class="text-center text-muted py-4">Loading…</div>
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
            <h5 class="modal-title mb-1">Submit Repayment</h5>
            <div class="text-muted small" id="repaymentBillMeta">This repayment will be submitted for approval.</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="repayment_bill_id">
          <div class="detail-grid">
            <div class="detail-box"><small>Bill</small><strong id="repaymentBillTitle">—</strong></div>
            <div class="detail-box"><small>Remaining</small><strong id="repaymentRemaining">Rs 0.00</strong></div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Repayment Date <span class="text-danger">*</span></label>
              <input id="repayment_date" type="date" class="cb-filter" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount <span class="text-danger">*</span></label>
              <input id="repayment_amount" type="number" min="0.01" step="0.01" class="cb-filter" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Proof Files</label>
              <input id="repayment_files" type="file" class="cb-filter" multiple>
            </div>
            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea id="repayment_note" class="cb-filter" style="height:auto;padding:10px 14px;" rows="4" placeholder="Optional note"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="d-flex gap-2 ms-auto">
            <button type="button" class="cb-btn" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="cb-btn" id="saveRepaymentBtn">
              <i class="fa-solid fa-paper-plane"></i>
              Submit
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    setTimeout(() => { window.location.href = '/client-user/login'; }, 400);
    return;
  }
  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const detailModal = new bootstrap.Modal(document.getElementById('billDetailModal'));
  const treeModal = new bootstrap.Modal(document.getElementById('clientTreeModal'));
  const repaymentModal = new bootstrap.Modal(document.getElementById('repaymentModal'));
  const state = { page: 1, total: 0, totalPages: 1, q: '', clientId: '', items: [], clients: [], clientTreeRoots: [], pendingClientId: '', repaymentBill: null };
  const els = {
    billSearch: document.getElementById('billSearch'),
    clientFilterBtn: document.getElementById('clientFilterBtn'),
    clientFilterLabel: document.getElementById('clientFilterLabel'),
    clearClientFilterBtn: document.getElementById('clearClientFilterBtn'),
    billRows: document.getElementById('billRows'),
    paginationInfo: document.getElementById('paginationInfo'),
    pager: document.getElementById('pager'),
    billDetailBody: document.getElementById('billDetailBody'),
    exportBillsBtn: document.getElementById('exportBillsBtn'),
    repaymentForm: document.getElementById('repaymentForm'),
    repaymentBillId: document.getElementById('repayment_bill_id'),
    repaymentBillTitle: document.getElementById('repaymentBillTitle'),
    repaymentBillMeta: document.getElementById('repaymentBillMeta'),
    repaymentRemaining: document.getElementById('repaymentRemaining'),
    repaymentDate: document.getElementById('repayment_date'),
    repaymentAmount: document.getElementById('repayment_amount'),
    repaymentFiles: document.getElementById('repayment_files'),
    repaymentNote: document.getElementById('repayment_note'),
    saveRepaymentBtn: document.getElementById('saveRepaymentBtn'),
    clientTreeSearch: document.getElementById('clientTreeSearch'),
    clientTreeShell: document.getElementById('clientTreeShell'),
    clientTreeSelectionLabel: document.getElementById('clientTreeSelectionLabel'),
    applyClientTreeBtn: document.getElementById('applyClientTreeBtn'),
  };
  const esc = (value = '') => String(value).replace(/[&<>"']/g, (m) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[m]));
  const fmtDate = (value) => {
    if (!value) return '—';
    const dt = new Date(value);
    return Number.isNaN(dt.getTime()) ? esc(value) : dt.toLocaleDateString('en-IN', { year:'numeric', month:'short', day:'numeric' });
  };
  const money = (value) => `Rs ${Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  function setBtnLoading(btn, on) {
    btn.disabled = !!on;
    btn.style.opacity = on ? '.75' : '1';
  }
  function exportBills() {
    if (!state.items.length) {
      return;
    }
    const rows = state.items.map((bill) => ({
      bill_id: bill.id || '',
      client: bill.client_name || '',
      bill_date: bill.bill_date || '',
      due_date: bill.due_date || '',
      total_amount: Number(bill.total_amount || 0).toFixed(2),
      published_at: bill.published_at || '',
    }));
    const headersRow = Object.keys(rows[0]);
    const csv = [
      headersRow.join(','),
      ...rows.map((row) => headersRow.map((key) => `"${String(row[key] ?? '').replace(/"/g, '""')}"`).join(',')),
    ].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `published_bills_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  async function fetchJSON(url) {
    const res = await fetch(url, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
    return data;
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

  function clientLabel(clientId, fallback = 'All Clients') {
    const match = state.clients.find((row) => String(row.id) === String(clientId || ''));
    return match ? (match.name || `Client #${match.id}`) : fallback;
  }

  function renderClientTree() {
    const query = String(els.clientTreeSearch.value || '').trim().toLowerCase();
    const activeId = Number(state.pendingClientId || state.clientId || 0);
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
    els.clientTreeSelectionLabel.textContent = activeId ? clientLabel(activeId, 'Choose Client') : 'All Clients';
  }

  async function loadClients() {
    const data = await fetchJSON('/api/clients/all');
    const rows = Array.isArray(data.data) ? data.data : [];
    state.clients = rows;
    state.clientTreeRoots = buildTree(rows);
    els.clientFilterLabel.textContent = state.clientId ? clientLabel(state.clientId) : 'All Clients';
    renderClientTree();
  }

  function renderRows() {
    if (!state.items.length) {
      els.billRows.innerHTML = '<tr><td colspan="9"><div class="empty-state"><h3>No published bills found</h3><p>No bills are currently visible inside your scope.</p></div></td></tr>';
      return;
    }
    els.billRows.innerHTML = state.items.map((bill) => `
      <tr>
        <td>
          <button class="bill-link" type="button" data-bill-id="${esc(bill.id)}">Bill #${esc(bill.id)}</button>
          <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Published ${fmtDate(bill.published_at)}</div>
        </td>
        <td>${esc(bill.client_name || '—')}</td>
        <td>${fmtDate(bill.bill_date)}</td>
        <td>${fmtDate(bill.due_date)}</td>
        <td style="font-weight:800;">${esc(money(bill.total_amount))}</td>
        <td>${esc(money(bill.approved_repayment_amount || 0))}</td>
        <td style="font-weight:800;color:${Number(bill.remaining_amount || 0) > 0.009 ? '#c2410c' : '#15803d'};">${esc(money(bill.remaining_amount ?? bill.total_amount ?? 0))}</td>
        <td><span class="bill-badge">Published</span></td>
        <td>
          <div class="cb-inline-actions">
            <button type="button" class="cb-icon-btn" data-bill-id="${esc(bill.id)}" title="View"><i class="fa-solid fa-eye"></i></button>
            <button type="button" class="cb-icon-btn" data-bill-pdf="${esc(bill.id)}" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></button>
            ${Number(bill.remaining_amount || 0) > 0.009 ? `<button type="button" class="cb-icon-btn" data-bill-repay="${esc(bill.id)}" title="Submit Repayment"><i class="fa-solid fa-money-bill-transfer"></i></button>` : ''}
          </div>
        </td>
      </tr>
    `).join('');
  }

  function renderPager() {
    const start = state.total ? ((state.page - 1) * 10) + 1 : 0;
    const end = Math.min(state.total, state.page * 10);
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} bills`;
    const buttons = [];
    buttons.push(`<button class="cb-page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let page = Math.max(1, state.page - 2); page <= Math.min(state.totalPages, state.page + 2); page += 1) {
      buttons.push(`<button class="cb-page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>`);
    }
    buttons.push(`<button class="cb-page-btn" data-page="${state.page + 1}" ${state.page >= state.totalPages ? 'disabled' : ''}>Next</button>`);
    els.pager.innerHTML = buttons.join('');
  }

  async function loadBills() {
    const params = new URLSearchParams({ page: state.page, per_page: 10, publish: 'published' });
    if (state.q) params.set('q', state.q);
    if (state.clientId) params.set('client_id', state.clientId);
    const data = await fetchJSON(`/api/client-bills?${params.toString()}`);
    state.items = Array.isArray(data.data) ? data.data : [];
    state.total = Number(data.meta?.total || 0);
    state.totalPages = Number(data.meta?.total_pages || 1);
    renderRows();
    renderPager();
  }

  async function openDetails(id) {
    detailModal.show();
    els.billDetailBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
    const data = await fetchJSON(`/api/client-bills/${encodeURIComponent(id)}`);
    const bill = data.data || {};
    const items = Array.isArray(bill.items) ? bill.items : [];
    const repayments = Array.isArray(bill.repayments) ? bill.repayments : [];
    els.billDetailBody.innerHTML = `
      <div class="detail-grid">
        <div class="detail-box"><small>Bill</small><strong>#${esc(bill.id || '—')}</strong></div>
        <div class="detail-box"><small>Client</small><strong>${esc(bill.client_name || '—')}</strong></div>
        <div class="detail-box"><small>Bill Date</small><strong>${fmtDate(bill.bill_date)}</strong></div>
        <div class="detail-box"><small>Due Date</small><strong>${fmtDate(bill.due_date)}</strong></div>
        <div class="detail-box"><small>Paid</small><strong>${esc(money(bill.approved_repayment_amount || 0))}</strong></div>
        <div class="detail-box"><small>Due</small><strong>${esc(money(bill.remaining_amount ?? bill.total_amount ?? 0))}</strong></div>
      </div>
      <div class="detail-items">
        ${items.length ? items.map((item) => `
          <div class="detail-item">
            <div>
              <strong>${esc(item.bill_head_title || 'Untitled')}</strong>
            </div>
            <div style="font-weight:800;">${esc(money(item.amount))}</div>
          </div>
        `).join('') : '<div class="empty-state" style="padding:16px;">No bill items found.</div>'}
      </div>
      <div class="detail-items" style="margin-top:16px;">
        ${repayments.length ? repayments.map((repayment) => `
          <div class="detail-item">
            <div>
              <strong>${fmtDate(repayment.repayment_date)}</strong>
              <div style="font-size:12px;color:#64748b;">${esc(String(repayment.status || 'pending').replaceAll('_', ' '))} · ${esc(repayment.submitted_by_name || '—')}</div>
              <div style="font-size:12px;color:#64748b;">${repayment.note ? esc(repayment.note) : 'No note'}</div>
              ${(Array.isArray(repayment.attachments) && repayment.attachments.length) ? `<div class="proof-links">${repayment.attachments.map((proof) => `
                <a class="proof-link" href="${esc(proof.absolute_url || proof.relative_url || '#')}" target="_blank" rel="noopener noreferrer">
                  <i class="fa-solid fa-paperclip"></i> ${esc(proof.original_name || 'Attachment')}
                </a>`).join('')}</div>` : '<div style="font-size:12px;color:#64748b;">No proof files</div>'}
            </div>
            <div style="font-weight:800;">${esc(money(repayment.amount || 0))}</div>
          </div>
        `).join('') : '<div class="empty-state" style="padding:16px;">No repayments recorded yet.</div>'}
      </div>
      ${Number(bill.remaining_amount || 0) > 0.009 ? `
        <div style="margin-top:16px;">
          <button type="button" class="cb-btn" data-detail-repay="${esc(bill.id)}">
            <i class="fa-solid fa-money-bill-transfer"></i>
            Submit Repayment
          </button>
        </div>` : ''}
      <div class="detail-total">Total: <span class="ms-2">${esc(money(bill.total_amount))}</span></div>`;
  }

  function openRepaymentModal(billId) {
    const bill = state.items.find((row) => String(row.id) === String(billId));
    if (!bill) return;
    state.repaymentBill = bill;
    els.repaymentForm.reset();
    els.repaymentBillId.value = String(bill.id);
    els.repaymentBillTitle.textContent = `Bill #${bill.id} · ${bill.client_name || '—'}`;
    els.repaymentBillMeta.textContent = `Total ${money(bill.total_amount || 0)} · Paid ${money(bill.approved_repayment_amount || 0)}`;
    els.repaymentRemaining.textContent = money(bill.remaining_amount ?? bill.total_amount ?? 0);
    els.repaymentDate.value = new Date().toISOString().slice(0, 10);
    els.repaymentAmount.value = Number(bill.remaining_amount || 0).toFixed(2);
    repaymentModal.show();
  }

  async function submitRepayment(event) {
    event.preventDefault();
    if (!state.repaymentBill) return;
    const amount = Number(els.repaymentAmount.value || 0);
    const remaining = Number(state.repaymentBill.remaining_amount || 0);
    if (!(amount > 0)) {
      window.alert('Enter a valid repayment amount.');
      return;
    }
    if (amount - remaining > 0.009) {
      window.alert(`Repayment cannot exceed remaining due of ${money(remaining)}.`);
      return;
    }

    const formData = new FormData();
    formData.append('client_bill_id', String(state.repaymentBill.id));
    formData.append('repayment_date', els.repaymentDate.value);
    formData.append('amount', String(amount));
    if (els.repaymentNote.value.trim()) formData.append('note', els.repaymentNote.value.trim());
    Array.from(els.repaymentFiles.files || []).forEach((file) => formData.append('attachments[]', file));

    setBtnLoading(els.saveRepaymentBtn, true);
    try {
      const res = await fetch('/api/client-bill-repayments', {
        method: 'POST',
        headers,
        body: formData,
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Unable to submit repayment');
      repaymentModal.hide();
      await loadBills();
      window.alert('Repayment submitted for approval.');
    } catch (error) {
      window.alert(error.message || error);
    } finally {
      setBtnLoading(els.saveRepaymentBtn, false);
    }
  }

  async function downloadBillPdf(id) {
    const res = await fetch(`/api/client-bills/${encodeURIComponent(id)}/pdf`, { headers });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data?.message || `HTTP ${res.status}`);
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
    state.pendingClientId = state.clientId || '';
    renderClientTree();
    treeModal.show();
  });
  els.clearClientFilterBtn.addEventListener('click', () => {
    state.clientId = '';
    state.page = 1;
    els.clientFilterLabel.textContent = 'All Clients';
    loadBills().catch(() => {});
  });
  els.clientTreeSearch.addEventListener('input', renderClientTree);
  els.clientTreeShell.addEventListener('change', (event) => {
    const input = event.target.closest('input[name="client_tree_pick"]');
    if (!input) return;
    state.pendingClientId = input.value;
    renderClientTree();
  });
  els.applyClientTreeBtn.addEventListener('click', () => {
    state.clientId = state.pendingClientId || '';
    state.page = 1;
    els.clientFilterLabel.textContent = state.clientId ? clientLabel(state.clientId, 'Choose Client') : 'All Clients';
    treeModal.hide();
    loadBills().catch(() => {});
  });
  els.pager.addEventListener('click', (event) => {
    const btn = event.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;
    const page = Number(btn.dataset.page || 1);
    if (!Number.isNaN(page) && page >= 1 && page <= state.totalPages && page !== state.page) {
      state.page = page;
      loadBills().catch(() => {});
    }
  });
  els.billRows.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-bill-id]');
    if (btn) {
      openDetails(btn.dataset.billId).catch((error) => {
        els.billDetailBody.innerHTML = `<div class="text-danger">${esc(error.message || error)}</div>`;
      });
      return;
    }
    const repayBtn = event.target.closest('[data-bill-repay]');
    if (repayBtn) {
      openRepaymentModal(repayBtn.dataset.billRepay);
      return;
    }
    const pdfBtn = event.target.closest('[data-bill-pdf]');
    if (!pdfBtn) return;
    downloadBillPdf(pdfBtn.dataset.billPdf).catch((error) => {
      window.alert(error.message || error);
    });
  });
  els.billDetailBody.addEventListener('click', (event) => {
    const repayBtn = event.target.closest('[data-detail-repay]');
    if (!repayBtn) return;
    detailModal.hide();
    openRepaymentModal(repayBtn.dataset.detailRepay);
  });
  els.exportBillsBtn.addEventListener('click', exportBills);
  els.repaymentForm.addEventListener('submit', submitRepayment);

  Promise.all([loadClients(), loadBills()]).catch((error) => {
    els.billRows.innerHTML = `<tr><td colspan="9" class="text-danger text-center py-4">${esc(error.message || error)}</td></tr>`;
  });
})();
</script>
@endpush
