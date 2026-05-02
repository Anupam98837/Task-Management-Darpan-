@extends('pages.users.admin.layout.structure')
@section('title','Mailers')

@push('styles')
<style>
* { box-sizing: border-box; }

.mailers-page {
  background: var(--bg-body);
  min-height: 100vh;
  padding: 24px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
}

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

/* Toolbar */
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
  white-space: nowrap;
  word-break: keep-all;
  line-height: 1;
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

/* Data Card */
.data-card {
  background: var(--surface);
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

/* Table */
.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  color: var(--text-color);
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
  opacity: 0.95;
}

tbody td {
  padding: 16px 18px;
  font-size: 14px;
  color: var(--text-color);
  vertical-align: middle;
}

.cell-id {
  color: #94a3b8;
  font-weight: 500;
}

/* Badge/Pills */
.badge-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}

.badge-ssl {
  background: #dcfce7;
  color: #16a34a;
}

.badge-tls {
  background: #dbeafe;
  color: #2563eb;
}

.badge-none {
  background: #f1f5f9;
  color: #64748b;
}

.from-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.from-address {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 12px;
  color: var(--text-color);
}

.from-name {
  font-size: 12px;
  color: #64748b;
}

.label-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 12px;
  color: var(--text-color);
}

/* Actions */
.actions-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toggle {
  position: relative;
  width: 48px;
  height: 26px;
  border-radius: 13px;
  background: #cbd5e1;
  cursor: pointer;
  transition: background 0.2s;
}

.toggle input {
  display: none;
}

.toggle-slider {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.toggle input:checked + .toggle-slider {
  transform: translateX(22px);
}

.toggle.active {
  background: #10b981;
}

.toggle input:disabled {
  cursor: not-allowed;
}

.btn-edit {
  height: 34px;
  padding: 0 14px;
  background: var(--surface);
  color: var(--text-color);
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: #fff;
}

.btn-delete {
  height: 34px;
  padding: 0 14px;
  background: var(--surface);
  color: #dc2626;
  border: 1px solid #fee2e2;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-delete:hover {
  background: #dc2626;
  border-color: #dc2626;
  color: #fff;
}

/* Empty State */
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

/* Modal */
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
}

.modal-body {
  padding: 28px;
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

.form-select {
  height: 44px;
  padding: 0 38px 0 14px;
  background: var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;
  appearance: none;
}

.modal-footer {
  padding: 20px 28px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Dark Theme Adjustments */
.theme-dark .search-box input,
.theme-dark .select-box,
.theme-dark .form-control,
.theme-dark .form-select {
  background: var(--surface) !important;
  border-color: var(--border-color) !important;
  color: var(--text-color) !important;
}

.theme-dark .search-box input::placeholder,
.theme-dark .form-control::placeholder {
  color: color-mix(in hsl, var(--muted-color) 75%, white 25%) !important;
}

.theme-dark table tbody tr {
  background: var(--surface) !important;
}

.theme-dark table tbody tr:nth-child(even) {
  background: color-mix(in hsl, var(--surface) 92%, black 8%) !important;
}

.theme-dark table tbody tr:hover {
  background: color-mix(in hsl, var(--accent-color) 18%, var(--surface)) !important;
}

.theme-dark table thead th {
  background: color-mix(in hsl, var(--surface) 95%, black 5%) !important;
  color: var(--text-color) !important;
}

.theme-dark .badge-chip,
.theme-dark .from-address,
.theme-dark .label-badge {
  background: color-mix(in hsl, var(--accent-color) 15%, var(--surface)) !important;
  border-color: color-mix(in hsl, var(--accent-color) 35%, var(--border-color)) !important;
  color: var(--text-color) !important;
}



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
}
</style>
@endpush

@section('content')
<div class="mailers-page">
  <div class="page-header">
    <h1>Mail Delivery</h1>
    <p>Configure and manage email delivery settings</p>
  </div>

  {{-- ===== Toolbar ===== --}}
  <div class="toolbar">
    {{-- Search with icon inside input --}}
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="q" type="text" placeholder="Search by driver, host, username, from address…">
    </div>

    <div class="filter-group">
      {{-- Driver filter --}}
      <select id="driver" class="select-box">
        <option value="">All Drivers</option>
        <option value="smtp">SMTP</option>
        <option value="sendmail">sendmail</option>
        <option value="ses">SES</option>
        <option value="mailgun">Mailgun</option>
        <option value="postmark">Postmark</option>
        <option value="log">log</option>
        <option value="array">array</option>
      </select>

      {{-- Encryption filter --}}
      <select id="encryption" class="select-box">
        <option value="">All Encryption</option>
        <option value="ssl">SSL</option>
        <option value="tls">TLS</option>
        <option value="none">None</option>
      </select>

      {{-- Rows per page --}}
      <select id="rows" class="select-box">
        <option value="10">Rows: 10</option>
        <option value="25">Rows: 25</option>
        <option value="50" selected>Rows: 50</option>
        <option value="100">Rows: 100</option>
      </select>
    </div>

    <button id="btnNew" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      New Mailer
    </button>
  </div>

  {{-- ===== Table Card ===== --}}
  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Default</th>
            <th>Driver</th>
            <th>Host</th>
            <th>Port</th>
            <th>Username</th>
            <th>Encryption</th>
            <th>From</th>
            <th>Label</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="rowsBody">
          <tr><td colspan="9" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ===== Add/Edit Modal ===== --}}
<div class="modal fade" id="mailerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add Mailer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="mailerForm" class="row g-3">
          <input type="hidden" id="mid">

          <div class="col-md-8">
            <label class="form-label">Label (optional)</label>
            <input id="f_label" type="text" class="form-control" placeholder="Primary SMTP / Mailgun EU / …">
          </div>

          <div class="col-md-4">
            <label class="form-label">Driver <span class="text-danger">*</span></label>
            <select id="f_mailer" class="form-control form-select">
              <option value="smtp" selected>SMTP</option>
              <option value="sendmail">sendmail</option>
              <option value="ses">SES</option>
              <option value="mailgun">Mailgun</option>
              <option value="postmark">Postmark</option>
              <option value="log">log</option>
              <option value="array">array</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Host <span class="text-danger">*</span></label>
            <input id="f_host" type="text" class="form-control" placeholder="smtp.mailserver.com">
          </div>

          <div class="col-md-3">
            <label class="form-label">Port <span class="text-danger">*</span></label>
            <input id="f_port" type="number" class="form-control" placeholder="587">
          </div>

          <div class="col-md-3">
            <label class="form-label">Encryption <span class="text-danger">*</span></label>
            <select id="f_encryption" class="form-control form-select">
              <option value="">None</option>
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input id="f_username" type="text" class="form-control" placeholder="user@domain.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input id="f_password" type="password" class="form-control" placeholder="••••••••">
          </div>

          <div class="col-md-6">
            <label class="form-label">From Address <span class="text-danger">*</span></label>
            <input id="f_from_address" type="email" class="form-control" placeholder="noreply@domain.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">From Name <span class="text-danger">*</span></label>
            <input id="f_from_name" type="text" class="form-control" placeholder="Your App Name">
          </div>

          <div class="col-12 d-flex align-items-center mt-2">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="f_is_default">
              <label class="form-check-label" for="f_is_default">Make this the default mailer</label>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSave" type="button" class="btn btn-primary">Save</button>
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
  
  const API_ROOT = '/api';
  const headers = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };

  let list = [];
  let mode = 'create';

  const qEl = document.getElementById('q');
  const driverEl = document.getElementById('driver');
  const encEl = document.getElementById('encryption');
  const rowsEl = document.getElementById('rows');
  const bodyEl = document.getElementById('rowsBody');
  const btnNew = document.getElementById('btnNew');

  const modalEl = document.getElementById('mailerModal');
  const modal = new bootstrap.Modal(modalEl);
  const modalTitle = document.getElementById('modalTitle');

  const f = {
    id: document.getElementById('mid'),
    mailer: document.getElementById('f_mailer'),
    host: document.getElementById('f_host'),
    port: document.getElementById('f_port'),
    username: document.getElementById('f_username'),
    password: document.getElementById('f_password'),
    encryption: document.getElementById('f_encryption'),
    from_address: document.getElementById('f_from_address'),
    from_name: document.getElementById('f_from_name'),
    label: document.getElementById('f_label'),
    is_default: document.getElementById('f_is_default')
  };

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function encBadge(v){
    if (!v) return '<span class="badge-chip badge-none">NONE</span>';
    if (v.toLowerCase()==='ssl') return '<span class="badge-chip badge-ssl">SSL</span>';
    if (v.toLowerCase()==='tls') return '<span class="badge-chip badge-tls">TLS</span>';
    return '<span class="badge-chip badge-none">'+esc(v)+'</span>';
  }

  function fromCell(row){
    const name = row.from_name || '';
    const addr = row.from_address || '';
    return `<div class="from-cell">
      <span class="from-address">${esc(addr)}</span>
      ${name ? `<span class="from-name">${esc(name)}</span>` : ''}
    </div>`;
  }

  function render(){
    if (!list.length){
      bodyEl.innerHTML = `
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <h3>No mailers found</h3>
              <p>Try adjusting your filters or create a new mailer</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }
    
    bodyEl.innerHTML = list.map(r => `
      <tr>
        <td>
          <label class="toggle ${r.is_default ? 'active' : ''}" title="Set as default">
            <input class="toggle-default" type="checkbox" ${r.is_default ? 'checked' : ''} data-id="${esc(r.id)}">
            <span class="toggle-slider"></span>
          </label>
        </td>
        <td class="fw-semibold">${esc((r.mailer || '').toUpperCase())}</td>
        <td>${esc(r.host || '—')}</td>
        <td>${esc(r.port || '—')}</td>
        <td>${esc(r.username || '—')}</td>
        <td>${encBadge(r.encryption)}</td>
        <td>${fromCell(r)}</td>
        <td>${r.label ? `<span class="label-badge">${esc(r.label)}</span>` : '—'}</td>
        <td>
          <div class="actions-cell">
            <button class="btn-edit" data-action="edit" data-id="${esc(r.id)}">Edit</button>
            <button class="btn-delete" data-action="del" data-id="${esc(r.id)}">Delete</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  async function fetchList(){
    bodyEl.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:#94a3b8">Loading…</td></tr>';
    const params = new URLSearchParams({
      q: qEl.value.trim(),
      driver: driverEl.value,
      encryption: encEl.value,
      rows: rowsEl.value
    });
    
    try{
      const res = await fetch(`${API_ROOT}/mailer?`+params.toString(), { headers });
      if(!res.ok){
        const j = await res.json().catch(()=>({}));
        console.error('Failed to fetch mailers:', j.message || res.status);
        bodyEl.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:#dc2626">Failed to load mailers</td></tr>';
        return;
      }
      const j = await res.json();
      list = Array.isArray(j?.data) ? j.data : [];
      render();
    }catch(ex){
      console.error('Network error:', ex);
      bodyEl.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:#dc2626">Network error</td></tr>';
    }
  }

  function openCreate(){
    mode = 'create';
    modalTitle.textContent = 'New Mailer';
    f.id.value = '';
    f.mailer.value = 'smtp';
    f.host.value = '';
    f.port.value = '';
    f.username.value = '';
    f.password.value = '';
    f.encryption.value = '';
    f.from_address.value = '';
    f.from_name.value = '';
    f.label.value = '';
    f.is_default.checked = false;
    modal.show();
  }

  async function openEdit(id){
    mode = 'edit';
    modalTitle.textContent = 'Edit Mailer';
    try{
      const res = await fetch(`${API_ROOT}/mailer/${id}`, { headers });
      if(!res.ok){
        const j = await res.json().catch(()=>({}));
        Swal.fire('Error', j.message || 'Failed to load mailer', 'error');
        return;
      }
      const j = await res.json();
      const r = j?.data || {};
      f.id.value = r.id;
      f.mailer.value = r.mailer || 'smtp';
      f.host.value = r.host || '';
      f.port.value = r.port || '';
      f.username.value = r.username || '';
      f.password.value = '';
      f.encryption.value = r.encryption || '';
      f.from_address.value = r.from_address || '';
      f.from_name.value = r.from_name || '';
      f.label.value = r.label || '';
      f.is_default.checked = !!r.is_default;
      modal.show();
    }catch(ex){
      console.error('Network error:', ex);
      Swal.fire('Error', 'Network error', 'error');
    }
  }

  function collectForm(){
    return {
      mailer: f.mailer.value,
      host: f.host.value,
      port: f.port.value ? parseInt(f.port.value,10) : '',
      username: f.username.value,
      password: f.password.value,
      encryption: f.encryption.value,
      from_address: f.from_address.value,
      from_name: f.from_name.value,
      label: f.label.value,
      is_default: f.is_default.checked
    };
  }

  async function save(){
    const payload = collectForm();
    let url = `${API_ROOT}/mailer`;
    let method = 'POST';
    if (mode === 'edit'){
      url = `${API_ROOT}/mailer/${f.id.value}`;
      method = 'PUT';
    }
    try{
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type':'application/json', ...headers },
        body: JSON.stringify(payload)
      });
      const j = await res.json();
      if (!res.ok){
        Swal.fire('Error', j?.message || 'Validation error', 'error');
        return;
      }
      modal.hide();
      Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 2000,
        showConfirmButton: false,
        icon: 'success',
        title: mode === 'create' ? 'Mailer created' : 'Mailer updated'
      });
      await fetchList();
    }catch(ex){
      console.error('Network error:', ex);
      Swal.fire('Error', 'Network error', 'error');
    }
  }

  async function makeDefault(id){
    try{
      const res = await fetch(`${API_ROOT}/mailer/${id}/default`, {
        method: 'PUT',
        headers
      });
      const j = await res.json();
      if (!res.ok){ 
        Swal.fire('Error', j?.message || 'Failed to set default', 'error'); 
        return; 
      }
      list = Array.isArray(j?.data) ? j.data : list;
      render();
      Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 1600,
        showConfirmButton: false,
        icon: 'success',
        title: 'Default mailer updated'
      });
    }catch(ex){
      console.error('Network error:', ex);
      Swal.fire('Error', 'Network error', 'error');
    }
  }

  async function remove(id){
    const result = await Swal.fire({
      title: 'Delete Mailer?',
      text: 'This action cannot be undone',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel'
    });
    if (!result.isConfirmed) return;
    
    try{
      const res = await fetch(`${API_ROOT}/mailer/${id}`, {
        method: 'DELETE',
        headers
      });
      const j = await res.json();
      if (!res.ok){ 
        Swal.fire('Error', j?.message || 'Delete failed', 'error'); 
        return; 
      }
      Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 2000,
        showConfirmButton: false,
        icon: 'success',
        title: 'Mailer deleted'
      });
      await fetchList();
    }catch(ex){
      console.error('Network error:', ex);
      Swal.fire('Error', 'Network error', 'error');
    }
  }

  btnNew.addEventListener('click', openCreate);
  document.getElementById('btnSave').addEventListener('click', save);

  let searchDebounce;
  qEl.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(fetchList, 300);
  });
  
  driverEl.addEventListener('change', fetchList);
  encEl.addEventListener('change', fetchList);
  rowsEl.addEventListener('change', fetchList);

  bodyEl.addEventListener('click', (e)=>{
    const t = e.target.closest('button,[type=checkbox]');
    if (!t) return;

    if (t.matches('.toggle-default')){
      const id = t.getAttribute('data-id');
      makeDefault(id);
      return;
    }

    const id = t.getAttribute('data-id');
    const act = t.getAttribute('data-action');
    if (act === 'edit') openEdit(id);
    if (act === 'del') remove(id);
  });

  fetchList();
})();
</script>
@endpush
