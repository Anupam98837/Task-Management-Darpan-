@push('styles')
<style>
  .main-content { padding:0; }
  /* page shell */
  .doc-upload-wrap{
    min-height: calc(100vh - 140px);
    display:flex;align-items:flex-start;justify-content:center;
     background: var(--bg-body);padding:32px 14px
  }
  .doc-card{
    width:100%;max-width:720px;background:var(--bg-body);border:1px solid #e6ebf0;
    border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.06);
    padding:26px
  }
  .doc-title{font-size:24px;font-weight:700;color:var(--text-color);text-align:center;margin-bottom:6px}
  .doc-sub{font-size:14px;color:#6b7280;text-align:center;margin-bottom:20px}
 
  /* form */
  .grid{display:grid;gap:14px}
  .g-2{grid-template-columns:1fr 1fr}
  @media (max-width:680px){ .g-2{grid-template-columns:1fr} }
 
  .label{font-size:13px;font-weight:600;color:var(--muted-color);margin-bottom:6px}
  .control{width:100%;height:40px;border:1px solid #d9dee5;border-radius:8px;
           padding:8px 12px;font-size:14px;color:var(--muted-color);background:var(--bg-body);}
  .control:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
  .muted{font-size:12px;color:#6b7280;margin-top:4px}
 
  /* dropzone */
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
 
  /* button */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    border-radius:10px;height:42px;padding:0 16px;border:1px solid transparent;
    font-weight:600
  }
  .btn-primary{background:#1d4ed8;color:#fff}
  .btn-primary:disabled{opacity:.65;cursor:not-allowed}
  .btn-outline{background:#fff;border-color:#d9dee5;color:#0f172a}
 
  .footer{margin-top:18px;display:flex;justify-content:flex-end}

  select {background: var(--bg-body);}
  label {}

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
<div class="doc-upload-wrap">
  <div class="doc-card">
    <div class="doc-title">Upload New Document</div>
    <div class="doc-sub">Fill in the details and upload your document below.</div>
 
    <form id="docForm" class="grid" autocomplete="off">
      <div>
        <div class="label">Client <span class="text-danger">*</span></div>
        <select id="client_id" class="control" required>
          <option value="">Select client</option>
        </select>
      </div>
 
      <div>
        <div class="label">Document Type <span class="text-danger">*</span></div>
        <select id="document_type_id" class="control" required>
          <option value="">Select type</option>
        </select>
      </div>
 
      <div class="g-2">
        <div>
          <div class="label">Issue Date <span class="text-danger">*</span></div>
          <input id="issue_date" type="date" class="control">
        </div>
        <div>
          <div class="label">Expiry Date <span class="text-danger">*</span></div>
          <input id="expiry_date" type="date" class="control">
        </div>
      </div>
 
      <div>
        <div class="label">Issuing Authority <span class="text-danger">*</span></div>
        <input id="issuing_authority" type="text" class="control" placeholder="Head Office" required>
      </div>
 
      <div>
        <div class="label">Document File <span class="text-danger">*</span></div>
        <div id="dropzone" class="dropzone">
          <div class="cloud"><i class="fa-regular fa-cloud-arrow-up"></i></div>
          <div>Drag &amp; drop your file here, or click to browse</div>
          <button type="button" id="browseBtn" class="btn btn-outline">Choose File</button>
          <input id="fileInput" type="file" style="display:none" />
          <div id="fileBadge" class="file-pill" style="display:none">
            <i class="fa-regular fa-file"></i><span id="fileName"></span>
          </div>
          <div class="muted" id="uploadHint">No file uploaded yet.</div>
        </div>
      </div>
 
      <input type="hidden" id="file_url"> {{-- will hold /uploads/documents/xyz.pdf --}}
 
      <div class="footer">
        <button id="submitBtn" type="submit" class="btn btn-primary" disabled>
          <span class="btn-label">Upload Document</span>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
 
@push('scripts')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
<script>
(function(){
  const token = (localStorage.getItem('token') || sessionStorage.getItem('token') || '').trim();
  // redirect target after success
  const REDIRECT_AFTER_SUCCESS = "{{ url('/admin/documents') }}";

  const els = {
    client:  document.getElementById('client_id'),
    dtype:   document.getElementById('document_type_id'),
    issue:   document.getElementById('issue_date'),
    expiry:  document.getElementById('expiry_date'),
    auth:    document.getElementById('issuing_authority'),
    drop:    document.getElementById('dropzone'),
    browse:  document.getElementById('browseBtn'),
    input:   document.getElementById('fileInput'),
    badge:   document.getElementById('fileBadge'),
    fname:   document.getElementById('fileName'),
    hint:    document.getElementById('uploadHint'),
    furl:    document.getElementById('file_url'),
    submit:  document.getElementById('submitBtn'),
    form:    document.getElementById('docForm'),
  };
 
  // Optional: restrict file types
  els.input.setAttribute('accept', '.pdf,.doc,.docx,.png,.jpg,.jpeg');
 
  /* ---------------- helpers ---------------- */
  function yn(b){ els.submit.disabled = !b; }
  function setBadge(name){ els.badge.style.display='inline-flex'; els.fname.textContent = name; }
  function clearBadge(){ els.badge.style.display='none'; els.fname.textContent=''; }
  function iso(d){ return d || ''; }
 
  async function jfetch(url, opt={}){
    const headers = {...(opt.headers||{})};
    if (token) headers.Authorization = 'Bearer ' + token;
    const res = await fetch(url, {...opt, headers});
    const json = await res.json().catch(()=> ({}));
    if(!res.ok) throw new Error(json?.message || `HTTP ${res.status}`);
    return json;
  }
 
  function okStatus(resp){ return resp?.status === true || resp?.status === 'success'; }
  function pickList(resp){ let list = resp?.data ?? resp?.items ?? resp?.rows ?? resp; return Array.isArray(list) ? list : []; }
 
  function fillSelect(el, rows, placeholder){
    el.innerHTML = `<option value="">${placeholder}</option>`;
    rows.forEach(it=>{
      const id   = it.id ?? it.client_id ?? it.value ?? null;
      const name = it.name ?? it.client_name ?? it.text ?? it.title ?? '';
      if(id && name){
        const o = document.createElement('option');
        o.value = id; o.textContent = name;
        el.appendChild(o);
      }
    });
  }

  /* NEW: loader helpers for save step */
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
      if (el.id === 'submitBtn') return;
      el.disabled = !!disabled;
    });
  }
 
  /* ---------------- hydrate selects ---------------- */
  (async function hydrate(){
    fillSelect(els.client, [], 'Loading clients…');
    fillSelect(els.dtype,  [], 'Loading types…');
 
    try{
      let resp = await jfetch('/api/clients?per_page=1000&status=active&sort=asc');
      if(!okStatus(resp)) throw new Error('Bad status');
      let rows = pickList(resp);
      if(!rows.length){
        resp = await jfetch('/api/clients/all?status=active&sort=asc');
        rows = pickList(resp);
      }
      fillSelect(els.client, rows, 'Select client');
    }catch(e){
      console.error('Clients load failed:', e.message);
      fillSelect(els.client, [], '⚠ Unable to load clients');
      Swal.fire({icon:'error', title:'Clients not loaded', text:e.message});
    }
 
    // Document types (correct endpoint)
    try{
      const resp = await jfetch('/api/doctypes?per_page=1000&status=active&sort_by=name&sort_dir=asc');
      if(!okStatus(resp)) throw new Error('Bad status');
      const rows = pickList(resp);
      fillSelect(els.dtype, rows, 'Select type');
    }catch(e){
      console.error('Doc types load failed:', e.message);
      fillSelect(els.dtype, [], '⚠ Unable to load types');
      Swal.fire({icon:'error', title:'Document types not loaded', text:e.message});
    }
  })();
 
/* ---------------- drag & drop upload ---------------- */
['dragenter','dragover'].forEach(ev => els.drop.addEventListener(ev, e=>{
  e.preventDefault(); e.stopPropagation(); els.drop.classList.add('drag');
}));
['dragleave','drop'].forEach(ev => els.drop.addEventListener(ev, e=>{
  e.preventDefault(); e.stopPropagation(); els.drop.classList.remove('drag');
}));

// FIX: Prevent event bubbling from browse button to dropzone
els.browse.addEventListener('click', (e)=> {
  e.stopPropagation(); // Prevent event from bubbling to dropzone
  els.input.click();
});

// Only dropzone should trigger file input, not the browse button
els.drop.addEventListener('click', (e)=> {
  // Only trigger if the click is directly on the dropzone, not on child elements
  if (e.target === els.drop) {
    els.input.click();
  }
});

els.drop.addEventListener('drop', e=>{
  if (e.dataTransfer?.files?.length) handleFile(e.dataTransfer.files[0]);
});
els.input.addEventListener('change', e=>{
  if (e.target.files?.[0]) handleFile(e.target.files[0]);
});
 
  // Robust upload URL attempts (covers inside/outside prefix)
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
          headers: token ? { Authorization:'Bearer '+token } : {},
          body: fd
        });
        let payload = {};
        try { payload = await res.clone().json(); } catch(_) { payload = { message: await res.text() }; }
        if (!res.ok || payload?.status === false) throw new Error(payload?.message || `HTTP ${res.status}`);
        return payload; // success
      }catch(err){ lastErr = err; }
    }
    throw lastErr || new Error('Upload failed');
  }
 
  // --- Upload to backend
  async function handleFile(file){
    clearBadge(); yn(false);
 
    const maxMb = 20;
    if (file.size > maxMb * 1024 * 1024) {
      els.hint.textContent = `File too large (>${maxMb}MB).`;
      Swal.fire({icon:'warning', title:'File too large', text:`Please upload a file ≤ ${maxMb}MB.`});
      return;
    }
 
    els.hint.textContent = 'Uploading…';
    try{
      const fd = new FormData();
      fd.append('file', file);
      // fd.append('folder', 'documents'); // optional
 
      const payload = await tryUpload(fd);
 
      // store returned path/url based on your API response shape
      // attempt common property names, fall back to payload.path
      const returnedPath = payload.path || payload.url || payload.file?.path || payload.data?.path || '';
      els.furl.value = returnedPath;
      setBadge(file.name);
      els.hint.textContent = 'Uploaded to: ' + returnedPath;
 
      // Toast success
      Swal.fire({
        toast:true, position:'top-end', timer:2000, showConfirmButton:false,
        icon:'success', title:'File uploaded'
      });
 
      yn(true);
    }catch(err){
      console.error('Upload failed:', err);
      els.hint.textContent = 'Upload failed: ' + err.message;
      els.furl.value = '';
      yn(false);
      Swal.fire({icon:'error', title:'Upload failed', text: err.message});
    }
  }
 
  /* ---------------- submit -> /api/documents ---------------- */
  els.form.addEventListener('submit', async function(e){
    e.preventDefault();
    yn(false);
 
    const payload = {
      client_id:        els.client.value || null,
      document_type_id: els.dtype.value,
      doc_name:         (els.dtype.options[els.dtype.selectedIndex]?.text || 'Document'),
      issue_date:       iso(els.issue.value) || null,
      expiry_date:      iso(els.expiry.value) || null,
      issuing_authority:els.auth.value || null,
      file_url:         els.furl.value,
      status:           'active'
    };
 
    if(!payload.document_type_id){
      yn(true);
      return Swal.fire({icon:'warning', title:'Select a document type'});
    }
    if(!payload.file_url){
      yn(true);
      return Swal.fire({icon:'warning', title:'Please upload a file first'});
    }
 
    try{
      /* NEW: show saving loader in button and lock form */
      setBtnLoading(els.submit, true, 'Saving...');
      setFormDisabled(els.form, true);

      const r = await fetch('/api/documents', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          ...(token ? { Authorization:'Bearer '+token } : {})
        },
        body: JSON.stringify(payload)
      });
      const j = await r.json().catch(()=> ({}));
      if(!r.ok || !j?.status) throw new Error(j?.message || 'Save failed');
 
      // show toast then redirect to manage documents
      await Swal.fire({
        icon:'success',
        title:'Document created',
        timer: 1200,
        showConfirmButton: false
      });

      // reset UI before redirect so the page is clean if redirect is blocked
      els.form.reset();
      clearBadge();
      els.hint.textContent = 'No file uploaded yet.';
      els.furl.value = '';
      yn(false);

      // navigate to Manage Documents page
      setTimeout(() => {
        window.location.href = REDIRECT_AFTER_SUCCESS;
      }, 100);

    }catch(err){
      console.error(err);
      yn(true);
      Swal.fire({icon:'error', title:'Could not save document', text: err.message});
    } finally {
      /* NEW: stop loader & unlock form (in case we stay on page) */
      setBtnLoading(els.submit, false);
      setFormDisabled(els.form, false);
    }
  });

  /* NEW: helper implementations (placed at end to keep file minimal) */
  function setBtnLoading(btn, on=true, labelWhenOn='Saving...'){
    if(!btn) return;
    if(on){
      btn.dataset.prevHtml = btn.innerHTML;
      btn.innerHTML = `<span class="spinner" aria-hidden="true"></span><span>${labelWhenOn}</span>`;
      btn.disabled = true;
    }else{
      if(btn.dataset.prevHtml) btn.innerHTML = btn.dataset.prevHtml;
      // do not force-enable if form-level logic disables it
    }
  }
  function setFormDisabled(form, disabled){
    if(!form) return;
    form.setAttribute('aria-busy', disabled ? 'true' : 'false');
    [...form.querySelectorAll('input,select,textarea,button')].forEach(el=>{
      if (el.id === 'submitBtn') return;
      el.disabled = !!disabled;
    });
  }
})();
</script>
@endpush
