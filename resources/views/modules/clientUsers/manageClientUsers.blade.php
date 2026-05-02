@push('styles')
<style>
* { box-sizing: border-box; }

.clientusers-page {background: var(--bg-body);min-height: 100vh;padding: 24px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;}
.page-header {margin-bottom: 28px;}
.page-header h1 {font-size: 28px;font-weight: 700;color: var(--text-color);margin: 0 0 6px;}
.page-header p {color: #64748b;font-size: 14px;margin: 0;}
.toolbar {display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;}
.search-box {position: relative;flex: 1;min-width: 280px;max-width: 420px;}
.search-box input {width: 100%;height: 44px;padding: 0 16px 0 42px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface);color: var(--text-color);}
.search-box svg {position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;}
.btn {display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 20px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;border:none;text-decoration:none;}
.btn-primary {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);color:#fff;box-shadow:0 2px 8px rgba(59,130,246,.25);}
.btn-secondary {background: var(--surface);color: var(--text-color);border:1px solid #e2e8f0;}
.select-box {height: 44px;padding: 0 38px 0 14px;border: 1px solid #e2e8f0;border-radius: 12px;font-size: 14px;background: var(--surface);color: var(--text-color);}
.data-card {background:var(--surface);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;}
.table-container {overflow-x:auto;}
table {width:100%;border-collapse:collapse;color:var(--text-color);}
thead {background: var(--light-color);}
thead th {padding: 14px 18px;text-align:left;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
tbody tr {border-bottom:1px solid #f1f5f9;background:var(--surface);}
tbody td {padding:16px 18px;font-size:14px;color:var(--text-color);vertical-align:middle;}
.badge {display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;}
.badge::before {content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
.badge.active {background:#dcfce7;color:#16a34a;}
.badge.inactive {background:#f1f5f9;color:#64748b;}
.badge.archived {background:#fee2e2;color:#dc2626;}
.actions-cell {display:flex;align-items:center;gap:10px;}
.btn-icon {display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:1px solid #e2e8f0;border-radius:8px;background:var(--surface);color:var(--text-color);cursor:pointer;padding:0;}
.btn-edit:hover {background:#3b82f6;border-color:#3b82f6;color:#fff;}
.btn-delete:hover {background:#dc2626;border-color:#dc2626;color:#fff;}
.switch {position:relative;width:48px;height:26px;border-radius:13px;background:#cbd5e1;cursor:pointer;transition:background .2s;}
.switch input {display:none;}
.switch .slider {position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;transition:all .2s;box-shadow:0 2px 4px rgba(0,0,0,.15);}
.switch input:checked + .slider {transform: translateX(22px);}
.switch.active {background:#10b981;}
.pagination {display:flex;align-items:center;justify-content:space-between;padding:18px 20px;background:var(--light-color);border-top:1px solid #f1f5f9;}
.pagination-controls {display:flex;gap:6px;}
.page-btn {min-width:38px;height:38px;padding:0 12px;border:1px solid #e2e8f0;border-radius:8px;background:var(--surface);color:var(--text-color);font-size:14px;font-weight:600;cursor:pointer;}
.page-btn.active {background:var(--primary-color);color:#fff;border-color:var(--primary-color);}
.page-btn:disabled {opacity:.4;cursor:not-allowed;}
.empty-state {text-align:center;padding:60px 20px;color:#94a3b8;}
.empty-state h3 {font-size:18px;font-weight:600;color:#475569;margin:0 0 8px;}
.modal-content {border-radius:16px;border:none;box-shadow:0 20px 40px rgba(0,0,0,.15);}
.modal-header {padding:24px 28px;border-bottom:1px solid #f1f5f9;background:var(--surface);}
.modal-body {padding:28px;}
.modal-footer {padding:20px 28px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;}
.form-group {margin-bottom:20px;}
.form-label {display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:8px;}
.form-control, .form-select {width:100%;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;color:#0f172a;background:#fff;}
textarea.form-control {height:auto;padding:10px 14px;resize:vertical;}
.role-other-wrap { margin-top: 10px; }
.password-input-wrapper {position:relative;display:flex;gap:8px;}
.password-input-wrapper .form-control {flex:1;}
.btn-generate {height:44px;padding:0 16px;background:var(--surface);color:var(--text-color);border:1px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;}
.switch-label {display:flex;align-items:center;gap:12px;cursor:pointer;user-select:none;}
.switch-input {display:none;}
.switch-slider {position:relative;width:48px;height:26px;border-radius:13px;background:#cbd5e1;transition:background .2s;}
.switch-slider::after {content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;transition:all .2s;box-shadow:0 2px 4px rgba(0,0,0,.15);}
.switch-input:checked + .switch-slider {background:#10b981;}
.switch-input:checked + .switch-slider::after {transform:translateX(22px);}
.selected-clients {display:flex;flex-wrap:wrap;gap:8px;min-height:44px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;}
.selected-chip {display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;}
.selected-empty {font-size:13px;color:#94a3b8;align-self:center;}
.picker-tree, .picker-tree ul {list-style:none;margin:0;padding-left:0;}
.picker-tree ul {padding-left:20px;margin-top:8px;display:none;}
.picker-item {display:flex;align-items:flex-start;gap:10px;padding:8px 10px;border-radius:10px;}
.picker-item:hover {background:#f8fafc;}
.picker-toggle {border:none;background:transparent;color:#64748b;width:20px;height:20px;padding:0;display:inline-flex;align-items:center;justify-content:center;}
.picker-toggle.open i {transform:rotate(90deg);}
.picker-title {display:flex;flex-direction:column;gap:2px;}
.picker-title strong {font-size:14px;color:#0f172a;}
.picker-title small {font-size:12px;color:#94a3b8;}
.btn.is-loading,.btn[aria-busy="true"]{pointer-events:none;opacity:.85;position:relative;}
.btn.is-loading .btn-label{visibility:hidden;}
.btn.is-loading::after{content:"";position:absolute;inset:0;margin:auto;width:18px;height:18px;border-radius:50%;border:2.5px solid rgba(255,255,255,.65);border-top-color:rgba(255,255,255,0);animation:spin .7s linear infinite;}
.clients-cell {display:flex;flex-wrap:wrap;gap:6px;max-width:320px;}
.client-pill {display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12px;color:#475569;}
.muted-small {font-size:12px;color:#94a3b8;}
@keyframes spin{to{transform:rotate(360deg);}}
</style>
@endpush

@section('content')
<div class="clientusers-page">
  <div class="page-header">
    <h1>Client Contacts</h1>
    <p>Create client-scoped contacts who can log in and view jobs for selected client trees.</p>
  </div>

  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search client contacts by name, email, role, or contact...">
    </div>

    <select id="statusFilter" class="select-box">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
      <option value="archived">Archived</option>
    </select>

    <button id="addClientUserBtn" class="btn btn-primary">
      <i class="fa-solid fa-user-plus"></i>
      New Client Contact
    </button>

    <button id="exportBtn" class="btn btn-secondary">
      <i class="fa-solid fa-file-export"></i>
      Export
    </button>
  </div>

  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Clients</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="rows">
          <tr><td colspan="7" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div id="paginationInfo">Showing 0-0 of 0 client contacts</div>
      <div class="pagination-controls" id="pager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="clientUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">New Client Contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="clientUserForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="client_user_id" name="id">

          <div class="row g-3">
            <div class="col-md-6 form-group">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="client_user_name" name="name" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="client_user_email" name="email" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="form-label">Role</label>
              <select class="form-select" id="client_user_role" name="role">
                <option value="">Select role</option>
                <option value="Viewer">Viewer</option>
                <option value="Manager">Manager</option>
                <option value="Finance">Finance</option>
                <option value="Accounts">Accounts</option>
                <option value="Legal">Legal</option>
                <option value="Operations">Operations</option>
                <option value="Other">Other</option>
              </select>
              <div class="role-other-wrap" id="clientUserRoleOtherWrap" style="display:none;">
                <input type="text" class="form-control" id="client_user_role_other" placeholder="Enter custom role">
              </div>
            </div>
            <div class="col-md-6 form-group">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="client_user_contact" name="contact_number">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-input-wrapper">
              <input type="text" class="form-control" id="client_user_password" name="password" placeholder="Leave blank to auto-generate on create">
              <button type="button" id="generatePasswordBtn" class="btn-generate">Generate</button>
            </div>
            <small class="muted-small" id="passwordHint">Leave blank to auto-generate on create. On edit, leave blank to keep the existing password.</small>
          </div>

          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" id="client_user_address" name="address">
          </div>

          <div class="form-group">
            <label class="form-label">Client Scope <span class="text-danger">*</span></label>
            <div class="selected-clients" id="selectedClientsBox">
              <span class="selected-empty">No clients selected yet.</span>
            </div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-secondary" id="chooseClientsBtn">
                <i class="fa-solid fa-diagram-project"></i>
                Choose Clients
              </button>
              <button type="button" class="btn btn-secondary" id="clearClientsBtn">
                <i class="fa-solid fa-xmark"></i>
                Clear
              </button>
            </div>
            <small class="muted-small mt-2 d-block">Selecting a parent client gives this user access to that full child tree.</small>
          </div>

          <div class="form-group">
            <label class="switch-label">
              <input type="checkbox" id="client_user_status" name="status" class="switch-input" checked>
              <span class="switch-slider"></span>
              <span class="switch-text">Active</span>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="saveBtn"><span class="btn-label">Save</span></button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="clientPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Choose Client Scope</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="clientTreeLoading" class="text-muted small mb-3" style="display:none;">Loading clients…</div>
        <ul class="picker-tree" id="clientTree"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="applyClientsBtn">Apply Selection</button>
      </div>
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
  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  if (!TOKEN) {
    Swal.fire('Auth Required', 'Session expired. Please login again.', 'warning').then(() => location.href = '/');
    return;
  }

  const PER_PAGE = 10;
  const clientUserModalEl = document.getElementById('clientUserModal');
  const clientPickerModalEl = document.getElementById('clientPickerModal');
  const clientUserModal = new bootstrap.Modal(clientUserModalEl);
  const clientPickerModal = new bootstrap.Modal(clientPickerModalEl);

  const els = {
    rows: document.getElementById('rows'),
    pager: document.getElementById('pager'),
    paginationInfo: document.getElementById('paginationInfo'),
    searchInput: document.getElementById('searchInput'),
    statusFilter: document.getElementById('statusFilter'),
    addBtn: document.getElementById('addClientUserBtn'),
    exportBtn: document.getElementById('exportBtn'),
    saveBtn: document.getElementById('saveBtn'),
    modalTitle: document.getElementById('modalTitle'),
    form: document.getElementById('clientUserForm'),
    id: document.getElementById('client_user_id'),
    name: document.getElementById('client_user_name'),
    email: document.getElementById('client_user_email'),
    role: document.getElementById('client_user_role'),
    roleOther: document.getElementById('client_user_role_other'),
    roleOtherWrap: document.getElementById('clientUserRoleOtherWrap'),
    contact: document.getElementById('client_user_contact'),
    password: document.getElementById('client_user_password'),
    address: document.getElementById('client_user_address'),
    status: document.getElementById('client_user_status'),
    passwordHint: document.getElementById('passwordHint'),
    generatePasswordBtn: document.getElementById('generatePasswordBtn'),
    selectedClientsBox: document.getElementById('selectedClientsBox'),
    chooseClientsBtn: document.getElementById('chooseClientsBtn'),
    clearClientsBtn: document.getElementById('clearClientsBtn'),
    clientTreeLoading: document.getElementById('clientTreeLoading'),
    clientTree: document.getElementById('clientTree'),
    applyClientsBtn: document.getElementById('applyClientsBtn'),
  };

  const state = {
    page: 1,
    total: 0,
    totalPages: 1,
    q: '',
    status: '',
    items: [],
    clients: [],
    clientLookup: new Map(),
    selectedClientIds: new Set(),
  };

  function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function fmtDateOut(d){ if(!d) return '—'; const x = new Date(d); return isNaN(x) ? String(d) : x.toISOString().slice(0,10); }
  function statusClass(s){ const k=(s||'').toLowerCase(); if(k==='active') return 'active'; if(k==='archived') return 'archived'; return 'inactive'; }
  function statusLabel(s){ const k=(s||'').toLowerCase(); if(k==='active') return 'Active'; if(k==='archived') return 'Archived'; return 'Inactive'; }
  function toast(icon, title, timer=1500){ return Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer, icon, title }); }
  function setBtnLoading(btn, isLoading){
    if (!btn) return;
    if (isLoading) {
      if (!btn.querySelector('.btn-label')) btn.innerHTML = `<span class="btn-label">${btn.innerHTML}</span>`;
      btn.classList.add('is-loading');
      btn.setAttribute('aria-busy', 'true');
      btn.disabled = true;
    } else {
      btn.classList.remove('is-loading');
      btn.removeAttribute('aria-busy');
      btn.disabled = false;
    }
  }
  function generatePassword(){
    const chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    let out=''; for(let i=0;i<12;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
    return out;
  }

  const KNOWN_ROLES = ['Viewer', 'Manager', 'Finance', 'Accounts', 'Legal', 'Operations'];
  function syncRoleUI(roleValue = null) {
    const value = roleValue !== null ? String(roleValue || '').trim() : String(els.role.value || '').trim();
    if (KNOWN_ROLES.includes(value)) {
      els.role.value = value;
      els.roleOtherWrap.style.display = 'none';
      els.roleOther.value = '';
      return;
    }
    if (value && value !== 'Other') {
      els.role.value = 'Other';
      els.roleOtherWrap.style.display = 'block';
      els.roleOther.value = value;
      return;
    }
    els.roleOtherWrap.style.display = els.role.value === 'Other' ? 'block' : 'none';
    if (els.role.value !== 'Other') {
      els.roleOther.value = '';
    }
  }
  function currentRoleValue() {
    if (els.role.value === 'Other') {
      return els.roleOther.value.trim() || null;
    }
    return els.role.value.trim() || null;
  }

  function renderSelectedClients(){
    const ids = Array.from(state.selectedClientIds);
    if (!ids.length) {
      els.selectedClientsBox.innerHTML = '<span class="selected-empty">No clients selected yet.</span>';
      return;
    }
    els.selectedClientsBox.innerHTML = ids.map(id => {
      const row = state.clientLookup.get(String(id));
      const label = row ? row.name : `Client #${id}`;
      return `<span class="selected-chip">${esc(label)}</span>`;
    }).join('');
  }

  function buildClientTree(rows){
    const map = new Map();
    rows.forEach(row => map.set(String(row.id), { ...row, children: [] }));
    const roots = [];
    rows.forEach(row => {
      const node = map.get(String(row.id));
      if (row.parent_id && map.has(String(row.parent_id))) {
        map.get(String(row.parent_id)).children.push(node);
      } else {
        roots.push(node);
      }
    });
    const sortRec = (items) => {
      items.sort((a,b) => String(a.name || '').localeCompare(String(b.name || '')));
      items.forEach(item => sortRec(item.children));
    };
    sortRec(roots);
    return roots;
  }

  function renderTreeNode(node){
    const li = document.createElement('li');
    const item = document.createElement('div');
    item.className = 'picker-item';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'picker-toggle';
    toggle.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    if (!node.children.length) toggle.style.visibility = 'hidden';

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.value = String(node.id);
    checkbox.checked = state.selectedClientIds.has(Number(node.id));

    const title = document.createElement('div');
    title.className = 'picker-title';
    title.innerHTML = `<strong>${esc(node.name || ('Client #' + node.id))}</strong><small>#${node.id}${node.parent_id ? ' • child client' : ''}</small>`;

    item.appendChild(toggle);
    item.appendChild(checkbox);
    item.appendChild(title);
    li.appendChild(item);

    const kids = document.createElement('ul');
    li.appendChild(kids);

    if (node.children.length) {
      node.children.forEach(child => kids.appendChild(renderTreeNode(child)));
      toggle.addEventListener('click', () => {
        const isOpen = kids.style.display === 'block';
        kids.style.display = isOpen ? 'none' : 'block';
        toggle.classList.toggle('open', !isOpen);
      });
    }

    checkbox.addEventListener('change', () => {
      const id = Number(checkbox.value);
      if (checkbox.checked) {
        state.selectedClientIds.add(id);
      } else {
        state.selectedClientIds.delete(id);
      }
    });

    return li;
  }

  function renderClientTree(){
    const roots = buildClientTree(state.clients);
    els.clientTree.innerHTML = '';
    roots.forEach(root => els.clientTree.appendChild(renderTreeNode(root)));
  }

  async function ensureClientsLoaded(){
    if (state.clients.length) {
      renderClientTree();
      return;
    }

    els.clientTreeLoading.style.display = 'block';
    try {
      const res = await fetch(`${API_BASE}/clients/all?sort=asc`, { headers });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to load clients');
      state.clients = Array.isArray(data?.data) ? data.data : [];
      state.clientLookup = new Map(state.clients.map(row => [String(row.id), row]));
      renderClientTree();
      renderSelectedClients();
    } catch (error) {
      Swal.fire({ icon: 'error', title: 'Unable to load clients', text: String(error.message || error) });
    } finally {
      els.clientTreeLoading.style.display = 'none';
    }
  }

  async function fetchClientUsers(){
    const params = new URLSearchParams({
      page: state.page,
      per_page: PER_PAGE,
      sort_by: 'created_at',
      sort_dir: 'desc',
    });
    if (state.q) params.set('q', state.q);
    if (state.status) params.set('status', state.status);

    try {
      const res = await fetch(`${API_BASE}/client-users?${params.toString()}`, { headers });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to fetch client users');

      state.items = Array.isArray(data?.data) ? data.data : [];
      state.total = data?.meta?.total || 0;
      state.totalPages = data?.meta?.total_pages || 1;
      renderRows();
      renderPager();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to fetch client users', text:String(error.message || error) });
    }
  }

  function rowClientsHtml(row){
    const clients = Array.isArray(row.clients) ? row.clients : [];
    if (!clients.length) return '<span class="muted-small">No clients</span>';
    const preview = clients.slice(0, 3).map(client => `<span class="client-pill">${esc(client.name)}</span>`).join('');
    const more = clients.length > 3 ? `<span class="muted-small">+${clients.length - 3} more</span>` : '';
    return `<div class="clients-cell">${preview}${more}</div>`;
  }

  function renderRows(){
    if (!state.items.length) {
      els.rows.innerHTML = `
        <tr><td colspan="7">
          <div class="empty-state">
            <h3>No client users found</h3>
            <p>Try adjusting the search or create a new client user.</p>
          </div>
        </td></tr>`;
      return;
    }

    els.rows.innerHTML = state.items.map(row => {
      const status = String(row.status || 'inactive').toLowerCase();
      const checked = status === 'active' ? 'checked' : '';
      const disabled = status === 'archived' ? 'disabled' : '';
      return `
        <tr data-id="${esc(row.id)}">
          <td>
            <div style="font-weight:600">${esc(row.name || '—')}</div>
            <div class="muted-small">#${esc(row.id)}</div>
          </td>
          <td>${esc(row.email || '—')}</td>
          <td>${esc(row.role || '—')}</td>
          <td>${rowClientsHtml(row)}</td>
          <td><span class="badge ${statusClass(status)}">${statusLabel(status)}</span></td>
          <td>${fmtDateOut(row.updated_at)}</td>
          <td>
            <div class="actions-cell">
              <label class="switch ${status === 'active' ? 'active' : ''}">
                <input type="checkbox" class="status-toggle" data-id="${esc(row.id)}" ${checked} ${disabled}>
                <span class="slider"></span>
              </label>
              <button class="btn-icon btn-edit" data-action="edit" data-id="${esc(row.id)}" title="Edit"><i class="fa-solid fa-pen"></i></button>
              <button class="btn-icon btn-delete" data-action="delete" data-id="${esc(row.id)}" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  function renderPager(){
    const totalPages = Math.max(1, state.totalPages || 1);
    const start = state.total ? ((state.page - 1) * PER_PAGE) + 1 : 0;
    const end = Math.min(state.total, state.page * PER_PAGE);
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} client contacts`;

    const buttons = [];
    buttons.push(`<button class="page-btn" data-page="${state.page - 1}" ${state.page <= 1 ? 'disabled' : ''}>Previous</button>`);
    for (let page = Math.max(1, state.page - 2); page <= Math.min(totalPages, state.page + 2); page++) {
      buttons.push(`<button class="page-btn ${page === state.page ? 'active' : ''}" data-page="${page}">${page}</button>`);
    }
    buttons.push(`<button class="page-btn" data-page="${state.page + 1}" ${state.page >= totalPages ? 'disabled' : ''}>Next</button>`);
    els.pager.innerHTML = buttons.join('');
  }

  function resetForm(){
    els.form.reset();
    els.id.value = '';
    els.status.checked = true;
    els.password.value = '';
    els.passwordHint.textContent = 'Leave blank to auto-generate on create. On edit, leave blank to keep the existing password.';
    els.role.value = '';
    els.roleOther.value = '';
    syncRoleUI('');
    state.selectedClientIds = new Set();
    renderSelectedClients();
  }

  function openCreateModal(){
    resetForm();
    els.modalTitle.textContent = 'New Client Contact';
    clientUserModal.show();
  }

  function populateForm(data){
    els.id.value = data.id || '';
    els.name.value = data.name || '';
    els.email.value = data.email || '';
    syncRoleUI(data.role || '');
    els.contact.value = data.contact_number || '';
    els.password.value = '';
    els.address.value = data.address || '';
    els.status.checked = String(data.status || 'active').toLowerCase() === 'active';
    els.passwordHint.textContent = 'On edit, leave blank to keep the existing password.';
    state.selectedClientIds = new Set((data.client_ids || []).map(id => Number(id)));
    renderSelectedClients();
  }

  async function loadForEdit(id){
    try {
      const res = await fetch(`${API_BASE}/client-users/${encodeURIComponent(id)}`, { headers });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to load client user');
      populateForm(data.data || {});
      els.modalTitle.textContent = 'Edit Client Contact';
      clientUserModal.show();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Unable to load client user', text:String(error.message || error) });
    }
  }

  els.addBtn.addEventListener('click', openCreateModal);
  els.generatePasswordBtn.addEventListener('click', () => { els.password.value = generatePassword(); });
  els.role.addEventListener('change', () => syncRoleUI());
  els.chooseClientsBtn.addEventListener('click', async () => {
    await ensureClientsLoaded();
    renderClientTree();
    clientPickerModal.show();
  });
  els.clearClientsBtn.addEventListener('click', () => {
    state.selectedClientIds = new Set();
    renderSelectedClients();
  });
  els.applyClientsBtn.addEventListener('click', () => {
    renderSelectedClients();
    clientPickerModal.hide();
  });

  let searchDebounce;
  els.searchInput.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      state.q = els.searchInput.value.trim();
      state.page = 1;
      fetchClientUsers();
    }, 300);
  });
  els.statusFilter.addEventListener('change', () => {
    state.status = els.statusFilter.value;
    state.page = 1;
    fetchClientUsers();
  });

  els.pager.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;
    const page = parseInt(btn.dataset.page, 10);
    if (!Number.isNaN(page) && page >= 1 && page <= state.totalPages && page !== state.page) {
      state.page = page;
      fetchClientUsers();
    }
  });

  els.rows.addEventListener('change', async (e) => {
    const t = e.target;
    if (!t.classList.contains('status-toggle')) return;
    const id = t.dataset.id;
    const row = t.closest('tr');
    const badge = row?.querySelector('.badge');
    const sw = t.closest('.switch');
    if (!id || !badge || !sw) return;

    const item = state.items.find(entry => String(entry.id) === String(id));
    const prevStatus = String(item?.status || 'inactive').toLowerCase();
    const prevChecked = prevStatus === 'active';
    const nextChecked = t.checked;

    sw.classList.toggle('active', nextChecked);
    badge.className = `badge ${nextChecked ? 'active' : 'inactive'}`;
    badge.textContent = nextChecked ? 'Active' : 'Inactive';
    t.disabled = true;

    try {
      const res = await fetch(`${API_BASE}/client-users/${encodeURIComponent(id)}/toggle`, { method:'PATCH', headers });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to update status');
      if (item) item.status = nextChecked ? 'active' : 'inactive';
      toast('success', 'Status updated');
    } catch (error) {
      t.checked = prevChecked;
      sw.classList.toggle('active', prevChecked);
      badge.className = `badge ${statusClass(prevStatus)}`;
      badge.textContent = statusLabel(prevStatus);
      toast('error', error.message || 'Failed to update status');
    } finally {
      t.disabled = false;
    }
  });

  document.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('button[data-action="edit"]');
    if (editBtn) {
      e.preventDefault();
      await loadForEdit(editBtn.dataset.id);
      return;
    }

    const deleteBtn = e.target.closest('button[data-action="delete"]');
    if (!deleteBtn) return;
    e.preventDefault();
    const id = deleteBtn.dataset.id;
    const result = await Swal.fire({
      title:'Delete client user?',
      text:'This action cannot be undone.',
      icon:'warning',
      showCancelButton:true,
      confirmButtonColor:'#dc2626',
      confirmButtonText:'Delete',
    });
    if (!result.isConfirmed) return;

    try {
      const res = await fetch(`${API_BASE}/client-users/${encodeURIComponent(id)}`, { method:'DELETE', headers });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to delete client user');
      toast('success', 'Client user deleted');
      fetchClientUsers();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Could not delete client user', text:String(error.message || error) });
    }
  });

  els.form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const clientIds = Array.from(state.selectedClientIds).map(id => Number(id)).filter(id => id > 0);
    if (!clientIds.length) {
      Swal.fire({ icon:'warning', title:'Client scope required', text:'Please select at least one client or client tree.' });
      return;
    }

    const id = els.id.value.trim();
    const isEdit = !!id;
    const payload = {
      name: els.name.value.trim(),
      email: els.email.value.trim(),
      role: currentRoleValue(),
      contact_number: els.contact.value.trim() || null,
      address: els.address.value.trim() || null,
      status: els.status.checked ? 'active' : 'inactive',
      client_ids: clientIds,
    };
    if (els.password.value.trim()) payload.password = els.password.value.trim();

    setBtnLoading(els.saveBtn, true);
    try {
      const url = isEdit ? `${API_BASE}/client-users/${encodeURIComponent(id)}` : `${API_BASE}/client-users`;
      const method = isEdit ? 'PUT' : 'POST';
      const res = await fetch(url, {
        method,
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const msg = data?.message || (isEdit ? 'Update failed' : 'Create failed');
        const errs = data?.errors ? Object.entries(data.errors).map(([key, value]) => `• ${key}: ${[].concat(value).join(', ')}`).join('\n') : '';
        throw new Error(errs ? `${msg}\n\n${errs}` : msg);
      }
      if (!isEdit && data?.plain_password) {
        await Swal.fire({
          icon:'success',
          title:'Client user created',
          html:`<div style="text-align:left"><p style="margin-bottom:8px">The client user was created successfully.</p><p style="margin:0"><strong>Generated password:</strong> <code>${esc(data.plain_password)}</code></p></div>`,
        });
      } else {
        toast('success', isEdit ? 'Client user updated' : 'Client user created');
      }
      clientUserModal.hide();
      fetchClientUsers();
    } catch (error) {
      Swal.fire({ icon:'error', title:'Could not save client user', text:String(error.message || error) });
    } finally {
      setBtnLoading(els.saveBtn, false);
    }
  });

  els.exportBtn.addEventListener('click', () => {
    if (!state.items.length) {
      Swal.fire({ icon:'info', title:'Nothing to export', text:'No client users are loaded in the current view.' });
      return;
    }
    const rows = [
      ['ID','Name','Email','Role','Status','Client Scope'],
      ...state.items.map(item => [
        item.id || '',
        item.name || '',
        item.email || '',
        item.role || '',
        statusLabel(item.status),
        (item.clients || []).map(client => client.name).join(' | '),
      ]),
    ];
    const csv = rows.map(row => row.map(value => {
      const text = String(value ?? '');
      return /[",\n]/.test(text) ? `"${text.replace(/"/g, '""')}"` : text;
    }).join(',')).join('\n');
    const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `client_users_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });

  ensureClientsLoaded();
  fetchClientUsers();
})();
</script>
@endpush
