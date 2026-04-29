@extends('pages.users.admin.layout.structure')

@section('title','Add New Client')

@push('styles')
<style>
  .main-content{ background: var(--bg-body); }
  
  /* Same shell used by other forms */
  .doc-panel{
    max-width: 760px;
    margin: 20px auto;
    padding: 22px 22px 14px;
    background: var(--surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    color: var(--text-color);
  }
  .doc-title{
    font-family: var(--font-head);
    font-weight: 700;
    font-size: 22px;
    margin: 2px 0 6px;
    color: var(--text-color);
    text-align:center;
  }
  .doc-sub{ margin: 0 0 18px; color: var(--muted-color); text-align:center; }

  .field-label{
    font-weight: 700;
    font-size: 12.5px;
    color: var(--muted-color);
    letter-spacing: .2px;
    margin-bottom: 6px;
  }
  .err{ font-size:12px; color:#b91c1c; margin-top:6px; }

  /* Actions row matches other forms */
  .actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }
  .tree-current{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:12px;color:#64748b}
  .tree-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--bg-body);border:1px solid var(--border-color);color:var(--text-color);font-weight:600}
  .tree-list{list-style:none;margin:0;padding:0 0 0 8px;position:relative}
  .tree-list::before{content:"";position:absolute;left:14px;top:0;bottom:8px;width:1px;background:var(--border-color)}
  .tree-list>li{position:relative;margin:0 0 8px 0;padding-left:24px}
  .tree-list>li::before{content:"";position:absolute;left:14px;top:16px;width:16px;height:1px;background:var(--border-color)}
  .tree-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--border-color);border-radius:12px;background:var(--surface)}
  .tree-toggle{width:28px;height:28px;border:1px solid var(--border-color);border-radius:8px;background:var(--bg-body);display:inline-flex;align-items:center;justify-content:center}
  .tree-toggle.open i{transform:rotate(90deg)}
  .tree-children{margin:8px 0 10px 0;padding-left:24px;display:none}
  .tree-children .tree-children{margin-left:16px}
  .tree-title{display:flex;flex-direction:column;gap:2px}
  .tree-title small{color:#64748b}

  /* Minor consistency tweaks */
  .form-control, .form-select{
    background: var(--bg-body);
    color: var(--text-color);
    border-color: var(--border-color);
    border-radius: 8px;
    min-height: 42px;
  }
  .form-control::placeholder{ color: #9aa4b2; }
  .form-control:focus, .form-select:focus{
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37,99,235,.18);
  }

  /* NEW: inline button spinner */
  .btn .spinner{
    display:inline-block; width:16px; height:16px; border-radius:50%;
    border:2px solid currentColor; border-right-color: transparent;
    animation: spin 0.8s linear infinite; vertical-align:-2px; margin-right:8px;
  }
  @keyframes spin { to { transform: rotate(360deg);} }
</style>
@endpush

@section('content')
<div class="container py-3">
  <div class="card doc-panel">
    <h2 class="doc-title">Add New Client</h2>
    <p class="doc-sub">Fill in the fields below to add a new client to the system.</p>

    <form id="clientForm" autocomplete="off">
      {{-- Client Details --}}
      <div class="row g-3">
        <div class="col-md-8">
          <label class="field-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="name" placeholder="Acme Corporation" required>
          <div class="err" data-error="name"></div>
        </div>
        <div class="col-md-4">
          <label class="field-label">Organization Type</label>
          <select class="form-select" name="org_type">
            <option value="company" selected>Company</option>
            <option value="hospital">Hospital</option>
            <option value="clinic">Clinic</option>
            <option value="ngo">NGO</option>
            <option value="individual">Individual</option>
            <option value="other">Other</option>
          </select>
          <div class="err" data-error="org_type"></div>
        </div>
        <div class="col-md-6">
          <label class="field-label">Parent Client</label>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="btnPickParentClient">
              <i class="fa-solid fa-sitemap"></i> Choose Parent
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnClearParentClient" title="Clear parent">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <input type="hidden" name="parent_id" id="parent_id">
          <div class="tree-current">
            <span>Current:</span>
            <span class="tree-badge" id="parentClientCurrent">Self (Root)</span>
          </div>
          <div class="err" data-error="parent_id"></div>
        </div>
      </div>

      {{-- Contact Information --}}
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="field-label">Client Email</label>
          <input type="email" class="form-control" name="email" placeholder="contact@acmecorp.com">
          <div class="err" data-error="email"></div>
        </div>
        <div class="col-md-6">
          <label class="field-label">Client Phone</label>
          <input type="text" class="form-control" name="phone" placeholder="+1 (555) 123-4567">
          <div class="err" data-error="phone"></div>
        </div>

        <div class="col-md-4">
          <label class="field-label">Contact Person Name</label>
          <input type="text" class="form-control" name="contact_name" placeholder="Jane Doe">
          <div class="err" data-error="contact_name"></div>
        </div>
        <div class="col-md-4">
          <label class="field-label">Contact Person Email</label>
          <input type="email" class="form-control" name="contact_email" placeholder="jane.doe@acmecorp.com">
          <div class="err" data-error="contact_email"></div>
        </div>
        <div class="col-md-4">
          <label class="field-label">Contact Person Phone</label>
          <input type="text" class="form-control" name="contact_phone" placeholder="+1 (555) 987-6543">
          <div class="err" data-error="contact_phone"></div>
        </div>
      </div>

      {{-- Location & Other Details --}}
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="field-label">Address</label>
          <input type="text" class="form-control" name="address" placeholder="123 Innovation Drive">
          <div class="err" data-error="address"></div>
        </div>
        <div class="col-md-3">
          <label class="field-label">City</label>
          <input type="text" class="form-control" name="city" placeholder="Techville">
          <div class="err" data-error="city"></div>
        </div>
        <div class="col-md-3">
          <label class="field-label">State / Province</label>
          <input type="text" class="form-control" name="state" placeholder="CA">
          <div class="err" data-error="state"></div>
        </div>
        <div class="col-md-3">
          <label class="field-label">Zip / Postcode</label>
          <input type="text" class="form-control" name="postcode" placeholder="90210">
          <div class="err" data-error="postcode"></div>
        </div>
        <div class="col-md-3">
          <label class="field-label">Country</label>
          <select class="form-select" name="country">
            <option value="US" selected>United States</option>
            <option value="IN">India</option>
            <option value="GB">United Kingdom</option>
            <option value="CA">Canada</option>
          </select>
          <div class="err" data-error="country"></div>
        </div>
        <div class="col-md-6">
          <label class="field-label">Timezone</label>
          <select class="form-select" name="timezone">
            <option value="America/Los_Angeles" selected>America/Los Angeles (PST)</option>
            <option value="America/New_York">America/ New York (EST)</option>
            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
            <option value="Europe/London">Europe/London (GMT)</option>
          </select>
          <div class="err" data-error="timezone"></div>
        </div>
      </div>

      {{-- Additional Information --}}
      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="field-label">Website URL</label>
          <input type="url" class="form-control" name="website_url" placeholder="https://www.acmecorp.com">
          <div class="err" data-error="website_url"></div>
        </div>
        <div class="col-md-6">
          <label class="field-label">Image URL</label>
          <input type="text" class="form-control" name="image_url" placeholder="https://api.placeholder.co/120x120">
          <div class="err" data-error="image_url"></div>
        </div>
        <div class="col-md-4">
          <label class="field-label">Client Status</label>
          <select class="form-select" name="status">
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
          <div class="err" data-error="status"></div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="actions">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
        <button id="saveClientBtn" type="submit" class="btn btn-primary">
          <i class="fa-regular fa-floppy-disk"></i> <span class="btn-label">Add Client</span>
        </button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="parentClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-sitemap me-2"></i>Choose Parent Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="parentClientLoading" class="text-muted small mb-3" style="display:none;">Loading clients…</div>
        <ul id="parentClientTree" class="tree-list"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSaveParentClient">Use Selection</button>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
  // === Token Handling (exactly like createJob.blade.php) ===
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headers = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };
  if(!TOKEN){ Swal.fire('Auth Required','Session expired. Please login again.','warning').then(()=>location.href='/'); return; }

  // === Config ===
  const API_BASE   = @json(url('/api'));
  const MANAGE_URL = @json(url('/admin/client/manage'));
  const parentClientModal = window.bootstrap ? new bootstrap.Modal(document.getElementById('parentClientModal')) : null;
  const parentClientTree = document.getElementById('parentClientTree');
  const parentClientLoading = document.getElementById('parentClientLoading');
  const parentIdInput = document.getElementById('parent_id');
  const parentCurrent = document.getElementById('parentClientCurrent');
  const btnPickParent = document.getElementById('btnPickParentClient');
  const btnClearParent = document.getElementById('btnClearParentClient');
  const btnSaveParent = document.getElementById('btnSaveParentClient');
  let clientTreeRows = [];
  let selectedParentClient = null;

  // === SweetAlert helpers ===
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1800,
    timerProgressBar: true
  });

  function swalError(title, html) {
    return Swal.fire({
      icon: 'error',
      title: title || 'Something went wrong',
      html: html || '',
      confirmButtonText: 'OK'
    });
  }

  function swalSuccessAndRedirect(message, url) {
    Toast.fire({ icon: 'success', title: message || 'Saved' }).then(() => {
      if (url) window.location.assign(url);
    });
  }

  function setFieldError(field, message) {
    const el = document.querySelector(`[data-error="${CSS.escape(field)}"]`);
    if (el) el.textContent = message || '';
  }

  function clearErrors() {
    document.querySelectorAll('[data-error]').forEach(e => e.textContent = '');
  }

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function syncParentLabel(id) {
    const match = clientTreeRows.find(row => String(row.id) === String(id || ''));
    parentCurrent.textContent = match ? `${match.name || `Client #${match.id}`}` : 'Self (Root)';
  }

  function normalizeNodes(rows) {
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

  function renderTreeNode(node) {
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
    radio.name = 'parentClientPick';
    radio.value = String(node.id);
    if (String(parentIdInput.value || '') === String(node.id)) radio.checked = true;

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
      node.children.forEach(child => children.appendChild(renderTreeNode(child)));
      toggle.addEventListener('click', () => {
        const open = children.style.display === 'block';
        children.style.display = open ? 'none' : 'block';
        toggle.classList.toggle('open', !open);
      });
    }

    radio.addEventListener('change', () => {
      selectedParentClient = { id: node.id, name: node.name };
    });

    return li;
  }

  function renderParentClientTree() {
    parentClientTree.innerHTML = '';

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
    rootRadio.name = 'parentClientPick';
    rootRadio.value = 'self';
    if (!parentIdInput.value) rootRadio.checked = true;

    const rootTitle = document.createElement('div');
    rootTitle.className = 'tree-title';
    rootTitle.innerHTML = '<strong>Self (Root)</strong><small>No parent client</small>';

    rootItem.appendChild(fakeToggle);
    rootItem.appendChild(rootRadio);
    rootItem.appendChild(rootTitle);
    rootLi.appendChild(rootItem);
    parentClientTree.appendChild(rootLi);

    rootRadio.addEventListener('change', () => {
      selectedParentClient = null;
    });

    normalizeNodes(clientTreeRows).forEach(node => parentClientTree.appendChild(renderTreeNode(node)));
  }

  async function ensureClientTreeRows() {
    if (clientTreeRows.length) return;
    parentClientLoading.style.display = 'block';
    try {
      const res = await fetch(`${API_BASE}/clients/all?sort=asc`, { headers });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || 'Failed to load clients');
      clientTreeRows = Array.isArray(data?.data) ? data.data : [];
    } finally {
      parentClientLoading.style.display = 'none';
    }
  }

  /* NEW: generic loading toggles */
  function setBtnLoading(btn, on=true, labelWhenOn='Saving...'){
    if(!btn) return;
    if(on){
      btn.dataset.prevHtml = btn.innerHTML;
      btn.innerHTML = `<span class="spinner" aria-hidden="true"></span><span>${labelWhenOn}</span>`;
      btn.disabled = true;
    }else{
      if(btn.dataset.prevHtml) btn.innerHTML = btn.dataset.prevHtml;
      btn.disabled = false;
    }
  }
  function setFormDisabled(form, disabled){
    if(!form) return;
    form.setAttribute('aria-busy', disabled ? 'true' : 'false');
    [...form.querySelectorAll('input,select,textarea,button')].forEach(el=>{
      if (el.id === 'saveClientBtn') return; // keep main toggle only from setBtnLoading
      el.disabled = !!disabled;
    });
  }

  // === Submit Form ===
  const form = document.getElementById('clientForm');
  const saveBtn = document.getElementById('saveClientBtn');

  btnPickParent?.addEventListener('click', async () => {
    try {
      selectedParentClient = parentIdInput.value
        ? { id: parentIdInput.value, name: parentCurrent.textContent }
        : null;
      await ensureClientTreeRows();
      renderParentClientTree();
      parentClientModal?.show();
    } catch (err) {
      await swalError('Unable to load clients', String(err.message || err));
    }
  });

  btnClearParent?.addEventListener('click', () => {
    parentIdInput.value = '';
    syncParentLabel('');
  });

  btnSaveParent?.addEventListener('click', () => {
    if (selectedParentClient && selectedParentClient.id !== 'self') {
      parentIdInput.value = String(selectedParentClient.id);
    } else {
      parentIdInput.value = '';
    }
    syncParentLabel(parentIdInput.value);
    parentClientModal?.hide();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();

    const fd = new FormData(form);

    try {
      setBtnLoading(saveBtn, true);
      setFormDisabled(form, true);

      const res = await fetch(`${API_BASE}/clients`, {
        method: 'POST',
        headers: headers,
        body: fd,
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        // Unauthorized / Forbidden
        if (res.status === 401 || res.status === 403) {
          const msg = (data && (data.message || data.error)) || 'Unauthorized Access';
          await swalError(
            'Unable to save client',
            `${msg}<br><small>(Check that your token is valid, belongs to an <b>admin</b>, and is the <b>plaintext</b> token.)</small>`
          );
        } else if (data && data.errors) {
          // Validation errors
          const list = Object.entries(data.errors).map(([f, msgs]) => {
            setFieldError(f, Array.isArray(msgs) ? msgs[0] : String(msgs));
            return `<li><b>${f}</b>: ${Array.isArray(msgs) ? msgs[0] : String(msgs)}</li>`;
          }).join('');
          await swalError('Please fix the highlighted fields', `<ul style="text-align:left;margin:0 6px">${list}</ul>`);
        } else {
          // Other errors
          const msg = (data && (data.message || data.error)) || `Request failed (${res.status})`;
          await swalError('Unable to save client', msg);
        }
        return;
      }

      swalSuccessAndRedirect('Client created successfully', MANAGE_URL);
    } catch (err) {
      console.error(err);
      await swalError('Network error', 'Please check your internet connection and try again.');
    } finally {
      setBtnLoading(saveBtn, false);
      setFormDisabled(form, false);
    }
  });

})();
</script>
@endpush
