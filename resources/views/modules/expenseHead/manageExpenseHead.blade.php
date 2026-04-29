{{-- resources/views/modules/expense-heads/manage.blade.php --}}

@section('content')
<div class="doctypes-page">
  <div class="page-header">
    <h1>Expense Head Management</h1>
    <p>Manage expense heads, view details, and perform actions</p>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search expense heads by title..." aria-label="Search expense heads">
    </div>

    <button id="filterToggle" type="button" class="btn btn-secondary" aria-expanded="false" aria-controls="filtersPanel">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;" aria-hidden="true">
        <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Filter
    </button>

    <div class="dropdown">
      <button class="select-box dropdown-toggle" type="button" id="sortByBtn" data-bs-toggle="dropdown" aria-expanded="false" style="text-align: left; padding-right: 38px;">
        <span id="sortBtnLabel">Sort By...</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortByBtn" style="min-width:220px">
        <li><a class="dropdown-item sortChoice" data-sort="">Sort By...</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item sortChoice" data-sort="title.asc">Title (A–Z)</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="title.desc">Title (Z–A)</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="created_at.desc">Newest</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="created_at.asc">Oldest</a></li>
        <li><a class="dropdown-item sortChoice" data-sort="updated_at.desc">Recently Updated</a></li>
      </ul>
    </div>

    <a href="{{ url('/admin/expenseHead/create') }}" class="btn btn-primary" aria-label="Add Expense Head">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      Add Expense Head
    </a>

    <button id="exportBtn" class="btn btn-secondary" aria-label="Export expense heads">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
          <label for="statusFilter" class="form-label">Status</label>
          <select id="statusFilter" class="select-box" style="width:160px">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
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
            <th scope="col">ID</th>
            <th scope="col">Title</th>
            <th scope="col">Status</th>
            <th scope="col">Created</th>
            <th scope="col">Updated</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody id="rows">
          <tr><td style="color:var(--muted-color);" colspan="6" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
     <!-- Empty State (initially hidden) -->
  <div id="emptyState" class="empty-state" style="display: none;">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M9 11H15M9 15H12M3 6C3 4.895 3.895 4 5 4H19C20.1046 4 21 4.895 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6Z"
        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <h3>No Expense Head data found</h3>
    <p>Try adjusting your filters or search query</p>
  </div>
    <div class="pagination">
      <div class="pagination-info" id="paginationInfo">
        Showing 1-10 of 0 expense heads
      </div>
      <div class="pagination-controls" id="pager"></div>
    </div>
  </div>
</div>

<!-- Edit Expense Head Modal -->
<div class="modal fade" id="editHeadModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-regular fa-pen-to-square me-2"></i>Edit Expense Head
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editHeadForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="edt_id" name="id"/>

          <div class="mb-3">
            <label for="edt_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="edt_title" name="title" required>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="edt_status" class="form-label">Status</label>
              <select class="form-control form-select" id="edt_status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-md-3" >
              <label for="edt_created_at" class="form-label">Created At</label>
              <input type="date" class="form-control" id="edt_created_at" name="created_at">
            </div>
            <div class="col-md-3">
              <label for="edt_updated_at" class="form-label">Updated At</label>
              <input type="date" class="form-control" id="edt_updated_at" name="updated_at">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="edt_submitBtn">
            <i class="fa fa-save me-1"></i><span class="btn-text">Save Changes</span>
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

/* Reuse same look as your doctypes page */
.doctypes-page {background: var(--bg-body);min-height: 100vh;padding: 24px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;}
.page-header {margin-bottom: 28px;}
.page-header h1 {font-size: 28px;font-weight: 700;color: var(--text-color);margin: 0 0 6px;}
.page-header p {color: #64748b;font-size: 14px;margin: 0;}

/* Toolbar */
.toolbar {display: flex;gap: 12px;margin-bottom: 20px;flex-wrap: wrap;align-items: center;}
.search-box {position: relative;flex: 1;min-width: 260px;max-width: 480px;}
.search-box input {width: 100%;height: 44px;padding: 0 16px 0 42px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface);color: var(--text-color);transition: all 0.2s;}
.search-box input:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);}
.search-box svg {position: absolute;left: 14px;top: 50%;transform: translateY(-50%);pointer-events: none;}
.select-box {height: 44px;padding: 0 38px 0 14px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;color: var(--text-color);cursor: pointer;transition: all 0.2s;}
.btn {display: inline-flex;align-items: center;gap: 8px;height: 44px;padding: 0 20px;border-radius: 12px;font-size: 14px;font-weight: 600;cursor: pointer;transition: all 0.2s;border: none;text-decoration: none;}
.btn-primary {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);color: #fff;box-shadow: 0 2px 8px rgba(59, 130, 246, 0.18);}
.btn-primary:hover {transform: translateY(-1px);box-shadow: 0 4px 12px rgba(59, 130, 246, 0.28);}
.btn-secondary {background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;}
.btn-secondary:hover {background: var(--primary-color);border-color: var(--primary-color);}

/* Card */
.data-card {background:var(--surface);border-radius: 16px;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);overflow: hidden;}

/* Table */
.table-container {overflow-x: auto;}
table {width: 100%;border-collapse: collapse;color: var(--text-color);font-size:14px;}
thead {background: var(--light-color);}
thead th {padding: 14px 18px;text-align: left;font-size: 12px;font-weight: 600;color: #64748b;text-transform: uppercase;letter-spacing: 0.5px;border-bottom: 1px solid #e2e8f0;white-space: nowrap;}
tbody tr {border-bottom: 1px solid #f1f5f9;transition: background 0.15s;background: var(--surface);}
tbody tr:hover {opacity: 0.98;}
tbody td {padding: 16px 18px;font-size: 14px;color: var(--text-color);vertical-align: middle;}
.cell-id {color: #94a3b8;font-weight: 500;}

/* Badges */
.badge {display: inline-flex;align-items: center;gap: 6px;padding: 6px 12px;border-radius: 8px;font-size: 12px;font-weight: 600;}
.badge::before {content: '';width: 6px;height: 6px;border-radius: 50%;background: currentColor;}
.badge.active {background: #dcfce7;color: #16a34a;}
.badge.inactive {background: #f1f5f9;color: #64748b;}

/* Actions */
.actions-cell {display: flex;align-items: center;gap: 10px;}
.switch {position: relative;width: 48px;height: 26px;border-radius: 13px;background: #cbd5e1;cursor: pointer;transition: background 0.2s;display:inline-block;}
.switch input {display: none;}
.switch .slider {position: absolute;top: 3px;left: 3px;width: 20px;height: 20px;border-radius: 50%;background: #fff;transition: all 0.2s;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);}
.switch input:checked + .slider {transform: translateX(22px);}
.switch.active {background: #10b981;}
.switch.saving{ filter: saturate(.75); box-shadow: inset 0 0 0 1px rgba(0,0,0,.05); }

/* Edit button */
.btn-edit {height: 34px;padding: 0 14px;background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;border-radius: 8px;font-size: 13px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.btn-edit:hover {background: var(--primary-color);border-color: var(--primary-color);color: var(--surface);}

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
.form-control:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59,130,246,0.06);}
textarea.form-control {height: auto;padding: 10px 14px;resize: vertical;}
.form-select {height: 44px;padding: 0 38px 0 14px;background: #fff url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;}
.modal-footer {padding: 20px 28px;border-top: 1px solid #f1f5f9;display: flex;justify-content: flex-end;gap: 10px;}

/* Loading indicator for buttons */
.btn.is-loading,
.btn[aria-busy="true"]{
  pointer-events: none;
  opacity: .85;
  position: relative;
}
.btn.is-loading .btn-text{ visibility: hidden; }
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

/* hide ID column if you want (uncomment) */
table thead th:first-child,
table tbody td:first-child { display: none; }
/* Balanced right-side spacing for "Actions" column */
table th:last-child,
table td:last-child {
  padding-right: 14px !important; /* moderate space — not too tight */
  text-align: center;              /* keep actions aligned neatly */
}

/* Keep toggle + button aligned to the right comfortably */
.actions-cell {
  justify-content: center;  /* align actions to right */
  gap: 10px;                  /* comfortable gap between toggle & Edit */
  padding-right: 2px;         /* very light edge space */
}

/* Edit button - keep comfortable width */
.btn-edit {
  padding: 0 12px;            /* slightly wider for better click area */
  min-width: 68px;
}
/* Table inline loading row */
.table-loading-row {
  text-align: center;
  color: var(--muted-color);
  padding: 28px 0;
}

/* small spinner used inside the loading row */
.table-loading-spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.08);
  border-top-color: rgba(0,0,0,0.25);
  border-radius: 50%;
  vertical-align: middle;
  margin-right: 10px;
  animation: tblspin .8s linear infinite;
}
@keyframes tblspin { to { transform: rotate(360deg); } }

/* when table is loading, reduce row hover effects for clarity */
.table-container.table-loading tbody tr { opacity: 0.8; pointer-events: none; }
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: #94a3b8;
}
.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: #475569;
  margin: 12px 0 8px;
}
.empty-state p {
  font-size: 14px;
  margin: 0;
}
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: #94a3b8;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}
.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: #475569;
  margin: 16px 0 8px;
}
.empty-state p {
  font-size: 14px;
  margin: 0;
  color: #64748b;
}

/* Ensure pagination is properly hidden when needed */
/* .pagination[style*="display: none"] {
  display: none !important;
} */
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
  const API_BASE = @json(url('/api'));
  const PER_PAGE = 10;

  // Auth
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headersBase = { 'Accept': 'application/json' };
  if (TOKEN) headersBase['Authorization'] = 'Bearer ' + TOKEN;

  // Basic state
  let state = {
    page: 1,
    per_page: PER_PAGE,
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

  // DOM refs (defensive)
  const get = id => document.getElementById(id);
  const els = {
    tbody: get('rows'),
    paginationInfo: get('paginationInfo'),
    paginationControls: get('pager'),
    searchInput: get('searchInput'),
    statusFilter: get('statusFilter'),
    createdFromEl: get('createdFrom'),
    createdToEl: get('createdTo'),
    clearFilters: get('btnClearFilters'),
    exportBtn: get('exportBtn'),
    filterToggle: get('filterToggle'),
    filtersPanel: get('filtersPanel'),
    sortBtnLabel: get('sortBtnLabel')
  };

  const modalEl = get('editHeadModal');
  const modal = (window.bootstrap && modalEl) ? new bootstrap.Modal(modalEl) : null;
  const f = {
    form: get('editHeadForm'),
    id: get('edt_id'),
    title: get('edt_title'),
    status: get('edt_status'),
    created_at: get('edt_created_at'),
    updated_at: get('edt_updated_at'),
    submit: get('edt_submitBtn'),
  };

  if (!els.tbody) {
    console.warn('Expense Heads: tbody#rows not found in DOM');
    return;
  }

  // Helpers
  function toast(icon, title, t=1400){ return Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:t, icon, title}); }
  function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function statusLabel(s){ const k = String(s||'').toLowerCase(); return k==='active' ? 'Active' : 'Inactive'; }
  function statusClass(s){ const k = String(s||'').toLowerCase(); return k==='active' ? 'active' : 'inactive'; }
  function fmtDateOut(d){ if(!d) return '—'; const x=new Date(d); if(isNaN(x)) return String(d); const y=x.getFullYear(), m=String(x.getMonth()+1).padStart(2,'0'), dd=String(x.getDate()).padStart(2,'0'); return `${y}-${m}-${dd}`; }
  function fmtDateIn(d){ if(!d) return ''; const x=new Date(d); if(isNaN(x)) return ''; const y=x.getFullYear(), m=String(x.getMonth()+1).padStart(2,'0'), dd=String(x.getDate()).padStart(2,'0'); return `${y}-${m}-${dd}`; }

  // Button loader helper
  function setBtnLoading(btn, isLoading){
    if(!btn) return;
    if(isLoading){
      if(!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
      btn.classList.add('is-loading'); btn.setAttribute('aria-busy','true'); btn.disabled = true;
    } else {
      btn.classList.remove('is-loading'); btn.removeAttribute('aria-busy'); btn.disabled = false;
      if(btn.dataset.origHtml){ btn.innerHTML = btn.dataset.origHtml; delete btn.dataset.origHtml; }
    }
  }

  // Table loading UI
  function showTableLoading(){
    els.tbody.innerHTML = `
      <tr class="table-loading-row">
        <td colspan="6" style="padding: 28px 18px; text-align: left;">
          <span class="table-loading-spinner" aria-hidden="true"></span>
          Loading expense heads...
        </td>
      </tr>
    `;
    const tc = document.querySelector('.table-container'); if(tc) tc.classList.add('table-loading');
  }
  function clearTableLoading(){
    const tc = document.querySelector('.table-container'); if(tc) tc.classList.remove('table-loading');
  }

  // Abort controller for fetches
  let currentFetchController = null;

  // Fetch list (ensures loader visible for minimum time)
  async function fetchExpenseHeads() {
    showTableLoading();

    if (currentFetchController) currentFetchController.abort();
    currentFetchController = new AbortController();
    const signal = currentFetchController.signal;

    const params = new URLSearchParams({
      page: state.page,
      per_page: state.per_page || PER_PAGE,
      sort_by: state.sort_by || 'created_at',
      sort_order: state.sort_dir || 'desc'
    });

    if (state.q) params.set('search', state.q);
    if (state.status) params.set('status', state.status);
    if (state.created_from) params.set('created_from', state.created_from);
    if (state.created_to) params.set('created_to', state.created_to);

    const started = Date.now();
    try {
      const res = await fetch(`${API_BASE}/expense-heads?${params}`, { headers: headersBase, signal });
      if (res.status === 401 || res.status === 403) {
        const d = await res.json().catch(()=>({}));
        await Swal.fire({ icon:'error', title:'Unauthorized', html: d.message || d.error || 'Access denied' });
        return;
      }
      const json = await res.json();
      if (!res.ok) throw new Error(json?.message || 'Request failed');

      state.items = Array.isArray(json?.data) ? json.data : [];
      const meta = json?.meta || {};
      state.total_pages = meta.last_page || 1;
      state.total = meta.total || 0;
      state.page = meta.current_page || state.page;

      // guarantee loader visibility (short)
      const elapsed = Date.now() - started;
      const MIN = 220;
      if (elapsed < MIN) await new Promise(r => setTimeout(r, MIN - elapsed));

      render();
    } catch (err) {
      if (err.name === 'AbortError') return;
      console.error('Fetch expense heads error', err);
      Swal.fire({ icon:'error', title:'Unable to fetch expense heads', text: String(err.message) });
      els.tbody.innerHTML = `
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <h3>Error loading</h3>
              <p>Unable to fetch expense heads. Try again.</p>
            </div>
          </td>
        </tr>
      `;
    } finally {
      currentFetchController = null;
      clearTableLoading();
    }
  }

  // Render rows
  function rowHtml(d){
    const id = d.id ?? '';
    const title = d.title ?? '';
    const status = String(d.status || 'inactive').toLowerCase();
    const created_at = d.created_at ?? '';
    const updated_at = d.updated_at ?? '';
    const activeChecked = status === 'active' ? 'checked' : '';
    const switchActiveClass = activeChecked ? 'active' : '';
    return `
      <tr data-id="${esc(id)}">
        <td class="cell-id">#${esc(id)}</td>
        <td>${esc(title)}</td>
        <td><span class="badge ${statusClass(status)}">${statusLabel(status)}</span></td>
        <td>${fmtDateOut(created_at)}</td>
        <td>${fmtDateOut(updated_at)}</td>
        <td>
          <div class="actions-cell" role="group" aria-label="Actions for ${esc(title)}">
            <label class="switch ${switchActiveClass}" title="Toggle Active/Inactive" role="switch" aria-checked="${activeChecked ? 'true' : 'false'}">
              <input type="checkbox" class="status-toggle" data-id="${esc(id)}" ${activeChecked} aria-label="Toggle status for ${esc(title)}">
              <span class="slider" aria-hidden="true"></span>
            </label>
            <button class="btn-edit" data-action="edit-head" data-id="${esc(id)}" aria-label="Edit ${esc(title)}">Edit</button>
          </div>
        </td>
      </tr>
    `;
  }
  // Render table / pagination
// Render table / pagination
function render() {
  clearTableLoading();

  const hasRows = Array.isArray(state.items) && state.items.length > 0;
  const tableContainer = document.getElementById('tableContainer');
  const emptyState = document.getElementById('emptyState');
  const pagination = document.querySelector('.pagination');

  if (!hasRows) {
    // Show empty state, hide table and pagination
    if (tableContainer) tableContainer.style.display = 'none';
    if (emptyState) emptyState.style.display = 'block';
    if (pagination) pagination.style.display = 'flex';
  } else {
    // Show table and pagination, hide empty state
    if (tableContainer) tableContainer.style.display = 'block';
    if (emptyState) emptyState.style.display = 'none';
    if (pagination) pagination.style.display = 'flex';

    // Render rows normally
    els.tbody.innerHTML = state.items.map(rowHtml).join('');
    
    // Update pagination numbers
    renderPagination();
  }
}
  function renderPagination(){
    const per = state.per_page || PER_PAGE;
    const start = (state.page - 1) * per + 1;
    const end = Math.min(state.page * per, state.total || 0);
    els.paginationInfo.textContent = `Showing ${state.total ? start : 0}-${state.total ? end : 0} of ${state.total || 0} expense heads`;

    const pages = state.total_pages || 1;
    const cur = state.page || 1;
    const windowSize = 5;
    let start_page = Math.max(1, cur - Math.floor(windowSize/2));
    let end_page = Math.min(pages, start_page + windowSize - 1);
    if (end_page - start_page + 1 < windowSize) start_page = Math.max(1, end_page - windowSize + 1);

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${cur-1}" ${cur <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let i = start_page; i <= end_page; i++) buttons.push(`<button class="page-btn ${i===cur ? 'active' : ''}" data-page="${i}">${i}</button>`);
    buttons.push(`<button class="page-btn" data-page="${cur+1}" ${cur >= pages ? 'disabled' : ''}>Next</button>`);
    if (els.paginationControls) els.paginationControls.innerHTML = buttons.join('');
  }

  // Optimistic status toggle
  document.body.addEventListener('change', async (e) => {
    const t = e.target;
    if (!t.classList.contains('status-toggle')) return;

    const id = t.getAttribute('data-id');
    const rowEl = t.closest('tr');
    const badge = rowEl?.querySelector('.badge');
    const switchLabel = t.closest('.switch');

    const item = state.items.find(r => String(r.id) === String(id));
    const prevStatus = item ? (item.status || 'inactive') : (t.checked ? 'inactive' : 'active');
    const newChecked = t.checked;
    const newStatus = newChecked ? 'active' : 'inactive';

    if (switchLabel){
      switchLabel.classList.toggle('active', newChecked);
      switchLabel.classList.add('saving');
      switchLabel.setAttribute('aria-checked', newChecked ? 'true' : 'false');
    }
    if (badge){
      badge.classList.remove('active','inactive');
      badge.classList.add(newStatus === 'active' ? 'active' : 'inactive');
      badge.textContent = newStatus === 'active' ? 'Active' : 'Inactive';
    }
    t.disabled = true;
    if (item) item.status = newStatus;

    try {
      const res = await fetch(`${API_BASE}/expense-heads/${encodeURIComponent(id)}/toggle-status`, { method:'PATCH', headers: headersBase });
      const data = await res.clone().json().catch(()=>({}));
      if (!res.ok) throw new Error(data?.message || 'Toggle failed');
      if (data?.data) {
        const idx = state.items.findIndex(r => String(r.id) === String(id));
        if (idx > -1) state.items[idx] = data.data;
      }
      toast('success', `Status updated to ${newStatus}`);
    } catch (err) {
      // rollback
      if (item) item.status = prevStatus;
      const backToChecked = prevStatus === 'active';
      t.checked = backToChecked;
      if (switchLabel) { switchLabel.classList.toggle('active', backToChecked); switchLabel.setAttribute('aria-checked', backToChecked ? 'true' : 'false'); }
      if (badge) { badge.classList.remove('active','inactive'); badge.classList.add(prevStatus === 'active' ? 'active' : 'inactive'); badge.textContent = statusLabel(prevStatus); }
      toast('error', err.message || 'Failed to update status');
    } finally {
      if (switchLabel) switchLabel.classList.remove('saving');
      t.disabled = false;
    }
  });

  // Edit flow (open modal and populate)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action="edit-head"]');
    if (!btn) return;
    if (!modal) { console.warn('Bootstrap modal not available'); return; }
    e.preventDefault();
    const id = btn.getAttribute('data-id');
    if (!id) return;

    let data = state.items.find(it => String(it.id || '') === String(id)) || null;
    if (!data) {
      try {
        const res = await fetch(`${API_BASE}/expense-heads/${encodeURIComponent(id)}`, { headers: headersBase });
        const json = await res.json();
        if (!res.ok) throw new Error(json?.message || 'Request failed');
        data = json?.data || null;
      } catch (err) {
        Swal.fire({ icon:'error', title:'Unable to load expense head', text: String(err.message) });
        return;
      }
    }

    if (f.id) f.id.value = data.id ?? '';
    if (f.title) f.title.value = data.title ?? '';
    if (f.status) f.status.value = (data.status ?? 'inactive').toLowerCase();
    if (f.created_at) f.created_at.value = fmtDateIn(data.created_at);
    if (f.updated_at) f.updated_at.value = fmtDateIn(data.updated_at);

    modal.show();
  });

  // Save update with loader
  if (f.form) {
    f.form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = f.id.value?.trim();
      if (!id) return;
      const payload = { title: f.title.value?.trim(), status: f.status.value || 'inactive' };
      setBtnLoading(f.submit, true);
      try {
        const res = await fetch(`${API_BASE}/expense-heads/${encodeURIComponent(id)}`, {
          method: 'PUT',
          headers: Object.assign({ 'Content-Type':'application/json' }, headersBase),
          body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=> ({}));
        if (!res.ok) {
          const msg = json?.message || 'Update failed';
          const errs = json?.errors ? Object.entries(json.errors).map(([k,v])=> `• ${k}: ${[].concat(v).join(', ')}`).join('\n') : '';
          throw new Error(errs ? `${msg}\n\n${errs}` : msg);
        }
        toast('success', 'Expense head updated');
        modal.hide();
        fetchExpenseHeads();
      } catch (err) {
        Swal.fire({ icon:'error', title:'Could not save changes', text: String(err.message) });
      } finally {
        setBtnLoading(f.submit, false);
      }
    });
  }

  // Export CSV
  if (els.exportBtn) {
    els.exportBtn.addEventListener('click', () => {
      if (!state.items.length) { Swal.fire({ icon:'info', title:'Nothing to export', text:'No rows in the current view.' }); return; }
      const headersCsv = ['ID','Title','Status','Created','Updated'];
      const rows = state.items.map(d => ([ d.id ?? '', d.title ?? '', statusLabel(d.status), fmtDateOut(d.created_at), fmtDateOut(d.updated_at) ]));
      const csv = [headersCsv, ...rows].map(r => r.map(csvEscape).join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url; a.download = `expense_heads_export_${new Date().toISOString().slice(0,10)}.csv`; document.body.appendChild(a); a.click(); document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
  }
  function csvEscape(v){ const s = String(v ?? ''); return /[",\n]/.test(s) ? `"${s.replace(/"/g,'""')}"` : s; }

  // Filters / Search bindings (defensive)
  let searchDebounce;
  if (els.searchInput) {
    els.searchInput.addEventListener('input', () => {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => { state.q = els.searchInput.value.trim(); state.page = 1; fetchExpenseHeads(); }, 300);
    });
  }
  if (els.statusFilter) els.statusFilter.addEventListener('change', () => { state.status = els.statusFilter.value; state.page = 1; fetchExpenseHeads(); });
  if (els.createdFromEl) els.createdFromEl.addEventListener('change', () => { state.created_from = els.createdFromEl.value; state.page = 1; fetchExpenseHeads(); });
  if (els.createdToEl) els.createdToEl.addEventListener('change', () => { state.created_to = els.createdToEl.value; state.page = 1; fetchExpenseHeads(); });
  if (els.clearFilters) els.clearFilters.addEventListener('click', () => { if (els.statusFilter) els.statusFilter.value=''; if (els.createdFromEl) els.createdFromEl.value=''; if (els.createdToEl) els.createdToEl.value=''; state.status = state.created_from = state.created_to = ''; state.page = 1; fetchExpenseHeads(); });

  document.querySelectorAll('.sortChoice').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const spec = a.dataset.sort || '';
      const [by, dir] = spec.split('.');
      state.sort_by = by || 'created_at';
      state.sort_dir = dir || 'desc';
      if (els.sortBtnLabel) els.sortBtnLabel.textContent = a.textContent || 'Sort By...';
      state.page = 1; fetchExpenseHeads();
    });
  });

  if (els.filterToggle && els.filtersPanel) {
    els.filterToggle.addEventListener('click', () => {
      const disp = getComputedStyle(els.filtersPanel).display;
      const isHidden = (disp === 'none');
      els.filtersPanel.style.display = isHidden ? 'block' : 'none';
      els.filterToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    });
  }

  if (els.paginationControls) {
    els.paginationControls.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-page]');
      if (!btn || btn.disabled) return;
      const p = parseInt(btn.getAttribute('data-page'), 10);
      if (isNaN(p) || p < 1 || p > state.total_pages || p === state.page) return;
      state.page = p; fetchExpenseHeads();
    });
  }

  // initial
  fetchExpenseHeads();

})();
</script>

@endpush
