@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = 'client-user';
  $portalDashboardUrl = '/client-user/dashboard';
  $portalJobsUrl = '/client-user/jobs/view';
  $portalBillsUrl = '/client-user/bills';
  $portalNotificationsUrl = '/client-user/notifications';
  $portalLoginUrl = '/client-user/login';
  $portalLogoutApi = '/api/client-users/logout';
  $portalThemeKey = 'theme:client-user';
@endphp

@section('title', 'Client Dashboard')

@push('styles')
<style>
.dashboard-page {
  background:
    radial-gradient(circle at top left, rgba(14,165,233,.10), transparent 25%),
    radial-gradient(circle at top right, rgba(245,158,11,.08), transparent 22%),
    radial-gradient(circle at bottom right, rgba(16,185,129,.08), transparent 26%),
    var(--bg-body);
  min-height:100vh;
  padding:24px;
  font-family:var(--font-sans);
}
.page-header {display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;}
.page-header h1 {margin:0;font-size:28px;font-weight:700;color:var(--text-color);}
.page-header p {margin:6px 0 0;color:#64748b;font-size:14px;}
.header-actions {display:flex;gap:10px;flex-wrap:wrap;}
.kpi-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:24px;}
.kpi-card {
  background:linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.90));
  border:1px solid rgba(226,232,240,.9);
  border-radius:22px;
  padding:20px;
  box-shadow:0 18px 36px rgba(15,23,42,.08);
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
.kpi-card.clients::before { background:linear-gradient(135deg,#06b6d4,#3b82f6); }
.kpi-card.jobs::before { background:linear-gradient(135deg,#8b5cf6,#ec4899); }
.kpi-card.documents::before { background:linear-gradient(135deg,#14b8a6,#22c55e); }
.kpi-card.due::before { background:linear-gradient(135deg,#f59e0b,#f97316); }
.kpi-card.overdue::before { background:linear-gradient(135deg,#ef4444,#f43f5e); }
.kpi-card.completed::before { background:linear-gradient(135deg,#10b981,#84cc16); }
.kpi-top {display:flex;align-items:center;justify-content:space-between;gap:14px;position:relative;z-index:1;}
.kpi-icon {
  width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:20px;box-shadow:0 12px 24px rgba(15,23,42,.12);
}
.kpi-card.clients .kpi-icon { background:linear-gradient(135deg,#06b6d4,#3b82f6); }
.kpi-card.jobs .kpi-icon { background:linear-gradient(135deg,#8b5cf6,#ec4899); }
.kpi-card.documents .kpi-icon { background:linear-gradient(135deg,#14b8a6,#22c55e); }
.kpi-card.due .kpi-icon { background:linear-gradient(135deg,#f59e0b,#f97316); }
.kpi-card.overdue .kpi-icon { background:linear-gradient(135deg,#ef4444,#f43f5e); }
.kpi-card.completed .kpi-icon { background:linear-gradient(135deg,#10b981,#84cc16); }
.kpi-label {font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.6px;}
.kpi-value {font-size:32px;font-weight:800;color:var(--text-color);margin-top:14px;position:relative;z-index:1;}
.section {
  background:rgba(255,255,255,.94);
  border:1px solid rgba(226,232,240,.92);
  border-radius:22px;
  padding:22px;
  box-shadow:0 18px 36px rgba(15,23,42,.08);
  margin-bottom:24px;
}
.section-head {display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.section-head h2 {margin:0;font-size:18px;font-weight:700;color:var(--text-color);}
.btn-linkish {display:inline-flex;align-items:center;gap:8px;height:42px;padding:0 16px;border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-weight:600;text-decoration:none;}
.btn-linkish.docs {background:#ecfdf5;border-color:#a7f3d0;color:#047857;}
.jobs-table {width:100%;border-collapse:collapse;}
.jobs-table th,.jobs-table td {padding:14px 10px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:14px;color:var(--text-color);vertical-align:top;}
.jobs-table th {font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;}
.badge {display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#eff6ff;color:#1d4ed8;}
.empty-state {padding:32px 12px;text-align:center;color:#94a3b8;}
.status-pill {display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12px;color:#475569;}
.doc-meta {display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;}
.doc-pill {display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:11px;color:#64748b;}
@media (max-width: 768px) {
  .dashboard-page { padding:16px; }
  .kpi-grid { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
<div class="dashboard-page">
  <div class="page-header">
    <div>
      <h1>Client Dashboard</h1>
      <p id="welcomeText">Loading your client scope…</p>
    </div>
    <div class="header-actions">
      <a href="/client-user/jobs/view" class="btn-linkish">
        <i class="fa-solid fa-briefcase"></i>
        View Jobs
      </a>
      <a href="/client-user/bills" class="btn-linkish docs">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        View Bills
      </a>
      <a href="/client-user/notifications" class="btn-linkish docs">
        <i class="fa-solid fa-bell"></i>
        Notifications
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
      <h2>Recent Published Bills</h2>
      <span class="status-pill" id="billScopeNote">Loading…</span>
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
          </tr>
        </thead>
        <tbody id="recentBillsRows">
          <tr><td colspan="5" class="text-center py-4">Loading…</td></tr>
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
    statDocuments: document.getElementById('statDocuments'),
    statDueToday: document.getElementById('statDueToday'),
    statOverdue: document.getElementById('statOverdue'),
    statCompleted: document.getElementById('statCompleted'),
    recentRows: document.getElementById('recentJobsRows'),
    recentBillsRows: document.getElementById('recentBillsRows'),
    recentDocumentsRows: document.getElementById('recentDocumentsRows'),
    billScopeNote: document.getElementById('billScopeNote'),
    docScopeNote: document.getElementById('docScopeNote'),
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
    if (!Array.isArray(rows) || !rows.length) {
      els.recentBillsRows.innerHTML = '<tr><td colspan="5"><div class="empty-state">No published bills are visible for your assigned client scope.</div></td></tr>';
      return;
    }

    els.recentBillsRows.innerHTML = rows.map(bill => `
      <tr>
        <td><a href="/client-user/bills" style="color:#2563eb;text-decoration:none;font-weight:600;">Bill #${esc(bill.id || '—')}</a></td>
        <td>${esc(bill.client_name || '—')}</td>
        <td>${fmtDate(bill.bill_date)}</td>
        <td>${fmtDate(bill.due_date)}</td>
        <td>${esc(Number(bill.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</td>
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
      const recentBills = dashboard?.data?.recent_bills || [];
      const recentDocuments = dashboard?.data?.recent_documents || [];

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

  loadDashboard();
})();
</script>
@endpush
