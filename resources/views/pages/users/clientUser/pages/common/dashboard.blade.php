@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = $portalPrefix ?? 'client-user';
  $portalDashboardUrl = $portalDashboardUrl ?? '/client-user/dashboard';
  $portalJobsUrl = $portalJobsUrl ?? '/client-user/jobs/view';
  $portalBillsUrl = $portalBillsUrl ?? '/client-user/bills';
  $portalRepaymentsUrl = $portalRepaymentsUrl ?? '/client-user/repayments';
  $portalNotificationsUrl = $portalNotificationsUrl ?? '/client-user/notifications';
  $portalLoginUrl = $portalLoginUrl ?? '/client-user/login';
  $portalLogoutApi = $portalLogoutApi ?? '/api/client-users/logout';
  $portalThemeKey = $portalThemeKey ?? 'theme:client-user';
  $dashboardMeApi = $dashboardMeApi ?? '/api/client-users/me';
  $dashboardApi = $dashboardApi ?? '/api/client-users/dashboard';
  $dashboardTitle = $dashboardTitle ?? 'Client Dashboard';
  $dashboardPrimaryLabel = $dashboardPrimaryLabel ?? 'View Jobs';
  $dashboardPrimaryUrl = $dashboardPrimaryUrl ?? $portalJobsUrl;
  $dashboardSecondaryLabel = $dashboardSecondaryLabel ?? 'View Bills';
  $dashboardSecondaryUrl = $dashboardSecondaryUrl ?? $portalBillsUrl;
  $dashboardTertiaryLabel = $dashboardTertiaryLabel ?? 'Notifications';
  $dashboardTertiaryUrl = $dashboardTertiaryUrl ?? $portalNotificationsUrl;
  $dashboardBadge = $dashboardBadge ?? 'Client Portal';
@endphp

@section('title', $dashboardTitle)

@push('styles')
<style>
.dashboard-page {
  --dash-brand:#0369a1;
  --dash-brand-dark:#082d58;
  --dash-brand-mid:#0ea5e9;
  --dash-brand-pale:#e0f2fe;
  --dash-border:#dbeafe;
  --dash-card:#ffffff;
  --dash-shadow:0 18px 36px rgba(8,45,88,.08);
}
.dashboard-page {
  background:
    radial-gradient(circle at top left, rgba(14,165,233,.12), transparent 25%),
    radial-gradient(circle at top right, rgba(59,130,246,.08), transparent 22%),
    radial-gradient(circle at bottom right, rgba(3,105,161,.08), transparent 26%),
    var(--bg-body);
  min-height:100vh;
  padding:24px;
  font-family:var(--font-sans);
}
.page-header {
  display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;
  padding:24px;border-radius:28px;background:linear-gradient(145deg,#061c3a 0%,#082d58 42%,#0a4480 100%);
  border:1px solid rgba(147,197,253,.22);box-shadow:0 26px 46px rgba(8,45,88,.18);position:relative;overflow:hidden;
}
.page-header::before,.page-header::after{content:'';position:absolute;border-radius:50%;pointer-events:none;filter:blur(2px);}
.page-header::before{width:220px;height:220px;right:-70px;top:-90px;background:radial-gradient(circle,rgba(56,189,248,.22),transparent 68%);}
.page-header::after{width:180px;height:180px;left:-40px;bottom:-70px;background:radial-gradient(circle,rgba(14,165,233,.14),transparent 70%);}
.page-header > * { position:relative; z-index:1; }
.hero-badge {
  display:inline-flex;align-items:center;gap:6px;background:rgba(224,242,254,.12);color:#bae6fd;border:1px solid rgba(186,230,253,.22);
  border-radius:999px;padding:5px 12px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.45px;margin-bottom:14px;
}
.page-header h1 {margin:0;font-size:32px;font-weight:800;color:#fff;letter-spacing:-.35px;}
.page-header p {margin:8px 0 0;color:rgba(255,255,255,.72);font-size:14px;max-width:620px;}
.header-actions {display:flex;gap:10px;flex-wrap:wrap;}
.kpi-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:24px;}
.kpi-card {
  background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(240,249,255,.96));
  border:1px solid var(--dash-border);
  border-radius:22px;
  padding:18px;
  box-shadow:var(--dash-shadow);
  position:relative;
  overflow:hidden;
}
.kpi-card::before {
  content:'';
  position:absolute;
  inset:auto -18px -30px auto;
  width:110px;
  height:110px;
  border-radius:50%;
  opacity:.18;
}
.kpi-card.clients::before { background:linear-gradient(135deg,#0ea5e9,#38bdf8); }
.kpi-card.jobs::before { background:linear-gradient(135deg,#0369a1,#2563eb); }
.kpi-card.documents::before { background:linear-gradient(135deg,#1d4ed8,#38bdf8); }
.kpi-card.due::before { background:linear-gradient(135deg,#0284c7,#0ea5e9); }
.kpi-card.overdue::before { background:linear-gradient(135deg,#0f766e,#0891b2); }
.kpi-card.completed::before { background:linear-gradient(135deg,#082d58,#0369a1); }
.kpi-top {display:flex;align-items:center;justify-content:space-between;gap:14px;position:relative;z-index:1;}
.kpi-icon {
  width:48px;height:48px;border-radius:15px;display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:18px;box-shadow:0 12px 24px rgba(8,45,88,.14);
}
.kpi-card.clients .kpi-icon { background:linear-gradient(135deg,#0ea5e9,#38bdf8); }
.kpi-card.jobs .kpi-icon { background:linear-gradient(135deg,#0369a1,#2563eb); }
.kpi-card.documents .kpi-icon { background:linear-gradient(135deg,#1d4ed8,#38bdf8); }
.kpi-card.due .kpi-icon { background:linear-gradient(135deg,#0284c7,#0ea5e9); }
.kpi-card.overdue .kpi-icon { background:linear-gradient(135deg,#0f766e,#0891b2); }
.kpi-card.completed .kpi-icon { background:linear-gradient(135deg,#082d58,#0369a1); }
.kpi-label {font-size:11px;color:#5b6b82;font-weight:800;text-transform:uppercase;letter-spacing:.65px;}
.kpi-value {font-size:30px;font-weight:800;color:var(--text-color);margin-top:14px;position:relative;z-index:1;}
.section {
  background:rgba(255,255,255,.95);
  border:1px solid var(--dash-border);
  border-radius:22px;
  padding:22px;
  box-shadow:var(--dash-shadow);
  margin-bottom:24px;
}
.section-head {display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.section-head h2 {margin:0;font-size:18px;font-weight:700;color:var(--text-color);}
.btn-linkish {
  display:inline-flex;align-items:center;gap:7px;height:36px;padding:0 13px;border-radius:11px;
  background:rgba(224,242,254,.16);border:1px solid rgba(186,230,253,.28);color:#fff;font-weight:700;text-decoration:none;font-size:13px;
}
.btn-linkish.docs { background:#eff6ff; border-color:#bfdbfe; color:#0369a1; }
.status-pill {
  display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:#eff6ff;border:1px solid #bfdbfe;
  font-size:12px;color:#0369a1;font-weight:700;
}
.jobs-table {width:100%;border-collapse:collapse;}
.jobs-table th,.jobs-table td {padding:14px 10px;border-bottom:1px solid #e0ecfb;text-align:left;font-size:14px;color:var(--text-color);vertical-align:top;}
.jobs-table th {font-size:11px;text-transform:uppercase;letter-spacing:.55px;color:#64748b;}
.badge {display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#eff6ff;color:#0369a1;border:1px solid #bfdbfe;}
.empty-state {padding:32px 12px;text-align:center;color:#94a3b8;}
.doc-meta {display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;}
.doc-pill {display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:#f0f9ff;border:1px solid #dbeafe;font-size:11px;color:#0369a1;}
.table-tools { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.cb-search { position:relative; }
.cb-search input {
  width:100%; height:44px; padding:0 14px 0 40px; border:1px solid #dbe5f0; border-radius:12px; background:#fff; color:#0f172a; font-size:14px;
}
.cb-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.mini-chip {
  display:inline-flex; align-items:center; justify-content:space-between; gap:8px; min-width:180px; height:36px; padding:0 12px;
  border-radius:11px; border:1px solid #bfdbfe; background:#fff; color:#0369a1; font-size:13px; font-weight:700; cursor:pointer;
}
.mini-icon-btn {
  width:34px; height:34px; border-radius:10px; border:1px solid #dbeafe; background:#fff; color:#0369a1;
  display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.mini-icon-btn:hover { background:#eff6ff; }
.bill-inline-actions { display:flex; gap:8px; flex-wrap:wrap; }
.tree-shell { border:1px solid #e2e8f0; border-radius:16px; background:#fff; max-height:420px; overflow:auto; padding:10px; }
.tree-node { display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border-radius:12px; }
.tree-node:hover { background:#f8fafc; }
.tree-node.active { background:#eff6ff; box-shadow: inset 0 0 0 1px #bfdbfe; }
.tree-children { margin-left:18px; padding-left:12px; border-left:1px solid #e2e8f0; }
.tree-meta { display:flex; flex-direction:column; gap:3px; }
.tree-meta strong { font-size:14px; color:#0f172a; }
.tree-meta small { color:#94a3b8; font-size:12px; }
@media (max-width: 768px) {
  .dashboard-page { padding:16px; }
  .kpi-grid { grid-template-columns:1fr; }
  .page-header { padding:18px; }
}
</style>
@endpush

@section('content')
<div class="dashboard-page">
  <div class="page-header">
    <div>
      <div class="hero-badge"><i class="fa-solid fa-wave-square"></i> {{ $dashboardBadge }}</div>
      <h1>{{ $dashboardTitle }}</h1>
      <p id="welcomeText">Loading your client scope…</p>
    </div>
    <div class="header-actions">
      <a href="{{ $dashboardPrimaryUrl }}" class="btn-linkish">
        <i class="fa-solid fa-briefcase"></i>
        {{ $dashboardPrimaryLabel }}
      </a>
      <a href="{{ $dashboardSecondaryUrl }}" class="btn-linkish docs">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        {{ $dashboardSecondaryLabel }}
      </a>
      <a href="{{ $dashboardTertiaryUrl }}" class="btn-linkish docs">
        <i class="fa-solid fa-bell"></i>
        {{ $dashboardTertiaryLabel }}
      </a>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card clients">
      <div class="kpi-top">
        <div class="kpi-label">Scoped Clients</div>
        <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
      </div>
      <div class="kpi-value" id="statClients">—</div>
    </div>
    <div class="kpi-card jobs">
      <div class="kpi-top">
        <div class="kpi-label">Visible Jobs</div>
        <div class="kpi-icon"><i class="fa-solid fa-briefcase"></i></div>
      </div>
      <div class="kpi-value" id="statJobs">—</div>
    </div>
    <div class="kpi-card documents">
      <div class="kpi-top">
        <div class="kpi-label">Documents</div>
        <div class="kpi-icon"><i class="fa-solid fa-folder-open"></i></div>
      </div>
      <div class="kpi-value" id="statDocuments">—</div>
    </div>
    <div class="kpi-card due">
      <div class="kpi-top">
        <div class="kpi-label">Due Today</div>
        <div class="kpi-icon"><i class="fa-solid fa-calendar-day"></i></div>
      </div>
      <div class="kpi-value" id="statDueToday">—</div>
    </div>
    <div class="kpi-card overdue">
      <div class="kpi-top">
        <div class="kpi-label">Overdue</div>
        <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
      </div>
      <div class="kpi-value" id="statOverdue">—</div>
    </div>
    <div class="kpi-card completed">
      <div class="kpi-top">
        <div class="kpi-label">Completed</div>
        <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
      </div>
      <div class="kpi-value" id="statCompleted">—</div>
    </div>
  </div>

  <div class="section">
    <div class="section-head">
      <h2>Recent Jobs In Your Scope</h2>
      <span class="status-pill" id="scopeNote">Loading…</span>
    </div>
    <div class="table-responsive">
      <table class="jobs-table">
        <thead>
          <tr>
            <th>Job</th>
            <th>Client</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Deadline</th>
          </tr>
        </thead>
        <tbody id="recentJobsRows">
          <tr><td colspan="5" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-head">
      <h2>All Published Bills In Your Scope</h2>
      <div class="table-tools">
        <button type="button" class="mini-chip" id="dashboardClientFilterBtn">
          <span id="dashboardClientFilterLabel">All Clients</span>
          <i class="fa-solid fa-sitemap"></i>
        </button>
        <button type="button" class="mini-icon-btn" id="clearDashboardClientFilterBtn" title="Clear client filter">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <button type="button" class="btn-linkish docs" id="exportBillsBtn">
          <i class="fa-solid fa-file-export"></i>
          Export
        </button>
        <a href="{{ $portalRepaymentsUrl }}" class="btn-linkish docs">
          <i class="fa-solid fa-money-bill-transfer"></i>
          Repayments
        </a>
        <span class="status-pill" id="billScopeNote">Loading…</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="jobs-table">
        <thead>
          <tr>
            <th>Bill</th>
            <th>Client</th>
            <th>Bill Date</th>
            <th>Due Date</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="recentBillsRows">
          <tr><td colspan="6" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-head">
      <h2>Recent Documents In Your Scope</h2>
      <span class="status-pill" id="docScopeNote">Loading…</span>
    </div>
    <div class="table-responsive">
      <table class="jobs-table">
        <thead>
          <tr>
            <th>Document</th>
            <th>Client</th>
            <th>Type</th>
            <th>Expiry</th>
            <th>Open</th>
          </tr>
        </thead>
        <tbody id="recentDocumentsRows">
          <tr><td colspan="5" class="text-center py-4">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="dashboardClientTreeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Choose Client Tree</h5>
          <div class="text-muted small">Filter the published bills section by one client branch.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="cb-search mb-3" style="max-width:none;">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="dashboardClientTreeSearch" placeholder="Search clients...">
        </div>
        <div class="tree-shell" id="dashboardClientTreeShell">
          <div class="text-center py-4 text-muted">Loading client tree…</div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="text-muted small" id="dashboardClientTreeSelectionLabel">All Clients</div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="applyDashboardClientFilterBtn">Use Client</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="dashboardBillDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bill Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="dashboardBillDetailBody">
        <div class="text-center text-muted py-4">Loading…</div>
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
    setTimeout(() => { window.location.href = @json($portalLoginUrl); }, 500);
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
  const dashboardClientTreeModal = new bootstrap.Modal(document.getElementById('dashboardClientTreeModal'));
  const dashboardBillDetailModal = new bootstrap.Modal(document.getElementById('dashboardBillDetailModal'));
  const esc = (str) => String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const fmtDate = (iso) => {
    if (!iso) return '—';
    const dt = new Date(iso);
    return isNaN(dt) ? '—' : dt.toLocaleDateString();
  };

  const els = {
    welcomeText: document.getElementById('welcomeText'),
    scopeNote: document.getElementById('scopeNote'),
    statClients: document.getElementById('statClients'),
    statJobs: document.getElementById('statJobs'),
    statDocuments: document.getElementById('statDocuments'),
    statDueToday: document.getElementById('statDueToday'),
    statOverdue: document.getElementById('statOverdue'),
    statCompleted: document.getElementById('statCompleted'),
    recentRows: document.getElementById('recentJobsRows'),
    recentBillsRows: document.getElementById('recentBillsRows'),
    recentDocumentsRows: document.getElementById('recentDocumentsRows'),
    billScopeNote: document.getElementById('billScopeNote'),
    docScopeNote: document.getElementById('docScopeNote'),
    exportBillsBtn: document.getElementById('exportBillsBtn'),
    dashboardClientFilterBtn: document.getElementById('dashboardClientFilterBtn'),
    dashboardClientFilterLabel: document.getElementById('dashboardClientFilterLabel'),
    clearDashboardClientFilterBtn: document.getElementById('clearDashboardClientFilterBtn'),
    dashboardClientTreeSearch: document.getElementById('dashboardClientTreeSearch'),
    dashboardClientTreeShell: document.getElementById('dashboardClientTreeShell'),
    dashboardClientTreeSelectionLabel: document.getElementById('dashboardClientTreeSelectionLabel'),
    applyDashboardClientFilterBtn: document.getElementById('applyDashboardClientFilterBtn'),
    dashboardBillDetailBody: document.getElementById('dashboardBillDetailBody'),
  };
  const state = { publishedBills: [], visiblePublishedBills: [], clients: [], clientTreeRoots: [], selectedClientId: '', pendingClientId: '' };

  async function fetchJSON(url) {
    const res = await fetch(url, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || ('HTTP ' + res.status));
    return data;
  }

  function buildTree(flatRows) {
    const byParent = new Map();
    flatRows.forEach((row) => {
      const key = row.parent_id == null ? 'root' : String(row.parent_id);
      if (!byParent.has(key)) byParent.set(key, []);
      byParent.get(key).push(row);
    });
    byParent.forEach((rows) => rows.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''))));
    const walk = (parentId = null) => {
      const key = parentId == null ? 'root' : String(parentId);
      return (byParent.get(key) || []).map((row) => ({ ...row, children: walk(row.id) }));
    };
    return walk(null);
  }

  function labelForClient(clientId, fallback = 'All Clients') {
    const match = state.clients.find((row) => String(row.id) === String(clientId || ''));
    return match ? (match.name || `Client #${match.id}`) : fallback;
  }

  function collectTreeIds(clientId) {
    const numericId = Number(clientId || 0);
    if (!numericId) return [];
    const seen = new Set();
    const queue = [numericId];
    while (queue.length) {
      const current = Number(queue.shift() || 0);
      if (!current || seen.has(current)) continue;
      seen.add(current);
      state.clients.filter((row) => Number(row.parent_id || 0) === current).forEach((row) => {
        if (!seen.has(Number(row.id))) queue.push(Number(row.id));
      });
    }
    return Array.from(seen);
  }

  function renderDashboardClientTree() {
    const query = String(els.dashboardClientTreeSearch.value || '').trim().toLowerCase();
    const activeId = Number(state.pendingClientId || state.selectedClientId || 0);
    const matchNode = (node) => {
      const selfMatch = !query || String(node.name || '').toLowerCase().includes(query);
      const childMatches = (node.children || []).map(matchNode).filter(Boolean);
      if (!selfMatch && !childMatches.length) return null;
      return { ...node, children: childMatches };
    };
    const filteredRoots = state.clientTreeRoots.map(matchNode).filter(Boolean);
    if (!filteredRoots.length) {
      els.dashboardClientTreeShell.innerHTML = '<div class="text-center py-4 text-muted">No clients match your search.</div>';
      return;
    }
    const renderNodes = (nodes, depth = 0) => nodes.map((node) => {
      const checked = activeId === Number(node.id) ? 'checked' : '';
      const active = activeId === Number(node.id) ? 'active' : '';
      return `
        <div class="tree-node ${active}">
          <input type="radio" name="dashboard_client_pick" value="${esc(node.id)}" ${checked}>
          <div class="tree-meta">
            <strong>${esc(node.name || `Client #${node.id}`)}</strong>
            <small>${depth === 0 ? 'Root client' : `Nested level ${depth}`}</small>
          </div>
        </div>
        ${node.children && node.children.length ? `<div class="tree-children">${renderNodes(node.children, depth + 1)}</div>` : ''}`;
    }).join('');
    els.dashboardClientTreeShell.innerHTML = renderNodes(filteredRoots);
    els.dashboardClientTreeSelectionLabel.textContent = activeId ? labelForClient(activeId, 'Choose Client') : 'All Clients';
  }

  function filterBillsForSelectedClient() {
    if (!state.selectedClientId) {
      state.visiblePublishedBills = [...state.publishedBills];
      els.dashboardClientFilterLabel.textContent = 'All Clients';
      return;
    }
    const treeIds = collectTreeIds(state.selectedClientId);
    state.visiblePublishedBills = state.publishedBills.filter((bill) => treeIds.includes(Number(bill.client_id || 0)));
    els.dashboardClientFilterLabel.textContent = labelForClient(state.selectedClientId, 'Choose Client');
  }

  function renderRecentJobs(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      els.recentRows.innerHTML = '<tr><td colspan="5"><div class="empty-state">No jobs are currently visible for your assigned client scope.</div></td></tr>';
      return;
    }

    els.recentRows.innerHTML = rows.map(job => `
      <tr>
        <td><a href="${@json($portalJobsUrl)}" style="color:#2563eb;text-decoration:none;font-weight:600;">${esc(job.title || '—')}</a></td>
        <td>${esc(job.client_name || '—')}</td>
        <td>${esc(String(job.status || '—').replaceAll('_', ' '))}</td>
        <td>${esc(String(job.priority || '—').replaceAll('_', ' '))}</td>
        <td>${fmtDate(job.planned_deadline_at)}</td>
      </tr>
    `).join('');
  }

  function renderRecentDocuments(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      els.recentDocumentsRows.innerHTML = '<tr><td colspan="5"><div class="empty-state">No documents are currently visible for your assigned client scope.</div></td></tr>';
      return;
    }

    els.recentDocumentsRows.innerHTML = rows.map(doc => `
      <tr>
        <td>
          <div style="font-weight:600;color:#0f172a">${esc(doc.doc_name || '—')}</div>
          <div class="doc-meta">
            ${doc.issuing_authority ? `<span class="doc-pill">${esc(doc.issuing_authority)}</span>` : ''}
            ${doc.status ? `<span class="doc-pill">${esc(String(doc.status).replaceAll('_',' '))}</span>` : ''}
          </div>
        </td>
        <td>${esc(doc.client_name || '—')}</td>
        <td>${esc(doc.document_type_name || '—')}</td>
        <td>${fmtDate(doc.expiry_date)}</td>
        <td>
          ${doc.file_url ? `<a href="${esc(doc.file_url)}" target="_blank" rel="noopener noreferrer" class="btn-linkish docs" style="height:36px;padding:0 12px;font-size:13px"><i class="fa-solid fa-arrow-up-right-from-square"></i>Open</a>` : '—'}
        </td>
      </tr>
    `).join('');
  }

  function renderRecentBills(rows) {
    state.publishedBills = Array.isArray(rows) ? rows : [];
    filterBillsForSelectedClient();
    if (!state.visiblePublishedBills.length) {
      els.recentBillsRows.innerHTML = '<tr><td colspan="6"><div class="empty-state">No published bills are visible for the selected client scope.</div></td></tr>';
      return;
    }

    els.recentBillsRows.innerHTML = state.visiblePublishedBills.map(bill => `
      <tr>
        <td><a href="${@json($portalBillsUrl)}" style="color:#2563eb;text-decoration:none;font-weight:600;">Bill #${esc(bill.id || '—')}</a></td>
        <td>${esc(bill.client_name || '—')}</td>
        <td>${fmtDate(bill.bill_date)}</td>
        <td>${fmtDate(bill.due_date)}</td>
        <td>${esc(Number(bill.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</td>
        <td>
          <div class="bill-inline-actions">
            <button type="button" class="mini-icon-btn" data-bill-view="${esc(bill.id)}" title="View"><i class="fa-solid fa-eye"></i></button>
            <button type="button" class="mini-icon-btn" data-bill-pdf="${esc(bill.id)}" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function exportBills() {
    if (!state.visiblePublishedBills.length) return;
    const rows = state.visiblePublishedBills.map((bill) => ({
      bill_id: bill.id || '',
      client: bill.client_name || '',
      bill_date: bill.bill_date || '',
      due_date: bill.due_date || '',
      total_amount: Number(bill.total_amount || 0).toFixed(2),
      published_at: bill.published_at || '',
    }));
    const headersRow = Object.keys(rows[0]);
    const csv = [
      headersRow.join(','),
      ...rows.map((row) => headersRow.map((key) => `"${String(row[key] ?? '').replace(/"/g, '""')}"`).join(',')),
    ].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `published_bills_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  async function downloadBillPdf(id) {
    const res = await fetch(`/api/client-bills/${encodeURIComponent(id)}/pdf`, { headers });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data?.message || `HTTP ${res.status}`);
    }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `client_bill_${id}.pdf`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  async function openBillDetail(id) {
    dashboardBillDetailModal.show();
    els.dashboardBillDetailBody.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
    const data = await fetchJSON(`/api/client-bills/${encodeURIComponent(id)}`);
    const bill = data.data || {};
    const items = Array.isArray(bill.items) ? bill.items : [];
    const repayments = Array.isArray(bill.repayments) ? bill.repayments : [];
    els.dashboardBillDetailBody.innerHTML = `
      <div class="detail-grid">
        <div class="detail-box"><small>Bill</small><strong>#${esc(bill.id || '—')}</strong></div>
        <div class="detail-box"><small>Client</small><strong>${esc(bill.client_name || '—')}</strong></div>
        <div class="detail-box"><small>Bill Date</small><strong>${fmtDate(bill.bill_date)}</strong></div>
        <div class="detail-box"><small>Due Date</small><strong>${fmtDate(bill.due_date)}</strong></div>
      </div>
      <div class="detail-items">
        ${items.length ? items.map((item) => `
          <div class="detail-item">
            <div><strong>${esc(item.bill_head_title || 'Untitled')}</strong></div>
            <div style="font-weight:800;">${esc(Number(item.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</div>
          </div>
        `).join('') : '<div class="empty-state" style="padding:16px;">No bill items found.</div>'}
      </div>
      <div class="section-card mt-3" style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc;">
        <h6 style="margin:0 0 10px;font-size:14px;font-weight:700;color:#0f172a;">Repayments</h6>
        ${repayments.length ? repayments.map((repayment) => `
          <div class="detail-item" style="margin-bottom:10px;">
            <div>
              <strong>${fmtDate(repayment.repayment_date)}</strong>
              <div style="font-size:12px;color:#64748b;">${esc(String(repayment.status || 'pending').replaceAll('_', ' '))}</div>
            </div>
            <div style="font-weight:800;">${esc(Number(repayment.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</div>
          </div>
        `).join('') : '<div class="text-muted small">No repayments recorded yet.</div>'}
      </div>
      <div class="detail-total">Total: <span class="ms-2">${esc(Number(bill.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</span></div>`;
  }

  async function loadDashboard() {
    try {
      const [me, dashboard, clients] = await Promise.all([
        fetchJSON(@json($dashboardMeApi)),
        fetchJSON(@json($dashboardApi)),
        fetchJSON('/api/clients/all'),
      ]);

      const user = me?.data || {};
      const quick = dashboard?.data?.quick_links || {};
      const recent = dashboard?.data?.recent_jobs || [];
      const recentBills = dashboard?.data?.all_published_bills || dashboard?.data?.recent_bills || [];
      const recentDocuments = dashboard?.data?.recent_documents || [];
      state.clients = Array.isArray(clients?.data) ? clients.data : [];
      state.clientTreeRoots = buildTree(state.clients);
      renderDashboardClientTree();

      els.welcomeText.textContent = `${user.name || 'Client'} can currently view jobs for ${quick.scoped_clients || 0} scoped client records.`;
      els.scopeNote.textContent = `${quick.scoped_clients || 0} clients in scope`;
      els.billScopeNote.textContent = `${quick.published_bills || 0} published bills in scope`;
      els.docScopeNote.textContent = `${quick.visible_documents || 0} documents in scope`;
      els.statClients.textContent = quick.scoped_clients ?? 0;
      els.statJobs.textContent = quick.visible_jobs ?? 0;
      els.statDocuments.textContent = quick.visible_documents ?? 0;
      els.statDueToday.textContent = quick.due_today ?? 0;
      els.statOverdue.textContent = quick.overdue ?? 0;
      els.statCompleted.textContent = quick.completed ?? 0;

      renderRecentJobs(recent);
      renderRecentBills(recentBills);
      renderRecentDocuments(recentDocuments);
    } catch (error) {
      els.welcomeText.textContent = error.message || 'Failed to load dashboard.';
      els.scopeNote.textContent = 'Unable to load';
      els.billScopeNote.textContent = 'Unable to load';
      els.docScopeNote.textContent = 'Unable to load';
      renderRecentJobs([]);
      renderRecentBills([]);
      renderRecentDocuments([]);
    }
  }

  els.exportBillsBtn.addEventListener('click', exportBills);
  els.dashboardClientFilterBtn.addEventListener('click', () => {
    state.pendingClientId = state.selectedClientId || '';
    els.dashboardClientTreeSearch.value = '';
    renderDashboardClientTree();
    dashboardClientTreeModal.show();
  });
  els.clearDashboardClientFilterBtn.addEventListener('click', () => {
    state.selectedClientId = '';
    filterBillsForSelectedClient();
    renderRecentBills(state.publishedBills);
  });
  els.dashboardClientTreeSearch.addEventListener('input', renderDashboardClientTree);
  els.dashboardClientTreeShell.addEventListener('change', (event) => {
    const input = event.target.closest('input[name="dashboard_client_pick"]');
    if (!input) return;
    state.pendingClientId = input.value;
    renderDashboardClientTree();
  });
  els.applyDashboardClientFilterBtn.addEventListener('click', () => {
    state.selectedClientId = state.pendingClientId || '';
    filterBillsForSelectedClient();
    renderRecentBills(state.publishedBills);
    dashboardClientTreeModal.hide();
  });
  els.recentBillsRows.addEventListener('click', (event) => {
    const viewBtn = event.target.closest('[data-bill-view]');
    if (viewBtn) {
      openBillDetail(viewBtn.dataset.billView).catch((error) => {
        els.dashboardBillDetailBody.innerHTML = `<div class="text-danger">${esc(error.message || error)}</div>`;
      });
      return;
    }
    const pdfBtn = event.target.closest('[data-bill-pdf]');
    if (!pdfBtn) return;
    downloadBillPdf(pdfBtn.dataset.billPdf).catch(() => {});
  });
  loadDashboard();
})();
</script>
@endpush
