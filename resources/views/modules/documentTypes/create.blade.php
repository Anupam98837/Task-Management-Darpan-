{{-- resources/views/modules/document-types/create.blade.php --}}
@section('content')
<div class="container py-3">

  <!-- centered panel -->
  <div class="card doc-panel">
    <h2 class="doc-title">Add New Document Type</h2>
    <p class="doc-sub">Fill in the fields below to add a new document to the system.</p>

    <form id="docTypeForm">
      <!-- Document Name -->
      <div class="mb-3">
        <label class="field-label">Document Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name"
               placeholder="Quarterly Financial Report Q3 2024" required>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label class="field-label">Description</label>
        <textarea class="form-control" name="description" rows="3"
                  placeholder="Detailed report summarizing the financial performance for the period..."></textarea>
      </div>

      <!-- Internal Notes -->
      <div class="mb-3">
        <label class="field-label">Internal Notes</label>
        <textarea class="form-control" name="note" rows="2"
                  placeholder="Requires review by Finance Director before approval."></textarea>
      </div>

      <!-- Status + Dates -->
      <div class="row g-3">
        <div class="col-md-4">
          <label class="field-label">Status</label>
          <select class="form-select" name="status" id="status">
            <!-- Per your data model: only Active / Inactive / Archived -->
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="field-label">Created At</label>
          <div class="input-group date-input">
            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
            <input type="text" class="form-control" id="created_at_display" placeholder="October 4th, 2025" readonly>
            <input type="date" class="d-none" name="created_at" id="created_at">
          </div>
        </div>

        <div class="col-md-4">
          <label class="field-label">Updated At</label>
          <div class="input-group date-input">
            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
            <input type="text" class="form-control" id="updated_at_display" placeholder="October 4th, 2025" readonly>
            <input type="date" class="d-none" name="updated_at" id="updated_at">
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="actions">
        <button id="docTypeSubmit" type="submit" class="btn btn-primary">
          <span class="btn-label">Submit</span>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
  .main-content { background: var(--bg-body); }
  /* Uses only your tokens from main.css; no hard colors */
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
  }
  .doc-sub{ margin: 0 0 18px; color: var(--muted-color); }
  .field-label{
    font-weight: 700; font-size: 12.5px; color: var(--muted-color);
    letter-spacing: .2px; margin-bottom: 6px;
  }
  .date-input .input-group-text{ border-radius: 8px; }
  .actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }

  /* NEW: inline button spinner */
  .btn .spinner{
    display:inline-block; width:16px; height:16px; border-radius:50%;
    border:2px solid currentColor; border-right-color: transparent;
    animation: spin 0.8s linear infinite; vertical-align:-2px; margin-right:8px;
  }
  @keyframes spin { to { transform: rotate(360deg);} }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const API_BASE = '/api/doctypes';

  // ---- helpers (auth + UI) ----
  const token = () => localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const authHeaders = (extra = {}) => {
    const h = {'Content-Type':'application/json'};
    const t = token(); if (t) h['Authorization'] = 'Bearer ' + t;
    return Object.assign(h, extra);
  };
  const toast = (icon, title, timer=1600) =>
    Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer, icon, title});

  // date helpers to mimic "October 4th, 2025"
  const ord = n => (n%10==1&&n%100!=11)?'st':(n%10==2&&n%100!=12)?'nd':(n%10==3&&n%100!=13)?'rd':'th';
  const fmtHuman = iso => {
    if (!iso) return '';
    const d = new Date(iso + 'T00:00:00');
    const mo = d.toLocaleString('en-US', { month:'long' });
    const day = d.getDate();
    const yr  = d.getFullYear();
    return `${mo} ${day}${ord(day)}, ${yr}`;
  };
  const todayISO = () => new Date().toISOString().slice(0,10);

  function bindDatePair(hiddenId, displayId){
    const h = document.getElementById(hiddenId);
    const v = document.getElementById(displayId);
    v.addEventListener('click', () => h.showPicker ? h.showPicker() : h.focus());
    h.addEventListener('change', () => v.value = fmtHuman(h.value));
  }

  /* NEW: loader helpers */
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
      if (el.id === 'docTypeSubmit') return;
      el.disabled = !!disabled;
    });
  }

  // init
  (function init(){
    const created = document.getElementById('created_at');
    const updated = document.getElementById('updated_at');
    const createdV = document.getElementById('created_at_display');
    const updatedV = document.getElementById('updated_at_display');

    created.value = todayISO();
    updated.value = todayISO();
    createdV.value = fmtHuman(created.value);
    updatedV.value = fmtHuman(updated.value);

    bindDatePair('created_at', 'created_at_display');
    bindDatePair('updated_at', 'updated_at_display');
  })();

  // submit
  document.getElementById('docTypeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.currentTarget;
    const btn = document.getElementById('docTypeSubmit');

    const payload = {
      name:        f.name.value?.trim(),
      description: f.description.value?.trim(),
      note:        f.note.value?.trim(),
      status:      f.status.value,                // 'active' | 'inactive' | 'archived'
      created_at:  f.created_at.value || null,    // ISO yyyy-mm-dd
      updated_at:  f.updated_at.value || null,
      is_active:   f.status.value === 'active'
    };

    if (!payload.name) {
      toast('warning', 'Enter a document name');
      return;
    }

    try{
      setBtnLoading(btn, true);
      setFormDisabled(f, true);

      const res = await fetch(API_BASE, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        let msg = 'Create failed';
        try { msg = (await res.json()).message || msg; } catch {}
        throw new Error(msg);
      }
      toast('success', 'Document type created');
      setTimeout(() => location.href = "{{ url('/admin/document-types') }}", 400);
    } catch(err){
      toast('error', err.message || 'Create failed');
    } finally {
      setBtnLoading(btn, false);
      setFormDisabled(f, false);
    }
  });
</script>
@endpush
