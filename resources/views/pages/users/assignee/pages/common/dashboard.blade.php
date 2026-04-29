{{-- resources/views/modules/assignee/dashboard.blade.php --}}
@extends('pages.users.assignee.layout.structure')
 
@section('title', 'My Jobs Dashboard')
 
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<style>
  * { box-sizing: border-box; }
  
  .dashboard-page {
    background: var(--bg-body);
    min-height: 100vh;
    padding: 24px;
    font-family: var(--font-sans);
  }

  .page-header {
    margin-bottom: 28px;
  }

  .page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-color);
    margin: 0 0 6px;
    font-family: var(--font-head);
  }

  .page-header p {
    color: var(--muted-color);
    font-size: 14px;
    margin: 0;
  }

  /* Toolbar */
  .toolbar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
  }

  .toolbar .left {
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .toolbar .right {
    display: flex;
    gap: 12px;
  }

  .badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 50px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: var(--transition);
  }
  
  .badge-purple { 
    background: linear-gradient(135deg, #8b5cf6, #a78bfa); 
    color: #fff; 
    border: none;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.25);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 44px;
    padding: 0 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    font-family: var(--font-head);
  }

  .btn-outline-primary {
    background: var(--surface);
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
  }
  
  .btn-outline-primary:hover {
    background: var(--primary-color);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
  }

  /* KPI Cards */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
  }

  .kpi-card {
    background: var(--surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    gap: 16px;
    align-items: center;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
  }

  .kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-color);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
  }

  .kpi-card:hover::before {
    opacity: 1;
  }

  .kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
  }

  .kpi-icon.assigned { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
  .kpi-icon.due { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; }
  .kpi-icon.overdue { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: #fff; }
  .kpi-icon.completed { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; }

  .kpi-content {
    flex: 1;
  }

  .kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-color);
    line-height: 1;
    margin-bottom: 4px;
  }

  .kpi-label {
    font-size: 0.875rem;
    color: var(--muted-color);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Sections */
  .section {
    background: var(--surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
    padding: 24px;
    margin-bottom: 24px;
    transition: var(--transition);
  }
  
  .section:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .section-title {
    margin: 0;
    font-weight: 700;
    font-size: 1.15rem;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-head);
  }
  
  .section-title i {
    color: var(--primary-color);
    font-size: 1.2rem;
  }

  /* Tabs */
  .tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  
  .tab {
    padding: 8px 16px;
    border: 1px solid var(--border-color);
    background: var(--surface);
    color: var(--text-color);
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    transition: var(--transition);
    cursor: pointer;
  }
  
  .tab.active, .tab:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
  }

  /* Charts */
  .chart-container {
    position: relative;
    height: 280px;
    width: 100%;
    background: var(--surface);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 24px;
  }

  .chart-container.short {
    height: 220px;
  }

  .chart-loading {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.8);
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    z-index: 5;
  }

  .chart-loading.show {
    display: flex;
  }

  .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(0,0,0,0.1);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Filters Bar */
  .filters-bar {
    background: var(--surface);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
  }

  .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
  }

  .filter-group {
    flex: 1;
    min-width: 180px;
  }

  .filter-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-color);
  }

  .form-control, .form-select {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 14px;
    color: var(--text-color);
    background: var(--surface);
    transition: all 0.2s;
  }

  .form-control:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .filter-actions {
    display: flex;
    gap: 8px;
  }

  /* Jobs Table */
  .table-responsive {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
  }
  
  .table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
  }
  
  .table th {
    font-size: 12px;
    color: var(--muted-color);
    font-weight: 700;
    padding: 14px 18px;
    background-color: var(--light-color);
    border-bottom: 1px solid var(--border-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    white-space: nowrap;
  }
  
  .table td {
    background: var(--surface);
    border-bottom: 1px solid var(--border-color);
    padding: 16px 18px;
    vertical-align: middle;
    transition: var(--transition);
    color: var(--text-color);
    font-size: 14px;
  }
  
  .table tbody tr {
    transition: var(--transition);
  }
  
  .table tbody tr:hover td {
    background-color: var(--light-color);
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  .job-title {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
  }

  .job-client {
    font-size: 0.8rem;
    color: var(--muted-color);
    display: flex;
    align-items: center;
    gap: 0.35rem;
  }

  /* Badges */
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
    letter-spacing: 0.3px;
  }

  .badge.type-task { background: linear-gradient(135deg, #6366f1, #818cf8); color: #fff; }
  .badge.type-milestone { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #fff; }
  .badge.type-bug { background: linear-gradient(135deg, #ef4444, #f87171); color: #fff; }
  .badge.type-feature { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
  .badge.type-epic { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: #fff; }
  .badge.type-other { background: linear-gradient(135deg, #6b7280, #9ca3af); color: #fff; }

  .badge.priority-lowest { background: linear-gradient(135deg, #94a3b8, #cbd5e1); color: #fff; }
  .badge.priority-low { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #fff; }
  .badge.priority-normal { background: linear-gradient(135deg, #6366f1, #818cf8); color: #fff; }
  .badge.priority-high { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #fff; }
  .badge.priority-urgent { background: linear-gradient(135deg, #ef4444, #f87171); color: #fff; }

  .badge.status-draft { background: linear-gradient(135deg, #6b7280, #9ca3af); color: #fff; }
  .badge.status-planned { background: linear-gradient(135deg, #3b82f6, #60a5fa); color: #fff; }
  .badge.status-in_progress { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #fff; }
  .badge.status-on_hold { background: linear-gradient(135deg, #f97316, #fb923c); color: #fff; }
  .badge.status-blocked { background: linear-gradient(135deg, #ef4444, #f87171); color: #fff; }
  .badge.status-completed { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
  .badge.status-cancelled { background: linear-gradient(135deg, #64748b, #94a3b8); color: #fff; }

  .date-cell {
    font-size: 0.875rem;
    white-space: nowrap;
  }

  .date-overdue {
    color: #dc2626;
    font-weight: 600;
  }

  .date-today {
    color: #ea580c;
    font-weight: 600;
  }

  .date-upcoming {
    color: var(--muted-color);
  }

  .action-btn {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--surface);
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    color: var(--text-color);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    font-weight: 600;
  }

  .action-btn:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
    transform: translateY(-1px);
  }

  /* Pagination */
  .pagination-bar {
    padding: 18px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    background: var(--light-color);
  }

  .pagination-info {
    font-size: 0.875rem;
    color: var(--muted-color);
  }

  .pagination-controls {
    display: flex;
    gap: 6px;
  }

  .page-btn {
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--surface);
    color: var(--text-color);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }

  .page-btn:hover:not(:disabled) {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
  }

  .page-btn.active {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
  }

  .page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  /* Empty State */
  .empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
    color: var(--muted-color);
  }

  .empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  .empty-state h5 {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
    color: var(--text-color);
  }

  /* Modal */
  .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  }

  .modal-header {
    padding: 24px 28px;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface);
  }

  .modal-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .modal-body {
    padding: 28px;
    background: var(--surface);
  }

  .details-table {
    width: 100%;
  }

  .details-table th {
    width: 220px;
    white-space: nowrap;
    color: var(--muted-color);
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid var(--border-color);
  }

  .details-table td {
    padding: 12px 16px;
    color: var(--text-color);
    border-bottom: 1px solid var(--border-color);
  }

  .modal-footer {
    padding: 20px 28px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: var(--surface);
  }

  /* Loading Overlay */
  .loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.8);
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    z-index: 10;
  }

  .loading-overlay.show {
    display: flex;
  }

  /* Toast */
  .toast-container {
    z-index: 1080;
  }

  .toast {
    border-radius: 12px;
    border: none;
    box-shadow: var(--shadow-md);
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .row.g-4 > div {
      margin-bottom: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    .dashboard-page {
      padding: 16px;
    }

    .kpi-grid {
      grid-template-columns: 1fr 1fr;
    }

    .toolbar {
      flex-direction: column;
      align-items: stretch;
    }

    .toolbar .left,
    .toolbar .right {
      width: 100%;
      justify-content: space-between;
    }

    .tabs {
      justify-content: center;
    }

    .section {
      padding: 20px;
    }

    .filter-row {
      flex-direction: column;
    }

    .filter-group {
      width: 100%;
    }

    .chart-container {
      height: 220px;
    }
  }

  @media (max-width: 576px) {
    .kpi-grid {
      grid-template-columns: 1fr;
    }

    .section {
      padding: 16px;
    }

    .table-responsive {
      font-size: 0.85rem;
    }

    .page-header h1 {
      font-size: 24px;
    }

    .table th, .table td {
      padding: 12px 10px;
    }
  }
  /* ===== NEW: Assignee Welcome (API only) ===== */
.assignee-welcome {
  display: none; /* shown by JS when API returns data */
  align-items: center;
  gap: 16px;
  margin: 14px 0 20px;
  padding: 14px 16px;
  border-radius: 12px;
  background: var(--surface);
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.assignee-welcome .avatar {
  width:56px;
  height:56px;
  border-radius:10px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:700;
  font-size:20px;
  color:#fff;
  background: linear-gradient(135deg,#667eea,#764ba2);
  flex-shrink:0;
}

.assignee-welcome .info {
  display:flex;
  flex-direction:column;
  gap:2px;
}

.assignee-welcome .info .name {
  font-weight:700;
  font-size:16px;
  color:var(--text-color);
}

.assignee-welcome .info .email {
  font-size:13px;
  color:var(--muted-color);
}

@media (max-width: 768px) {
  .assignee-welcome { flex-direction: column; align-items: flex-start; }
}

</style>
@endpush
 
@section('content')
<div class="dashboard-page">
  <!-- Page Header -->
  <div class="page-header">
    <h1><i class="fa-solid fa-briefcase me-2"></i>My Jobs Dashboard</h1>
    <p>Track and manage your assigned tasks efficiently</p>
  </div>

  <!-- Assignee Welcome (inserted below header and above toolbar) -->
 

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="left" >
      <!-- Welcome block — add inside .toolbar .left -->
<div id="assigneeWelcome" style="align-items:center; gap:12px;">
  <div id="assigneeAvatar"
       aria-hidden="true"
       style="width:44px; height:44px; border-radius:10px; display:flex;align-items:center;justify-content:center;
              font-weight:700; font-size:16px; background:linear-gradient(135deg,#1273D1,#2493ff); color:#fff;">
    A
  </div>

  <div style="display:flex;flex-direction:column;min-width:0;">
    <div style="font-size:14px; font-weight:700; color:var(--text-color);">
      Welcome,
      <span id="assigneeName" style="margin-left:6px;">Assignee</span>
    </div>
    <div id="assigneeEmail" style="font-size:12px; color:var(--muted-color); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
      assignee@example.com
    </div>
  </div>
</div>

      <span class="badge-pill badge-purple" style="display:none">
        <i class="fa-regular fa-clock"></i>
        <span id="lastUpdated">Just now</span>
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
    <div class="kpi-card">
      <div class="kpi-icon assigned">
        <i class="fa-solid fa-tasks"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-value" id="statAssigned">—</div>
        <div class="kpi-label">Assigned Jobs</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon due">
        <i class="fa-solid fa-calendar-day"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-value" id="statDueToday">—</div>
        <div class="kpi-label">Due Today</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon overdue">
        <i class="fa-solid fa-exclamation-triangle"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-value" id="statOverdue">—</div>
        <div class="kpi-label">Overdue</div>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon completed">
        <i class="fa-solid fa-check-circle"></i>
      </div>
      <div class="kpi-content">
        <div class="kpi-value" id="statCompleted">—</div>
        <div class="kpi-label">Completed</div>
      </div>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="row g-4">
    <!-- Charts Row - 50/50 Split -->
    <div class="col-lg-6">
      <!-- Completion Chart -->
      <div class="section">
        <div class="section-header">
          <h6 class="section-title">
            <i class="fa-solid fa-chart-line"></i>
            Job Completion Trend
          </h6>
          <div class="tabs">
            <button class="tab" data-period="7">7 Days</button>
            <button class="tab active" data-period="30">30 Days</button>
            <button class="tab" data-period="60">60 Days</button>
            <button class="tab" data-period="90">90 Days</button>
          </div>
        </div>
        <div class="chart-container">
          <div class="chart-loading" id="chartLoading">
            <div class="spinner"></div>
          </div>
          <canvas id="completionChart"></canvas>
        </div>
        <div id="chartEmptyState" class="empty-state" style="display: none; padding: 2rem;">
          <i class="fa-solid fa-chart-line"></i>
          <h5>No completion data available</h5>
          <p>There's no job completion data for the selected period.</p>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <!-- Status Distribution -->
      <div class="section">
        <div class="section-header">
          <h6 class="section-title">
            <i class="fa-solid fa-chart-pie"></i>
            Job Status Distribution
          </h6>
        </div>
        <div class="chart-container">
          <div class="chart-loading" id="pieChartLoading">
            <div class="spinner"></div>
          </div>
          <canvas id="statusPieChart"></canvas>
        </div>
        <div id="pieChartEmptyState" class="empty-state" style="display: none; padding: 2rem;">
          <i class="fa-solid fa-chart-pie"></i>
          <h5>No status data available</h5>
          <p>There's no job status data to display.</p>
        </div>
      </div>
    </div>

    <!-- Full Width Sections -->
    <div class="col-12">
      <!-- Filters -->
      <div class="filters-bar">
        <div class="filter-row">
          <div class="filter-group">
            <label for="filterSearch">Search</label>
            <input type="text" class="form-control" id="filterSearch" placeholder="Search by title...">
          </div>
          <div class="filter-group">
            <label for="filterType">Type</label>
            <select class="form-select" id="filterType">
              <option value="">All Types</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterPriority">Priority</label>
            <select class="form-select" id="filterPriority">
              <option value="">All Priorities</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="filterStatus">Status</label>
            <select class="form-select" id="filterStatus">
              <option value="">All Status</option>
            </select>
          </div>
          <div class="filter-actions">
            <button class="btn btn-primary" id="btnApplyFilters">
              <i class="fa-solid fa-filter"></i>
              Apply
            </button>
            <button class="btn btn-outline-primary" id="btnClearFilters">
              <i class="fa-solid fa-xmark"></i>
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Jobs Table -->
      <div class="section" style="position: relative;">
        <div class="loading-overlay" id="loadingOverlay">
          <div class="spinner"></div>
        </div>

        <div class="section-header">
          <h6 class="section-title">
            <i class="fa-solid fa-list"></i>
            My Assigned Jobs
          </h6>
          <span class="badge-pill badge-purple" id="jobsCount">0 jobs</span>
        </div>

        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Job</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Assigned Date</th>
                <th>Deadline</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="jobsTableBody">
              <!-- Jobs will be inserted here -->
            </tbody>
          </table>
        </div>

        <div id="emptyState" class="empty-state" style="display: none;">
          <i class="fa-solid fa-inbox"></i>
          <h5>No jobs found</h5>
          <p>You don't have any jobs assigned matching the current filters.</p>
        </div>

        <div class="pagination-bar" id="paginationBar" style="display: none;">
          <div class="pagination-info" id="paginationInfo">
            Showing 0 of 0 jobs
          </div>
          <div class="pagination-controls" id="paginationControls">
            <!-- Pagination buttons will be inserted here -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="jobDetailsLabel">
          <i class="fa-solid fa-clipboard-list"></i> Job Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table details-table">
            <tbody id="jobDetailsBody">
              <!-- Filled dynamically -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <a id="jobOpenFullLink" href="#" class="btn btn-outline-primary" target="_blank" rel="noopener" style="display:none">
          <i class="fa-solid fa-up-right-from-square"></i>Open Full Page
        </a>
        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Success</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Error</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection
 
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
  'use strict';

  // Toast helpers
  const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastError = new bootstrap.Toast(document.getElementById('toastError'));
  const ok = (msg) => { document.getElementById('toastSuccessText').textContent = msg || 'Success'; toastSuccess.show(); };
  const err = (msg) => { document.getElementById('toastErrorText').textContent = msg || 'Something went wrong'; toastError.show(); };

  // Auth token - assignee login (used for API calls)
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    console.error('No authentication token found');
    err('Please login to continue');
    setTimeout(() => { window.location.href = '/assignee/login'; }, 1400);
    return;
  }
  const headers = {
    'Authorization': 'Bearer ' + TOKEN,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  };
  console.log('🔑 Token loaded (first 20 chars):', TOKEN.substring(0, 20) + '...');

  // API endpoints
  const API = {
    myJobs: '/api/assignedpeople/my-jobs',
    enums: '/api/job-details/enums',
    completionStats: '/api/assignedpeople/my-completion-stats',
    statusStats: '/api/assignedpeople/status-stats',
    me: '/api/assignedpeople/me'
  };

  // Fallback enums
  const fallbackEnums = {
    types: ['task', 'milestone', 'bug', 'feature', 'epic', 'other'],
    priority: ['lowest', 'low', 'normal', 'high', 'urgent'],
    status: ['draft', 'planned', 'in_progress', 'on_hold', 'blocked', 'completed', 'cancelled']
  };

  // State
  let currentPage = 1;
  let perPage = 10;
  let totalJobs = 0;
  let totalPages = 0;
  let stats = { assigned: 0, due_today: 0, overdue: 0, completed: 0 };
  let enums = { types: [], priority: [], status: [] };
  let lastJobs = [];
  let completionChart = null;
  let statusPieChart = null;
  let chartPeriod = 30;

  // DOM elements
  const loadingOverlay = document.getElementById('loadingOverlay');
  const jobsTableBody = document.getElementById('jobsTableBody');
  const emptyState = document.getElementById('emptyState');
  const paginationBar = document.getElementById('paginationBar');
  const paginationInfo = document.getElementById('paginationInfo');
  const paginationControls = document.getElementById('paginationControls');
  const jobsCount = document.getElementById('jobsCount');

  const statAssigned = document.getElementById('statAssigned');
  const statDueToday = document.getElementById('statDueToday');
  const statOverdue = document.getElementById('statOverdue');
  const statCompleted = document.getElementById('statCompleted');

  const filterSearch = document.getElementById('filterSearch');
  const filterType = document.getElementById('filterType');
  const filterPriority = document.getElementById('filterPriority');
  const filterStatus = document.getElementById('filterStatus');

  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const btnClearFilters = document.getElementById('btnClearFilters');
  const btnRefresh = document.getElementById('btnRefresh');
  const refreshIcon = document.getElementById('refreshIcon');

  // Chart elements
  const chartCanvas = document.getElementById('completionChart');
  const pieChartCanvas = document.getElementById('statusPieChart');
  const chartLoading = document.getElementById('chartLoading');
  const pieChartLoading = document.getElementById('pieChartLoading');
  const chartEmptyState = document.getElementById('chartEmptyState');
  const pieChartEmptyState = document.getElementById('pieChartEmptyState');
  const periodButtons = document.querySelectorAll('.tab[data-period]');

  // Assignee welcome elements (IDs should match the HTML you inserted)
  // Make sure you have an HTML block with ids: assigneeWelcome, assigneeName, assigneeEmail, assigneeAvatar
  const assigneeWelcome = document.getElementById('assigneeWelcome');
  const assigneeNameEl = document.getElementById('assigneeName');
  const assigneeEmailEl = document.getElementById('assigneeEmail');
  const assigneeAvatarEl = document.getElementById('assigneeAvatar');

  // Helper: escape HTML
  const esc = (str) => String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const dash = (v) => (v === null || v === undefined || v === '') ? '—' : v;

  function setRefreshing(spin) {
    if (!refreshIcon) return;
    if (spin) {
      refreshIcon.classList.add('fa-spin');
      btnRefresh?.setAttribute('disabled', 'disabled');
    } else {
      refreshIcon.classList.remove('fa-spin');
      btnRefresh?.removeAttribute('disabled');
    }
  }

  function updateLastUpdated() {
    const el = document.getElementById('lastUpdated');
    if (el) {
      const now = new Date();
      // show HH:MM in user's locale (en-GB for consistency with dates used elsewhere)
      el.textContent = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
  }

  // Helper: fetch JSON with better error handling (reuses headers variable)
  async function fetchJSON(url, options = {}) {
    try {
      console.log('📡 Fetching:', url);
      const response = await fetch(url, { headers, ...options });

      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ API Error Response:', errorText);

        let errorMsg = `HTTP ${response.status}: ${response.statusText}`;
        try {
          const errorJson = JSON.parse(errorText);
          if (errorJson.message) errorMsg = errorJson.message;
        } catch {}
        throw new Error(errorMsg);
      }

      const data = await response.json();
      console.log('✅ Response:', data);
      return data;
    } catch (error) {
      console.error('❌ Fetch error for', url, error);
      throw error;
    }
  }

  /* ---------- Load assignee info via API only (no storage reads/writes) ---------- */
  async function loadAssigneeInfo() {
    try {
      if (!API.me || !TOKEN) {
        if (assigneeWelcome) assigneeWelcome.style.display = 'none';
        return;
      }

      const res = await fetch(API.me, { headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' } });
      if (!res.ok) {
        console.warn('/api/assignedpeople/me returned', res.status);
        if (assigneeWelcome) assigneeWelcome.style.display = 'none';
        return;
      }

      const json = await res.json().catch(()=>({}));
      // API might return { data: { name, email } } or { name, email }
      const data = json?.data || json || null;
      if (!data) {
        if (assigneeWelcome) assigneeWelcome.style.display = 'none';
        return;
      }

      const name = (data.name || data.full_name || data.username || '').trim();
      const email = (data.email || '').trim();
      if (!name && !email) {
        if (assigneeWelcome) assigneeWelcome.style.display = 'none';
        return;
      }

      if (assigneeNameEl) assigneeNameEl.textContent = name || 'Assignee';
      if (assigneeEmailEl) assigneeEmailEl.textContent = email || '—';
      const initials = (name ? name.split(' ').filter(Boolean).map(n => n[0]).slice(0,2).join('').toUpperCase() : 'A');
      if (assigneeAvatarEl) assigneeAvatarEl.textContent = initials;

      if (assigneeWelcome) assigneeWelcome.style.display = 'flex';
    } catch (e) {
      console.error('loadAssigneeInfo error', e);
      if (assigneeWelcome) assigneeWelcome.style.display = 'none';
    }
  }

  /* ---------- Enums ---------- */
  async function loadEnums() {
    try {
      const data = await fetchJSON(API.enums);
      enums = data?.data || fallbackEnums;
      console.log('📋 Enums loaded:', enums);
    } catch (error) {
      console.warn('⚠️ Using fallback enums due to error:', error?.message || error);
      enums = fallbackEnums;
    }

    // Populate filter dropdowns
    if (filterType) filterType.innerHTML = '<option value="">All Types</option>' + enums.types.map(t => `<option value="${esc(t)}">${esc(t.replace(/_/g, ' '))}</option>`).join('');
    if (filterPriority) filterPriority.innerHTML = '<option value="">All Priorities</option>' + enums.priority.map(p => `<option value="${esc(p)}">${esc(p.replace(/_/g, ' '))}</option>`).join('');
    if (filterStatus) filterStatus.innerHTML = '<option value="">All Status</option>' + enums.status.map(s => `<option value="${esc(s)}">${esc(s.replace(/_/g, ' '))}</option>`).join('');
  }

  /* ---------- Completion stats (line chart) ---------- */
  async function loadCompletionStats(period = 30) {
    if (chartLoading) chartLoading.classList.add('show');
    chartEmptyState.style.display = 'none';

    try {
      const periodParam = `${period}d`;
      console.log('📊 Loading completion stats for period:', periodParam);
      const response = await fetchJSON(`${API.completionStats}?period=${periodParam}`);

      console.log('📊 Full completion stats response:', response);
      let chartData = null;

      if (response && response.data) {
        // expect response.data.daily_completed = [{date, completed}, ...]
        if (Array.isArray(response.data.daily_completed)) {
          chartData = response.data.daily_completed.map(item => ({
            date: item.date,
            count: Number(item.completed || 0)
          }));
        } else if (Array.isArray(response.data)) {
          // fallback: if API returns an array directly
          chartData = response.data.map(item => ({ date: item.date, count: Number(item.completed || item.count || 0) }));
        }
      }

      if (chartData && chartData.length > 0) {
        renderLineChart(chartData);
      } else {
        if (completionChart) { completionChart.destroy(); completionChart = null; }
        chartCanvas.style.display = 'none';
        chartEmptyState.style.display = 'block';
      }
    } catch (error) {
      console.error('❌ Failed to load completion stats:', error);
      if (completionChart) { completionChart.destroy(); completionChart = null; }
      chartCanvas.style.display = 'none';
      chartEmptyState.style.display = 'block';
      chartEmptyState.innerHTML = `
        <i class="fa-solid fa-exclamation-triangle"></i>
        <h5>Failed to load chart data</h5>
        <p>${esc(error.message || 'Unable to load completion statistics')}</p>
      `;
    } finally {
      if (chartLoading) chartLoading.classList.remove('show');
    }
  }

  function renderLineChart(data) {
    if (!data || !data.length) {
      console.warn('No data available for line chart');
      if (chartCanvas) chartCanvas.style.display = 'none';
      if (chartEmptyState) chartEmptyState.style.display = 'block';
      return;
    }

    // Hide/show
    chartCanvas.style.display = 'block';
    chartEmptyState.style.display = 'none';

    const hasNonZero = data.some(d => Number(d.count) > 0);
    if (!hasNonZero) {
      if (completionChart) { completionChart.destroy(); completionChart = null; }
      chartCanvas.style.display = 'none';
      chartEmptyState.style.display = 'block';
      chartEmptyState.innerHTML = `
        <i class="fa-solid fa-chart-line"></i>
        <h5>No completion activity</h5>
        <p>No jobs were completed in the selected period.</p>
      `;
      return;
    }

    const dates = data.map(item => {
      const dt = new Date(item.date);
      // fallback to raw string if invalid
      return isNaN(dt) ? item.date : dt.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    });
    const counts = data.map(item => Number(item.count || 0));

    const computedStyle = getComputedStyle(document.documentElement);
    const textColor = computedStyle.getPropertyValue('--text-color') || '#1f2937';
    const borderColor = computedStyle.getPropertyValue('--border-color') || '#e5e7eb';
    const primaryColor = computedStyle.getPropertyValue('--primary-color') || '#3b82f6';

    if (completionChart) completionChart.destroy();

    const ctx = chartCanvas.getContext('2d');
    completionChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Jobs Completed',
          data: counts,
          borderColor: primaryColor,
          backgroundColor: 'rgba(37, 99, 235, 0.15)',
          borderWidth: 2,
          tension: 0.35,
          fill: true,
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: primaryColor,
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => `Jobs completed: ${ctx.parsed.y}`,
              title: (ctx) => {
                try {
                  const fullDate = new Date(data[ctx[0].dataIndex].date);
                  return isNaN(fullDate) ? ctx[0].label : fullDate.toLocaleDateString('en-GB', { weekday:'short', year:'numeric', month:'short', day:'numeric' });
                } catch (e) {
                  return ctx[0].label;
                }
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: borderColor, drawBorder: false },
            ticks: { color: textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
          },
          y: {
            beginAtZero: true,
            grace: '5%',
            grid: { color: borderColor, drawBorder: false },
            ticks: { color: textColor, precision: 0 }
          }
        }
      }
    });
  }

  /* ---------- Status Pie Chart ---------- */
  async function loadStatusStats() {
    if (pieChartLoading) pieChartLoading.classList.add('show');
    pieChartEmptyState.style.display = 'none';

    try {
      console.log('📊 Loading status stats for pie chart');
      const data = await fetchJSON(API.statusStats);

      if (data && data.data) {
        renderPieChart(data.data);
      } else {
        if (statusPieChart) { statusPieChart.destroy(); statusPieChart = null; }
        pieChartCanvas.style.display = 'none';
        pieChartEmptyState.style.display = 'block';
      }
    } catch (error) {
      console.error('❌ Failed to load status stats:', error);
      if (statusPieChart) { statusPieChart.destroy(); statusPieChart = null; }
      pieChartCanvas.style.display = 'none';
      pieChartEmptyState.style.display = 'block';
      pieChartEmptyState.innerHTML = `
        <i class="fa-solid fa-exclamation-triangle"></i>
        <h5>Failed to load status data</h5>
        <p>${esc(error.message || 'Unable to load job status statistics')}</p>
      `;
    } finally {
      if (pieChartLoading) pieChartLoading.classList.remove('show');
    }
  }

  function renderPieChart(data) {
  // Helper to safely extract numbers from API fields
  const getCount = (obj, keys) => {
    if (!obj) return 0;
    for (let k of keys) {
      if (obj[k] !== undefined && obj[k] !== null && !isNaN(obj[k])) {
        return Number(obj[k]);
      }
    }
    return 0;
  };

  // ---- Compute from API or fallback from lastJobs ----
  let counts = {
    planned: 0,
    in_progress: 0,
    on_hold: 0,
    overdue: 0,
    completed: 0,
    other: 0
  };

  // If API data is given
  if (data && typeof data === 'object') {
    const src = data.data && typeof data.data === 'object' ? data.data : data;

    counts.planned     = getCount(src, ['planned']);
    counts.in_progress = getCount(src, ['in_progress']); // ✅ only exact in_progress
    counts.on_hold     = getCount(src, ['on_hold']);
    counts.overdue     = getCount(src, ['overdue']);
    counts.completed   = getCount(src, ['completed']);
  }

  // If everything is zero, fallback to local jobs (client-side)
  if (Object.values(counts).reduce((a, b) => a + b, 0) === 0 && Array.isArray(lastJobs)) {
    counts = { planned: 0, in_progress: 0, on_hold: 0, overdue: 0, completed: 0, other: 0 };
    lastJobs.forEach(job => {
      const s = (job.status || '').trim().toLowerCase();
      switch (s) {
        case 'planned': counts.planned++; break;
        case 'in_progress': counts.in_progress++; break; // ✅ exact match only
        case 'on_hold': counts.on_hold++; break;
        case 'overdue': counts.overdue++; break;
        case 'completed': counts.completed++; break;
        // explicitly ignore "assigned"
        case 'assigned': break;
        default: counts.other++; break;
      }
    });
  }

  // ---- Build chart arrays ----
  const labels = [];
  const dataVals = [];
  const colors = [];
  const colorMap = {
    planned: '#a874f1ff',
    in_progress: '#edb65cff',
    on_hold: '#454547ff',
    overdue: '#ed776cff',
    completed: '#7ce89aff',
    other: '#cbd5e1ff'
  };

  for (const [key, value] of Object.entries(counts)) {
    if (value > 0 && key !== 'assigned') {
      labels.push(key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
      dataVals.push(value);
      colors.push(colorMap[key] || '#ccc');
    }
  }

  // ---- Handle empty ----
  if (dataVals.length === 0) {
    if (statusPieChart) { statusPieChart.destroy(); statusPieChart = null; }
    pieChartCanvas.style.display = 'none';
    pieChartEmptyState.style.display = 'block';
    return;
  }

  // ---- Draw chart ----
  if (statusPieChart) statusPieChart.destroy();
  pieChartCanvas.style.display = 'block';
  pieChartEmptyState.style.display = 'none';

  const ctx = pieChartCanvas.getContext('2d');
  const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-color') || '#111';
  const lightColor = getComputedStyle(document.documentElement).getPropertyValue('--light-color') || '#eee';

  statusPieChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: dataVals,
        backgroundColor: colors,
        borderColor: lightColor,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: textColor, padding: 12, usePointStyle: true, pointStyle: 'circle' }
        },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const label = ctx.label || '';
              const val = ctx.parsed || 0;
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const perc = total ? Math.round((val / total) * 100) : 0;
              return `${label}: ${val} (${perc}%)`;
            }
          }
        }
      }
    }
  });
}

  /* ---------- Filters / Query builder ---------- */
  function buildQueryParams() {
    const params = new URLSearchParams({ page: currentPage, per_page: perPage });
    const q = filterSearch?.value?.trim();
    if (q) params.set('q', q);
    const type = filterType?.value;
    if (type) params.set('type', type);
    const priority = filterPriority?.value;
    if (priority) params.set('priority', priority);
    const status = filterStatus?.value;
    if (status) params.set('status', status);
    return params.toString();
  }

  /* ---------- Jobs listing ---------- */
  async function loadJobs() {
    if (loadingOverlay) loadingOverlay.classList.add('show');
    if (emptyState) emptyState.style.display = 'none';
    if (paginationBar) paginationBar.style.display = 'none';

    try {
      const queryString = buildQueryParams();
      console.log('🔍 Loading jobs with query:', queryString);
      const data = await fetchJSON(`${API.myJobs}?${queryString}`);

      const jobs = Array.isArray(data.data) ? data.data : [];
      lastJobs = jobs;
      const meta = data.meta || {};
// after you set lastJobs, meta, totalJobs, totalPages, stats
totalJobs = meta.total || 0;
totalPages = meta.total_pages || 0;
stats = meta.stats || { assigned: 0, due_today: 0, overdue: 0, completed: 0 };

updateStats();
renderJobs(jobs);
renderPagination();

// update jobs count badge to show total (not page length)
if (jobsCount) jobsCount.textContent = `${totalJobs} ${totalJobs === 1 ? 'job' : 'jobs'}`;

// show a clear toast: number loaded on page vs total assigned
const loadedOnPage = Array.isArray(jobs) ? jobs.length : 0;
ok(`Loaded ${totalJobs} jobs`);

    } catch (error) {
      console.error('❌ Failed to load jobs:', error);

      const msg = error?.message || '';
      if (msg.includes('401') || msg.toLowerCase().includes('unauthorized')) {
        err('Session expired. Please login again.');
        setTimeout(() => { window.location.href = '/assignee/login'; }, 1400);
      } else if (msg.includes('403') || msg.toLowerCase().includes('forbidden')) {
        err('Access denied. You may not have permission to view jobs.');
      } else if (msg.includes('404')) {
        err('Jobs endpoint not found. Please contact administrator.');
      } else if (msg.toLowerCase().includes('network') || msg.toLowerCase().includes('failed to fetch')) {
        err('Network error. Please check your connection.');
      } else {
        err('Failed to load jobs: ' + (error.message || error));
      }

      if (jobsTableBody) jobsTableBody.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
    } finally {
      if (loadingOverlay) loadingOverlay.classList.remove('show');
    }
  }

  function updateStats() {
    if (statAssigned) statAssigned.textContent = stats.assigned || 0;
    if (statDueToday) statDueToday.textContent = stats.due_today || 0;
    if (statOverdue) statOverdue.textContent = stats.overdue || 0;
    if (statCompleted) statCompleted.textContent = stats.completed || 0;
  }

  function getDeadlineClass(deadline) {
    if (!deadline) return 'date-upcoming';
    const deadlineDate = new Date(deadline);
    const today = new Date(); today.setHours(0,0,0,0);
    const deadlineDay = new Date(deadlineDate); deadlineDay.setHours(0,0,0,0);
    if (deadlineDay < today) return 'date-overdue';
    if (deadlineDay.getTime() === today.getTime()) return 'date-today';
    return 'date-upcoming';
  }

  function formatDate(dateString) {
    if (!dateString) return null;
    try {
      const d = new Date(dateString);
      if (isNaN(d)) return null;
      return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    } catch {
      return null;
    }
  }

  function renderJobs(jobs) {
    if (!Array.isArray(jobs) || jobs.length === 0) {
      if (jobsTableBody) jobsTableBody.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      return;
    }
    if (emptyState) emptyState.style.display = 'none';

    if (!jobsTableBody) return;
    jobsTableBody.innerHTML = jobs.map((job, idx) => {
      const deadlineClass = getDeadlineClass(job.planned_deadline_at);
      const deadlineText = formatDate(job.planned_deadline_at) || '—';
      const assignedDate = formatDate(job.assigned_at) || '—';
      const client = job.client_name ? esc(job.client_name) : '<em>No client</em>';
      const type = esc((job.type || 'task').replace(/_/g, ' '));
      const priority = esc((job.priority || 'normal').replace(/_/g, ' '));
      const status = esc((job.status || 'planned').replace(/_/g, ' '));
      return `
        <tr>
          <td>
            <div class="job-title">${esc(job.title)}</div>
            <div class="job-client">
              <i class="fa-regular fa-building"></i>
              ${client}
            </div>
          </td>
          <td><span class="badge type-${esc(job.type || 'task')}">${type}</span></td>
          <td><span class="badge priority-${esc(job.priority || 'normal')}">${priority}</span></td>
          <td><span class="badge status-${esc(job.status || 'planned')}">${status}</span></td>
          <td class="date-cell">${assignedDate}</td>
          <td class="date-cell ${deadlineClass}">${deadlineText}</td>
          <td>
            <button type="button" class="action-btn btn-view" data-idx="${idx}" title="View Details">
              <i class="fa-solid fa-eye"></i> View
            </button>
          </td>
        </tr>
      `;
    }).join('');
  }

  /* ---------- Pagination ---------- */
  function renderPagination() {
    if (!paginationBar) return;
    if (totalJobs === 0) {
      paginationBar.style.display = 'none';
      return;
    }

    paginationBar.style.display = 'flex';
    const start = totalJobs > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
    const end = Math.min(currentPage * perPage, totalJobs);
    if (paginationInfo) paginationInfo.textContent = `Showing ${start}-${end} of ${totalJobs} jobs`;

    let buttons = '';
    buttons += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}"><i class="fa-solid fa-chevron-left"></i></button>`;

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages || 1, startPage + maxButtons - 1);
    if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);

    for (let i = startPage; i <= endPage; i++) {
      buttons += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }

    buttons += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}"><i class="fa-solid fa-chevron-right"></i></button>`;
    paginationControls.innerHTML = buttons;

    paginationControls.querySelectorAll('.page-btn[data-page]').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!btn.disabled) {
          currentPage = parseInt(btn.getAttribute('data-page'), 10);
          loadJobs();
        }
      });
    });
  }

  /* ---------- Job details modal ---------- */
  function buildDetailsTable(job) {
    const rows = [];
    const labeled = (label, value, isHTML=false) => {
      const v = dash(value);
      rows.push(`
        <tr>
          <th>${esc(label)}</th>
          <td>${isHTML ? v : esc(v)}</td>
        </tr>
      `);
    };

    labeled('Job ID', job.id);
    labeled('Title', job.title);
    labeled('Client', job.client_name || null);
    labeled('Type', (job.type || '').replace(/_/g, ' '));
    labeled('Priority', (job.priority || '').replace(/_/g, ' '));
    labeled('Status', (job.status || '').replace(/_/g, ' '));
    labeled('Assigned At', formatDate(job.assigned_at));
    labeled('Deadline', formatDate(job.planned_deadline_at));
    labeled('Planned Start', formatDate(job.planned_start_at));
    labeled('Planned End', formatDate(job.planned_end_at));
    labeled('Document', job.document_name || null);
    labeled('Description', job.description ? `<div style="white-space:pre-wrap">${esc(job.description)}</div>` : null, true);

    if (job.parent_id) labeled('Parent Job', `#${job.parent_id}`);
    if (job.ordering !== undefined) labeled('Ordering', job.ordering);
    if (job.created_at) labeled('Created At', formatDate(job.created_at));
    if (job.updated_at) labeled('Last Updated', formatDate(job.updated_at));

    return rows.join('');
  }

  const jobDetailsModalEl = document.getElementById('jobDetailsModal');
  const jobDetailsBody = document.getElementById('jobDetailsBody');
  const jobModal = jobDetailsModalEl ? new bootstrap.Modal(jobDetailsModalEl) : null;

  function showJobModal(job) {
    if (!jobModal || !jobDetailsBody) return;
    const label = document.getElementById('jobDetailsLabel');
    if (label) label.innerHTML = `<i class="fa-solid fa-clipboard-list"></i>Job #${esc(job.id)} — ${esc(job.title || 'Details')}`;
    jobDetailsBody.innerHTML = buildDetailsTable(job);
    jobModal.show();
  }

  // Delegate click on "View" buttons
  if (jobsTableBody) {
    jobsTableBody.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-view');
      if (!btn) return;
      const idx = parseInt(btn.getAttribute('data-idx'), 10);
      const job = lastJobs[idx];
      if (!job) return;
      showJobModal(job);
    });
  }

  // Period buttons
  periodButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      periodButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      chartPeriod = parseInt(btn.getAttribute('data-period'), 10);
      loadCompletionStats(chartPeriod);
    });
  });

  // Filters
  function applyFilters() { currentPage = 1; loadJobs(); }
  function clearFilters() {
    if (filterSearch) filterSearch.value = '';
    if (filterType) filterType.value = '';
    if (filterPriority) filterPriority.value = '';
    if (filterStatus) filterStatus.value = '';
    currentPage = 1;
    loadJobs();
  }

  // Event listeners
  if (btnApplyFilters) btnApplyFilters.addEventListener('click', applyFilters);
  if (btnClearFilters) btnClearFilters.addEventListener('click', clearFilters);
  if (btnRefresh) btnRefresh.addEventListener('click', () => {
    setRefreshing(true);
    currentPage = 1;
    Promise.all([ loadJobs(), loadCompletionStats(chartPeriod), loadStatusStats() ])
      .finally(() => { setRefreshing(false); updateLastUpdated(); ok('Dashboard refreshed successfully'); });
  });
  if (filterSearch) filterSearch.addEventListener('keypress', (e) => { if (e.key === 'Enter') applyFilters(); });

  // Initialize
  async function init() {
    console.log('🚀 Initializing assignee dashboard...');
    updateLastUpdated();

    // Load assignee welcome first
    await loadAssigneeInfo();

    await loadEnums();
    await loadJobs();
    await loadCompletionStats(chartPeriod);
    await loadStatusStats();
  }

  init();

  // Auto-refresh every 5 minutes
  setInterval(() => {
    loadJobs();
    loadCompletionStats(chartPeriod);
    loadStatusStats();
    updateLastUpdated();
  }, 5 * 60 * 1000);

})();
</script>
@endpush
