{{-- resources/views/modules/documents/manageDocuments.blade.php --}}

@section('content')

{{-- ===== Header ===== --}}
<div class="documents-page">
  <div class="page-header">
    <h1>Document Library</h1>
    <p>Overview of all uploaded documents</p>
  </div>

  {{-- ===== Toolbar ===== --}}
  <div class="toolbar">
    {{-- Search with icon inside input --}}
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="text" placeholder="Search by name, client, type, authority…">
    </div>

    <div class="filter-group">
      {{-- Client filter --}}
      <select id="clientFilter" class="select-box">
        <option value="">All Clients</option>
      </select>

      {{-- DocType filter --}}
      <select id="typeFilter" class="select-box">
        <option value="">All Types</option>
      </select>

      {{-- Status filter: ONLY Active / Inactive / Archived --}}
      <select id="statusFilter" class="select-box">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="archived">Archived</option>
      </select>

      {{-- Sort By dropdown --}}
      <select id="sortSelect" class="select-box">
        <option value="created_at.desc">Newest First</option>
        <option value="created_at.asc">Oldest First</option>
        <option value="issue_date.desc">Issue Date (New→Old)</option>
        <option value="issue_date.asc">Issue Date (Old→New)</option>
        <option value="expiry_date.asc">Expiry (Soonest)</option>
        <option value="expiry_date.desc">Expiry (Latest)</option>
        <option value="doc_name.asc">Name (A–Z)</option>
        <option value="doc_name.desc">Name (Z–A)</option>
      </select>
    </div>

    {{-- Filter drawer toggle --}}
    <button id="filterToggle" type="button" class="btn btn-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M3 6h18M8 12h8M11 18h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Filter
    </button>

    <a href="{{ url('/documents/upload') }}" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Upload
    </a>

    <button id="exportBtn" class="btn btn-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5-5 5 5M12 5v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
      Export
    </button>
  </div>

  {{-- ===== Filters Drawer ===== --}}
  <div id="filtersPanel" style="display:none;margin-bottom:20px">
    <div class="filter-card">
      <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
        <div>
          <label class="form-label">Issue Date From</label>
          <input id="issueFrom" type="date" class="form-control" style="height:44px;min-width:190px">
        </div>
        <div>
          <label class="form-label">Issue Date To</label>
          <input id="issueTo" type="date" class="form-control" style="height:44px;min-width:190px">
        </div>
        <div>
          <label class="form-label">Expiry Date From</label>
          <input id="expiryFrom" type="date" class="form-control" style="height:44px;min-width:190px">
        </div>
        <div>
          <label class="form-label">Expiry Date To</label>
          <input id="expiryTo" type="date" class="form-control" style="height:44px;min-width:190px">
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
            <th>ID</th>
            <th>Document Name</th>
            <th>Type</th>
            <th>Client</th>
            <th>Issue Date</th>
            <th>Expiry Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <!-- In the table section -->
        <tbody id="rows">
          <tr><td style="color:var(--muted-color);" colspan="8" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div class="pagination-info" id="paginationInfo">
        Showing 1-10 of 100 documents
      </div>
      <div class="pagination-controls" id="pager">
        <!-- pagination injected by JS -->
      </div>
    </div>
  </div>
</div>

{{-- ===== Edit Document Modal ===== --}}
<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa-regular fa-pen-to-square me-2"></i>Edit Document Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editDocumentForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" id="edt_id" name="id"/>
          <input type="hidden" id="edt_file_url" name="file_url">
          
          <div class="mb-3">
            <label class="form-label">Document Name</label>
            <input type="text" class="form-control" id="edt_doc_name" name="doc_name" required>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Client</label>
              <select id="edt_client_id" class="form-control" name="client_id">
                <option value="">Select client</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Document Type</label>
              <select id="edt_document_type_id" class="form-control" name="document_type_id" required>
                <option value="">Select type</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <label class="form-label">Issue Date</label>
              <input type="date" class="form-control" id="edt_issue_date" name="issue_date">
            </div>
            <div class="col-md-6">
              <label class="form-label">Expiry Date</label>
              <input type="date" class="form-control" id="edt_expiry_date" name="expiry_date">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Issuing Authority</label>
            <input type="text" class="form-control" id="edt_issuing_authority" name="issuing_authority" placeholder="Head Office">
          </div>

          {{-- File Upload Section --}}
          <div class="mt-3">
            <label class="form-label">Document File</label>
            <div id="edt_dropzone" class="dropzone">
              <div class="cloud"><i class="fa-regular fa-cloud-arrow-up"></i></div>
              <div>Drag &amp; drop your file here, or click to browse</div>
              <button type="button" id="edt_browseBtn" class="btn btn-outline">Choose File</button>
              <input id="edt_fileInput" type="file" style="display:none" />
              <div id="edt_fileBadge" class="file-pill" style="display:none">
                <i class="fa-regular fa-file"></i><span id="edt_fileName"></span>
              </div>
              <div class="muted" id="edt_uploadHint">No file uploaded yet.</div>
            </div>
            <div class="form-text">Upload a new file to replace the current document</div>
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

.documents-page {background: var(--bg-body);min-height: 100vh;padding: 24px;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;}
.page-header {margin-bottom: 28px;}
.page-header h1 {font-size: 28px;font-weight: 700;color: var(--text-color);margin: 0 0 6px;}
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

/* Filter Card */
.filter-card {background: var(--surface);border: 1px solid #e2e8f0;border-radius: 16px;padding: 20px;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04)}
.form-label {display: block;font-size: 13px;font-weight: 600;color: #475569;margin-bottom: 8px}
.form-control {width: 100%;height: 44px;padding: 0 14px;border: 1px solid #e2e8f0;border-radius: 10px;font-size: 14px;color: var(--text-color);background: var(--surface);transition: all 0.2s}
.form-control:focus {outline: none;border-color: #3b82f6;box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1)}

/* Data Card */
.data-card {background: var(--surface);border-radius: 16px;box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);overflow: hidden;}

/* Table */
.table-container { overflow-x: auto; }

table {width: 100%;border-collapse: collapse;color: var(--text-color);}
thead {background: var(--light-color);}
thead th {padding: 14px 18px;text-align: left;font-size: 12px;font-weight: 600;color: #64748b;text-transform: uppercase;letter-spacing: 0.5px;border-bottom: 1px solid #e2e8f0;white-space: nowrap;}
tbody tr {border-bottom: 1px solid #f1f5f9;transition: background 0.15s;background: var(--surface);}
tbody tr:hover {opacity: 0.95;}
tbody td {padding: 16px 18px;font-size: 14px;color: var(--text-color);vertical-align: middle;}
.cell-id {color: #94a3b8;font-weight: 500;}

/* Status Badge */
.badge {display: inline-flex;align-items: center;gap: 6px;padding: 6px 12px;border-radius: 8px;font-size: 12px;font-weight: 600;}
.badge::before {content: '';width: 6px;height: 6px;border-radius: 50%;background: currentColor;}
.badge.active {background: #dcfce7;color: #16a34a;}
.badge.inactive {background: #f1f5f9;color: #64748b;}
.badge.archived {background: #fee2e2;color: #dc2626;}

/* Actions */
.actions-cell {display: flex;align-items: center;gap: 10px;}
.toggle {position: relative;width: 48px;height: 26px;border-radius: 13px;background: #cbd5e1;cursor: pointer;transition: background 0.2s;}
.toggle input {display: none;}
.toggle-slider {position: absolute;top: 3px;left: 3px;width: 20px;height: 20px;border-radius: 50%;background: #fff;transition: transform 0.2s;box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);}
.toggle input:checked + .toggle-slider {transform: translateX(22px);}
/* (note) the following sibling selector in the original was not functional; we rely on .toggle.active */
.toggle.active {background: #10b981;}
.toggle input:disabled {cursor: not-allowed;}
.btn-edit {height: 34px;padding: 0 14px;background: var(--surface);color: var(--text-color);border: 1px solid #e2e8f0;border-radius: 8px;font-size: 13px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.btn-edit:hover {background: var(--primary-color);border-color: var(--primary-color);color: #fff;}

/* Pagination */
.pagination {display: flex;align-items: center;justify-content: space-between;padding: 18px 20px;background: var(--light-color);border-top: 1px solid #f1f5f9;}
.pagination-info {font-size: 14px;color: #64748b;}
.pagination-controls {display: flex;gap: 6px;}
.page-btn {min-width: 38px;height: 38px;padding: 0 12px;border: 1px solid #e2e8f0;border-radius: 8px;background: var(--surface);color: var(--text-color);font-size: 14px;font-weight: 600;cursor: pointer;transition: all 0.2s;}
.page-btn:hover:not(:disabled) {background: var(--primary-color);border-color: var(--primary-color);color: var(--surface);}
.page-btn.active {background: var(--primary-color);color: #fff;border-color: var(--primary-color);}
.page-btn:disabled {opacity: 0.4;cursor: not-allowed;}

/* Empty State */
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
/* Dropzone styles for edit modal */
.dropzone{
  border:2px dashed #d9dee5;border-radius:12px;padding:22px;text-align:center;background:var(--bg-body);
}
.dropzone.drag{border-color:#3b82f6;background:#f6f9ff}
.dropzone .cloud{font-size:28px;line-height:1;margin-bottom:8px;color:#9aa4b2}
.dropzone .btn{margin-top:10px}
.file-pill{
  display:inline-flex;gap:8px;align-items:center;background:#eef2ff;border:1px solid #dbe3ff;
  color:#1e40af;border-radius:999px;padding:6px 10px;font-size:12px;margin-top:10px
}

/* Button styles for modal */
.btn-outline{background:#fff;border-color:#d9dee5;color:#0f172a}

/* --- NEW: Minimal loader for Save button --- */
.btn.is-loading,
.btn[aria-busy="true"]{
  pointer-events: none;
  opacity: .85;
  position: relative;
}
.btn.is-loading .btn-label{
  visibility: hidden; /* keep width */
}
.btn.is-loading::after{
  content: "";
  position: absolute;
  inset: 0;
  margin: auto;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2.5px solid rgba(255,255,255,.65);
  border-top-color: rgba(255,255,255,0);
  animation: spin .7s linear infinite;
}
@keyframes spin{ to { transform: rotate(360deg); } }

/* --- (Optional) subtle waiting state for toggle while saving --- */
.toggle.saving{
  filter: saturate(.7);
  box-shadow: inset 0 0 0 1px rgba(0,0,0,.05);
}

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

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
  const API_BASE = @json(url('/api'));
  const PER_PAGE = 10;

  // ---------- Token helpers (matching first file) ----------
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  
  if (!TOKEN) {
    Swal.fire('Auth Required', 'Session expired. Please login again.', 'warning')
      .then(() => location.href = '/');
    return;
  }

  // ---------- State ----------
  let state = {
    page: 1,
    total_pages: 1,
    total: 0,
    q: '',
    client_id: '',
    document_type_id: '',
    status: '',
    issue_from: '',
    issue_to: '',
    expiry_from: '',
    expiry_to: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    items: []
  };

  // ---------- Elements ----------
  const els = {
    tbody: document.getElementById('rows'),
    paginationInfo: document.getElementById('paginationInfo'),
    paginationControls: document.getElementById('pager'),
    searchInput: document.getElementById('searchInput'),
    clientFilter: document.getElementById('clientFilter'),
    typeFilter: document.getElementById('typeFilter'),
    statusFilter: document.getElementById('statusFilter'),
    sortSelect: document.getElementById('sortSelect'),
    filterToggle: document.getElementById('filterToggle'),
    filtersPanel: document.getElementById('filtersPanel'),
    issueFrom: document.getElementById('issueFrom'),
    issueTo: document.getElementById('issueTo'),
    expiryFrom: document.getElementById('expiryFrom'),
    expiryTo: document.getElementById('expiryTo'),
    clearFilters: document.getElementById('btnClearFilters'),
    exportBtn: document.getElementById('exportBtn')
  };

  // Modal Elements - Updated with file upload
  const modalEl = document.getElementById('editDocumentModal');
  const modal = window.bootstrap && modalEl ? new bootstrap.Modal(modalEl) : null;
  const f = {
    id: document.getElementById('edt_id'),
    doc_name: document.getElementById('edt_doc_name'),
    client_id: document.getElementById('edt_client_id'),
    document_type_id: document.getElementById('edt_document_type_id'),
    issue_date: document.getElementById('edt_issue_date'),
    expiry_date: document.getElementById('edt_expiry_date'),
    issuing_authority: document.getElementById('edt_issuing_authority'),
    dropzone: document.getElementById('edt_dropzone'),
    browseBtn: document.getElementById('edt_browseBtn'),
    fileInput: document.getElementById('edt_fileInput'),
    fileBadge: document.getElementById('edt_fileBadge'),
    fileName: document.getElementById('edt_fileName'),
    uploadHint: document.getElementById('edt_uploadHint'),
    file_url: document.getElementById('edt_file_url'),
    submit: document.getElementById('edt_submitBtn'),
    form: document.getElementById('editDocumentForm'),
  };

  // ---------- Helpers ----------
  function toast(icon, title, timer=1600){
    return Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer, icon, title});
  }

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function fmtDate(d){
    if(!d) return '';
    const dt = new Date(d);
    return isNaN(dt) ? (d||'') : dt.toISOString().slice(0,10);
  }

  function statusClass(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'active';
    if (k === 'archived') return 'archived';
    return 'inactive';
  }

  function statusLabel(s) {
    const k = String(s || '').toLowerCase();
    if (k === 'active') return 'Active';
    if (k === 'archived') return 'Archived';
    return 'Inactive';
  }

  // --- NEW: helpers for button loading state ---
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

  // File upload helpers
  function setBadge(name){ 
    f.fileBadge.style.display='inline-flex'; 
    f.fileName.textContent = name; 
  }
  function clearBadge(){ 
    f.fileBadge.style.display='none'; 
    f.fileName.textContent=''; 
  }
  function enableSubmit(b){ 
    if (f.submit) f.submit.disabled = !b; 
  }

  // File upload functionality
  function initFileUpload() {
    if (!f.dropzone) return;

    // Optional: restrict file types
    if (f.fileInput) {
      f.fileInput.setAttribute('accept', '.pdf,.doc,.docx,.png,.jpg,.jpeg');
    }

    // Drag & drop events
    ['dragenter','dragover'].forEach(ev => f.dropzone.addEventListener(ev, e=>{
      e.preventDefault(); e.stopPropagation(); f.dropzone.classList.add('drag');
    }));
    ['dragleave','drop'].forEach(ev => f.dropzone.addEventListener(ev, e=>{
      e.preventDefault(); e.stopPropagation(); f.dropzone.classList.remove('drag');
    }));
    f.browseBtn?.addEventListener('click', (e)=> {
      e.stopPropagation();
      f.fileInput?.click();
    });
    f.dropzone.addEventListener('click', (e)=> {
      if (e.target === f.dropzone) {
        f.fileInput?.click();
      }
    });
    f.dropzone.addEventListener('drop', e=>{
      if (e.dataTransfer?.files?.length) handleFile(e.dataTransfer.files[0]);
    });
    f.fileInput?.addEventListener('change', e=>{
      if (e.target.files?.[0]) handleFile(e.target.files[0]);
    });
  }

  // Robust upload URL attempts
  const UPLOAD_TRIES = [
    '/api/uploads/documents',
    '/api/documents/uploads',
    '/api/documents/uploads/documents'
  ];

  async function tryUpload(fd){
    let lastErr;
    for (const url of UPLOAD_TRIES){
      try{
        const res = await fetch(url, {
          method:'POST',
          headers: { 'Authorization': 'Bearer ' + TOKEN },
          body: fd
        });
        let payload = {};
        try { payload = await res.clone().json(); } catch(_) { payload = { message: await res.text() }; }
        if (!res.ok || payload?.status === false) throw new Error(payload?.message || `HTTP ${res.status}`);
        return payload;
      }catch(err){ lastErr = err; }
    }
    throw lastErr || new Error('Upload failed');
  }

  async function handleFile(file){
    clearBadge(); 
    enableSubmit(false);

    const maxMb = 20;
    if (file.size > maxMb * 1024 * 1024) {
      f.uploadHint.textContent = `File too large (>${maxMb}MB).`;
      Swal.fire({icon:'warning', title:'File too large', text:`Please upload a file ≤ ${maxMb}MB.`});
      return;
    }

    f.uploadHint.textContent = 'Uploading…';
    try{
      const fd = new FormData();
      fd.append('file', file);

      const payload = await tryUpload(fd);

      const returnedPath = payload.path || payload.url || payload.file?.path || payload.data?.path || '';
      f.file_url.value = returnedPath;
      setBadge(file.name);
      f.uploadHint.textContent = 'Uploaded to: ' + returnedPath;

      Swal.fire({toast:true,position:'top-end',timer:2000,showConfirmButton:false,icon:'success',title:'File uploaded'});
      enableSubmit(true);
    }catch(err){
      console.error('Upload failed:', err);
      f.uploadHint.textContent = 'Upload failed: ' + err.message;
      f.file_url.value = '';
      enableSubmit(false);
      Swal.fire({icon:'error', title:'Upload failed', text: err.message});
    }
  }

  // ---------- Load Filter Data ----------
  async function loadFilterData(){
    try{
      // Load Clients
      const clientRes = await fetch(`${API_BASE}/clients?per_page=1000&status=active&sort=asc`, { headers });
      if (clientRes.ok) {
        const clientData = await clientRes.json();
        const clients = clientData?.data || [];
        clients.forEach(c => {
          const o = document.createElement('option');
          o.value = c.id;
          o.textContent = c.name || `Client #${c.id}`;
          els.clientFilter.appendChild(o);
        });
        if (f.client_id) {
          clients.forEach(c => {
            const o = document.createElement('option');
            o.value = c.id;
            o.textContent = c.name || `Client #${c.id}`;
            f.client_id.appendChild(o);
          });
        }
      }

      // Load Document Types
      const typeRes = await fetch(`${API_BASE}/doctypes?per_page=1000&status=active&sort_by=name&sort_dir=asc`, { headers });
      if (typeRes.ok) {
        const typeData = await typeRes.json();
        const types = typeData?.data || [];
        types.forEach(t => {
          const o = document.createElement('option');
          o.value = t.id;
          o.textContent = t.name || `Type #${t.id}`;
          els.typeFilter.appendChild(o);
        });
        if (f.document_type_id) {
          types.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            o.textContent = t.name || `Type #${t.id}`;
            f.document_type_id.appendChild(o);
          });
        }
      }
    }catch(err){
      console.error('Error loading filter data:', err);
    }
  }

  // ---------- Fetch Documents ----------
  async function fetchDocuments() {
    const params = new URLSearchParams({
      page: state.page,
      per_page: PER_PAGE,
      sort_by: state.sort_by || 'created_at',
      sort_dir: state.sort_dir || 'desc'
    });
    if (state.q) params.set('q', state.q);
    if (state.client_id) params.set('client_id', state.client_id);
    if (state.document_type_id) params.set('document_type_id', state.document_type_id);
    if (state.status) params.set('status', state.status);
    if (state.issue_from) params.set('issue_from', state.issue_from);
    if (state.issue_to) params.set('issue_to', state.issue_to);
    if (state.expiry_from) params.set('expiry_from', state.expiry_from);
    if (state.expiry_to) params.set('expiry_to', state.expiry_to);

    try {
      const res = await fetch(`${API_BASE}/documents?${params}`, { headers });

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
      state.total_pages = data?.meta?.last_page || data?.meta?.total_pages || 1;
      state.total = data?.meta?.total || 0;
     
      render();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Unable to fetch documents', text: String(err.message) });
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
              <h3>No documents found</h3>
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

  function rowHtml(r) {
    const id = r.id ?? '';
    const name = r.doc_name || `Document #${id}`;
    const status = String(r.status || 'active').toLowerCase();
    const stClass = statusClass(status);
    const stLabel = statusLabel(status);

    return `
      <tr>
        <td class="cell-id">#${esc(id)}</td>
        <td>${esc(name)}</td>
        <td>${esc(r.type_name || '—')}</td>
        <td>${esc(r.client_name || '—')}</td>
        <td>${esc(fmtDate(r.issue_date))}</td>
        <td>${esc(fmtDate(r.expiry_date))}</td>
        <td>
          <span class="badge ${stClass}">${stLabel}</span>
        </td>
        <td>
          <div class="actions-cell">
            <label class="toggle ${stClass === 'active' ? 'active' : ''}" title="Toggle Active/Inactive">
              <input type="checkbox" class="status-toggle" data-id="${esc(id)}"
                ${stClass === 'active' ? 'checked' : ''}
                ${stClass === 'archived' ? 'disabled' : ''}>
              <span class="toggle-slider"></span>
            </label>
            <button class="btn-edit" data-action="edit-doc" data-id="${esc(id)}">Edit</button>
          </div>
        </td>
      </tr>
    `;
  }

  function renderPagination(){
    const pages = state.total_pages || 1;
    const cur   = state.page;
    const start = (cur - 1) * PER_PAGE + 1;
    const end   = Math.min(cur * PER_PAGE, state.total);
    
    els.paginationInfo.textContent = `Showing ${start}-${end} of ${state.total} documents`;

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

  // ---------- Status Toggle (Optimistic UI)  --- REPLACED ---
  els.tbody.addEventListener('change', async (e) => {
    const t = e.target;
    if (!t.classList.contains('status-toggle')) return;

    const id = t.getAttribute('data-id');
    const rowEl = t.closest('tr');
    const badge = rowEl?.querySelector('.badge');
    const toggleLabel = t.closest('.toggle');

    // Read previous state before change
    const prevChecked = !t.checked ? false : true;

    // New state after the click
    const newChecked = t.checked;
    const newStatus = newChecked ? 'active' : 'inactive';

    // --- OPTIMISTIC: update visuals immediately ---
    if (toggleLabel){
      toggleLabel.classList.toggle('active', newChecked);
      toggleLabel.classList.add('saving');   // subtle visual while saving
    }
    if (badge){
      badge.classList.remove('active','inactive','archived');
      badge.classList.add(newStatus === 'active' ? 'active' : 'inactive');
      badge.textContent = newStatus === 'active' ? 'Active' : 'Inactive';
    }
    t.disabled = true;

    // reflect into local table state
    const item = state.items.find(r => String(r.id) === String(id));
    const prevStatus = item ? (item.status || 'inactive') : 'inactive';
    if (item) item.status = newStatus;

    try {
      // Try PATCH first
      let res = await fetch(`${API_BASE}/documents/${encodeURIComponent(id)}`, {
        method: 'PATCH',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus })
      });

      if (res.status === 404) {
        // fallback PUT
        res = await fetch(`${API_BASE}/documents/${encodeURIComponent(id)}`, {
          method: 'PUT',
          headers: { ...headers, 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus })
        });
      }

      if (res.status === 401 || res.status === 403) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data.message || data.error || 'Access denied');
      }

      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data?.message || 'Failed to update status');
      }

      toast('success', `Status updated to ${newStatus}`);
    } catch (err) {
      // Roll back UI if failed
      if (item) item.status = prevStatus;

      t.checked = prevStatus === 'active';
      if (toggleLabel){
        toggleLabel.classList.toggle('active', t.checked);
      }
      if (badge){
        const cls = prevStatus === 'active' ? 'active' : (prevStatus === 'archived' ? 'archived' : 'inactive');
        badge.classList.remove('active','inactive','archived');
        badge.classList.add(cls);
        badge.textContent = prevStatus === 'active' ? 'Active' : (prevStatus === 'archived' ? 'Archived' : 'Inactive');
      }
      toast('error', err.message || 'Failed to update status');
    } finally {
      if (toggleLabel) toggleLabel.classList.remove('saving');
      t.disabled = false;
    }
  });

  // ---------- Edit Document ----------
  document.addEventListener('click', (e) => {
    const a = e.target.closest('button[data-action="edit-doc"]');
    if (!a) return;

    if (!modal) return;
    e.preventDefault();

    const id = a.dataset.id;
    if (!id) return;

    const row = state.items.find(r => String(r.id) === String(id));
    if (!row) return;

    // Populate all fields in the edit modal
    f.id.value = row.id ?? '';
    f.doc_name.value = row.doc_name ?? '';
    f.client_id.value = row.client_id ?? '';
    f.document_type_id.value = row.document_type_id ?? '';
    f.issue_date.value = fmtDate(row.issue_date);
    f.expiry_date.value = fmtDate(row.expiry_date);
    f.issuing_authority.value = row.issuing_authority ?? '';
    f.file_url.value = row.file_url ?? '';
    
    // Show current file if exists
    if (row.file_url) {
      const fileName = row.file_url.split('/').pop() || 'Current file';
      setBadge(fileName);
      f.uploadHint.textContent = 'Current file: ' + fileName;
    } else {
      clearBadge();
      f.uploadHint.textContent = 'No file uploaded yet.';
    }
    
    enableSubmit(true);
    modal.show();
  });

  // Modal save  --- REPLACED to add loader ---
  f.form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = f.id.value;
    if (!id) return;
    
    const payload = {
      doc_name: f.doc_name.value.trim(),
      client_id: f.client_id.value || null,
      document_type_id: f.document_type_id.value,
      issue_date: f.issue_date.value,
      expiry_date: f.expiry_date.value,
      issuing_authority: f.issuing_authority.value || null,
    };
    if (f.file_url.value) {
      payload.file_url = f.file_url.value;
    }

    // turn on loader on Save button
    setBtnLoading(f.submit, true);
    
    try {
      const res = await fetch(`${API_BASE}/documents/${encodeURIComponent(id)}`, {
        method: 'PATCH',
        headers: { ...headers, 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (res.status === 401 || res.status === 403) {
        const data = await res.json().catch(() => ({}));
        await Swal.fire({
          icon: 'error',
          title: 'Unauthorized',
          html: (data.message || data.error || 'Access denied')
        });
        return;
      }

      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Failed to save');

      toast('success', 'Document updated successfully!');
      modal.hide();
      
      // Reset file upload state
      clearBadge();
      f.file_url.value = '';
      f.uploadHint.textContent = 'No file uploaded yet.';
      if (f.fileInput) f.fileInput.value = '';
      
      fetchDocuments(); // Refresh the table to show new data
    } catch (err) {
      toast('error', err.message);
    } finally {
      // turn off loader no matter what
      setBtnLoading(f.submit, false);
    }
  });

  // ---------- Export ----------
  els.exportBtn.addEventListener('click', () => {
    if (!state.items.length) {
      toast('info','Nothing to export'); 
      return;
    }
    
    const cols = ['id','doc_name','type_name','client_name','issue_date','expiry_date','status','file_url','created_at','updated_at'];
    const csvRows = [cols.join(',')];
    state.items.forEach(r=>{
      const line = cols.map(k=>{
        const v = ((r[k] ?? '') + '').replace(/"/g,'""');
        return `"${v}"`;
      }).join(',');
      csvRows.push(line);
    });
    
    const csv = csvRows.join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = `documents_export_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });

  // ---------- Filters ----------
  let searchDebounce;
  els.searchInput.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      state.q = els.searchInput.value.trim();
      state.page = 1;
      fetchDocuments();
    }, 300);
  });

  els.clientFilter.addEventListener('change', () => {
    state.client_id = els.clientFilter.value;
    state.page = 1;
    fetchDocuments();
  });

  els.typeFilter.addEventListener('change', () => {
    state.document_type_id = els.typeFilter.value;
    state.page = 1;
    fetchDocuments();
  });

  els.statusFilter.addEventListener('change', () => {
    state.status = els.statusFilter.value;
    state.page = 1;
    fetchDocuments();
  });

  // Sort
  els.sortSelect.addEventListener('change', ()=>{
    const spec = els.sortSelect.value || '';
    const [by, dir] = spec.split('.');
    state.sort_by  = by  || 'created_at';
    state.sort_dir = dir || 'desc';
    state.page = 1;
    fetchDocuments();
  });

  // Filter drawer toggle
  els.filterToggle.addEventListener('click', () => {
    const isOpen = getComputedStyle(els.filtersPanel).display !== 'none';
    els.filtersPanel.style.display = isOpen ? 'none' : 'block';
  });

  // Date filters
  els.issueFrom.addEventListener('change', () => {
    state.issue_from = els.issueFrom.value || '';
    state.page = 1;
    fetchDocuments();
  });
  els.issueTo.addEventListener('change', () => {
    state.issue_to = els.issueTo.value || '';
    state.page = 1;
    fetchDocuments();
  });
  els.expiryFrom.addEventListener('change', () => {
    state.expiry_from = els.expiryFrom.value || '';
    state.page = 1;
    fetchDocuments();
  });
  els.expiryTo.addEventListener('change', () => {
    state.expiry_to = els.expiryTo.value || '';
    state.page = 1;
    fetchDocuments();
  });

  // Clear filters
  els.clearFilters.addEventListener('click', (e) => {
    e.preventDefault();
    els.issueFrom.value = '';
    els.issueTo.value = '';
    els.expiryFrom.value = '';
    els.expiryTo.value = '';
    state.issue_from = state.issue_to = state.expiry_from = state.expiry_to = '';
    state.page = 1;
    fetchDocuments();
  });

  // Pagination clicks
  els.paginationControls.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-page]');
    if (!btn || btn.disabled) return;

    const p = parseInt(btn.getAttribute('data-page'), 10);
    if (isNaN(p) || p < 1 || p > state.total_pages || p === state.page) return;

    state.page = p;
    fetchDocuments();
  });

  // ---------- Initial Load ----------
  initFileUpload();
  loadFilterData().finally(() => fetchDocuments());
})();
</script>
@endpush
