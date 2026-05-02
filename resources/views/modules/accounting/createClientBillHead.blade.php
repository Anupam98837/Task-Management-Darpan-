@section('content')
<div class="container py-3">

  <!-- centered panel -->
  <div class="card doc-panel">
    <h2 class="doc-title">Add New Client Bill Head</h2>
    <p class="doc-sub">Create a reusable client bill head for future billing line items.</p>

    <form id="expenseHeadForm" autocomplete="off" novalidate>
      <!-- Title -->
      <div class="mb-3">
        <label for="title" class="field-label">Title <span class="text-danger">*</span></label>
        <input id="title" type="text" class="form-control" name="title"
               placeholder="Office Supplies" required>
        <div class="field-error small text-danger" id="err_title" style="display:none"></div>
      </div>

      <!-- Status -->
      <div class="mb-3">
        <label for="status" class="field-label">Status</label>
        <select id="status" class="form-select" name="status">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="draft" style="display:none">Draft</option>
        </select>
        <div class="field-error small text-danger" id="err_status" style="display:none"></div>
      </div>

      <!-- Dates -->
      <div class="row g-3" style="display:none">
        <div class="col-md-6">
          <label class="field-label">Created At</label>
          <div class="input-group date-input">
            <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
            <input type="text" class="form-control" id="created_at_display" placeholder="October 4th, 2025" readonly>
            <input type="date" class="d-none" name="created_at" id="created_at">
          </div>
        </div>

        <div class="col-md-6" >
          <label class="field-label">Updated At</label>
          <div class="input-group date-input">
            <span class="input-group-text" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
            <input type="text" class="form-control" id="updated_at_display" placeholder="October 4th, 2025" readonly>
            <input type="date" class="d-none" name="updated_at" id="updated_at">
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="actions">
        <a href="{{ url('admin/accounting/bill-heads/manage') }}" class="btn btn-secondary" style="display:none">Cancel</a>
        <button id="expenseHeadSubmit" type="submit" class="btn btn-primary">
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
    font-size: 24px;
    margin: 2px 0 6px;
    color: var(--text-color);
  }
  .doc-sub{font-size: 14px; margin: 0 0 18px; color: var(--muted-color); }
  .field-label{
    font-weight: 700; font-size: 12.5px; color: var(--muted-color);
    letter-spacing: .2px; margin-bottom: 6px;
  }
  .date-input .input-group-text{ border-radius: 8px; }
  .actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }

  .btn .spinner{
    display:inline-block; width:16px; height:16px; border-radius:50%;
    border:2px solid currentColor; border-right-color: transparent;
    animation: spin 0.8s linear infinite; vertical-align:-2px; margin-right:8px;
  }
  @keyframes spin { to { transform: rotate(360deg);} }

  .field-error { margin-top:6px; }

  /* minimal focus states for accessibility */
  input:focus, select:focus, textarea:focus { outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.08); border-color: #3b82f6; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  (function(){
    const API_BASE = '/api/client-bill-heads';
    const token = () => localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    const authHeaders = (extra = {}) => {
      const h = {'Content-Type':'application/json', 'Accept':'application/json'};
      const t = token(); if (t) h['Authorization'] = 'Bearer ' + t;
      return Object.assign(h, extra);
    };
    const toast = (icon, title, timer=1600) =>
      Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer, icon, title});

    // Human date formatting (like "October 4th, 2025")
    const ord = n => (n%10==1&&n%100!=11)?'st':(n%10==2&&n%100!=12)?'nd':(n%10==3&&n%100!=13)?'rd':'th';
    const fmtHuman = iso => {
      if (!iso) return '';
      // parse ISO yyyy-mm-dd into local date without timezone shift
      const parts = iso.split('-').map(Number);
      if (parts.length<3) return iso;
      const d = new Date(parts[0], parts[1]-1, parts[2]);
      const mo = d.toLocaleString('en-US', { month:'long' });
      const day = d.getDate();
      const yr  = d.getFullYear();
      return `${mo} ${day}${ord(day)}, ${yr}`;
    };
    const todayISO = () => new Date().toISOString().slice(0,10);

    function bindDatePair(hiddenId, displayId){
      const h = document.getElementById(hiddenId);
      const v = document.getElementById(displayId);
      if (!h || !v) return;
      v.addEventListener('click', () => h.showPicker ? h.showPicker() : h.focus());
      h.addEventListener('change', () => v.value = fmtHuman(h.value));
    }

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
        if (el.id === 'expenseHeadSubmit') return;
        el.disabled = !!disabled;
      });
    }

    // init dates
    (function init(){
      const created = document.getElementById('created_at');
      const updated = document.getElementById('updated_at');
      const createdV = document.getElementById('created_at_display');
      const updatedV = document.getElementById('updated_at_display');

      created.value = todayISO();
      updated.value = todayISO();
      if (createdV) createdV.value = fmtHuman(created.value);
      if (updatedV) updatedV.value = fmtHuman(updated.value);

      bindDatePair('created_at', 'created_at_display');
      bindDatePair('updated_at', 'updated_at_display');
    })();

    // Inline field error helper
    function showFieldErrors(errors = {}) {
      ['title','status','created_at','updated_at'].forEach(k => {
        const el = document.getElementById('err_' + k);
        if (!el) return;
        if (errors[k]) {
          el.style.display = 'block';
          el.textContent = [].concat(errors[k]).join(', ');
        } else {
          el.style.display = 'none';
          el.textContent = '';
        }
      });
    }

    document.getElementById('expenseHeadForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const f = e.currentTarget;
      const btn = document.getElementById('expenseHeadSubmit');

      // gather payload
      const payload = {
        title:       (f.title.value || '').trim(),
        status:      f.status.value || 'active',
        created_at:  f.created_at.value || null,
        updated_at:  f.updated_at.value || null,
      };

      if (!payload.title) {
        showFieldErrors({ title: ['Title is required'] });
        toast('warning', 'Enter a title');
        return;
      } else {
        showFieldErrors({});
      }

      try {
        setBtnLoading(btn, true);
        setFormDisabled(f, true);

        const res = await fetch(API_BASE, {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify(payload)
        });

        const text = await res.text();
        let body = {};
        try { body = text ? JSON.parse(text) : {}; } catch(_) { body = { message: text || res.statusText }; }

        if (res.status === 201 || res.ok) {
          toast('success', body.message || 'Client bill head created');
          // redirect to list (adjust URL if your resource list is under admin area)
          return setTimeout(() => location.href = "{{ url('admin/accounting/bill-heads/manage') }}", 400);
        }

        if (res.status === 422 && body.errors) {
          showFieldErrors(body.errors);
          toast('error', body.message || 'Validation failed');
          return;
        }

        if (res.status === 401) {
          toast('error', 'Session expired — please login again');
          return location.href = '/';
        }

        throw new Error(body.message || `Request failed (${res.status})`);
      } catch (err) {
        toast('error', err.message || 'Create failed');
        console.error(err);
      } finally {
        setBtnLoading(btn, false);
        setFormDisabled(f, false);
      }
    });
  })();
</script>
@endpush
