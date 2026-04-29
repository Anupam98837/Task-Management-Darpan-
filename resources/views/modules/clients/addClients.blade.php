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
