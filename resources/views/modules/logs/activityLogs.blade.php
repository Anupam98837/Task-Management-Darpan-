@push('styles')
<style>
* { box-sizing: border-box; }

/* ===== Main Container ===== */
.activity-page {
  background: var(--bg-body);
  min-height: 100vh;
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
}

/* ===== Page Header ===== */
.page-header {
  margin-bottom: 28px;
}
.page-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-color);
  margin: 0 0 6px;
}
.page-header p {
  color: #64748b;
  font-size: 14px;
  margin: 0;
}

/* ===== Toolbar ===== */
.toolbar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 280px;
  max-width: 420px;
}

.search-box input {
  width: 100%;
  height: 44px;
  padding: 0 16px 0 42px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 14px;
  background: var(--surface);
  color: var(--text-color);
  transition: all 0.2s;
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-box svg {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

.filter-group {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.select-box {
  height: 44px;
  padding: 0 38px 0 14px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 14px;
  background: var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;
  appearance: none;
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
}

.select-box:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 44px;
  padding: 0 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

.btn-secondary {
  background: var(--surface);
  color: var(--text-color);
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

/* ===== Filter Card ===== */
.filter-card {
  background: var(--surface);
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 8px;
}

.form-control {
  width: 100%;
  height: 44px;
  padding: 0 14px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: var(--text-color);
  background: var(--surface);
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* ===== Data Card ===== */
.data-card {
  background: var(--surface);
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

/* ===== Table ===== */
.table-container {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

table {
  width: 100%;
  border-collapse: collapse;
  color: var(--text-color);
  min-width: 1100px;
}

thead {
  background: var(--light-color);
}

thead th {
  padding: 14px 18px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

tbody tr {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
  background: var(--surface);
}

tbody tr:hover {
  background: color-mix(in hsl, var(--accent-color) 12%, var(--surface));
  box-shadow: inset 0 1px 0 rgba(0,0,0,.04), inset 0 -1px 0 rgba(0,0,0,.04);
}

tbody td {
  padding: 16px 18px;
  font-size: 14px;
  color: var(--text-color);
  vertical-align: middle;
}

/* ===== Actor Badge (matching Document Management style) ===== */
.actor-badge {
  display: inline-flex;
  gap: 6px;
  align-items: center;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: 12px;
  border: 1px solid color-mix(in hsl, var(--primary-color) 45%, var(--border-color));
  background: color-mix(in hsl, var(--primary-color) 10%, var(--surface));
  color: var(--text-color);
}

/* ===== Activity Dot ===== */
.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 8px;
  background: var(--accent-color);
  box-shadow: 0 0 0 2px color-mix(in hsl, var(--accent-color) 20%, transparent);
}

/* ===== Mono Text ===== */
.mono {
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.2px;
  font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
}

/* ===== Note Cell Clamping ===== */
.note-cell > div {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ===== Action Icons ===== */
.actions-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.act-link, .view-link {
  color: var(--muted-color);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid transparent;
  transition: var(--transition);
  background: var(--surface);
}

.act-link:hover, .view-link:hover {
  color: var(--primary-color);
  background: color-mix(in hsl, var(--primary-color) 10%, var(--surface));
  border-color: color-mix(in hsl, var(--primary-color) 25%, var(--border-color));
}

/* ===== Pagination ===== */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px;
  background: var(--light-color);
  border-top: 1px solid #f1f5f9;
}

.pagination-info {
  font-size: 14px;
  color: #64748b;
}

.pagination-controls {
  display: flex;
  gap: 6px;
}

.page-btn {
  min-width: 38px;
  height: 38px;
  padding: 0 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: var(--surface);
  color: var(--text-color);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: #fff;
}

.page-btn.active {
  background: var(--primary-color);
  color: #fff;
  border-color: var(--primary-color);
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ===== Empty State ===== */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state svg {
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: #475569;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 14px;
  margin: 0;
}

/* ===== Modal (matching Document Management style) ===== */
.modal-content {
  border-radius: 16px;
  border: none;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  background: var(--surface);
}

.modal-header {
  padding: 24px 28px;
  border-bottom: 1px solid #f1f5f9;
  background: var(--surface);
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-color);
  display: inline-flex;
  gap: 8px;
  align-items: center;
}

.modal-title i {
  color: var(--accent-color);
}

.modal-body {
  padding: 28px;
  max-height: 70vh;
  overflow: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--border-color) transparent;
}

.modal-body::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: var(--border-color);
  border-radius: 8px;
}

.modal-footer {
  padding: 20px 28px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Details table in modal */
#logDetails .table {
  margin: 0;
  font-size: 0.94rem;
  color: var(--text-color);
}

#logDetails th {
  width: 220px;
  white-space: nowrap;
  color: #64748b;
  background: var(--light-color);
  border-right: 1px dashed var(--border-color);
}

#logDetails td,
#logDetails th {
  vertical-align: top;
  border-color: var(--border-color);
  color: var(--text-color);
  padding: 8px 10px;
  background: var(--surface);
}

#logDetails pre {
  margin: 0;
  background: var(--light-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 8px 10px;
  color: var(--text-color);
  font-size: 0.9rem;
  line-height: 1.35;
  overflow: auto;
}

/* Modal backdrop */
.modal-backdrop.show {
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(2px);
}

/* ===== Dark Theme ===== */


/* ===== Responsive ===== */
@media (max-width: 768px) {
  .toolbar {
    flex-direction: column;
  }
 
  .search-box {
    max-width: 100%;
  }
 
  .filter-group {
    width: 100%;
    justify-content: space-between;
  }
 
  .activity-page {
    padding: 16px;
  }
}

@media (max-width: 576px) {
  table thead th,
  table tbody td {
    padding: 8px 10px;
  }
}
</style>
@endpush

@section('content')
<div class="activity-page">
  {{-- ===== Header ===== --}}
  <div class="page-header">
    <h1>Activity Logs</h1>
    <p>Track all system activities and user actions</p>
  </div>

  {{-- ===== Toolbar ===== --}}
  <div class="toolbar">
    {{-- Search with icon inside input --}}
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="q" type="text" placeholder="Search note, actor, record id…">
    </div>

    <div class="filter-group">
      <select id="module" class="select-box" style="width:160px">
        <option value="">All Modules</option>
        <option value="Auth">Auth</option>
        <option value="Clients">Clients</option>
        <option value="documents">Documents</option>
        <option value="mailer">Mailer</option>
      </select>

      <select id="activity" class="select-box" style="width:160px">
        <option value="">All Activities</option>
        <option value="store">Store</option>
        <option value="update">Update</option>
        <option value="delete">Delete</option>
        <option value="default">Default</option>
        <option value="toggled on">Toggled On</option>
        <option value="toggled off">Toggled Off</option>
      </select>

      <select id="actor" class="select-box" style="width:220px">
        <option value="">All Actors</option>
      </select>

      <select id="sort" class="select-box" style="width:170px">
        <option value="desc">Newest First</option>
        <option value="asc">Oldest First</option>
      </select>

      <select id="limit" class="select-box" style="width:100px">
        <option>10</option>
        <option>25</option>
        <option>50</option>
      </select>
    </div>

    <button id="filterToggle" type="button" class="btn btn-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M3 6h18M8 12h8M11 18h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Filter
    </button>
  </div>

  {{-- ===== Filters Drawer ===== --}}
  <div id="filtersPanel" style="display:none">
    <div class="filter-card">
      <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
        <div>
          <label class="form-label">From Date</label>
          <input id="from" type="date" class="form-control" style="height:44px;min-width:190px">
        </div>
        <div>
          <label class="form-label">To Date</label>
          <input id="to" type="date" class="form-control" style="height:44px;min-width:190px">
        </div>
        <button id="btnClearFilters" class="btn btn-secondary" style="height:44px">Clear Filters</button>
      </div>
    </div>
  </div>

  {{-- ===== Table Card ===== --}}
  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>WHEN</th>
            <th>ACTOR</th>
            <th>MODULE</th>
            <th>ACTIVITY</th>
            <th>NOTE</th>
            <th>TARGET</th>
            <th style="width:94px">ACTIONS</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div class="pagination-info" id="pageInfo">
        Page 1 of 1 • 0 logs
      </div>
      <div class="pagination-controls" id="pager">
        <!-- pagination injected by JS -->
      </div>
    </div>
  </div>
</div>

{{-- ===== Row Details Modal ===== --}}
<div class="modal fade" id="logViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-regular fa-eye"></i>Log Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="logDetails" class="table-responsive"></div>
      </div>
      <div class="modal-footer">
        <button id="copyJson" type="button" class="btn btn-secondary">
          <i class="fa-regular fa-copy"></i> Copy JSON
        </button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if(!TOKEN){
    Swal.fire('Auth Required','Session expired. Please login again.','warning')
      .then(()=>location.href='/');
    return;
  }
 
  const API_BASE = '/api';
  const API_LIST = `${API_BASE}/activity-logs`;
  const PER_PAGE = 10;

  let page = 1, pages = 1, total = 0;
  const state = { q:'', module:'', activity:'', actor:'', from:'', to:'', sort:'desc', limit:10 };
  let lastRows = [];
 
  const $ = id => document.getElementById(id);
  const tbody = $('tbody');
  const pageInfo = $('pageInfo');
  const pager = $('pager');

  const fmtExact = iso => {
    if(!iso) return '';
    const d = new Date(iso);
    const P=n=>String(n).padStart(2,'0');
    return `${P(d.getDate())}/${P(d.getMonth()+1)}/${d.getFullYear()}, ${P(d.getHours())}:${P(d.getMinutes())}:${P(d.getSeconds())}`;
  };
 
  const timeAgo = iso => {
    if(!iso) return '';
    const s = (Date.now()-new Date(iso).getTime())/1000;
    const m=Math.floor(s/60), h=Math.floor(m/60), d=Math.floor(h/24);
    if (m<1) return 'just now';
    if (m<60) return `${m}m ago`;
    if (h<24) return `${h}h ago`;
    return `${d}d ago`;
  };
 
  const esc = s => (s==null?'':String(s))
    .replaceAll('&','&amp;').replaceAll('<','&lt;')
    .replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');

  const buildQuery = () => {
    const p = new URLSearchParams();
    if (state.q)        p.set('q', state.q);
    if (state.module)   p.set('module', state.module);
    if (state.activity) p.set('activity', state.activity);
    if (state.actor)    p.set('actor', state.actor);
    if (state.from)     p.set('from', state.from);
    if (state.to)       p.set('to', state.to);
    p.set('sort',  state.sort);
    p.set('limit', state.limit);
    p.set('page',  page);
    return p.toString();
  };

  const headers = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };

  function explainHttpError(status){
    if (status === 401) return 'Unauthorized (401): Please check your authentication.';
    if (status === 403) return 'Forbidden (403): Permission denied.';
    if (status === 404) return 'Not found (404): Check the API endpoint.';
    if (status === 419) return 'Session expired (419): Please login again.';
    if (status >=500)   return 'Server error: Please check server logs.';
    return `HTTP ${status}`;
  }
 
  // Modal
  const detailContainer = $('logDetails');
  const copyBtn = $('copyJson');
  let modal;
 
  function ensureModal(){
    if (!modal) modal = new bootstrap.Modal($('logViewModal'));
  }
 
  function renderDetails(row){
    const entries = Object.entries(row ?? {})
      .sort((a,b)=>String(a[0]).localeCompare(String(b[0])));
   
    if (!entries.length) {
      detailContainer.innerHTML = '<div class="text-muted">No fields</div>';
      return;
    }
   
    const rows = entries.map(([k,v])=>{
      const val = typeof v === 'object' && v !== null
        ? JSON.stringify(v, null, 2)
        : String(v ?? '');
      const pre = /[\r\n]/.test(val) || val.length > 80;
     
      return `
        <tr>
          <th style="white-space:nowrap;vertical-align:top;border-color:var(--border-color)">${esc(k)}</th>
          <td style="border-color:var(--border-color)">
            ${pre ? `<pre class="mb-0" style="white-space:pre-wrap">${esc(val)}</pre>` : esc(val)}
          </td>
        </tr>`;
    }).join('');
   
    detailContainer.innerHTML = `<table class="table table-sm"><tbody>${rows}</tbody></table>`;
   
    copyBtn.onclick = async () => {
      try {
        await navigator.clipboard.writeText(JSON.stringify(row, null, 2));
        copyBtn.innerHTML = '<i class="fa-regular fa-check-circle"></i> Copied';
      } catch {
        copyBtn.innerHTML = '<i class="fa-regular fa-circle-xmark"></i> Failed';
      } finally {
        setTimeout(()=> copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy JSON', 1200);
      }
    };
  }

  // Render rows
  const renderRows = (rows=[]) => {
    lastRows = rows.slice();
   
    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <path d="M9 11H15M9 15H12M3 6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <h3>No logs found</h3>
              <p>Try adjusting your filters or search query</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }
   
    tbody.innerHTML = rows.map((r, i) => {
      const ts = r.created_at || r.occurred_at || r.when;
      const actorId = r.performed_by ?? r.user_id ?? r.actor ?? '—';
      const actorName = r.performed_by_role ?? r.user_name ?? '';
      const module = r.module ?? r.module_name ?? '—';
      const activity = r.activity ?? r.action ?? '—';
      const note = r.log_note ?? r.details ?? r.description ?? r.message ?? '';
      const target = r.target ?? r.record_table ?? r.record_id ?? '';
      const openUrl = r.open_url ?? '#';
     
      return `
        <tr>
          <td class="mono">
            <div style="color:#64748b">${esc(timeAgo(ts))}</div>
            <div style="opacity:.8;font-size:13px">${esc(fmtExact(ts))}</div>
          </td>
          <td>
            <span class="actor-badge">
              <i class="fa-regular fa-user"></i>
              <span>#${esc(actorId)}</span>
              ${actorName ? `<span style="opacity:.8">· ${esc(actorName)}</span>` : ''}
            </span>
          </td>
          <td>${esc(module)}</td>
          <td>
            <span class="activity-dot"></span>
            <span class="mono">${esc(activity)}</span>
          </td>
          <td class="note-cell" style="max-width:420px">
            <div style="white-space:pre-wrap">${esc(note)}</div>
          </td>
          <td class="mono">${esc(target)}</td>
          <td>
            <div class="actions-cell">
              <a href="#" class="view-link js-view" data-index="${i}" title="View details">
                <i class="fa-regular fa-eye"></i>
              </a>
              ${openUrl !== '#' ? `
                <a class="act-link" href="${openUrl}" title="Open record" target="_blank" rel="noopener">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
              ` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
  };

  // Eye → modal
  tbody.addEventListener('click', (e)=>{
    const a = e.target.closest('.js-view');
    if (!a) return;
    e.preventDefault();
    const idx = Number(a.dataset.index);
    const row = lastRows[idx] ?? null;
    ensureModal();
    renderDetails(row);
    modal.show();
  });

  const setPageInfo = () => {
    const start = (page - 1) * state.limit + 1;
    const end = Math.min(page * state.limit, total);
    pageInfo.textContent = `Showing ${start}-${end} of ${total} logs`;
  };

  const renderPagination = () => {
    const windowSize = 5;
    let start_page = Math.max(1, page - Math.floor(windowSize / 2));
    let end_page = Math.min(pages, start_page + windowSize - 1);
   
    if (end_page - start_page + 1 < windowSize) {
      start_page = Math.max(1, end_page - windowSize + 1);
    }

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>Previous</button>`);
   
    for (let i = start_page; i <= end_page; i++) {
      buttons.push(`<button class="page-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</button>`);
    }
   
    buttons.push(`<button class="page-btn" data-page="${page + 1}" ${page >= pages ? 'disabled' : ''}>Next</button>`);
   
    pager.innerHTML = buttons.join('');
  };

  // Fetch list
  const fetchList = async (initOnly=false) => {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr>`;
    const url = `${API_LIST}?${buildQuery()}`;
   
    try {
      const res = await fetch(url, { headers });
     
      if (!res.ok) {
        const text = await res.text().catch(()=> '');
        console.error('[activity-logs] HTTP error', res.status, text);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">${explainHttpError(res.status)}</td></tr>`;
        return;
      }
     
      const data = await res.json();
      const rows = data.data ?? [];
      page = data.page ?? 1;
      total = data.total ?? rows.length;
      pages = Math.max(1, Math.ceil(total / (data.limit ?? state.limit)));

      renderRows(rows);
      setPageInfo();
      renderPagination();

      if (initOnly) populateFromRows(rows);
    } catch (e) {
      console.error('[activity-logs] fetch failed', e);
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">Network error — check console for details.</td></tr>`;
    }
  };

  // Populate actor dropdown
  const populateFromRows = (rows=[]) => {
    const actorSet = new Set();
   
    rows.forEach(r => {
      const aid = r.performed_by ?? r.user_id ?? r.actor ?? '';
      const an = r.performed_by_role ?? r.user_name ?? '';
      if (aid || an) actorSet.add(`${aid}|${an}`);
    });

    const actorSelect = $('actor');
    [...actorSet].sort((a, b) => String(a).localeCompare(String(b))).forEach(v => {
      const [id, name] = v.split('|');
      const o = document.createElement('option');
      o.value = id || name || '';
      o.textContent = name ? `${name} (#${id})` : `#${id}`;
      actorSelect.appendChild(o);
    });
  };

  // Debounced search
  const debounce = (fn, ms=350) => {
    let t;
    return (...a) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...a), ms);
    };
  };
 
  $('q').addEventListener('input', debounce(e => {
    state.q = e.target.value.trim();
    page = 1;
    fetchList();
  }));

  // Filter changes
  ['module','activity','actor','sort','limit','from','to'].forEach(id => {
    const el = $(id);
    if (!el) return;
    el.addEventListener('change', e => {
      state[id] = id === 'limit' ? (parseInt(e.target.value, 10) || 10) : e.target.value;
      page = 1;
      fetchList();
    });
  });

  // Filter toggle
  $('filterToggle').addEventListener('click', () => {
    const panel = $('filtersPanel');
    const isOpen = getComputedStyle(panel).display !== 'none';
    panel.style.display = isOpen ? 'none' : 'block';
  });

  // Clear filters
  $('btnClearFilters').addEventListener('click', (e) => {
    e.preventDefault();
    $('from').value = '';
    $('to').value = '';
    state.from = state.to = '';
    page = 1;
    fetchList();
  });

  // Pagination clicks
  pager.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;

    const p = parseInt(btn.getAttribute('data-page'), 10);
    if (isNaN(p) || p < 1 || p > pages || p === page) return;

    page = p;
    fetchList();
  });

  // Initial load
  (async function init(){
    await fetchList(true);
  })();
})();
</script>
@endpush
