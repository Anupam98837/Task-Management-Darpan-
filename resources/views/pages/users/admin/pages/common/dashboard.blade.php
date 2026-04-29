@extends('pages.users.admin.layout.structure')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
  * { box-sizing: border-box; }
  
  .dashboard-page {
    background: var(--bg-body);
    min-height: 100vh;
    padding: 24px;
    font-family: var(--font-sans);
    position: relative; /* needed for overlays positioned inside */
  }

  .page-header { margin-bottom: 28px; }
  .page-header h1 {
    font-size: 28px; font-weight: 700; color: var(--text-color);
    margin: 0 0 6px; font-family: var(--font-head);
  }
  .page-header p { color: var(--muted-color); font-size: 14px; margin: 0; }

  /* Toolbar */
  .toolbar {
    display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
    align-items: center; justify-content: space-between;
  }
  .toolbar .left { display: flex; gap: 12px; align-items: center; }
  .toolbar .right { display: flex; gap: 12px; }

  .badge-pill{
    display: inline-flex; align-items: center; gap: 8px; border-radius: 50px;
    padding: 8px 16px; font-size: 13px; font-weight: 600; letter-spacing: .3px;
    transition: var(--transition);
  }
  .badge-purple{
    background: linear-gradient(135deg,#8b5cf6,#a78bfa); color:#fff; border:none;
    box-shadow: 0 2px 8px rgba(139,92,246,.25);
  }

  .btn{
    display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 10px;
    border-radius:12px; font-size:14px; font-weight:600; cursor:pointer;
    transition:var(--transition); border:none; font-family:var(--font-head);
  }
  .btn-outline-primary{ background:var(--surface); color:var(--primary-color); border:1px solid var(--primary-color); }
  .btn-outline-primary:hover{
    background:var(--primary-color); color:#fff; transform:translateY(-1px);
    box-shadow:0 6px 20px rgba(37,99,235,.3);
  }

  /* KPI Cards */
  .kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-bottom:24px; }
  .kpi-card{
    background:var(--surface); border:1px solid var(--border-color); border-radius:16px;
    padding:20px; box-shadow:var(--shadow-sm); display:flex; gap:16px; align-items:center;
    transition:var(--transition); position:relative; overflow:hidden;
  }
  .kpi-card::before{ content:''; position:absolute; inset:0 auto 0 0; width:4px; background:var(--primary-color); opacity:0; transition:opacity .3s; }
  .kpi-card:hover{ transform:translateY(-3px); box-shadow:var(--shadow-md); border-color:var(--primary-color); }
  .kpi-card:hover::before{ opacity:1; }
  .kpi-icon{ width:60px; height:60px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
  .kpi-icon.primary{ background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; }
  .kpi-icon.info{ background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%); color:#fff; }
  .kpi-icon.indigo{ background:linear-gradient(135deg,#6366f1 0%,#818cf8 100%); color:#fff; }
  .kpi-icon.success{ background:linear-gradient(135deg,#43e97b 0%,#38f9d7 100%); color:#fff; }
  .kpi-icon.warning{ background:linear-gradient(135deg,#fa709a 0%,#fee140 100%); color:#fff; }
  .kpi-icon.teal{ background:linear-gradient(135deg,#0ea5e9 0%,#38bdf8 100%); color:#fff; }
  .kpi-icon.danger{ background:linear-gradient(135deg,#ff6b6b 0%,#ffa8a8 100%); color:#fff; }
  .kpi-content{ flex:1; }
  .kpi-value{ font-size:2rem; font-weight:800; color:var(--text-color); line-height:1; margin-bottom:4px; }
  .kpi-label{ font-size:.875rem; color:var(--muted-color); font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

  /* sections */
  .section{
    background:var(--surface); border:1px solid var(--border-color); border-radius:16px;
    box-shadow:var(--shadow-sm); padding:24px; margin-bottom:24px; transition:var(--transition);
  }
  .section:hover{ box-shadow:var(--shadow-md); transform:translateY(-2px); }
  .section-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
  .section-title{
    margin:0; font-weight:700; font-size:1.15rem; color:var(--text-color);
    display:flex; align-items:center; gap:10px; font-family:var(--font-head);
  }
  .section-title i{ color:var(--primary-color); font-size:1.2rem; }

  /* Tabs */
  .tabs{ display:flex; gap:8px; flex-wrap:wrap; }
  .tab{
    padding:8px 16px; border:1px solid var(--border-color); background:var(--surface);
    color:var(--text-color); border-radius:10px; font-weight:600; font-size:13px;
    transition:var(--transition); cursor:pointer;
  }
  .tab.active,.tab:hover{ background:var(--primary-color); color:#fff; border-color:var(--primary-color); }

  /* Charts */
  .chart-container{ position:relative; height:280px; width:100%; background:var(--surface); border-radius:12px; padding:1rem; margin-bottom:24px; }
  .chart-container.short{ height:220px; }
/* Period loader placed inside .chart-container */
/* Jobs chart local loader (period loader inside chart container) */
.chart-container { position: relative; overflow: hidden; } /* ensure overlay clips nicely */

#jobsPeriodLoader {
  display: none;
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.15); /* transparent dark veil */
  backdrop-filter: blur(8px) saturate(120%);
  -webkit-backdrop-filter: blur(8px) saturate(120%);
  z-index: 50;
  align-items: center;
  justify-content: center;
  gap: 12px;
  border-radius: 12px;
  pointer-events: none;
  transition: opacity .25s ease;
  opacity: 0;
}
#jobsPeriodLoader.show {
  display: flex;
  pointer-events: auto;
  opacity: 1;
}

/* Spinner + text */
#jobsPeriodLoader .spinner {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top-color: rgba(255, 255, 255, 0.9);
  animation: spin 0.9s linear infinite;
  box-shadow: 0 0 12px rgba(255, 255, 255, 0.3);
}


#jobsPeriodLoader .text {
font-weight: 700;
  font-size: 14px;
  color: var(--text-color, #111827);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Dark theme fine-tuning */
@media (prefers-color-scheme: dark) {
  #jobsPeriodLoader {
    background: rgba(0, 0, 0, 0.25);
    backdrop-filter: blur(8px) saturate(140%);
  }
  #jobsPeriodLoader .spinner {
    border: 4px solid rgba(255,255,255,0.1);
    border-top-color: #818cf8;
  }
}

/* Optional: if you toggle dark mode via .dark class */
body.dark #jobsPeriodLoader {
  background: rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(8px) saturate(140%);
}
body.dark #jobsPeriodLoader .spinner {
  border: 4px solid rgba(255,255,255,0.1);
  border-top-color: #818cf8;
}

  /* Activity List */
  .activity-wrapper{
    max-height:600px; overflow-y:auto; position:relative;
    padding-bottom: 24px;
    scrollbar-gutter: stable both-edges;
    overscroll-behavior: contain;
    overflow-x: hidden;
    scrollbar-width: thin;
  }

  .list-activity{ list-style:none; margin:0; padding:0; }
  .list-activity li{
    display:flex; gap:14px; align-items:flex-start;
    padding:16px 12px;
    border-bottom:1px dashed var(--border-color);
    transition:var(--transition);
  }
  .list-activity li:hover{
    background-color:var(--light-color);
    border-radius:12px;
    transform:translateX(2px);
  }
  .list-activity li:last-child{ border-bottom:none; }

  .activity-icon{
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600;
  }
  .activity-icon.blue{ background:linear-gradient(135deg,#3b82f6,#60a5fa); color:#fff; }

  .activity-content{ flex:1; }
  .activity-text{ margin-bottom:4px; }
  .activity-text strong{ font-weight:600; color:var(--text-color); margin-right:8px; }
  .activity-text span{ color:var(--muted-color); }
  .activity-time{ font-size:.75rem; color:var(--muted-color); display:flex; align-items:center; gap:4px; }

  #activityLoader, #activityEnd{
    padding:14px 12px; text-align:center; color:var(--muted-color); font-size:.875rem; display:none;
    background: var(--surface);
    position: sticky; bottom: 0;
    border-top: 1px solid var(--border-color);
    box-shadow: 0 -6px 14px rgba(0,0,0,.06);
  }
  #activityEnd{ font-weight:600; opacity:.9; }

  /* High Priority Table */
  .table-responsive{ border-radius:12px; overflow:hidden; border:1px solid var(--border-color); }
  .table{ width:100%; border-collapse:separate; border-spacing:0; margin:0; }
  .table th{
    font-size:12px; color:var(--muted-color); font-weight:700; padding:14px 18px;
    background-color:var(--light-color); border-bottom:1px solid var(--border-color);
    text-transform:uppercase; letter-spacing:.5px; text-align:left; white-space:nowrap;
  }
  .table td{
    background:var(--surface); border-bottom:1px solid var(--border-color);
    padding:16px 18px; vertical-align:middle; transition:var(--transition);
    color:var(--text-color); font-size:14px;
  }
  .table tbody tr:hover td{ background-color:var(--light-color); }
  .table tbody tr:last-child td{ border-bottom:none; }
  .hp-count{
    font-size:13px; color:var(--muted-color); background:var(--light-color);
    padding:6px 12px; border-radius:50px; font-weight:600; border:1px solid var(--border-color);
  }

  /* Badges */
  .badge{ display:inline-flex; align-items:center; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; text-transform:capitalize; letter-spacing:.3px; }
  .badge-green{ background:linear-gradient(135deg,#10b981,#34d399); color:#fff; }
  .badge-red{ background:linear-gradient(135deg,#ef4444,#f87171); color:#fff; }
  .badge-amber{ background:linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff; }
  .badge-blue{ background:linear-gradient(135deg,#3b82f6,#60a5fa); color:#fff; }

  .table a{ color:var(--primary-color); text-decoration:none; font-weight:600; transition:var(--transition); }
  .table a:hover{ color:var(--secondary-color); text-decoration:underline; }

  /* Toast */
  .toast-container{ z-index:1080; }
  .toast{ border-radius:12px; border:none; box-shadow:var(--shadow-md); }

  /* Responsive */
  @media (max-width: 992px){ .activity-wrapper{ max-height:400px; } }
  @media (max-width: 768px){
    .dashboard-page{ padding:16px; }
    .kpi-grid{ grid-template-columns:1fr 1fr; }
    .toolbar{ flex-direction:column; align-items:stretch; }
    .toolbar .left,.toolbar .right{ width:100%; justify-content:space-between; }
    .tabs{ justify-content:center; }
    .section{ padding:20px; }
  }
  @media (max-width: 576px){
    .kpi-grid{ grid-template-columns:1fr; }
    .section{ padding:16px; }
    .table-responsive{ font-size:.85rem; }
    .page-header h1{ font-size:24px; }
  }

  /* WebKit scrollbar (vertical only) */
  .activity-wrapper::-webkit-scrollbar{ width:6px; height:6px; }
  .activity-wrapper::-webkit-scrollbar-track{ background:var(--light-color); border-radius:10px; }
  .activity-wrapper::-webkit-scrollbar-thumb{ background:var(--border-color); border-radius:10px; }
  .activity-wrapper::-webkit-scrollbar-thumb:hover{ background:var(--muted-color); }
</style>
@endpush

@section('content')
<div class="dashboard-page">
  <!-- Page Header -->
  <div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Monitor key metrics, track activities, and manage your organization</p>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="left">
      <span class="badge-pill badge-purple" style="display:none">
        <i class="fa-regular fa-clock"></i>
        <span id="generatedAt">—</span>
      </span>
    </div>
    <div class="right">
      <button id="btnRefresh" class="btn btn-outline-primary">
        <i id="refreshIcon" class="fa fa-rotate-right"></i>
        Refresh Dashboard
      </button>
    </div>
  </div>

  <!-- KPI Cards -->
<div class="kpi-grid">
  <div class="kpi-card" data-url="/admin/jobs/view" role="link" tabindex="0" aria-label="View all jobs">
    <div class="kpi-icon primary"><i class="fa-solid fa-briefcase"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsTotal">0</div>
      <div class="kpi-label">Total Jobs</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/client/manage" role="link" tabindex="0" aria-label="Manage clients">
    <div class="kpi-icon info"><i class="fa-solid fa-users"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiClients">0</div>
      <div class="kpi-label">Total Clients</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/assignedpeople/manage" role="link" tabindex="0" aria-label="Manage assigned people">
    <div class="kpi-icon indigo"><i class="fa-solid fa-user-check"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiAssignedPeople">0</div>
      <div class="kpi-label">Assigned People</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/jobs/view?filter=completed" role="link" tabindex="0" aria-label="View completed jobs">
    <div class="kpi-icon success"><i class="fa-solid fa-circle-check"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsCompleted">0</div>
      <div class="kpi-label">Jobs Completed</div>
    </div>
  </div>
    <div class="kpi-card" data-url="/admin/jobs/view?filter=in_progress" role="link" tabindex="0" aria-label="View in-progress jobs">
    <div class="kpi-icon indigo"><i class="fa-solid fa-spinner"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsInProgress">0</div>
      <div class="kpi-label">Jobs In Progress</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/jobs/view?filter=pending" role="link" tabindex="0" aria-label="View pending jobs">
    <div class="kpi-icon warning"><i class="fa-solid fa-hourglass-half"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsPending">0</div>
      <div class="kpi-label">Jobs Pending</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/jobs/view?filter=assigned" role="link" tabindex="0" aria-label="View assigned jobs">
    <div class="kpi-icon teal"><i class="fa-solid fa-link"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsAssigned">0</div>
      <div class="kpi-label">Assigned Jobs</div>
    </div>
  </div>

  <div class="kpi-card" data-url="/admin/jobs/view?filter=unassigned" role="link" tabindex="0" aria-label="View unassigned jobs">
    <div class="kpi-icon danger"><i class="fa-solid fa-unlink"></i></div>
    <div class="kpi-content">
      <div class="kpi-value" id="kpiJobsUnassigned">0</div>
      <div class="kpi-label">Unassigned Jobs</div>
    </div>
  </div>
</div>

  <!-- Main Content Grid -->
  <div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-7">
      <!-- Jobs Created Chart -->
      <div class="section">
        <div class="section-header">
          <h6 class="section-title"><i class="fa-solid fa-chart-line"></i> Jobs Created (Day-wise)</h6>
          <div class="tabs">
            <button class="tab" data-period="today" style="display:none">Today</button>
            <button class="tab" data-period="7d">7 days</button>
            <button class="tab active" data-period="30d">30 days</button>
            <button class="tab" data-period="60d">60 days</button>
            <button class="tab" data-period="90d">90 days</button>
          </div>
        </div>
        <div class="chart-container">
          <!-- Local period loader placed inside the chart container -->
          <div id="jobsPeriodLoader" role="status" aria-live="polite" aria-hidden="true">
            <div class="spinner" aria-hidden="true"></div>
            <div class="text" id="jobsPeriodLoaderText">Loading data…</div>
          </div>
          <canvas id="jobsCreatedLine"></canvas>
        </div>
      </div>

      <!-- Assigned vs Unassigned Chart -->
      <div class="section">
        <div class="section-header">
          <h6 class="section-title"><i class="fa-solid fa-chart-column"></i> Assigned vs Unassigned (Current)</h6>
        </div>
        <div class="chart-container short"><canvas id="assignBar"></canvas></div>
      </div>

      <!-- High Priority Jobs -->
      <div class="section">
        <div class="section-header">
          <h6 class="section-title"><i class="fa-solid fa-triangle-exclamation"></i> High Priority — Open</h6>
          <span class="hp-count" id="hpCount">0 items</span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Job</th><th>Client</th><th>Priority</th><th>Status</th><th>Deadline</th><th>Assignees</th>
              </tr>
            </thead>
            <tbody id="hpTableBody">
              <tr><td colspan="6" class="text-center text-muted py-4">No high-priority tasks at the moment</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-5">
      <div class="section">
        <div class="section-header">
          <h6 class="section-title"><i class="fa-solid fa-list"></i> Recent Activity</h6>
        </div>
        <div class="activity-wrapper" id="recentActivityWrap">
          <ul class="list-activity" id="recentActivity">
            <li class="text-muted py-3 text-center">No recent activity</li>
          </ul>
          <div id="activityLoader">
            <i class="fa fa-spinner fa-spin me-1"></i> Loading more activities...
          </div>
          <div id="activityEnd">No more activity to display</div>
          <div id="activitySentinel" aria-hidden="true" style="height:1px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toasts -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Dashboard updated successfully</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Failed to load dashboard data</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const EL = id => document.getElementById(id);
  const btnRefresh = EL('btnRefresh');
  const refreshIcon = EL('refreshIcon');
  const toastSuccess = new bootstrap.Toast(EL('toastSuccess'));
  const toastError   = new bootstrap.Toast(EL('toastError'));

  // local jobs chart loader refs
  const jobsPeriodLoader = EL('jobsPeriodLoader');
  const jobsPeriodLoaderText = EL('jobsPeriodLoaderText');

  let data = null;
  let currentPeriod = '30d';
  let actBefore = null, actBusy = false, actDone = false;
  const seenKeys = new Set();
  const ACT_LIMIT = 20;

  const getToken = () => localStorage.getItem('token') || sessionStorage.getItem('token') || '';

  function periodLabel(period){
    const map = { today:'today', '7d':'7 days', '30d':'30 days', '60d':'60 days', '90d':'90 days' };
    return map[period] || String(period);
  }

  function showJobsChartLoader(show, message){
    if (!jobsPeriodLoader) return;
    if (show){
      jobsPeriodLoaderText.textContent = message || `Loading ${periodLabel(currentPeriod)}…`;
      jobsPeriodLoader.classList.add('show');
      jobsPeriodLoader.setAttribute('aria-hidden','false');
    } else {
      jobsPeriodLoader.classList.remove('show');
      jobsPeriodLoader.setAttribute('aria-hidden','true');
      jobsPeriodLoaderText.textContent = 'Loading data…';
    }
  }

  function setRefreshing(spin){
    if (!refreshIcon) return;
    if (spin){ refreshIcon.classList.add('fa-spin'); btnRefresh?.setAttribute('disabled','disabled'); }
    else { refreshIcon.classList.remove('fa-spin'); btnRefresh?.removeAttribute('disabled'); }
  }
  const showSuccess = msg => { EL('toastSuccessText').textContent = msg; toastSuccess.show(); };
  const showError   = msg => { EL('toastErrorText').textContent   = msg; toastError.show(); };

  function dashboardUrl(){
    const url = new URL('/api/admin/dashboard', window.location.origin);
    url.searchParams.set('period', currentPeriod);
    url.searchParams.set('recent_limit', '10');
    return url.toString();
  }

  async function loadDashboard(period){
    if (period) currentPeriod = period;
    try{
      setRefreshing(true);
      const res = await fetch(dashboardUrl(), { headers: { 'Authorization': `Bearer ${getToken()}` } });
      const json = await res.json();
      if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to load');
      data = json.data || {};
      renderAll();

      resetActivityScroller();
      const seedRows = data.recent_activity || data.recent_activities || [];
      if (seedRows.length) {
        appendActivity(seedRows);
        actBefore = getOldestTimestamp(seedRows);
      }
      fillIfShort();
      maybeLoadMoreActivity();

      showSuccess(`Dashboard updated (${periodLabel(currentPeriod)})`);
    }catch(e){
      console.error(e);
      showError(e.message || 'Failed to load dashboard data');
    }finally{
      setRefreshing(false);
      showJobsChartLoader(false);
    }
  }

  function renderAll(){
    renderKPIs();
    renderJobsCreatedLine();
    renderAssignedBarChart();
    renderHighPriority();

    const dr = data.charts?.date_range;
    const genEl = EL('generatedAt');
    const genBadge = document.querySelector('.badge-pill.badge-purple');
    if (dr && dr.start && dr.end) {
      genEl.textContent = `${dr.start} → ${dr.end}`;
      if (genBadge) genBadge.style.display = 'inline-flex';
    } else if (data.generated_at) {
      genEl.textContent = data.generated_at;
      if (genBadge) genBadge.style.display = 'inline-flex';
    } else {
      genEl.textContent = '—';
      if (genBadge) genBadge.style.display = 'none';
    }
  }

  function renderKPIs(){
  const q = data.quick_links || {};

  EL('kpiClients').textContent        = num(q.total_clients);
  EL('kpiAssignedPeople').textContent = num(q.total_assigned_people);
  EL('kpiJobsTotal').textContent      = num(q.total_jobs_created);

  // ✅ Each KPI corresponds directly to backend key
  EL('kpiJobsCompleted').textContent  = num(q.total_jobs_completed);
  EL('kpiJobsInProgress').textContent = num(q.total_jobs_in_progress);
  EL('kpiJobsPending').textContent    = num(q.total_jobs_pending);

  EL('kpiJobsAssigned').textContent   = num(q.total_assigned_jobs);
  EL('kpiJobsUnassigned').textContent = num(q.total_unassigned_jobs);

  // Make KPI cards clickable
  setupKpiQuicklinks();
}

  function num(v){ return Number(v || 0); }
  function fmtDateLabel(dStr){
    const d = new Date(dStr); if (isNaN(d)) return dStr;
    const m = d.toLocaleString('en-US',{month:'short'}); const day = d.toLocaleString('en-US',{day:'2-digit'});
    return `${m}-${day}`;
  }
  function fmtDate(d){ try{ return new Date(d).toLocaleString(); }catch{ return d; } }
  const htmlMap = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}; 
  const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, ch => htmlMap[ch]);

  function renderJobsCreatedLine(){
    const seriesMap = (data.charts && data.charts.created_daily) || {};
    const keys = Object.keys(seriesMap).sort();
    const labels = keys.map(fmtDateLabel);
    const values = keys.map(k => num(seriesMap[k]));
    const ctx = document.getElementById('jobsCreatedLine').getContext('2d');
    if (window.jobsCreatedChart) window.jobsCreatedChart.destroy();
    window.jobsCreatedChart = new Chart(ctx,{
      type:'line',
      data:{ labels, datasets:[{ label:'Jobs Created', data:values, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.15)', tension:.35, fill:true, pointRadius:3, pointHoverRadius:5 }]},
      options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ title:items=>items.length?items[0].label:'', label:c=>`Created: ${c.raw}` } }}, scales:{ y:{ beginAtZero:true, ticks:{precision:0}, grid:{ color:'rgba(0,0,0,0.05)'} }, x:{ grid:{display:false}, ticks:{ maxRotation:0, autoSkip:true, autoSkipPadding:8 } } } }
    });

    showJobsChartLoader(false);
  }

  function renderAssignedBarChart(){
    const pair = (data.charts && data.charts.assigned_vs_unassigned) || {assigned:0, unassigned:0};
    const labels = ['Assigned','Unassigned']; const values = [num(pair.assigned), num(pair.unassigned)];
    const ctx = document.getElementById('assignBar').getContext('2d');
    if (window.assignBarChart) window.assignBarChart.destroy();
    window.assignBarChart = new Chart(ctx,{
      type:'bar',
      data:{ labels, datasets:[{ label:'Jobs', data:values, backgroundColor:['#22c55e','#ef4444'], borderWidth:0 }]},
      options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:c=>`${c.label}: ${c.raw}` } } }, scales:{ y:{ beginAtZero:true, ticks:{precision:0} }, x:{ grid:{display:false} } } }
    });
  }

  function renderHighPriority(){
    const rows = (data.high_priority && data.high_priority.open) || [];
    EL('hpCount').textContent = `${rows.length} item${rows.length===1?'':'s'}`;
    const tbody = EL('hpTableBody'); tbody.innerHTML='';
    if (!rows.length){
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No high-priority open jobs 🎉</td></tr>`;
      return;
    }
    rows.slice(0,20).forEach(r=>{
      const pri = badgePriority(r.priority);
      const st  = badgeStatus(r.status);
      const deadline = r.planned_deadline_at ? fmtDate(r.planned_deadline_at) : '—';
      const clientName = r.client_name || '—';
      const assignees  = num(r.assignees_count);
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><a href="#" class="fw-semibold text-decoration-none">${escapeHtml(r.title || '—')}</a></td>
        <td>${escapeHtml(clientName)}</td>
        <td>${pri}</td>
        <td>${st}</td>
        <td>${deadline}</td>
        <td>${assignees}</td>
      `;
      tbody.appendChild(tr);
    });
  }
  function badgePriority(p){ const label=(p||'').toString(); const map={urgent:'badge-red', high:'badge-amber', normal:'badge-blue', low:'badge'}; const cls=map[label]||'badge-amber'; return `<span class="badge ${cls}">${escapeHtml(label||'high')}</span>`; }
  function badgeStatus(s){ const label=(s||'').toString(); const map={completed:'badge-green', in_progress:'badge-blue', planned:'badge-amber', blocked:'badge-red', on_hold:'badge-amber'}; const cls=map[label]||'badge'; return `<span class="badge ${cls}">${escapeHtml(label||'open')}</span>`; }

  // -------------------
  // Activity list utils
  // -------------------
  function resetActivityScroller(){
    seenKeys.clear(); actBefore=null; actBusy=false; actDone=false;
    EL('recentActivity').innerHTML=''; EL('activityEnd').style.display='none';
  }
  function getOldestTimestamp(rows){
    let oldest=null;
    rows.forEach(a=>{
      const when=a.created_at||a.occurred_at||a.when||null;
      if (when){ const iso=new Date(when).toISOString(); if (!oldest || iso<oldest) oldest=iso; }
    });
    return oldest;
  }
  function activityKey(a){
    const when=a.created_at||a.occurred_at||a.when||''; const mod=a.module||a.table_name||''; const act=a.activity||''; const rid=(a.record_id??'').toString();
    return `${when}|${mod}|${act}|${rid}`;
  }
  function appendActivity(rows){
    const ul = EL('recentActivity');
    if (!rows || !rows.length){
      if (!ul.children.length) ul.innerHTML = '<li class="text-muted py-3 text-center">No recent activity</li>';
      return 0;
    }
    const frag = document.createDocumentFragment(); let added=0;
    rows.forEach(a=>{
      const key=activityKey(a); if (seenKeys.has(key)) return; seenKeys.add(key);
      const when=a.created_at||a.occurred_at||a.when||''; const mod=a.module||a.table_name||'—'; const act=a.activity||'—'; const note=a.log_note||a.description||a.message||'';
      const li=document.createElement('li');
      li.innerHTML=`
        <div class="activity-icon blue"><i class="fa-regular fa-note-sticky"></i>${escapeHtml(mod)}</div>
        <div class="activity-content">
          <div class="activity-text"><strong>${escapeHtml(act)}</strong> <span>${escapeHtml(note)}</span></div>
          <div class="activity-time"><i class="fa-regular fa-clock"></i>${escapeHtml(when)}</div>
        </div>`;
      frag.appendChild(li); added++;
    });
    if (!ul.children.length) ul.innerHTML='';
    if (added) ul.appendChild(frag);
    return added;
  }

  function recentUrl(){
    const url=new URL('/api/admin/dashboard/recent-activity', window.location.origin);
    url.searchParams.set('limit', String(ACT_LIMIT));
    if (actBefore){
      url.searchParams.set('before', actBefore);
      url.searchParams.set('older_than', actBefore);
      url.searchParams.set('cursor', actBefore);
      url.searchParams.set('max_created_at', actBefore);
    }
    return url.toString();
  }

  async function fetchRecentPage(){
    if (actBusy || actDone) return;
    actBusy=true; EL('activityLoader').style.display='block';
    try{
      const res=await fetch(recentUrl(), { headers:{ 'Authorization':`Bearer ${getToken()}` } });
      if (res.status===404 || res.status===405){ actDone=true; EL('activityEnd').style.display='block'; return; }
      const json=await res.json();
      if (!res.ok || json.status!=='success') throw new Error(json.message || 'Failed');
      const items=json.data?.items || json.data || [];
      const added=appendActivity(items);
      if (items && items.length){
        const oldestFromBatch=getOldestTimestamp(items);
        if (oldestFromBatch) actBefore=oldestFromBatch;
      }
      if (!items.length || items.length<ACT_LIMIT || added===0){
        actDone=true; EL('activityEnd').style.display='block';
      } else {
        fillIfShort();
      }
    }catch(e){
      console.error('recent-activity page error:', e);
      actDone=true; EL('activityEnd').style.display='block';
    }finally{
      actBusy=false; EL('activityLoader').style.display='none';
    }
  }

  function isNearBottom(el, px=80){ return el.scrollTop + el.clientHeight >= el.scrollHeight - px; }
  function maybeLoadMoreActivity(){ const wrap=EL('recentActivityWrap'); if (isNearBottom(wrap)) fetchRecentPage(); }
  function fillIfShort(){
    const wrap=EL('recentActivityWrap');
    if (!actDone && wrap.scrollHeight <= wrap.clientHeight + 10){
      fetchRecentPage();
    }
  }

  let io=null;
  function setupObserver(){
    const wrap=EL('recentActivityWrap'); const sentinel=EL('activitySentinel');
    if (!wrap || !sentinel) return;
    if (io){ io.disconnect(); io=null; }
    io=new IntersectionObserver(entries=>{
      if (entries.some(e=>e.isIntersecting)) fetchRecentPage();
    }, { root:wrap, rootMargin:'200px', threshold:0.0 });
    io.observe(sentinel);
  }

  // -----------------------
  // KPI quicklink handlers
  // -----------------------
  function setupKpiQuicklinks(){
    // idempotent: mark cards that have been initialized with data-kpi-init="1"
    document.querySelectorAll('.kpi-card').forEach(card=>{
      if (card.getAttribute('data-kpi-init') === '1') return;
      // set a11y attributes (redundant-safe)
      card.style.cursor = 'pointer';
      card.setAttribute('role','link');
      if (!card.hasAttribute('tabindex')) card.setAttribute('tabindex','0');

      const onClick = (ev) => {
        // prefer data-url if present; otherwise build from data-filter -> /admin/jobs/view
        const raw = (card.getAttribute('data-url') || '').trim();
        const df = (card.dataset.filter || '').trim();
        const final = raw ? new URL(raw, window.location.origin) : new URL('/admin/jobs/view', window.location.origin);
        if (!raw && df) final.searchParams.set('filter', df);
        // if both data-url and data-filter provided, data-url wins but we still ensure data-filter is set
        if (raw && df) final.searchParams.set('filter', df);

        if (ev.button === 1 || ev.ctrlKey || ev.metaKey || ev.shiftKey) {
          window.open(final.toString(), '_blank');
        } else {
          window.location.href = final.toString();
        }
      };

      const onKeyDown = (ev) => {
        if (ev.key === 'Enter' || ev.key === ' ' || ev.key === 'Spacebar') {
          ev.preventDefault();
          // synthesize a minimal event-like object
          onClick({ button: 0, ctrlKey: ev.ctrlKey, metaKey: ev.metaKey, shiftKey: ev.shiftKey });
        }
      };

      const onFocus = () => card.style.outline = '3px solid rgba(37,99,235,0.18)';
      const onBlur  = () => card.style.outline = 'none';

      card.addEventListener('click', onClick);
      card.addEventListener('keydown', onKeyDown);
      card.addEventListener('focus', onFocus);
      card.addEventListener('blur', onBlur);

      card.setAttribute('data-kpi-init','1');
    });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    setActiveTab(currentPeriod);
    loadDashboard(currentPeriod);
    const wrap=EL('recentActivityWrap');
    wrap.addEventListener('scroll', maybeLoadMoreActivity); // fallback
    setupObserver();
    window.addEventListener('resize', ()=>{ fillIfShort(); });

    // initial KPI setup (in case there is static data or you're rendering server-side values)
    setupKpiQuicklinks();
  });

  // Refresh handler shows explicit friendly message and local jobs loader
  btnRefresh?.addEventListener('click', ()=> {
    showJobsChartLoader(true, `Loading ${periodLabel(currentPeriod)}…`);
    setRefreshing(true);
    loadDashboard(currentPeriod);
  });

  // Tabs: show loader immediately with friendly label (local to jobs chart) then load data
  document.querySelectorAll('.tab').forEach(tab=>{
    tab.addEventListener('click', ()=>{
      document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      currentPeriod = tab.dataset.period;
      showJobsChartLoader(true, `Loading ${periodLabel(currentPeriod)}…`);
      loadDashboard(currentPeriod);
    });
  });
  function setActiveTab(period){ document.querySelectorAll('.tab').forEach(t=> t.classList.toggle('active', t.dataset.period===period)); }

  // Periodic refresh (keeps dashboard fresh)
  setInterval(()=> loadDashboard(currentPeriod), 5*60*1000);
})();
</script>

@endsection
