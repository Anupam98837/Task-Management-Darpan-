{{-- resources/views/modules/document-types/manage.blade.php --}}

@section('content')
<div class="doctypes-page">
  <div class="page-header">
    <h1>Document Type Library</h1>
    <p>Review document types, view details, and keep records structured.</p>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search documents by name, description, or note...">
    </div>

    <!-- Filter button -->
    <button id="filterToggle" type="button" class="btn btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;">
        <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Filter
    </button>

    <!-- Sort By dropdown -->
    <div class="dropdown">
      <button class="select-box dropdown-toggle" type="button" id="sortByBtn" data-bs-toggle="dropdown" aria-expanded="false" style="text-align: left; padding-right: 38px;">
        <span id="sortBtnLabel">Sort By...</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortByBtn" style="min-width:220px">
        <li><a class="dropdown-item sortChoice" data-sort="">Sort By...</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item sortChoice" data-sort="name.asc">Name (A–Z)</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="name.desc">Name (Z–A)</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="created_at.desc">Newest</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="created_at.asc">Oldest</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="updated_at.desc">Recently Updated</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="updated_at.asc">Least Recently Updated</a></li>
      </ul>
    </div>

    <a href="{{ url('/admin/document-types/create') }}" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      Add Document Type
    </a>

    <button id="exportBtn" class="btn btn-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5-5 5 5M12 5v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Export
    </button>
  </div>

  <!-- Filters drawer -->
  <div id="filtersPanel" style="display:none; margin-bottom: 20px;">
    <div class="data-card" style="padding: 20px;">
      <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:end">
        <div>
          <label class="form-label">Status</label>
          <select id="statusFilter" class="select-box" style="width:160px">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="draft">Draft</option>
          </select>
        </div>
        <div>
          <label class="form-label">Created From</label>
          <input id="createdFrom" type="date" class="form-control" style="width: 160px; height: 44px;">
        </div>
        <div>
          <label class="form-label">Created To</label>
          <input id="createdTo" type="date" class="form-control" style="width: 160px; height: 44px;">
        </div>

        <button id="btnClearFilters" class="btn btn-secondary" style="height: 44px;">Clear Filters</button>
      </div>
    </div>
  </div>

  <!-- Data Card -->
  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Note</th>
            <th>Status</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="rows">
          <tr><td style="color:var(--muted-color);" colspan="8" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div class="pagination-info" id="paginationInfo">
        Showing 1-10 of 100 document types
      </div>
      <div class="pagination-controls" id="pager">
        <!-- Pagination buttons injected by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Edit DocType Modal -->
<div class="modal fade" id="editDocTypeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-regular fa-pen-to-square me-2"></i>Edit Document Type
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editDocTypeForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="edt_id" name="id"/>

          <div class="mb-3">
            <label class="form-label">Document Name</label>
            <input type="text" class="form-control" id="edt_name" name="name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="edt_description" name="description" rows="3"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Internal Notes</label>
            <textarea class="form-control" id="edt_note" name="note" rows="2"></textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select class="form-control form-select" id="edt_status" name="status">
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Created At</label>
              <input type="date" class="form-control" id="edt_created_at" name="created_at">
            </div>
            <div class="col-md-4">
              <label class="form-label">Updated At</label>
              <input type="date" class="form-control" id="edt_updated_at" name="updated_at">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="edt_submitBtn">
            <i class="fa fa-save me-1"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
* { box-sizing: border-box; }

.doctypes-page {background: var(--bg-body);min-height: 100vh;padding: 24px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;}
.page-header {margin-bottom: 28px;}
.page-header h1 {font-size: 28px;font-weight: 700;color: var(--text-color);margin: 0 0 6px;}
.page-header p {color: #64748b;font-size: 14px;margin: 0;}

/* Toolbar */
.toolbar {display: flex;gap: 12px;margin-bottom: 20px;flex-wrap: wrap;align-items: center;}
.search-box {position: relative;flex: 1;min-width: 280px;max-width: 420px;}
.search-box input {width: 100%;height: 44px;padding: 0 16px 0 42px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface);color: var(--text-color);transition: all 0.2s;}
.search-box input:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
.search-box svg {position: absolute;left: 14px;top: 50%;transform: translateY(-50%);pointer-events: none;}
.select-box {height: 44px;padding: 0 38px 0 14px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;color: var(--text-color);cursor: pointer;transition: all 0.2s;}
.btn {display: inline-flex;align-items: center;gap: 8px;height: 44px;padding: 0 20px;border-radius: 12px;font-size: 14px;font-weight: 600;cursor: pointer;transition: all 0.2s;border: none;text-decoration: none;}
.btn-primary {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);color: #fff;box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);}
.btn-primary:hover {transform: translateY(-1px);box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);}
.btn-secondary {background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;}
.btn-secondary:hover {background: var(--primary-color);border-color: var(--primary-color);}

/* Card */
.data-card {background:var(--surface);border-radius: 16px;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);overflow: hidden;}

/* Table */
.table-container {overflow-x: auto;}
table {width: 100%;border-collapse: collapse;color: var(--text-color);}
thead {background: var(--light-color);}
thead th {padding: 14px 18px;text-align: left;font-size: 12px;font-weight: 600;color: #64748b;text-transform: uppercase;letter-spacing: 0.5px;border-bottom: 1px solid #e2e8f0;white-space: nowrap;}
tbody tr {border-bottom: 1px solid #f1f5f9;transition: background 0.15s;background: var(--surface);}
tbody tr:hover {opacity: 0.95;}
tbody td {padding: 16px 18px;font-size: 14px;color: var(--text-color);vertical-align: middle;}
.cell-id {color: #94a3b8;font-weight: 500;}

/* Badges */
.badge {display: inline-flex;align-items: center;gap: 6px;padding: 6px 12px;border-radius: 8px;font-size: 12px;font-weight: 600;}
.badge::before {content: '';width: 6px;height: 6px;border-radius: 50%;background: currentColor;}
.badge.active {background: #dcfce7;color: #16a34a;}
.badge.pending {background: #fef3c7;color: #d97706;}
.badge.inactive {background: #f1f5f9;color: #64748b;}
.badge.archived {background: #fee2e2;color: #dc2626;}

/* Actions */
.actions-cell {display: flex;align-items: center;gap: 10px;}
/* Switch Toggle */
.switch {position: relative;width: 48px;height: 26px;border-radius: 13px;background: #cbd5e1;cursor: pointer;transition: background 0.2s;}
.switch input {display: none;}
.switch .slider {position: absolute;top: 3px;left: 3px;width: 20px;height: 20px;border-radius: 50%;background: #fff;transition: all 0.2s;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);}
.switch input:checked + .slider {transform: translateX(22px);}
/* we’ll also toggle .active via JS for broad support */
.switch.active {background: #10b981;}
.switch.saving{ filter: saturate(.7); box-shadow: inset 0 0 0 1px rgba(0,0,0,.05); }
.switch input:disabled {cursor: not-allowed;}

.btn-edit {height: 34px;padding: 0 14px;background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;border-radius: 8px;font-size: 13px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.btn-edit:hover {background: var(--primary-color);border-color: var(--primary-color);color: var(--text-color);}

/* Pagination */
.pagination {display: flex;align-items: center;justify-content: space-between;padding: 18px 20px;background: var(--light-color);border-top: 1px solid #f1f5f9;}
.pagination-info {font-size: 14px;color: #64748b;}
.pagination-controls {display: flex;gap: 6px;}
.page-btn {min-width: 38px;height: 38px;padding: 0 12px;border: 1px solid #e2e8f0;border-radius: 8px;background: var(--surface);color: var(--text-color);font-size: 14px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.page-btn:hover:not(:disabled) {background: var(--primary-color);border-color: var(--primary-color);color: var(--surface);}
.page-btn.active {background: var(--primary-color);color: #fff;border-color: var(--primary-color);}
.page-btn:disabled {opacity: 0.4;cursor: not-allowed;}

/* Empty state */
.empty-state {text-align: center;padding: 60px 20px;color: #94a3b8;}
.empty-state svg {margin-bottom: 16px;}
.empty-state h3 {font-size: 18px;font-weight: 600;color: #475569;margin: 0 0 8px 0;}
.empty-state p {font-size: 14px;margin: 0;}

/* Modal */
.modal-content {border-radius: 16px;border: none;box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);}
.modal-header {padding: 24px 28px;border-bottom: 1px solid #f1f5f9;background: var(--surface);}
.modal-title {font-size: 20px;font-weight: 700;color: var(--text-color);}
.modal-body {padding: 28px;}
.form-label {display: block;font-size: 13px;font-weight: 600;color: #475569;margin-bottom: 8px;}
.form-control {width: 100%;height: 44px;padding: 0 14px;border: 1px solid #e2e8f0;border-radius: 10px;font-size: 14px;color: #0f172a;background: #fff;transition: all 0.2s;}
.form-control:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
textarea.form-control {height: auto;padding: 10px 14px;resize: vertical;}
.form-select {height: 44px;padding: 0 38px 0 14px;background: #fff url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;}
.modal-footer {padding: 20px 28px;border-top: 1px solid #f1f5f9;display: flex;justify-content: flex-end;gap: 10px;}

/* --- NEW: Minimal loader for Save button --- */
.btn.is-loading,
.btn[aria-busy="true"]{
  pointer-events: none;
  opacity: .85;
  position: relative;
}
.btn.is-loading .btn-label{ visibility: hidden; }
.btn.is-loading::after{
  content: "";
  position: absolute; inset: 0; margin: auto;
  width: 18px; height: 18px; border-radius: 50%;
  border: 2.5px solid rgba(255,255,255,.65);
  border-top-color: rgba(255,255,255,0);
  animation: spin .7s linear infinite;
}
@keyframes spin{ to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .toolbar {flex-direction: column;}
  .search-box {max-width: 100%;}
}

/* hide the ID column (first column) */
table thead th:first-child,
table tbody td:first-child {
  display: none;
}
 
 
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
  const API_BASE = @json(url('/api'));
  const PER_PAGE = 10;

  // ---------- Token helpers ----------
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  if (!TOKEN) {
    Swal.fire('Auth Required', 'Session expired. Please login again.', 'warning')
      .then(() => location.href = '/');
    return;
  }

  let state = {
    page: 1,
    total_pages: 1,
    total: 0,
    q: '',
    status: '',
    created_from: '',
    created_to: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    items: []
  };

  const els = {
    tbody: document.getElementById('rows'),
    paginationInfo: document.getElementById('paginationInfo'),
    paginationControls: document.getElementById('pager'),
    searchInput: document.getElementById('searchInput'),
    statusFilter: document.getElementById('statusFilter'),
    createdFromEl: document.getElementById('createdFrom'),
    createdToEl: document.getElementById('createdTo'),
    clearFilters: document.getElementById('btnClearFilters'),
    exportBtn: document.getElementById('exportBtn'),
    filterToggle: document.getElementById('filterToggle'),
    filtersPanel: document.getElementById('filtersPanel'),
    sortBtnLabel: document.getElementById('sortBtnLabel')
  };

  const modalEl = document.getElementById('editDocTypeModal');
  const modal = window.bootstrap && modalEl ? new bootstrap.Modal(modalEl) : null;
  const f = {
    form: document.getElementById('editDocTypeForm'),
    id: document.getElementById('edt_id'),
    name: document.getElementById('edt_name'),
    description: document.getElementById('edt_description'),
    note: document.getElementById('edt_note'),
    status: document.getElementById('edt_status'),
    created_at: document.getElementById('edt_created_at'),
    updated_at: document.getElementById('edt_updated_at'),
    submit: document.getElementById('edt_submitBtn'),
  };

  // ---------- Helpers ----------
  function toast(icon, title, timer=1600){
    return Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer, icon, title});
  }
  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
  function statusLabel(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'Active';
    if (k === 'draft') return 'Draft';
    if (k === 'archived') return 'Archived';
    return 'Inactive';
  }
  function statusClass(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'active';
    if (k === 'draft') return 'pending';
    if (k === 'archived') return 'archived';
    return 'inactive';
  }
  function fmtDateOut(d) {
    if (!d) return '—';
    const x = new Date(d);
    return isNaN(x) ? String(d) : x.toISOString().slice(0, 10);
  }
  function fmtDateIn(d) {
    if (!d) return '';
    const x = new Date(d);
    return isNaN(x) ? '' : x.toISOString().slice(0, 10);
  }
  // NEW: button loading state
  function setBtnLoading(btn, isLoading){
    if(!btn) return;
    if(isLoading){
      if(!btn.querySelector('.btn-label')){
        btn.innerHTML = `<span class="btn-label">${btn.innerHTML}</span>`;
      }
      btn.classList.add('is-loading');
      btn.setAttribute('aria-busy','true');
      btn.disabled = true;
    }else{
      btn.classList.remove('is-loading');
      btn.removeAttribute('aria-busy');
      btn.disabled = false;
    }
  }

  // ---------- Fetch ----------
  async function fetchDocTypes() {
    const params = new URLSearchParams({
      page: state.page,
      per_page: PER_PAGE,
      sort_by: state.sort_by || 'created_at',
      sort_dir: state.sort_dir || 'desc'
    });
    if (state.q) params.set('q', state.q);
    if (state.status) params.set('status', state.status);
    if (state.created_from) params.set('created_from', state.created_from);
    if (state.created_to) params.set('created_to', state.created_to);

    try {
      const res = await fetch(`${API_BASE}/doctypes?${params}`, { headers });
      if (res.status === 401 || res.status === 403) {
        const data = await res.json().catch(() => ({}));
        await Swal.fire({ icon: 'error', title: 'Unauthorized', html: (data.message || data.error || 'Access denied') });
        return;
      }
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || 'Request failed');

      state.items = Array.isArray(data?.data) ? data.data : [];
      state.total_pages = data?.meta?.last_page || 1;
      state.total = data?.meta?.total || 0;
      render();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Unable to fetch document types', text: String(err.message) });
    }
  }

  // ---------- Render ----------
  function render() {
    if (!state.items.length) {
      els.tbody.innerHTML = `
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <path d="M9 11H15M9 15H12M3 6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <h3>No document types found</h3>
              <p>Try adjusting your filters or search query</p>
            </div>
          </td>
        </tr>
      `;
    } else {
      els.tbody.innerHTML = state.items.map(rowHtml).join('');
    }
    renderPagination();
  }

  function rowHtml(d) {
    const id = d.id ?? '';
    const name = d.name ?? '';
    const description = d.description ?? '';
    const note = d.note ?? '';
    const status = String(d.status || 'active').toLowerCase();
    const created_at = d.created_at ?? '';
    const updated_at = d.updated_at ?? '';

    const activeChecked = ['active','approved','enabled'].includes(status) ? 'checked' : '';
    const isArchived = status === 'archived' ? 'disabled' : '';
    const switchActiveClass = activeChecked ? 'active' : '';

    return `
      <tr data-id="${esc(id)}">
        <td class="cell-id">#${esc(id)}</td>
        <td>${esc(name)}</td>
        <td>${esc(description)}</td>
        <td>${esc(note)}</td>
        <td>
          <span class="badge ${statusClass(status)}">${statusLabel(status)}</span>
        </td>
        <td>${fmtDateOut(created_at)}</td>
        <td>${fmtDateOut(updated_at)}</td>
        <td>
          <div class="actions-cell">
            <label class="switch ${switchActiveClass}" title="Toggle Active/Inactive">
              <input type="checkbox" class="status-toggle" data-id="${esc(id)}" ${activeChecked} ${isArchived}>
              <span class="slider"></span>
            </label>
            <button class="btn-edit" data-action="edit-doc" data-id="${esc(id)}">Edit</button>
          </div>
        </td>
      </tr>
    `;
  }

  function renderPagination() {
    const start = (state.page - 1) * PER_PAGE + 1;
    const end = Math.min(state.page * PER_PAGE, state.total);
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} document types`;

    const pages = state.total_pages || 1;
    const cur = state.page;
    const windowSize = 5;
    let start_page = Math.max(1, cur - Math.floor(windowSize / 2));
    let end_page = Math.min(pages, start_page + windowSize - 1);
    if (end_page - start_page + 1 < windowSize) start_page = Math.max(1, end_page - windowSize + 1);

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${cur - 1}" ${cur <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let i = start_page; i <= end_page; i++) {
      buttons.push(`<button class="page-btn ${i === cur ? 'active' : ''}" data-page="${i}">${i}</button>`);
    }
    buttons.push(`<button class="page-btn" data-page="${cur + 1}" ${cur >= pages ? 'disabled' : ''}>Next</button>`);
    els.paginationControls.innerHTML = buttons.join('');
  }

  // ---------- Optimistic Status Toggle ----------
  els.tbody.addEventListener('change', async (e) => {
    const t = e.target;
    if (!t.classList.contains('status-toggle')) return;

    const id = t.getAttribute('data-id');
    const rowEl = t.closest('tr');
    const badge = rowEl?.querySelector('.badge');
    const switchLabel = t.closest('.switch');

    // prev and new state
    const prevChecked = !(!t.checked); // simple read before API
    const newChecked = t.checked;
    const newStatus = newChecked ? 'active' : 'inactive';

    // OPTIMISTIC: update visuals immediately
    if (switchLabel){
      switchLabel.classList.toggle('active', newChecked);
      switchLabel.classList.add('saving');
    }
    if (badge){
      badge.classList.remove('active','inactive','archived','pending');
      badge.classList.add(newStatus === 'active' ? 'active' : 'inactive');
      badge.textContent = newStatus === 'active' ? 'Active' : 'Inactive';
    }
    t.disabled = true;

    // mirror into local state
    const item = state.items.find(r => String(r.id) === String(id));
    const prevStatus = item ? (item.status || 'inactive') : 'inactive';
    if (item) item.status = newStatus;

    try {
      // Your existing endpoint
      const res = await fetch(`${API_BASE}/doctypes/${encodeURIComponent(id)}/toggle`, {
        method: 'PATCH',
        headers
      });

      let data = {};
      try { data = await res.clone().json(); } catch(_) {}

      if (!res.ok) {
        throw new Error(data?.message || 'Toggle failed');
      }
      toast('success', `Status updated to ${newStatus}`);
    } catch (err) {
      // rollback
      if (item) item.status = prevStatus;

      const backToChecked = prevStatus === 'active';
      t.checked = backToChecked;

      if (switchLabel){
        switchLabel.classList.toggle('active', backToChecked);
      }
      if (badge){
        const cls = prevStatus === 'active' ? 'active' :
                    prevStatus === 'archived' ? 'archived' :
                    prevStatus === 'draft' ? 'pending' : 'inactive';
        badge.classList.remove('active','inactive','archived','pending');
        badge.classList.add(cls);
        badge.textContent = statusLabel(prevStatus);
      }
      toast('error', err.message || 'Failed to update status');
    } finally {
      if (switchLabel) switchLabel.classList.remove('saving');
      t.disabled = false;
    }
  });

  // ---------- Edit ----------
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action="edit-doc"]');
    if (!btn) return;
    if (!modal) return;

    e.preventDefault();
    const id = btn.getAttribute('data-id');
    if (!id) return;

    const row = state.items.find(it => String(it.id || '') === String(id));
    let data = row || null;

    if (!data) {
      try {
        const res = await fetch(`${API_BASE}/doctypes/${encodeURIComponent(id)}`, { headers });
        const json = await res.json();
        if (!res.ok) throw new Error(json?.message || 'Request failed');
        data = json?.data || null;
      } catch (err) {
        Swal.fire({ icon: 'error', title: 'Unable to load document type', text: String(err.message) });
        return;
      }
    }

    f.id.value = data.id ?? '';
    f.name.value = data.name ?? '';
    f.description.value = data.description ?? '';
    f.note.value = data.note ?? '';
    f.status.value = (data.status ?? 'draft').toLowerCase();
    f.created_at.value = fmtDateIn(data.created_at);
    f.updated_at.value = fmtDateIn(data.updated_at);

    modal.show();
  });

  // ---------- Save (with loader) ----------
  f.form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = f.id.value?.trim();
    if (!id) return;

    const payload = {
      name: f.name.value?.trim(),
      description: f.description.value?.trim(),
      note: f.note.value?.trim(),
      status: f.status.value || 'draft'
    };

    setBtnLoading(f.submit, true);

    try {
      const res = await fetch(`${API_BASE}/doctypes/${encodeURIComponent(id)}`, {
        method: 'PUT',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json().catch(()=> ({}));
      if (!res.ok) {
        const msg = json?.message || 'Update failed';
        const errs = json?.errors ? Object.entries(json.errors)
          .map(([k, v]) => `• ${k}: ${[].concat(v).join(', ')}`).join('\n') : '';
        throw new Error(errs ? `${msg}\n\n${errs}` : msg);
      }

      toast('success', 'Document type updated');
      modal.hide();
      fetchDocTypes();
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Could not save changes', text: String(err.message) });
    } finally {
      setBtnLoading(f.submit, false);
    }
  });

  // ---------- Export ----------
  els.exportBtn.addEventListener('click', () => {
    if (!state.items.length) {
      Swal.fire({ icon: 'info', title: 'Nothing to export', text: 'No rows in the current view.' });
      return;
    }
    const headersCsv = ['ID','Name','Description','Note','Status','Created','Updated'];
    const rows = state.items.map(d => ([
      d.id ?? '', d.name ?? '', d.description ?? '', d.note ?? '',
      statusLabel(d.status), fmtDateOut(d.created_at), fmtDateOut(d.updated_at)
    ]));
    const csv = [headersCsv, ...rows].map(r => r.map(csvEscape).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `document_types_export_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });
  function csvEscape(v){ const s=String(v??''); return /[",\n]/.test(s)?`"${s.replace(/"/g,'""')}"`:s; }

  // ---------- Filters / Search / Sort ----------
  let searchDebounce;
  els.searchInput.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      state.q = els.searchInput.value.trim();
      state.page = 1; fetchDocTypes();
    }, 300);
  });
  els.statusFilter.addEventListener('change', () => {
    state.status = els.statusFilter.value; state.page = 1; fetchDocTypes();
  });
  els.createdFromEl.addEventListener('change', () => {
    state.created_from = els.createdFromEl.value; state.page = 1; fetchDocTypes();
  });
  els.createdToEl.addEventListener('change', () => {
    state.created_to = els.createdToEl.value; state.page = 1; fetchDocTypes();
  });
  els.clearFilters.addEventListener('click', () => {
    els.statusFilter.value = ''; els.createdFromEl.value = ''; els.createdToEl.value = '';
    state.status = state.created_from = state.created_to = ''; state.page = 1; fetchDocTypes();
  });
  document.querySelectorAll('.sortChoice').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const spec = a.dataset.sort || '';
      const [by, dir] = spec.split('.');
      state.sort_by = by || 'created_at';
      state.sort_dir = dir || 'desc';
      const lbl = document.getElementById('sortBtnLabel'); if (lbl) lbl.textContent = a.textContent || 'Sort By...';
      state.page = 1; fetchDocTypes();
    });
  });
  els.filterToggle.addEventListener('click', () => {
    if (!els.filtersPanel) return;
    const disp = getComputedStyle(els.filtersPanel).display;
    els.filtersPanel.style.display = (disp === 'none') ? 'block' : 'none';
  });
  els.paginationControls.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;
    const p = parseInt(btn.getAttribute('data-page'), 10);
    if (isNaN(p) || p < 1 || p > state.total_pages || p === state.page) return;
    state.page = p; fetchDocTypes();
  });

  // ---------- Initial ----------
  fetchDocTypes();
})();
</script>
@endpush
