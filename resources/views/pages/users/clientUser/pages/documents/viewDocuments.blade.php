@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = 'client-user';
  $portalDashboardUrl = '/client-user/dashboard';
  $portalJobsUrl = '/client-user/jobs/view';
  $portalDocumentsUrl = '/client-user/documents';
  $portalNotificationsUrl = '/client-user/notifications';
  $portalLoginUrl = '/client-user/login';
  $portalLogoutApi = '/api/client-users/logout';
  $portalThemeKey = 'theme:client-user';
@endphp

@section('title', 'My Documents')

@push('styles')
<style>
  .docs-page{ padding: 18px 22px; }
  .docs-head{
    display:flex; justify-content:space-between; align-items:flex-end; gap:14px;
    flex-wrap:wrap; margin-bottom: 18px;
  }
  .docs-head h1{
    margin:0; font-size: 22px; font-weight: 800; letter-spacing: -.02em;
    color: var(--text-color); font-family: var(--font-head, 'Plus Jakarta Sans');
  }
  .docs-head .subtitle{ margin-top:4px; font-size: 12.5px; color: var(--muted-color); }
  .docs-toolbar{
    display:flex; gap:10px; flex-wrap:wrap; align-items:center;
    background: var(--surface, #fff); border: 1px solid var(--border-color);
    padding: 10px 12px; border-radius: 12px; box-shadow: var(--shadow-xs);
    margin-bottom: 14px;
  }
  .docs-toolbar .form-control, .docs-toolbar .form-select{
    height: 34px; font-size: 12.5px;
  }
  .picker-tree{list-style:none;margin:0;padding:0 0 0 8px;position:relative;}
  .picker-tree::before{content:"";position:absolute;left:14px;top:0;bottom:8px;width:1px;background:#e2e8f0;}
  .picker-tree>li{position:relative;margin:0 0 8px 0;padding-left:24px;}
  .picker-tree>li::before{content:"";position:absolute;left:14px;top:16px;width:16px;height:1px;background:#e2e8f0;}
  .picker-item{
    display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid var(--border-color);
    border-radius:12px;background:var(--surface,#fff);
  }
  .picker-toggle{
    width:28px;height:28px;border:none;border-radius:8px;background:#eef2ff;color:#4f46e5;
    display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;margin-top:1px;
  }
  .picker-toggle i{transition:transform .2s ease;}
  .picker-toggle.open i{transform:rotate(90deg);}
  .picker-title{display:flex;flex-direction:column;gap:2px;min-width:0;}
  .picker-title strong{font-size:13px;color:var(--text-color);}
  .picker-title small{font-size:11px;color:var(--muted-color);}
  .picker-children{display:none;margin-top:8px;}
  .docs-toolbar .stat-pill{
    margin-left: auto; display:inline-flex; align-items:center; gap:6px;
    padding: 5px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 700;
    color: var(--primary-color); background: rgba(3, 105, 161, .08);
    border: 1px solid rgba(3, 105, 161, .18); letter-spacing: .3px;
  }

  .docs-grid{
    display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
  }
  .doc-card{
    background: var(--surface, #fff); border: 1px solid var(--border-color);
    border-radius: 14px; padding: 14px; box-shadow: var(--shadow-xs);
    transition: var(--transition); display:flex; flex-direction:column; gap:10px;
    position: relative; overflow:hidden;
  }
  .doc-card::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width: 3px;
    background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
    opacity:.0; transition: opacity .18s var(--ease, ease);
  }
  .doc-card:hover{ box-shadow: var(--shadow-md); transform: translateY(-2px); border-color: rgba(3,105,161,.22); }
  .doc-card:hover::before{ opacity: 1; }

  .doc-row1{ display:flex; align-items:flex-start; gap: 11px; }
  .doc-icon{
    width: 38px; height: 38px; border-radius: 10px;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size: 14px; flex-shrink:0;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    box-shadow: 0 4px 10px rgba(3,105,161,.18);
  }
  .doc-name{
    font-weight: 700; color: var(--text-color); font-size: 13.5px; line-height: 1.35;
    word-break: break-word; letter-spacing: -.005em;
  }
  .doc-client{
    font-size: 11.5px; color: var(--muted-color); font-weight: 600; margin-top: 3px;
  }

  .doc-meta{ display:flex; flex-wrap:wrap; gap:5px; }
  .doc-meta .chip{
    font-size: 10.5px; padding: 3px 8px; border-radius: 999px;
    background: #f1f5f9; color: var(--text-color); font-weight: 600;
    border: 1px solid var(--border-color); letter-spacing: .2px;
  }
  .doc-meta .chip.type{ background: rgba(3, 105, 161, .08); color: var(--primary-color); border-color: rgba(3,105,161,.18); }
  .doc-meta .chip.status-active{ background: rgba(22, 163, 74, .10); color: #15803d; border-color: rgba(22,163,74,.22); }
  .doc-meta .chip.status-expired{ background: rgba(239, 68, 68, .10); color: #b91c1c; border-color: rgba(239,68,68,.22); }
  .doc-meta .chip.status-soon{ background: rgba(245, 158, 11, .10); color: #b45309; border-color: rgba(245,158,11,.25); }

  .doc-dates{
    display:flex; justify-content:space-between; align-items:center; gap:10px;
    font-size: 11.5px; color: var(--muted-color); margin-top: 2px;
  }
  .doc-dates b{ color: var(--text-color); font-weight: 600; }

  .doc-actions{ display:flex; gap:8px; margin-top: 4px; }
  .doc-actions .btn{
    flex:1; height: 32px; font-size: 11.5px; padding: 0 10px;
    display:inline-flex; align-items:center; justify-content:center; gap:6px;
  }

  .docs-empty{
    grid-column: 1 / -1; padding: 48px 18px; text-align:center;
    color: var(--muted-color); background: var(--surface,#fff);
    border: 1px dashed var(--border-color); border-radius: 14px;
  }
  .docs-empty i{ font-size: 28px; opacity: .55; margin-bottom: 8px; display:block; }
  .docs-empty .title{ font-weight: 700; color: var(--text-color); font-size: 13.5px; }
  .docs-empty .desc{ font-size: 12px; margin-top: 4px; }

  .docs-skeleton{
    height: 162px; border-radius: 14px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 37%, #f1f5f9 63%);
    background-size: 400% 100%; animation: skeleton 1.4s ease infinite;
    border: 1px solid var(--border-color);
  }
  @keyframes skeleton{ 0%{background-position:100% 50%;} 100%{background-position:0 50%;} }

  /* Dark */
  html.theme-dark .doc-card{ background: var(--surface); border-color: var(--border-color); }
  html.theme-dark .doc-meta .chip{ background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.06); color: var(--text-color); }
  html.theme-dark .docs-empty{ background: var(--surface); }
  html.theme-dark .docs-skeleton{ background: linear-gradient(90deg, #0f172a 25%, #1e293b 37%, #0f172a 63%); background-size:400% 100%; }
</style>
@endpush

@section('content')
<div class="docs-page">
  <div class="docs-head">
    <div>
      <h1><i class="fa-regular fa-folder-open me-2" style="color: var(--primary-color);"></i>My Documents</h1>
      <div class="subtitle" id="docsSubtitle">Loading documents from your assigned clients…</div>
    </div>
  </div>

  <div class="docs-toolbar">
    <div class="d-flex align-items-center gap-2" style="min-width: 240px; flex:1; max-width: 360px;">
      <i class="fa-solid fa-magnifying-glass text-muted small"></i>
      <input id="docSearch" type="search" class="form-control" placeholder="Search by document, client, type or authority…">
    </div>
    <div style="min-width:220px;max-width:260px;">
      <div class="d-flex gap-2">
        <button type="button" id="btnPickDocClient" class="btn btn-secondary" style="height:34px;padding:0 12px;min-width:0;">
          <i class="fa-regular fa-building"></i><span id="btnPickDocClientText">Choose Client</span>
        </button>
        <button type="button" id="clearDocClient" class="btn btn-secondary" style="height:34px;padding:0 10px;min-width:0;" title="Clear client filter">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <select id="docClientFilter" class="form-select" style="display:none">
        <option value="">All Clients</option>
      </select>
    </div>
    <select id="docStatusFilter" class="form-select" style="max-width: 160px;">
      <option value="">All Statuses</option>
      <option value="active">Active</option>
      <option value="expired">Expired</option>
      <option value="soon">Expiring Soon</option>
    </select>
    <span class="stat-pill" id="docStatPill"><i class="fa-regular fa-file-lines"></i><span id="docStatText">—</span></span>
  </div>

  <div id="docsGrid" class="docs-grid">
    <div class="docs-skeleton"></div>
    <div class="docs-skeleton"></div>
    <div class="docs-skeleton"></div>
    <div class="docs-skeleton"></div>
    <div class="docs-skeleton"></div>
    <div class="docs-skeleton"></div>
  </div>
</div>

<div class="modal fade" id="docClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fa-regular fa-building me-2"></i>Choose Client Filter</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="docClientLoad" class="text-muted small mb-2" style="display:none;">Loading clients…</div>
        <ul id="docClientTree" class="picker-tree"></ul>
        <div class="small text-muted">Selecting a parent client includes its child clients too.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="btnSaveDocClient"><i class="fa-solid fa-check me-1"></i>Apply Filter</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    setTimeout(() => { window.location.href = '/client-user/login'; }, 400);
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const fmt = (iso) => {
    if (!iso) return '—';
    const d = new Date(iso);
    return isNaN(d) ? '—' : d.toLocaleDateString(undefined, { day:'2-digit', month:'short', year:'numeric' });
  };
  const daysUntil = (iso) => {
    if (!iso) return null;
    const d = new Date(iso); if (isNaN(d)) return null;
    const ms = d - new Date(); return Math.ceil(ms / 86400000);
  };

  const els = {
    grid: document.getElementById('docsGrid'),
    subtitle: document.getElementById('docsSubtitle'),
    statPill: document.getElementById('docStatPill'),
    statText: document.getElementById('docStatText'),
    search: document.getElementById('docSearch'),
    clientFilter: document.getElementById('docClientFilter'),
    clientButtonText: document.getElementById('btnPickDocClientText'),
    statusFilter: document.getElementById('docStatusFilter'),
    clientTree: document.getElementById('docClientTree'),
    clientLoad: document.getElementById('docClientLoad'),
    btnPickClient: document.getElementById('btnPickDocClient'),
    btnSaveClient: document.getElementById('btnSaveDocClient'),
    clearClient: document.getElementById('clearDocClient'),
  };

  let allDocs = [];
  let clientRowsCache = [];
  let selectedClientNode = null;
  const docClientModal = window.bootstrap ? new bootstrap.Modal(document.getElementById('docClientModal')) : null;

  function statusBadgeFor(doc){
    const days = daysUntil(doc.expiry_date);
    if (doc.status && String(doc.status).toLowerCase() === 'expired') return { cls: 'status-expired', label: 'Expired' };
    if (days !== null && days < 0) return { cls: 'status-expired', label: 'Expired' };
    if (days !== null && days <= 30) return { cls: 'status-soon', label: `Expires in ${days}d` };
    return { cls: 'status-active', label: doc.status ? String(doc.status).replaceAll('_', ' ') : 'Active' };
  }

  function effectiveStatusKey(doc){
    const days = daysUntil(doc.expiry_date);
    if (doc.status && String(doc.status).toLowerCase() === 'expired') return 'expired';
    if (days !== null && days < 0) return 'expired';
    if (days !== null && days <= 30) return 'soon';
    return 'active';
  }

  function renderEmpty(reason){
    els.grid.innerHTML = `
      <div class="docs-empty">
        <i class="fa-regular fa-folder-open"></i>
        <div class="title">${reason || 'No documents found'}</div>
        <div class="desc">Documents from your assigned clients will appear here.</div>
      </div>`;
  }

  function render(){
    const q = (els.search.value || '').trim().toLowerCase();
    const clientFilter = els.clientFilter.value;
    const scopedClientIds = clientFilter ? getDescendantClientIds(clientFilter) : null;
    const statusFilter = els.statusFilter.value;

    const filtered = allDocs.filter(d => {
      if (scopedClientIds && !scopedClientIds.has(String(d.client_id))) return false;
      if (statusFilter && effectiveStatusKey(d) !== statusFilter) return false;
      if (!q) return true;
      const blob = `${d.doc_name||''} ${d.client_name||''} ${d.document_type_name||''} ${d.issuing_authority||''}`.toLowerCase();
      return blob.includes(q);
    });

    els.statText.textContent = `${filtered.length} of ${allDocs.length} documents`;

    if (!filtered.length) { renderEmpty(allDocs.length ? 'No documents match your filters' : 'No documents are visible for your scope'); return; }

    els.grid.innerHTML = filtered.map(doc => {
      const sb = statusBadgeFor(doc);
      const fileUrl = doc.file_url || '';
      return `
        <div class="doc-card">
          <div class="doc-row1">
            <div class="doc-icon"><i class="fa-regular fa-file-lines"></i></div>
            <div style="flex:1; min-width:0;">
              <div class="doc-name">${esc(doc.doc_name || '—')}</div>
              <div class="doc-client"><i class="fa-regular fa-building"></i> ${esc(doc.client_name || 'Unknown client')}</div>
            </div>
          </div>
          <div class="doc-meta">
            ${doc.document_type_name ? `<span class="chip type">${esc(doc.document_type_name)}</span>` : ''}
            <span class="chip ${sb.cls}">${esc(sb.label)}</span>
            ${doc.issuing_authority ? `<span class="chip">${esc(doc.issuing_authority)}</span>` : ''}
          </div>
          <div class="doc-dates">
            <span>Issued: <b>${fmt(doc.issue_date)}</b></span>
            <span>Expires: <b>${fmt(doc.expiry_date)}</b></span>
          </div>
          <div class="doc-actions">
            ${fileUrl
              ? `<a class="btn btn-primary" href="${esc(fileUrl)}" target="_blank" rel="noopener noreferrer">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
                 </a>`
              : `<button class="btn btn-light" disabled><i class="fa-regular fa-circle-xmark"></i> No file</button>`}
          </div>
        </div>`;
    }).join('');
  }

  function syncClientLabel(){
    els.clientButtonText.textContent = selectedClientNode?.title || 'Choose Client';
  }

  function toClientTree(rows){
    const map = new Map();
    rows.forEach(r => map.set(String(r.id), {
      id: r.id,
      title: String(r.name || (`Client #${r.id}`)).trim(),
      parent_id: r.parent_id || null,
      children: []
    }));
    const roots = [];
    rows.forEach(r => {
      const node = map.get(String(r.id));
      if (r.parent_id && map.has(String(r.parent_id))) {
        map.get(String(r.parent_id)).children.push(node);
      } else {
        roots.push(node);
      }
    });
    const sortRec = (arr) => { arr.sort((a,b)=>a.title.localeCompare(b.title)); arr.forEach(n=>sortRec(n.children)); };
    sortRec(roots);
    return roots;
  }

  function getDescendantClientIds(rootId){
    const wanted = new Set([String(rootId)]);
    let changed = true;
    while (changed) {
      changed = false;
      clientRowsCache.forEach(row => {
        const rowId = String(row.id);
        const parentId = row.parent_id ? String(row.parent_id) : '';
        if (!wanted.has(rowId) && parentId && wanted.has(parentId)) {
          wanted.add(rowId);
          changed = true;
        }
      });
    }
    return wanted;
  }

  function renderClientTree(nodes, container){
    container.innerHTML = '';

    const liRoot = document.createElement('li');
    const itemRoot = document.createElement('div'); itemRoot.className='picker-item';
    const fakeT = document.createElement('button'); fakeT.type='button'; fakeT.className='picker-toggle'; fakeT.style.visibility='hidden'; fakeT.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
    const radioRoot = document.createElement('input'); radioRoot.type='radio'; radioRoot.name='docClientPick'; radioRoot.value='';
    if (!els.clientFilter.value) radioRoot.checked = true;
    const titleRoot = document.createElement('div'); titleRoot.className='picker-title';
    titleRoot.innerHTML='<strong>All Clients</strong><small>Clear the client filter</small>';
    itemRoot.appendChild(fakeT); itemRoot.appendChild(radioRoot); itemRoot.appendChild(titleRoot);
    liRoot.appendChild(itemRoot); container.appendChild(liRoot);
    radioRoot.addEventListener('change', ()=>{ selectedClientNode = null; });

    nodes.forEach(node => container.appendChild(renderClientNode(node)));
  }

  function renderClientNode(node){
    const li = document.createElement('li');
    const item = document.createElement('div'); item.className='picker-item';
    const toggle = document.createElement('button'); toggle.type='button'; toggle.className='picker-toggle'; toggle.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
    if (!node.children || !node.children.length) toggle.style.visibility='hidden';
    const radio = document.createElement('input'); radio.type='radio'; radio.name='docClientPick'; radio.value=String(node.id);
    if (els.clientFilter.value && String(node.id) === String(els.clientFilter.value)) radio.checked = true;
    const title = document.createElement('div'); title.className='picker-title';
    title.innerHTML = `<strong>${esc(node.title)}</strong><small>#${node.id}${node.parent_id ? ' • child' : ''}</small>`;
    item.appendChild(toggle); item.appendChild(radio); item.appendChild(title); li.appendChild(item);

    const kids = document.createElement('ul'); kids.className='picker-children picker-tree'; li.appendChild(kids);
    if (node.children && node.children.length){
      node.children.forEach(ch => kids.appendChild(renderClientNode(ch)));
      if (radio.checked) {
        kids.style.display = 'block';
        toggle.classList.add('open');
      }
      toggle.addEventListener('click', ()=>{
        const open = kids.style.display === 'block';
        kids.style.display = open ? 'none' : 'block';
        toggle.classList.toggle('open', !open);
      });
    }

    radio.addEventListener('change', ()=>{
      selectedClientNode = { id: node.id, title: node.title };
      expandClientAncestors(radio);
    });
    return li;
  }

  function expandClientAncestors(radio){
    let node = radio.closest('li');
    while (node) {
      const tree = node.querySelector(':scope > .picker-children');
      const toggle = node.querySelector(':scope > .picker-item .picker-toggle');
      if (tree && toggle && toggle.style.visibility !== 'hidden') {
        tree.style.display = 'block';
        toggle.classList.add('open');
      }
      node = node.parentElement?.closest('li');
    }
  }

  async function loadClientTree(){
    els.clientLoad.style.display = 'block';
    try {
      const res = await fetch('/api/clients?per_page=500&status=active', { headers });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || ('HTTP ' + res.status));
      clientRowsCache = Array.isArray(data?.data) ? data.data : [];
      const opts = ['<option value="">All Clients</option>']
        .concat(clientRowsCache.map(c => `<option value="${c.id}">${esc(c.name || ('Client #'+c.id))}</option>`));
      els.clientFilter.innerHTML = opts.join('');
      renderClientTree(toClientTree(clientRowsCache), els.clientTree);
      syncClientLabel();
    } finally {
      els.clientLoad.style.display = 'none';
    }
  }

  async function load(){
    try{
      const res = await fetch('/api/client-users/documents?limit=200', { headers });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.message || ('HTTP ' + res.status));
      allDocs = Array.isArray(data?.data) ? data.data : [];
      els.subtitle.textContent = allDocs.length
        ? `Showing all ${allDocs.length} documents from your assigned clients (view-only).`
        : 'No documents are visible for your client scope yet.';
      await loadClientTree();
      render();
    }catch(err){
      console.error('[client-user docs] failed', err);
      els.subtitle.textContent = 'Failed to load documents.';
      renderEmpty('Could not load documents');
    }
  }

  els.search.addEventListener('input', render);
  els.clientFilter.addEventListener('change', render);
  els.statusFilter.addEventListener('change', render);
  els.btnPickClient?.addEventListener('click', async () => {
    if (!clientRowsCache.length) await loadClientTree().catch(() => {});
    renderClientTree(toClientTree(clientRowsCache), els.clientTree);
    docClientModal?.show();
  });
  els.btnSaveClient?.addEventListener('click', () => {
    els.clientFilter.value = selectedClientNode ? String(selectedClientNode.id) : '';
    syncClientLabel();
    render();
    docClientModal?.hide();
  });
  els.clearClient?.addEventListener('click', () => {
    selectedClientNode = null;
    els.clientFilter.value = '';
    syncClientLabel();
    render();
  });

  load();
})();
</script>
@endpush
