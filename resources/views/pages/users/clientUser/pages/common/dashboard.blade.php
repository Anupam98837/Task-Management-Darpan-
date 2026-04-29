@extends('pages.users.assignee.layout.structure')

@php
  $portalPrefix = 'client-user';
  $portalDashboardUrl = '/client-user/dashboard';
  $portalJobsUrl = '/client-user/jobs/view';
  $portalNotificationsUrl = '/client-user/notifications';
  $portalLoginUrl = '/client-user/login';
  $portalLogoutApi = '/api/client-users/logout';
  $portalThemeKey = 'theme:client-user';
@endphp

@section('title', 'Client User Dashboard')

@push('styles')
<style>
.dashboard-page {background: var(--bg-body);min-height:100vh;padding:24px;font-family:var(--font-sans);}
.page-header {display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;}
.page-header h1 {margin:0;font-size:28px;font-weight:700;color:var(--text-color);}
.page-header p {margin:6px 0 0;color:#64748b;font-size:14px;}
.kpi-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:24px;}
.kpi-card {background:var(--surface);border:1px solid var(--border-color);border-radius:18px;padding:20px;box-shadow:var(--shadow-sm);}
.kpi-label {font-size:13px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.4px;}
.kpi-value {font-size:32px;font-weight:800;color:var(--text-color);margin-top:10px;}
.section {background:var(--surface);border:1px solid var(--border-color);border-radius:18px;padding:22px;box-shadow:var(--shadow-sm);}
.section-head {display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.section-head h2 {margin:0;font-size:18px;font-weight:700;color:var(--text-color);}
.btn-linkish {display:inline-flex;align-items:center;gap:8px;height:40px;padding:0 16px;border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-weight:600;text-decoration:none;}
.jobs-table {width:100%;border-collapse:collapse;}
.jobs-table th,.jobs-table td {padding:14px 10px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:14px;color:var(--text-color);}
.jobs-table th {font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;}
.badge {display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#eff6ff;color:#1d4ed8;}
.empty-state {padding:32px 12px;text-align:center;color:#94a3b8;}
.status-pill {display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12px;color:#475569;}
</style>
@endpush

@section('content')
<div class="dashboard-page">
  <div class="page-header">
    <div>
      <h1>Client User Dashboard</h1>
      <p id="welcomeText">Loading your client scope…</p>
    </div>
    <a href="/client-user/jobs/view" class="btn-linkish">
      <i class="fa-solid fa-briefcase"></i>
      View Jobs
    </a>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Scoped Clients</div>
      <div class="kpi-value" id="statClients">—</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Visible Jobs</div>
      <div class="kpi-value" id="statJobs">—</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Due Today</div>
      <div class="kpi-value" id="statDueToday">—</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Overdue</div>
      <div class="kpi-value" id="statOverdue">—</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Completed</div>
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
</div>
@endsection

@push('scripts')
<script>
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    setTimeout(() => { window.location.href = '/client-user/login'; }, 500);
    return;
  }

  const headers = { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' };
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
    statDueToday: document.getElementById('statDueToday'),
    statOverdue: document.getElementById('statOverdue'),
    statCompleted: document.getElementById('statCompleted'),
    recentRows: document.getElementById('recentJobsRows'),
  };

  async function fetchJSON(url) {
    const res = await fetch(url, { headers });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || ('HTTP ' + res.status));
    return data;
  }

  function renderRecentJobs(rows) {
    if (!Array.isArray(rows) || !rows.length) {
      els.recentRows.innerHTML = '<tr><td colspan="5"><div class="empty-state">No jobs are currently visible for your assigned client scope.</div></td></tr>';
      return;
    }

    els.recentRows.innerHTML = rows.map(job => `
      <tr>
        <td><a href="/client-user/jobs/view" style="color:#2563eb;text-decoration:none;font-weight:600;">${esc(job.title || '—')}</a></td>
        <td>${esc(job.client_name || '—')}</td>
        <td>${esc(String(job.status || '—').replaceAll('_', ' '))}</td>
        <td>${esc(String(job.priority || '—').replaceAll('_', ' '))}</td>
        <td>${fmtDate(job.planned_deadline_at)}</td>
      </tr>
    `).join('');
  }

  async function loadDashboard() {
    try {
      const [me, dashboard] = await Promise.all([
        fetchJSON('/api/client-users/me'),
        fetchJSON('/api/client-users/dashboard'),
      ]);

      const user = me?.data || {};
      const quick = dashboard?.data?.quick_links || {};
      const recent = dashboard?.data?.recent_jobs || [];

      els.welcomeText.textContent = `${user.name || 'Client user'} can currently view jobs for ${quick.scoped_clients || 0} scoped client records.`;
      els.scopeNote.textContent = `${quick.scoped_clients || 0} clients in scope`;
      els.statClients.textContent = quick.scoped_clients ?? 0;
      els.statJobs.textContent = quick.visible_jobs ?? 0;
      els.statDueToday.textContent = quick.due_today ?? 0;
      els.statOverdue.textContent = quick.overdue ?? 0;
      els.statCompleted.textContent = quick.completed ?? 0;

      renderRecentJobs(recent);
    } catch (error) {
      els.welcomeText.textContent = error.message || 'Failed to load dashboard.';
      els.scopeNote.textContent = 'Unable to load';
      renderRecentJobs([]);
    }
  }

  loadDashboard();
})();
</script>
@endpush
