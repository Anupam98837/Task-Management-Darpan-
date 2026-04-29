{{-- resources/views/modules/document-types/edit.blade.php --}}

@section('content')
<div class="page-head" id="editPage" data-id="{{ $id ?? request()->route('id') }}">
  <div class="page-indicator">
    <i class="fa-regular fa-pen-to-square"></i>
    <strong>Edit Document Details</strong>
  </div>
  <p class="text-muted">Update the fields below and save your changes.</p>
</div>

<form id="docTypeForm" class="card" style="max-width:900px;padding:16px;display:none">
  <div class="mb-3">
    <label class="form-label">Document Name</label>
    <input type="text" class="form-control" name="name" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Internal Notes</label>
    <textarea class="form-control" name="note" rows="2"></textarea>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Created At</label>
      <input type="date" class="form-control" name="created_at">
    </div>
    <div class="col-md-4">
      <label class="form-label">Updated At</label>
      <input type="date" class="form-control" name="updated_at">
    </div>
  </div>

  <div style="display:flex;gap:10px;justify-content:space-between;margin-top:16px">
    <a href="{{ url('/admin/document-types') }}" class="btn btn-light">Cancel</a>
    <div style="display:flex;gap:8px">
      <button id="btnDelete" type="button" class="btn btn-outline-danger">Delete</button>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </div>
</form>

<div id="loadingNote" class="muted">Loading...</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '/api/doctypes';
function token(){ return (localStorage.getItem('token') || sessionStorage.getItem('token') || ''); }
function authHeaders(extra={}){ const h={'Content-Type':'application/json'}; const t=token(); if(t) h['Authorization']='Bearer '+t; return Object.assign(h, extra); }
function toast(icon, title, timer=1600){ return Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer,icon,title}); }
function fmtDate(d){ if(!d) return ''; const dt=new Date(d); return isNaN(dt)?d:dt.toISOString().slice(0,10); }

const root = document.getElementById('editPage');
const id = root.getAttribute('data-id');
const form = document.getElementById('docTypeForm');
const loading = document.getElementById('loadingNote');

if(!id){
  loading.textContent = 'Missing ID in route.'; // developer hint
}else{
  load();
}

async function load(){
  try{
    const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}`, { headers: authHeaders() });
    if(!res.ok) throw new Error('Load failed');
    const row = await res.json();
    // Normalize shapes:
    const data = row?.data ?? row;

    form.name.value = data.name ?? data.type_name ?? data.title ?? '';
    form.description.value = data.description ?? data.desc ?? '';
    form.note.value = data.note ?? data.notes ?? data.internal_notes ?? '';
    const status = (data.status ?? (data.is_active ? 'active' : 'inactive')) || 'draft';
    form.status.value = status.toLowerCase();
    form.created_at.value = fmtDate(data.created_at ?? data.createdAt);
    form.updated_at.value = fmtDate(data.updated_at ?? data.updatedAt);

    loading.style.display = 'none';
    form.style.display = 'block';
  }catch(err){
    loading.textContent = err.message || 'Failed to load';
  }
}

form.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(form);
  const payload = {
    name: fd.get('name')?.trim(),
    description: fd.get('description')?.trim(),
    note: fd.get('note')?.trim(),
    status: fd.get('status'),
    created_at: fd.get('created_at') || null,
    updated_at: fd.get('updated_at') || null,
    is_active: (fd.get('status') === 'active')
  };
  try{
    const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
      method:'PUT', headers: authHeaders(), body: JSON.stringify(payload)
    });
    if(!res.ok){
      const t = await res.text(); throw new Error(t || 'Update failed');
    }
    toast('success','Saved');
    setTimeout(()=> location.href = "{{ url('/admin/document-types') }}", 400);
  }catch(err){
    toast('error', err.message || 'Update failed');
  }
});

document.getElementById('btnDelete').addEventListener('click', async ()=>{
  const ask = await Swal.fire({
    title:'Delete document type?', text:'This cannot be undone.',
    icon:'warning', showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#d33'
  });
  if(!ask.isConfirmed) return;
  try{
    const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
      method:'DELETE', headers: authHeaders()
    });
    if(!res.ok) throw new Error('Delete failed');
    toast('success','Deleted');
    setTimeout(()=> location.href = "{{ url('/admin/document-types') }}", 350);
  }catch(err){
    toast('error', err.message || 'Delete failed');
  }
});
</script>
@endpush
