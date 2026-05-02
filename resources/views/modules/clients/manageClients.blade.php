{{-- resources/views/modules/clients/manageClients.blade.php --}}

@extends('pages.users.admin.layout.structure')
 
@section('title', 'Client Directory')
 
@push('styles')
<style>
* { box-sizing: border-box; }

.clients-page {background: var(--bg-body);min-height: 100vh;padding: 24px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;}
.page-header {margin-bottom: 28px;}
.page-header h1 {font-size: 28px;font-weight: 700;color: var(--text-color);margin: 0 0 6px }
.page-header p {color: #64748b;font-size: 14px;margin: 0;}

/* Toolbar */
.toolbar {display: flex;gap: 12px;margin-bottom: 20px;flex-wrap: wrap;align-items: center;}
.search-box {position: relative;flex: 1;min-width: 280px;max-width: 420px;}
.search-box input {width: 100%;height: 44px;padding: 0 16px 0 42px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface);color: var(--text-color);transition: all 0.2s;}
.search-box input:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
.search-box svg {position: absolute;left: 14px;top: 50%;transform: translateY(-50%);pointer-events: none;}
.filter-group {display: flex;gap: 8px;flex-wrap: wrap;}
.select-box {height: 44px;padding: 0 38px 0 14px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;color: var(--text-color);cursor: pointer;transition: all 0.2s;}
.select-box:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}
.btn {display: inline-flex;align-items: center;gap: 8px;height: 44px;padding: 0 20px;border-radius: 12px;font-size: 14px;font-weight: 600;cursor: pointer;transition: all 0.2s;border: none;text-decoration: none;}
.btn-primary {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);color: #fff;box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);}
.btn-primary:hover {transform: translateY(-1px);box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);}
.btn-secondary {background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;}
.btn-secondary:hover {background: var(--primary-color);border-color: var(--primary-color);}
.btn[disabled] {opacity: 0.65; cursor: not-allowed;}

/* --- Save button loader --- */
.btn-loading { position: relative; }
.btn-loading .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.6); border-top-color:#fff; border-radius:50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Card */
.data-card {background: var(--surface);border-radius: 16px;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);overflow: hidden;}

/* Table */
.table-container {overflow-x: auto;}
table {width: 100%;border-collapse: collapse;color: var(--text-color);}
thead {background: var(--light-color);}
thead th {padding: 14px 18px;text-align: left;font-size: 12px;font-weight: 600;color: #64748b;text-transform: uppercase;letter-spacing: 0.5px;border-bottom: 1px solid #e2e8f0;white-space: nowrap;}
tbody tr {border-bottom: 1px solid #f1f5f9;transition: background 0.15s;background: var(--surface);}
tbody tr:hover {opacity: 0.95;}
tbody tr.child-row {background: var(--light-color);}
tbody td {padding: 16px 18px;font-size: 14px;color: var(--text-color);vertical-align: middle;}
tbody tr.child-row td:nth-child(2) {padding-left: calc(18px + var(--indent, 0px)) !important;}
.cell-id {color: #94a3b8;font-weight: 500;}
.cell-client {display: flex;align-items: center;gap: 12px;}
.avatar {width: 38px;height: 38px;border-radius: 10px;background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);display: flex;align-items: center;justify-content: center;font-weight: 700;font-size: 14px;color: #fff;flex-shrink: 0;}
.avatar img {width: 100%;height: 100%;object-fit: cover;border-radius: 10px;}
.client-info {display: flex;flex-direction: column;gap: 2px;}
.client-name {font-weight: 600;color: var(--text-color);}
.client-type {font-size: 12px;color: #94a3b8;}
.client-parent {font-size: 12px;color: #64748b;}
.contact-link {color: #3b82f6;text-decoration: none;font-size: 13px;}
.contact-link:hover {text-decoration: underline;}
.badge {display: inline-flex;align-items: center;gap: 6px;padding: 6px 12px;border-radius: 8px;font-size: 12px;font-weight: 600;}
.badge::before {content: '';width: 6px;height: 6px;border-radius: 50%;background: currentColor;}
.badge.active {background: #dcfce7;color: #16a34a;}
.badge.pending {background: #fef3c7;color: #d97706;}
.badge.inactive {background: #f1f5f9;color: #64748b;}
.badge.archived {background: #fee2e2;color: #dc2626;}
.actions-cell {display: flex;align-items: center;gap: 10px;}

/* Toggle switch */
.toggle {position: relative;width: 48px;height: 26px;border-radius: 13px;background: #cbd5e1;cursor: pointer;transition: background 0.2s;}
.toggle input {display: none;}
.toggle-slider {position: absolute;top: 3px;left: 3px;width: 20px;height: 20px;border-radius: 50%;background: #fff;transition: transform 0.2s;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);}
.toggle input:checked + .toggle-slider {transform: translateX(22px);}
.toggle.active {background: #10b981;} /* rely on .active class only for instant color */

.btn-edit {height: 34px;padding: 0 14px;background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;border-radius: 8px;font-size: 13px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.btn-edit:hover {background: var(--primary-color);border-color: var(--primary-color);color: var(--text-color);}
.expander-wrap {position: relative;display: inline-flex;align-items: center;gap: 8px;flex-shrink: 0;}
.expander {width: 34px;height: 34px;border: 1px solid #e2e8f0;border-radius: 8px;background: var(--surface);display: inline-flex;align-items: center;justify-content: center;cursor: pointer;transition: all 0.2s;}
.expander:hover {background: var(--primary-color);border-color: var(--primary-color);color: #fff;}
.expander-child-badge {background: #ef4444;color: #fff;border-radius: 999px;padding: 2px 6px;font-size: 11px;line-height: 1;min-width: 18px;text-align: center;font-weight: 700;margin-left: -10px;transform: translateX(-6px) translateY(-10px);pointer-events: none;}
.expander-placeholder {width: 34px;height: 34px;flex-shrink: 0;}

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
.form-select {height: 44px;padding: 0 38px 0 14px;background: #fff url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance: none;}
.modal-footer {padding: 20px 28px;border-top: 1px solid #f1f5f9;display: flex;justify-content: flex-end;gap: 10px;}
.tree-current {display:flex;align-items:center;gap:8px;margin-top:8px;font-size:12px;color:#64748b}
.tree-badge {display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--bg-body);border:1px solid #e2e8f0;color:var(--text-color);font-weight:600}
.tree-list {list-style:none;margin:0;padding:0 0 0 8px;position:relative}
.tree-list::before {content:"";position:absolute;left:14px;top:0;bottom:8px;width:1px;background:#e2e8f0}
.tree-list>li {position:relative;margin:0 0 8px 0;padding-left:24px}
.tree-list>li::before {content:"";position:absolute;left:14px;top:16px;width:16px;height:1px;background:#e2e8f0}
.tree-item {display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid #e2e8f0;border-radius:12px;background:var(--surface)}
.tree-toggle {width:28px;height:28px;border:1px solid #e2e8f0;border-radius:8px;background:var(--bg-body);display:inline-flex;align-items:center;justify-content:center}
.tree-toggle.open i {transform:rotate(90deg)}
.tree-children {margin:8px 0 10px 0;padding-left:24px;display:none}
.tree-children .tree-children {margin-left:16px}
.tree-title {display:flex;flex-direction:column;gap:2px}
.tree-title small {color:#64748b}

@media (max-width: 768px) {
  .toolbar {flex-direction: column;}
  .search-box {max-width: 100%;}
  .filter-group {width: 100%;justify-content: space-between;}
}

/* hide the ID column (first column) */
table thead th:first-child,
table tbody td:first-child {
  display: none;
}
 
 
</style>
@endpush
 
@section('content')
<div class="clients-page">
  <div class="page-header">
    <h1>Client Directory</h1>
    <p>Review your client list, open details, and keep account records organized.</p>
  </div>
 
  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search clients...">
    </div>
 
    <div class="filter-group">
      <select id="orgTypeSelect" class="select-box">
        <option value="">All Types</option>
        <option value="company">Company</option>
        <option value="hospital">Hospital</option>
        <option value="clinic">Clinic</option>
        <option value="ngo">NGO</option>
        <option value="individual">Individual</option>
        <option value="other">Other</option>
      </select>
 
      <select id="statusSelect" class="select-box">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="inactive">Inactive</option>
        <option value="archived">Archived</option>
      </select>
 
      <select id="sortSelect" class="select-box">
        <option value="desc">Newest First</option>
        <option value="asc">Oldest First</option>
      </select>
    </div>
 
    <a href="{{ url('admin/client/add') }}" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      Add Client
    </a>
 
    <button id="exportBtn" class="btn btn-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5-5 5 5M12 5v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Export
    </button>
  </div>
 
  <!-- Data Card -->
  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Contact</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>

        <!-- In the table section -->
        <tbody id="tableBody">
          <tr><td style="color:var(--muted-color);" colspan="6" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
 
    <div class="pagination">
      <div class="pagination-info" id="paginationInfo">
        Showing 1-10 of 100 clients
      </div>
      <div class="pagination-controls" id="paginationControls">
        <!-- Pagination buttons injected by JS -->
      </div>
    </div>
  </div>
</div>
 
<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-regular fa-pen-to-square me-2"></i>Edit Client Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
 
      <form id="editClientForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="ec_slug">
          <input type="hidden" id="ec_id">
 
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Client Name</label>
              <input type="text" class="form-control" id="ec_name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Organization Type</label>
              <select class="form-control form-select" id="ec_org_type">
                <option value="">—</option>
                <option value="company">Company</option>
                <option value="hospital">Hospital</option>
                <option value="clinic">Clinic</option>
                <option value="ngo">NGO</option>
                <option value="individual">Individual</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label">Parent Client</label>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" id="btnEditPickParentClient">
                  <i class="fa-solid fa-sitemap me-1"></i>Choose Parent
                </button>
                <button type="button" class="btn btn-secondary" id="btnEditClearParentClient" title="Clear parent">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <input type="hidden" id="ec_parent_id">
              <div class="tree-current">
                <span>Current:</span>
                <span class="tree-badge" id="ec_parent_current">Self (Root)</span>
              </div>
            </div>
 
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="ec_email">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" id="ec_phone">
            </div>
 
            <div class="col-md-6">
              <label class="form-label">City</label>
              <input type="text" class="form-control" id="ec_city">
            </div>
            <div class="col-md-3">
              <label class="form-label">State</label>
              <input type="text" class="form-control" id="ec_state">
            </div>
            <div class="col-md-3">
              <label class="form-label">Country</label>
              <input type="text" class="form-control" id="ec_country" maxlength="2">
            </div>
 
            <div class="col-md-12">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" id="ec_address">
            </div>
 
            <div class="col-md-6">
              <label class="form-label">Website URL</label>
              <input type="url" class="form-control" id="ec_website_url">
            </div>
            <div class="col-md-6">
              <label class="form-label">Timezone</label>
              <input type="text" class="form-control" id="ec_timezone">
            </div>
 
            <div class="col-md-4">
              <label class="form-label">Contact Name</label>
              <input type="text" class="form-control" id="ec_contact_name">
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact Email</label>
              <input type="email" class="form-control" id="ec_contact_email">
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact Phone</label>
              <input type="text" class="form-control" id="ec_contact_phone">
            </div>
 
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select class="form-control form-select" id="ec_status">
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
        </div>
 
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="saveChangesBtn">
            <i class="fa fa-save me-1"></i><span class="btn-text">Save Changes</span>
            <span class="spinner" style="display:none;"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editParentClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-sitemap me-2"></i>Choose Parent Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="editParentClientLoading" class="text-muted small mb-3" style="display:none;">Loading clients…</div>
        <ul id="editParentClientTree" class="tree-list"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSaveEditParentClient">Use Selection</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
  const API_BASE = @json(url('/api'));
  const PER_PAGE = 10;
  const LOGIN_URL = @json(url('/admin/login'));

  // ---------- Token helpers ----------
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headers = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };
  
  if(!TOKEN){ 
    Swal.fire('Auth Required','Session expired. Please login again.','warning')
      .then(()=>location.href='/'); 
    return; 
  }

  let state = {
    page: 1,
    total_pages: 1,
    total: 0,
    q: '',
    status: '',
    orgType: '',
    sort: 'desc',
    items: []
  };
  const expanded = new Set();

  const els = {
    tbody: document.getElementById('tableBody'),
    paginationInfo: document.getElementById('paginationInfo'),
    paginationControls: document.getElementById('paginationControls'),
    searchInput: document.getElementById('searchInput'),
    statusSelect: document.getElementById('statusSelect'),
    orgTypeSelect: document.getElementById('orgTypeSelect'),
    sortSelect: document.getElementById('sortSelect'),
    exportBtn: document.getElementById('exportBtn')
  };

  const modalEl = document.getElementById('editClientModal');
  const modal = window.bootstrap ? new bootstrap.Modal(modalEl) : null;
  const editParentClientModalEl = document.getElementById('editParentClientModal');
  const editParentClientModal = window.bootstrap ? new bootstrap.Modal(editParentClientModalEl) : null;
  const editParentTree = document.getElementById('editParentClientTree');
  const editParentLoading = document.getElementById('editParentClientLoading');
  let clientTreeRows = [];
  let selectedEditParent = null;
  const f = {
    form: document.getElementById('editClientForm'),
    slug: document.getElementById('ec_slug'),
    id: document.getElementById('ec_id'),
    parent_id: document.getElementById('ec_parent_id'),
    parent_current: document.getElementById('ec_parent_current'),
    btnPickParent: document.getElementById('btnEditPickParentClient'),
    btnClearParent: document.getElementById('btnEditClearParentClient'),
    name: document.getElementById('ec_name'),
    org_type: document.getElementById('ec_org_type'),
    email: document.getElementById('ec_email'),
    phone: document.getElementById('ec_phone'),
    city: document.getElementById('ec_city'),
    state: document.getElementById('ec_state'),
    country: document.getElementById('ec_country'),
    address: document.getElementById('ec_address'),
    website_url: document.getElementById('ec_website_url'),
    timezone: document.getElementById('ec_timezone'),
    contact_name: document.getElementById('ec_contact_name'),
    contact_email: document.getElementById('ec_contact_email'),
    contact_phone: document.getElementById('ec_contact_phone'),
    status: document.getElementById('ec_status'),
    saveBtn: document.getElementById('saveChangesBtn')
  };

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function orgLabel(v) {
    const map = {
      company: 'Company', hospital: 'Hospital', clinic: 'Clinic',
      ngo: 'NGO', individual: 'Individual', other: 'Other'
    };
    return map[String(v || '').toLowerCase()] || v || '';
  }

  function syncEditParentLabel(id) {
    const match = clientTreeRows.find(row => String(row.id) === String(id || ''));
    f.parent_current.textContent = match ? `${match.name || `Client #${match.id}`}` : 'Self (Root)';
  }

  function buildTree(rows) {
    const map = new Map();
    rows.forEach(row => {
      map.set(String(row.id), {
        id: row.id,
        name: String(row.name || `Client #${row.id}`),
        parent_id: row.parent_id || null,
        children: []
      });
    });
    const roots = [];
    rows.forEach(row => {
      const node = map.get(String(row.id));
      if (row.parent_id && map.has(String(row.parent_id))) {
        map.get(String(row.parent_id)).children.push(node);
      } else {
        roots.push(node);
      }
    });
    const sortRec = (arr) => {
      arr.sort((a, b) => a.name.localeCompare(b.name));
      arr.forEach(node => sortRec(node.children));
    };
    sortRec(roots);
    return roots;
  }

  function descendantIdSet(rootId) {
    const childrenByParent = new Map();
    clientTreeRows.forEach(row => {
      const pid = row.parent_id ? String(row.parent_id) : '';
      if (!childrenByParent.has(pid)) childrenByParent.set(pid, []);
      childrenByParent.get(pid).push(String(row.id));
    });
    const blocked = new Set([String(rootId)]);
    const queue = [String(rootId)];
    while (queue.length) {
      const current = queue.shift();
      const kids = childrenByParent.get(current) || [];
      kids.forEach(childId => {
        if (!blocked.has(childId)) {
          blocked.add(childId);
          queue.push(childId);
        }
      });
    }
    return blocked;
  }

  function renderTreeNode(node, blockedIds) {
    if (blockedIds.has(String(node.id))) return null;

    const li = document.createElement('li');
    const item = document.createElement('div');
    item.className = 'tree-item';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'tree-toggle';
    toggle.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    if (!node.children.length) toggle.style.visibility = 'hidden';

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'editParentClientPick';
    radio.value = String(node.id);
    if (String(f.parent_id.value || '') === String(node.id)) radio.checked = true;

    const title = document.createElement('div');
    title.className = 'tree-title';
    title.innerHTML = `<strong>${esc(node.name)}</strong><small>#${esc(node.id)}</small>`;

    item.appendChild(toggle);
    item.appendChild(radio);
    item.appendChild(title);
    li.appendChild(item);

    const children = document.createElement('ul');
    children.className = 'tree-children tree-list';
    li.appendChild(children);

    if (node.children.length) {
      node.children.forEach(child => {
        const rendered = renderTreeNode(child, blockedIds);
        if (rendered) children.appendChild(rendered);
      });
      if (!children.childElementCount) {
        toggle.style.visibility = 'hidden';
      } else {
        toggle.addEventListener('click', () => {
          const open = children.style.display === 'block';
          children.style.display = open ? 'none' : 'block';
          toggle.classList.toggle('open', !open);
        });
      }
    }

    radio.addEventListener('change', () => {
      selectedEditParent = { id: node.id, name: node.name };
    });

    return li;
  }

  function renderEditParentTree() {
    editParentTree.innerHTML = '';
    const blockedIds = f.id.value ? descendantIdSet(f.id.value) : new Set();

    const rootLi = document.createElement('li');
    const rootItem = document.createElement('div');
    rootItem.className = 'tree-item';

    const fakeToggle = document.createElement('button');
    fakeToggle.type = 'button';
    fakeToggle.className = 'tree-toggle';
    fakeToggle.style.visibility = 'hidden';
    fakeToggle.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

    const rootRadio = document.createElement('input');
    rootRadio.type = 'radio';
    rootRadio.name = 'editParentClientPick';
    rootRadio.value = 'self';
    if (!f.parent_id.value) rootRadio.checked = true;

    const rootTitle = document.createElement('div');
    rootTitle.className = 'tree-title';
    rootTitle.innerHTML = '<strong>Self (Root)</strong><small>No parent client</small>';

    rootItem.appendChild(fakeToggle);
    rootItem.appendChild(rootRadio);
    rootItem.appendChild(rootTitle);
    rootLi.appendChild(rootItem);
    editParentTree.appendChild(rootLi);

    rootRadio.addEventListener('change', () => {
      selectedEditParent = null;
    });

    buildTree(clientTreeRows).forEach(node => {
      const rendered = renderTreeNode(node, blockedIds);
      if (rendered) editParentTree.appendChild(rendered);
    });
  }

  async function ensureClientTreeRows() {
    if (clientTreeRows.length) return;
    editParentLoading.style.display = 'block';
    try {
      const res = await fetch(`${API_BASE}/clients/all?sort=asc`, { headers });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || 'Failed to load clients');
      clientTreeRows = Array.isArray(data?.data) ? data.data : [];
    } finally {
      editParentLoading.style.display = 'none';
    }
  }

  function statusLabel(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'Active';
    if (k === 'pending') return 'Pending';
    if (k === 'archived') return 'Archived';
    return 'Inactive';
  }

  function statusClass(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'active';
    if (k === 'pending') return 'pending';
    if (k === 'archived') return 'archived';
    return 'inactive';
  }

  function filteredItems() {
    return state.orgType
      ? state.items.filter(it => (it.org_type || '').toLowerCase() === state.orgType.toLowerCase())
      : state.items.slice();
  }

  function buildHierarchy(rows) {
    const map = new Map();
    const childrenByParent = new Map();
    rows.forEach(row => {
      const clone = { ...row };
      map.set(String(clone.id), clone);
      const pid = clone.parent_id ? String(clone.parent_id) : '';
      if (!childrenByParent.has(pid)) childrenByParent.set(pid, []);
      childrenByParent.get(pid).push(clone);
    });

    for (const kids of childrenByParent.values()) {
      kids.sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
    }

    const roots = rows
      .filter(row => !row.parent_id || !map.has(String(row.parent_id)))
      .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));

    return { childrenByParent, roots };
  }

  function descendantCount(id, childrenByParent) {
    let count = 0;
    const queue = [String(id)];
    while (queue.length) {
      const current = queue.shift();
      const kids = childrenByParent.get(current) || [];
      count += kids.length;
      kids.forEach(child => queue.push(String(child.id)));
    }
    return count;
  }

  function collectVisibleRows(root, childrenByParent, level = 0) {
    const rows = [{ row: root, level }];
    if (!expanded.has(String(root.id))) return rows;
    const kids = childrenByParent.get(String(root.id)) || [];
    kids.forEach(child => rows.push(...collectVisibleRows(child, childrenByParent, level + 1)));
    return rows;
  }

  async function fetchClients() {
    const params = new URLSearchParams({
      sort: state.sort || 'desc'
    });
    if (state.q) params.set('q', state.q);
    if (state.status) params.set('status', state.status);
    if (state.orgType) params.set('org_type', state.orgType);

    try {
      const res = await fetch(`${API_BASE}/clients/all?${params}`, {headers});

      if (res.status === 401 || res.status === 403) {
        const data = await res.json().catch(() => ({}));
        await Swal.fire({
          icon: 'error',
          title: 'Unauthorized',
          html: (data.message || data.error || 'Access denied') +
                '<br><small>Make sure your Bearer token is the <b>plaintext</b> admin token.</small>'
        });
        return;
      }

      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || 'Request failed');

      state.items = Array.isArray(data?.data) ? data.data : [];
      render();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Unable to fetch clients', text: String(err.message) });
    }
  }

  function render() {
    const filtered = filteredItems();
    const { childrenByParent, roots } = buildHierarchy(filtered);
    state.total = roots.length;
    state.total_pages = Math.max(1, Math.ceil(Math.max(roots.length, 1) / PER_PAGE));
    if (state.page > state.total_pages) state.page = state.total_pages;
    const start = (state.page - 1) * PER_PAGE;
    const pagedRoots = roots.slice(start, start + PER_PAGE);
    const visibleRows = pagedRoots.flatMap(root => collectVisibleRows(root, childrenByParent, 0));

    if (!roots.length) {
      els.tbody.innerHTML = `
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <path d="M9 11H15M9 15H12M3 6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <h3>No clients found</h3>
              <p>Try adjusting your filters or search query</p>
            </div>
          </td>
        </tr>
      `;
    } else {
      els.tbody.innerHTML = visibleRows.map(({ row, level }) => rowHtml(row, level, childrenByParent)).join('');
    }

    renderPagination();
  }

  function rowHtml(c, level = 0, childrenByParent = new Map()) {
    const slug = c.slug ?? '';
    const id = c.id ?? '';
    const name = c.name ?? '';
    const org = c.org_type ?? '';
    const email = c.email ?? '';
    const phone = c.phone ?? '';
    const city = c.city ?? '';
    const state = c.state ?? '';
    const country = c.country ?? '';
    const status = String(c.status || 'active').toLowerCase();

    const avatar = (c.image_full_url || c.image_url)
      ? `<div class="avatar"><img src="${esc(c.image_full_url || c.image_url)}" alt=""></div>`
      : `<div class="avatar">${(name || '?')[0].toUpperCase()}</div>`;

    const location = [city, state, country].filter(Boolean).join(', ') || '—';
    const childCount = descendantCount(id, childrenByParent);
    const hasChildren = childCount > 0;
    const expander = hasChildren
      ? `<div class="expander-wrap">
          <button class="expander js-expand" data-id="${esc(id)}" aria-expanded="${expanded.has(String(id)) ? 'true' : 'false'}" title="Expand/collapse linked clients">
            <i class="fa ${expanded.has(String(id)) ? 'fa-caret-down' : 'fa-caret-right'}"></i>
          </button>
          <span class="expander-child-badge" title="${childCount} linked client${childCount > 1 ? 's' : ''}">${childCount > 9 ? '9+' : childCount}</span>
        </div>`
      : `<span class="expander-placeholder"></span>`;

    return `
      <tr data-slug="${esc(slug)}" data-id="${esc(id)}" class="${level > 0 ? 'child-row' : ''}" style="--indent:${level * 18}px">
        <td class="cell-id">#${esc(id)}</td>
        <td>
          <div class="cell-client">
            ${expander}
            ${avatar}
            <div class="client-info">
              <div class="client-name">${esc(name)}</div>
              <div class="client-type">${esc(orgLabel(org))}</div>
              ${c.parent_name ? `<div class="client-parent">Child of ${esc(c.parent_name)}</div>` : ''}
            </div>
          </div>
        </td>
        <td>
          ${email ? `<a href="mailto:${esc(email)}" class="contact-link">${esc(email)}</a><br>` : ''}
          ${phone ? `<a href="tel:${esc(phone)}" class="contact-link">${esc(phone)}</a>` : ''}
          ${!email && !phone ? '—' : ''}
        </td>
        <td>${esc(location)}</td>
        <td>
          <span class="badge ${statusClass(status)}">${statusLabel(status)}</span>
        </td>
        <td>
          <div class="actions-cell">
            <label class="toggle ${status === 'active' ? 'active' : ''}" title="Toggle status">
              <input type="checkbox" class="status-toggle" data-slug="${esc(slug)}"
                ${status === 'active' ? 'checked' : ''}
                ${status === 'archived' ? 'disabled' : ''}>
              <span class="toggle-slider"></span>
            </label>
            <button class="btn-edit" data-action="edit-client" data-slug="${esc(slug)}">Edit</button>
          </div>
        </td>
      </tr>
    `;
  }

  function renderPagination() {
    const start = state.total ? ((state.page - 1) * PER_PAGE + 1) : 0;
    const end = Math.min(state.page * PER_PAGE, state.total);
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} root clients`;

    const pages = state.total_pages || 1;
    const cur = state.page;
    const windowSize = 5;
    let start_page = Math.max(1, cur - Math.floor(windowSize / 2));
    let end_page = Math.min(pages, start_page + windowSize - 1);
    if (end_page - start_page + 1 < windowSize) {
      start_page = Math.max(1, end_page - windowSize + 1);
    }

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${cur - 1}" ${cur <= 1 ? 'disabled' : ''}>Previous</button>`);
   
    for (let i = start_page; i <= end_page; i++) {
      buttons.push(`<button class="page-btn ${i === cur ? 'active' : ''}" data-page="${i}">${i}</button>`);
    }
   
    buttons.push(`<button class="page-btn" data-page="${cur + 1}" ${cur >= pages ? 'disabled' : ''}>Next</button>`);
   
    els.paginationControls.innerHTML = buttons.join('');
  }

  els.tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-expand');
    if (!btn) return;
    e.preventDefault();
    const id = String(btn.getAttribute('data-id') || '');
    if (!id) return;
    if (expanded.has(id)) expanded.delete(id); else expanded.add(id);
    render();
  });

  // --------- Optimistic Status Toggle (instant color change) ---------
  els.tbody.addEventListener('change', async (e) => {
    const t = e.target;
    if (!t.classList.contains('status-toggle')) return;
    const slug = t.getAttribute('data-slug');
    if (!slug) return;

    const tr = t.closest('tr');
    const toggleLabel = t.closest('.toggle');
    const badge = tr ? tr.querySelector('.badge') : null;

    // Snapshot previous UI to revert on failure
    const prevChecked = !t.checked ? false : true; // state *after* change
    const prevWasChecked = !prevChecked;          // before change
    const previousBadgeClass = badge ? badge.className : '';
    const previousBadgeText = badge ? badge.textContent : '';
    const previousToggleHadActive = toggleLabel ? toggleLabel.classList.contains('active') : false;

    // ---- Instant UI feedback ----
    // Toggle background color immediately
    if (toggleLabel) toggleLabel.classList.toggle('active', t.checked);
    // Update badge immediately
    if (badge) {
      if (t.checked) {
        badge.className = 'badge active';
        badge.textContent = 'Active';
      } else {
        badge.className = 'badge inactive';
        badge.textContent = 'Inactive';
      }
    }

    t.disabled = true; // prevent double toggles while saving

    // Call API
    try {
      const res = await fetch(`${API_BASE}/clients/${encodeURIComponent(slug)}/toggle`, {
        method: 'PATCH',
        headers
      });
      const data = await res.json();
      if (!res.ok || data?.status !== 'success') {
        throw new Error(data?.message || 'Toggle failed');
      }

      // Optionally refresh list to reflect server canonical state
      await fetchClients();

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Status updated',
        showConfirmButton: false,
        timer: 1500
      });
    } catch (err) {
      // Revert UI on error
      t.checked = prevWasChecked;
      if (toggleLabel) toggleLabel.classList.toggle('active', prevWasChecked);
      if (badge) {
        badge.className = previousBadgeClass;
        badge.textContent = previousBadgeText;
      }

      Swal.fire({ icon: 'error', title: 'Could not toggle status', text: String(err.message) });
    } finally {
      t.disabled = false;
    }
  });

  // Edit client (open modal)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action="edit-client"]');
    if (!btn) return;

    if (!modal) return;
    e.preventDefault();

    const slug = btn.getAttribute('data-slug');
    if (!slug) return;

    const row = state.items.find(it => String(it.slug || '').toLowerCase() === String(slug).toLowerCase());
    let data = row || null;

    if (!data) {
      try {
        const res = await fetch(`${API_BASE}/clients/${encodeURIComponent(slug)}`, {headers});
        const json = await res.json();
        if (!res.ok) throw new Error(json?.message || 'Request failed');
        data = json?.data || null;
      } catch (err) {
        Swal.fire({ icon: 'error', title: 'Unable to load client', text: String(err.message) });
        return;
      }
    }

    f.slug.value = data.slug ?? '';
    f.id.value = data.id ?? '';
    f.parent_id.value = data.parent_id ? String(data.parent_id) : '';
    f.parent_current.textContent = data.parent_name ? String(data.parent_name) : 'Self (Root)';
    f.name.value = data.name ?? '';
    f.org_type.value = (data.org_type ?? '').toLowerCase();
    f.email.value = data.email ?? '';
    f.phone.value = data.phone ?? '';
    f.city.value = data.city ?? '';
    f.state.value = data.state ?? '';
    f.country.value = data.country ?? '';
    f.address.value = data.address ?? '';
    f.website_url.value = data.website_url ?? '';
    f.timezone.value = data.timezone ?? '';
    f.contact_name.value = data.contact_name ?? '';
    f.contact_email.value = data.contact_email ?? '';
    f.contact_phone.value = data.contact_phone ?? '';
    f.status.value = (data.status ?? 'active').toLowerCase();

    modal.show();
  });

  f.btnPickParent?.addEventListener('click', async () => {
    try {
      selectedEditParent = f.parent_id.value
        ? { id: f.parent_id.value, name: f.parent_current.textContent }
        : null;
      await ensureClientTreeRows();
      renderEditParentTree();
      editParentClientModal?.show();
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Unable to load clients', text: String(err.message || err) });
    }
  });

  f.btnClearParent?.addEventListener('click', () => {
    f.parent_id.value = '';
    syncEditParentLabel('');
  });

  document.getElementById('btnSaveEditParentClient')?.addEventListener('click', () => {
    if (selectedEditParent && selectedEditParent.id !== 'self') {
      f.parent_id.value = String(selectedEditParent.id);
    } else {
      f.parent_id.value = '';
    }
    syncEditParentLabel(f.parent_id.value);
    editParentClientModal?.hide();
  });

  // --------- Save client with loader on button ---------
  f.form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const slug = f.slug.value?.trim();
    if (!slug) return;

    // Activate loader on Save button
    const btn = f.saveBtn;
    const spinner = btn.querySelector('.spinner');
    const btnText = btn.querySelector('.btn-text');
    btn.classList.add('btn-loading');
    btn.setAttribute('aria-busy', 'true');
    btn.disabled = true;
    if (spinner) spinner.style.display = 'inline-block';

    // (Optional) lock the fields while saving
    const controls = f.form.querySelectorAll('input,select,textarea,button');
    controls.forEach(el => {
      if (el !== btn) el.setAttribute('disabled', 'disabled');
    });

    const payload = {
      name: f.name.value?.trim(),
      parent_id: f.parent_id.value ? parseInt(f.parent_id.value, 10) : null,
      org_type: f.org_type.value || null,
      email: f.email.value?.trim() || null,
      phone: f.phone.value?.trim() || null,
      city: f.city.value?.trim() || null,
      state: f.state.value?.trim() || null,
      country: f.country.value?.trim() || null,
      address: f.address.value?.trim() || null,
      website_url: f.website_url.value?.trim() || null,
      timezone: f.timezone.value?.trim() || null,
      contact_name: f.contact_name.value?.trim() || null,
      contact_email: f.contact_email.value?.trim() || null,
      contact_phone: f.contact_phone.value?.trim() || null,
      status: f.status.value || 'active'
    };

    try {
      const res = await fetch(`${API_BASE}/clients/${encodeURIComponent(slug)}`, {
        method: 'PUT',
        headers: { 'Content-Type':'application/json', ...headers },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (!res.ok || json?.status !== 'success') {
        const msg = json?.message || 'Update failed';
        const errs = json?.errors ? Object.entries(json.errors)
          .map(([k, v]) => `• ${k}: ${[].concat(v).join(', ')}`).join('\n') : '';
        throw new Error(errs ? `${msg}\n\n${errs}` : msg);
      }

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Client updated',
        showConfirmButton: false,
        timer: 1500
      });
      modal.hide();
      await fetchClients();
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Could not save changes', text: String(err.message) });
    } finally {
      // Turn off loader and re-enable controls
      btn.classList.remove('btn-loading');
      btn.removeAttribute('aria-busy');
      btn.disabled = false;
      if (spinner) spinner.style.display = 'none';
      controls.forEach(el => {
        if (el !== btn) el.removeAttribute('disabled');
      });
    }
  });

  // Export
  els.exportBtn.addEventListener('click', () => {
    const rows = Array.from(els.tbody.querySelectorAll('tr'));
    if (!rows.length || rows[0].querySelector('.empty-state')) {
      Swal.fire({ icon: 'info', title: 'Nothing to export', text: 'No rows in the current view.' });
      return;
    }

    const headers = ['ID', 'Client Name', 'Org Type', 'Email', 'Phone', 'City', 'State', 'Country', 'Status'];
    const data = [headers];

    filteredItems().forEach(c => {
      data.push([
        c.id ?? '',
        c.name ?? '',
        orgLabel(c.org_type),
        c.email ?? '',
        c.phone ?? '',
        c.city ?? '',
        c.state ?? '',
        c.country ?? '',
        statusLabel(c.status)
      ]);
    });

    const csv = data.map(r => r.map(csvEscape).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `clients_export_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });

  function csvEscape(v) {
    const s = String(v ?? '');
    if (/[",\n]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
    return s;
  }

  // Filters
  let searchDebounce;
  els.searchInput.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      state.q = els.searchInput.value.trim();
      state.page = 1;
      fetchClients();
    }, 300);
  });

  els.statusSelect.addEventListener('change', () => {
    state.status = els.statusSelect.value;
    state.page = 1;
    fetchClients();
  });

  els.orgTypeSelect.addEventListener('change', () => {
    state.orgType = els.orgTypeSelect.value;
    state.page = 1;
    fetchClients();
  });

  els.sortSelect.addEventListener('change', () => {
    state.sort = els.sortSelect.value || 'desc';
    state.page = 1;
    fetchClients();
  });

  // Pagination clicks
  els.paginationControls.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;

    const p = parseInt(btn.getAttribute('data-page'), 10);
    if (isNaN(p) || p < 1 || p > state.total_pages || p === state.page) return;

    state.page = p;
    fetchClients();
  });

  // Initial fetch
  fetchClients();
})();
</script>
@endpush
