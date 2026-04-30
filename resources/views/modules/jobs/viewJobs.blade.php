{{-- resources/views/modules/jobs/viewJobs.blade.php --}}
@php($jobPortalRole = strtolower(trim((string)($jobPortalRole ?? ''))))
@section('title','Jobs')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box}.attach-preview{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.preview-item{position:relative;border:1px solid #e2e8f0;border-radius:8px;padding:6px;background:var(--surface);transition:.2s}.preview-item:hover{border-color:#3b82f6}.preview-image{width:60px;height:60px;object-fit:cover;border-radius:4px}.preview-file{display:flex;align-items:center;gap:6px;padding:6px 8px;min-width:120px}.preview-file i{font-size:16px;color:#64748b}.preview-info{flex:1;min-width:0}.preview-name{font-size:11px;font-weight:500;color:var(--text-color);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.preview-size{font-size:10px;color:#94a3b8}.remove-preview{position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#dc2626;color:#fff;border:0;display:flex;align-items:center;justify-content:center;font-size:10px;cursor:pointer;opacity:0;transition:.2s}.preview-item:hover .remove-preview{opacity:1}.remove-preview:hover{background:#b91c1c;transform:scale(1.1)}.attach-chip{display:inline-flex;align-items:center;gap:6px;border:1px solid #e2e8f0;background:var(--light-color);padding:6px 10px;border-radius:8px;font-size:12px;color:var(--text-color);margin:2px}.attach-chip.removed{opacity:.5;text-decoration:line-through}.attach-chip .fa-rotate-left{color:#16a34a}.attach-chip .fa-xmark{color:#dc2626}
.jobs-page{background:var(--bg-body);min-height:100vh;padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Inter',sans-serif}.page-header{margin-bottom:28px}.page-header h1{font-size:28px;font-weight:700;color:var(--text-color);margin:0 0 6px}.page-header p{color:#64748b;font-size:14px;margin:0}.toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
/* Filter labels */.filter-group{display:flex;gap:12px;flex-wrap:wrap}.filter-field{display:flex;flex-direction:column;gap:6px;min-width:130px}.filter-field label{font-size:11px;font-weight:600;color:#64748b;padding-left:2px}
.search-box{position:relative;flex:1;min-width:280px;max-width:420px}.search-box input{width:100%;height:44px;padding:0 16px 0 42px;border:1px solid #e2e8f0;border-radius:12px;font-size:14px;background:var(--surface);color:var(--text-color);transition:.2s}.search-box input:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}.search-box svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none}
.select-box{height:44px;padding:0 38px 0 14px;border:1px solid #e2e8f0;border-radius:12px;font-size:14px;background:var(--surface) url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 8l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;appearance:none;color:var(--text-color);cursor:pointer;transition:.2s}.select-box:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.btn{display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 20px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:.2s;border:0;text-decoration:none}.btn-primary{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;box-shadow:0 2px 8px rgba(59,130,246,.25)}.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(59,130,246,.35)}.btn-secondary{background:var(--surface);color:var(--text-color);border:1px solid #e2e8f0}.btn-secondary:hover{background:var(--primary-color);border-color:var(--primary-color);color:#fff}
.filter-tree-current{font-size:12px;color:#64748b;padding-left:2px}
.picker-tree{list-style:none;margin:0;padding:0 0 0 8px;position:relative}
.picker-tree::before{content:"";position:absolute;left:14px;top:0;bottom:8px;width:1px;background:#e2e8f0}
.picker-tree>li{position:relative;margin:0 0 8px 0;padding-left:24px}
.picker-tree>li::before{content:"";position:absolute;left:14px;top:16px;width:16px;height:1px;background:#e2e8f0}
.picker-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid #e2e8f0;border-radius:12px;background:var(--surface)}
.picker-toggle{width:28px;height:28px;border:1px solid #e2e8f0;border-radius:8px;background:var(--bg-body);display:inline-flex;align-items:center;justify-content:center}
.picker-toggle.open i{transform:rotate(90deg)}
.picker-children{margin:8px 0 10px 0;padding-left:24px;display:none}
.picker-children .picker-children{margin-left:16px}
.picker-title{display:flex;flex-direction:column;gap:2px}
.picker-title small{color:#64748b}
.data-card{background:var(--surface);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}.table-container{overflow-x:auto}table{width:100%;border-collapse:collapse;color:var(--text-color)}thead{background:var(--light-color)}thead th{padding:14px 18px;text-align:left;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #e2e8f0;white-space:nowrap}tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s;background:var(--surface)}tbody tr:hover{opacity:.95}tbody tr.child-row{background:var(--light-color)}tbody td{padding:16px 18px;font-size:14px;color:var(--text-color);vertical-align:middle}tbody tr.child-row td:first-child{padding-left:calc(18px + var(--indent,0px))!important}
.badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600}.badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}.badge.planned{background:#fef3c7;color:#d97706}.badge.in_progress{background:#dbeafe;color:#2563eb}.badge.completed{background:#dcfce7;color:#16a34a}.badge.on_hold{background:#f1f5f9;color:#64748b}.badge.cancelled{background:#fee2e2;color:#dc2626}.badge.low{background:#f1f5f9;color:#64748b}.badge.normal{background:#dbeafe;color:#2563eb}.badge.high{background:#fef3c7;color:#d97706}.badge.urgent{background:#fee2e2;color:#dc2626}
.expander{width:26px;height:26px;border:1px solid #e2e8f0;border-radius:6px;background:var(--light-color);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:.2s}.expander:hover{background:#3b82f6;border-color:#3b82f6;color:#fff}.expander[aria-busy=true]{opacity:.6;cursor:progress}
.btn-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:1px solid #e2e8f0;border-radius:8px;background:var(--surface);color:var(--text-color);cursor:pointer;transition:.2s;padding:0}.btn-icon:hover{transform:translateY(-1px)}.btn-edit:hover{background:#3b82f6;border-color:#3b82f6;color:#fff}.btn-delete:hover{background:#dc2626;border-color:#dc2626;color:#fff}.btn-view:hover{background:#10b981;border-color:#10b981;color:#fff}.btn-assign:hover{background:#8b5cf6;border-color:#8b5cf6;color:#fff}
.pagination{display:flex;align-items:center;justify-content:space-between;padding:18px 20px;background:var(--light-color);border-top:1px solid #f1f5f9}.pagination-info{font-size:14px;color:#64748b}.pagination-controls{display:flex;gap:6px}.page-btn{min-width:38px;height:38px;padding:0 12px;border:1px solid #e2e8f0;border-radius:8px;background:var(--surface);color:var(--text-color);font-size:14px;font-weight:600;cursor:pointer;transition:.2s}.page-btn:hover:not(:disabled){background:var(--primary-color);border-color:var(--primary-color);color:#fff}.page-btn.active{background:var(--primary-color);color:#fff;border-color:var(--primary-color)}.page-btn:disabled{opacity:.4;cursor:not-allowed}
.empty-state{text-align:center;padding:60px 20px;color:#94a3b8}.empty-state svg{margin-bottom:16px}.empty-state h3{font-size:18px;font-weight:600;color:#475569;margin:0 0 8px}.empty-state p{font-size:14px;margin:0}
th.sortable{cursor:pointer;user-select:none}th.sortable:hover{color:#3b82f6}.tiny-sort-icon{font-size:12px;margin-left:6px;opacity:.6}.tiny-sort-icon.active{opacity:1;color:#3b82f6}
.modal-content{border-radius:16px;border:0;box-shadow:0 20px 40px rgba(0,0,0,.15)}.modal-header{padding:24px 28px;border-bottom:1px solid #f1f5f9;background:var(--surface)}.modal-title{font-size:20px;font-weight:700;color:var(--text-color)}.modal-body{padding:28px;background:var(--surface)}.modal-footer{padding:20px 28px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;background:var(--light-color)}
.form-label{display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:8px}.form-control,.form-select{width:100%;height:44px;padding:0 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;color:var(--text-color);background:var(--surface);transition:.2s}.form-control:focus,.form-select:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}.chip{display:inline-flex;align-items:center;gap:6px;border:1px solid #e2e8f0;background:var(--light-color);padding:6px 10px;border-radius:8px;font-size:12px;color:var(--text-color)}
.spinner-border{width:18px;height:18px;border:3px solid rgba(0,0,0,.1);border-top-color:var(--primary-color);border-radius:50%;animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
.offcanvas{width:560px;background:var(--bg-body)}.offcanvas-header{padding:24px;border-bottom:1px solid #e2e8f0;background:var(--surface)}.offcanvas-body{padding:24px;background:var(--bg-body);border-bottom:1px solid #e2e8f0}.chat-box{display:flex;flex-direction:column;gap:10px;height:48vh;overflow:auto;padding:6px 2px;border:1px solid #e2e8f0;border-radius:12px;background:var(--surface)}.msg{display:flex;align-items:flex-end;gap:8px;max-width:80%;margin:12px 0}.msg.me{align-self:flex-end;flex-direction:row-reverse}.avatar{flex:0 0 32px;width:32px;height:32px;border-radius:50%;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;background:var(--secondary-color,#6B2528)}.bubble{max-width:82%;border:1px solid #e2e8f0;border-radius:16px;padding:12px 14px;background:var(--surface);color:var(--text-color);word-break:break-word}.msg.me .avatar{background:var(--primary-color,#9E363A)}.msg.me .bubble{background:var(--primary-color,#9E363A);color:#fff;border-color:transparent;border-bottom-right-radius:6px}.msg:not(.me){align-self:flex-start}.msg:not(.me) .bubble{background:var(--light-color,#FBECEC);border-bottom-left-radius:6px}
.composer-sticky{bottom:0;padding-top:12px;}.chat-composer{display:flex;align-items:center;gap:8px;border:1px solid #e2e8f0;border-radius:18px;background:var(--surface);padding:6px}.icon-btn{width:40px;height:40px;border:1px solid #e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--surface);color:var(--text-color);cursor:pointer;transition:all .2s ease;flex:0 0 40px}.icon-btn:hover{background:var(--light-color);transform:scale(1.05)}.icon-btn.primary{background:var(--primary-color);color:#fff;border-color:var(--primary-color)}.icon-btn.primary:hover{transform:scale(1.08);box-shadow:0 4px 12px rgba(59,130,246,.35)}#composer{min-height:32px;max-height:140px;overflow:auto;flex:1 1 auto;border:0;background:transparent;outline:0;padding:6px}#composer:empty:before{content:attr(data-placeholder);color:#9aa1a9}
.assign-list{max-height:420px;overflow:auto;border:1px solid #e2e8f0;border-radius:12px;padding:8px;background:var(--surface)}.assign-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;border-bottom:1px solid #f1f5f9;transition:background .15s}.assign-item:hover{background:var(--light-color)}.assign-item:last-child{border-bottom:0}
@media(max-width:992px){.offcanvas{width:100%;max-width:580px}}@media(max-width:768px){.toolbar{flex-direction:column;align-items:stretch}.search-box{max-width:100%}.filter-group{width:100%}table{min-width:980px}}@media (max-width: 767.98px){.offcanvas.offcanvas-end{width:100%}}
.skeleton{position:relative;overflow:hidden;background:linear-gradient(90deg,rgba(0,0,0,.03),rgba(0,0,0,.08),rgba(0,0,0,.03));background-size:200% 100%;animation:shimmer 1.2s ease-in-out infinite;border-radius:6px}@keyframes shimmer{from{background-position:200% 0}to{background-position:-200% 0}}.sk-title{height:18px;width:70%}tbody tr.row-updating{position:relative;opacity:.85}tbody tr.row-updating::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,rgba(59,130,246,0) 0%,rgba(59,130,246,.6) 50%,rgba(59,130,246,0) 100%);background-size:140% 100%;animation:loadingbar 1.1s linear infinite}@keyframes loadingbar{from{background-position:-40% 0}to{background-position:140% 0}}.skel-pill{display:inline-block;height:22px;width:110px;border-radius:999px}
/* Hide PDF export options in both modals (kept) */input[value="pdf"]+.form-check-label{display:none!important}
/* NEW: full-screen loading overlay inside Assign People modal */.modal-loading{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.05);backdrop-filter:blur(2px);z-index:5}.modal-loading .spinner-border{width:22px;height:22px;border-width:3px}
/* Drawer description box */.drawer-section-title{font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;margin:16px 0 8px}.drawer-desc{border:1px solid #e2e8f0;border-radius:12px;padding:16px;background:var(--surface);max-height:200px;overflow:auto}.msg{display:flex;align-items:flex-end;gap:10px;margin:10px 0}.msg.me{flex-direction:row-reverse}.msg .avatar{width:32px;height:32px;border-radius:50%;background:#e2e8f0;color:#334155;display:flex;align-items:center;justify-content:center;font-weight:700;flex:0 0 32px}.msg.me .avatar{background:#1273D1;color:#fff}.msg .bubble{max-width:70%;background:#eef2f7;border:1px solid #e5e7eb;border-radius:14px;padding:10px 12px;color:#0f172a}.msg.me .bubble{background:#1273D1;color:#fff;border-color:#1273D1}
/* Assignees dropdown */.assignees-dd{position:fixed;z-index:1400;min-width:260px;max-width:360px;max-height:320px;overflow:auto;background:var(--surface);border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.12);padding:6px;display:none}.assignees-dd{position:fixed;z-index:1600;width:280px;max-height:340px;background:var(--surface);border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 14px 40px rgba(0,0,0,.16);padding:6px;display:none;overflow:auto;scrollbar-width:thin}.assignees-dd::-webkit-scrollbar{height:8px;width:8px}.assignees-dd::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:999px}.assignees-dd::before{content:"";position:absolute;width:12px;height:12px;background:var(--surface);border:1px solid #e2e8f0;border-bottom:none;border-right:none;transform:rotate(45deg);z-index:-1}.assignees-dd.arrow-bottom::before{top:-6px}.assignees-dd.arrow-top::before{bottom:-6px;transform:rotate(225deg)}.assignees-dd .dd-header{display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-bottom:1px solid #f1f5f9;margin-bottom:2px}.assignees-dd .dd-title{font-weight:700;font-size:13.5px;color:#0f172a}.assignees-dd .dd-count{font-size:12px;color:#64748b}.right-msg{text-align:right;justify-content:flex-end}.assignees-dd .dd-item{display:grid;grid-template-columns:32px 1fr auto;align-items:center;gap:10px;padding:10px 12px;border-radius:10px}.assignees-dd .dd-item:hover{background:#f8fafc}.assignees-dd .dd-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;background:#6b7280;border:1px solid #e2e8f0}.assignees-dd .dd-name{font-weight:700;color:#0f172a;font-size:13px;line-height:1.2}.assignees-dd .dd-sub{font-size:12px;color:#64748b;line-height:1.2;margin-top:2px;word-break:break-all}.assignees-dd .dd-right{margin-left:8px}.assignees-dd .dd-empty{padding:14px;text-align:center;color:#94a3b8;font-size:13px}
/* make chip clearly clickable */.chip.clickable{cursor:pointer;user-select:none}.chip.clickable:hover{filter:brightness(0.98)}
/* Expense bubble styles */
.hidden {
  display: none !important;
}
/* --- Hide chat composer when it's not in the active tab --- */
/* Hide chat composer unless the conversation tab is active */
.tab-pane:not(.active) .composer-sticky {
  display: none !important;
}

.tab-pane:not(.active)#conversation {
  padding-bottom: 0 !important;
}

.nav-tabs .nav-link {
  color: #64748b;
  font-weight: 600;
  font-size: 14px;
  border: none;
  background: none;
  transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
  color: #2563eb; /* blue hover */
  background-color: #f1f5f9; /* light gray-blue hover background */
}

.nav-tabs .nav-link.active {
  color: #2563eb; /* bright blue text */
  background-color: #e0f2fe; /* soft blue background */
  border-bottom: 3px solid #2563eb; /* blue underline */
  border-radius: 6px 6px 0 0;
}
.nav-tabs .nav-link {
  color: #64748b;
  font-weight: 600;
  font-size: 14px;
  border: none;
  background: none;
  transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
  color: #2563eb;
  background-color: #f1f5f9;
}

.nav-tabs .nav-link.active {
  color: #2563eb;
  background-color: #e0f2fe;
  border-bottom: 3px solid #2563eb;
  border-radius: 6px 6px 0 0;
}
#btnViewMoreExpenses {
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  color: #334155;
  border-radius: 8px;
  font-size: 14px;
  padding: 6px 0;
  transition: background 0.2s;
}
#btnViewMoreExpenses:hover {
  background: #e2e8f0;
}
/* Center all tab headings */
#drawerTabs {
  display: flex;
  justify-content: center;
  align-items: center;
  border-bottom: 1px solid #e2e8f0; /* soft divider */
}

/* Base style for tabs */
#drawerTabs .nav-link {
  display: flex;
  align-items: center;
  gap: 6px; /* space between icon and text */
  text-align: center;
  font-weight: 500;
  color: #475569; /* slate-600 */
  margin: 0 8px;
  border: none;
  border-bottom: 3px solid transparent;
  border-radius: 10px 10px 0 0;
  background-color: transparent;
  transition: all 0.2s ease;
  padding: 8px 16px;
}

/* Hover effect */
#drawerTabs .nav-link:hover {
  background-color: #f8fafc; /* very light gray-blue */
  color: #1e3a8a; /* blue text */
}

/* Active tab */
#drawerTabs .nav-link.active {
  color: #0f172a !important;  /* dark navy text */
  background-color: #e0f2fe !important; /* soft blue background */
  border-bottom: 3px solid #3b82f6; /* blue underline */
  font-weight: 600;
  box-shadow: 0 -1px 3px rgba(59,130,246,0.15);
}
/* expander badge (attached to left expander) */
.expander-wrap { position: relative; display: inline-flex; align-items: center; }
.expander { width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; padding:0; background:var(--surface); border:1px solid #e6eef6; cursor:pointer; }
.expander i { font-size:12px; }

.expander-child-badge{
  background: #ef4444;
  color: #fff;
  border-radius: 999px;
  padding: 2px 6px;
  font-size: 11px;
  line-height: 1;
  min-width: 18px;
  text-align: center;
  display: inline-block;
  font-weight: 700;
  margin-left: -10px; /* tuck the badge closer to the button */
  transform: translateX(-6px) translateY(-12px); /* fine-tune position */
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  pointer-events: none; /* keep clicks for the expander */
}

/* small-screen adjustment */
@media (max-width:600px){
  .expander { width:32px; height:32px; }
  .expander-child-badge { font-size:10px; padding:2px 5px; transform: translateX(-4px) translateY(-10px); }
}
/* For Bootstrap 5 */
.badge.badge-pending,
.badge.pending {
    /* background-color: ; */
    color: #ffc107;
    border: 1px solid #ffc107;
}
/* Badge Draft - Warning Style */
.badge.badge-draft,
.badge.draft {
    /* background-color: #ffc107; */
    color: #ffc107;
    border: 1px solid #ffc107;
}
.badge.badge-blocked,
.badge.blocked {
    /* background-color: ; */
    color: var(--bs-danger);
    border: 1px solid var(--bs-danger);
}
</style>
@endpush
@section('content')
<div class="jobs-page">
  <div class="page-header">
    <h1>Jobs Management</h1>
    <p>Manage jobs, assign people, and track progress</p>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-box">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <input id="searchInput" type="search" placeholder="Search jobs by title or description...">
    </div>

    <!-- Labeled filter fields (no logic change) -->
    <div class="filter-group">
      <div class="filter-field" style="min-width:180px">
        <label for="filterClient">Client</label>
        <div class="d-flex gap-2">
          <button type="button" id="btnPickFilterClient" class="btn btn-secondary" style="height:44px;padding:0 14px;min-width:0;">
            <i class="fa-regular fa-building"></i><span>Choose Client</span>
          </button>
          <button type="button" id="clearFilterClient" class="btn btn-secondary" style="height:44px;padding:0 12px;min-width:0;" title="Clear client filter">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <select id="filterClient" class="select-box" style="display:none">
          <option value="">All Clients</option>
        </select>
        <div id="filterClientCurrent" class="filter-tree-current">All Clients</div>
      </div>

      <div class="filter-field" style="min-width:130px">
        <label for="filterType">Type</label>
        <select id="filterType" class="select-box">
          <option value="">All Types</option>
        </select>
      </div>

      <div class="filter-field" style="min-width:130px">
        <label for="filterPriority">Priority</label>
        <select id="filterPriority" class="select-box">
          <option value="">All Priority</option>
        </select>
      </div>

      <div class="filter-field" style="min-width:160px">
        <label for="filterStatus">Status</label>
        <select id="filterStatus" class="select-box">
          <option value="">All Status</option>
        </select>
      </div>

      <div class="filter-field" style="min-width:100px">
        <label for="perPage">Per page</label>
        <select id="perPage" class="select-box">
          <option>10</option>
          <option>20</option>
          <option>50</option>
          <option>100</option>
        </select>
      </div>
    </div>
  <!-- put this just before the Add Job button inside .toolbar -->
<div id="appliedFilterPill" style="display:none;align-self:center;margin-left:8px;">
  <span class="chip" id="appliedFilterText"></span>
  <button id="clearAppliedFilter" class="btn btn-secondary" style="height:34px;padding:0 8px;margin-left:8px;">Clear</button>
</div>

    <a href="/admin/jobs/add" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      Add Job
    </a>
  </div>

  <!-- Data Card -->
  <div class="data-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th style="width:54px;"></th>
            <th class="sortable" data-sort="title">
              Title
              <i class="fa fa-sort tiny-sort-icon" id="iconSortTitle"></i>
            </th>
            <th>Client</th>
            <th class="sortable" data-sort="priority">
              Priority
              <i class="fa fa-sort tiny-sort-icon" id="iconSortPriority"></i>
            </th>
            <th class="sortable" data-sort="status">
              Status
              <i class="fa fa-sort tiny-sort-icon" id="iconSortStatus"></i>
            </th>
            <th>Planned</th>
            <th class="sortable" data-sort="created">
              Created
              <i class="fa fa-sort tiny-sort-icon" id="iconSortCreated"></i>
            </th>
            <th style="width:80px;">Actions</th>
          </tr>
        </thead>
        <tbody id="jTbody">
          <tr>
            <td colspan="8" class="text-center" style="padding: 40px;">
              <div style="display: flex; align-items: center; justify-content: center; gap: 10px; color: #94a3b8;">
                <div class="spinner-border"></div>
                <span>Loading jobs...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <div class="pagination-info" id="resultsInfo">
        Showing 0-0 of 0 jobs
      </div>
      <div class="pagination-controls" id="pager"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="filterClientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fa-regular fa-building me-2"></i>Choose Client Filter</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="filterClientLoad" class="text-muted small mb-2" style="display:none;">Loading clients…</div>
        <ul id="filterClientTree" class="picker-tree"></ul>
        <div class="tiny muted">Selecting a parent client includes its child clients too.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="btnSaveFilterClient"><i class="fa-solid fa-check me-1"></i>Apply Filter</button>
      </div>
    </div>
  </div>
</div>

{{-- Assign People Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- NEW: full overlay while the modal fetches -->
      <div id="assignModalLoading" class="modal-loading">
        <div style="display:flex;align-items:center;gap:10px;color:#64748b">
          <div class="spinner-border"></div>
          <span>Loading people…</span>
        </div>
      </div>
      <!-- /NEW -->

      <div class="modal-header">
        <h5 class="modal-title">Assign People to <span id="assignJobTitle" class="text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
          <input id="assignSearch" type="search" class="form-control" placeholder="Search people...">
          <div class="form-check" style="min-width: fit-content;">
            <input class="form-check-input" type="checkbox" id="assignSelectAll">
            <label class="form-check-label" for="assignSelectAll" style="font-size: 12px;">Select all</label>
          </div>
        </div>

        <!-- existing inline busy row (kept) -->
        <div id="assignBusy" style="display: none; margin-bottom: 12px;">
          <div style="display: flex; align-items: center; gap: 8px; color: #64748b;">
            <div class="spinner-border"></div>
            <span>Loading people...</span>
          </div>
        </div>

        <div id="assignList" class="assign-list"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="assignSaveBtn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a 2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="2"/>
            <path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="2"/>
          </svg>
          Save
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Full Details Modal --}}
<div class="modal fade" id="jobViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Job #<span id="vId">—</span></div>
          <h5 class="modal-title" id="vTitle">—</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px;">
          <span class="chip"><i class="fa-regular fa-building"></i><span id="vClient">—</span></span>
          <span class="chip"><i class="fa-regular fa-flag"></i><span id="vType">—</span></span>
          <span class="chip"><i class="fa-solid fa-signal"></i><span id="vPriority">—</span></span>
          <span class="chip"><i class="fa-solid fa-bars-progress"></i><span id="vStatus">—</span></span>
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; background: var(--light-color); margin-bottom: 20px;">
          <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Planned Schedule</div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div><i class="fa-regular fa-calendar-days" style="margin-right: 6px;"></i>Start: <span id="vStart">—</span></div>
            <div><i class="fa-regular fa-calendar-check" style="margin-right: 6px;"></i>End: <span id="vEnd">—</span></div>
            <div><i class="fa-regular fa-hourglass-half" style="margin-right: 6px;"></i>Deadline: <span id="vDeadline">—</span></div>
            <div><i class="fa-regular fa-clock" style="margin-right: 6px;"></i>Duration: <span id="vDuration">—</span></div>
          </div>
        </div>

        <div class="drawer-section-title">Description</div>
        <div class="drawer-desc" id="vDesc">—</div>

        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 16px 0 8px;">Assignees</div>
        <div id="vAssignees" style="font-size: 13px;">—</div>

        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 16px 0 8px;">Attachments</div>
        <div id="vMedia" style="font-size: 13px;">—</div>
      </div>
    </div>
  </div>
</div>

{{-- Right Drawer: Job Quick View + Messages & Expenses --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="jobDrawer">
  <div class="offcanvas-header">
    <div style="min-width: 0; flex: 1;">
      <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Job #<span id="dJobId">—</span></div>
      <div id="dTitleSk" class="skeleton sk-title" style="display: none;"></div>
      <h5 class="offcanvas-title" id="dTitle" style="margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">—</h5>
      <div style="font-size: 12px; color: #64748b; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
        <i class="fa-regular fa-building" style="margin-right: 4px;"></i><span id="dClient">—</span>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  
  <div class="offcanvas-body position-relative">
    <div id="drawerBusy" style="position: absolute; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,.05); backdrop-filter: blur(2px); z-index: 10;">
      <div class="spinner-border"></div>
    </div>

    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 16px;">
      <span class="chip"><i class="fa-regular fa-flag"></i><span id="dChipType">—</span></span>
      <span class="chip"><i class="fa-solid fa-signal"></i><span id="dChipPriority">—</span></span>
      <span class="chip"><i class="fa-regular fa-user"></i><span id="dChipAssignees">—</span></span>
    </div>

    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
      <label style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 0;">Status</label>
      <select id="dStatus" class="form-select" style="max-width: 220px; height: 38px;"></select>
      <button class="btn btn-primary" id="dSaveStatus" style="height: 38px; padding: 0 16px;">
        <span class="spinner-border" id="dStatusSpin" style="display: none; margin-right: 6px;"></span>Update
      </button>
    </div>

    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; background: var(--light-color); margin-bottom: 20px;">
      <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Planned Schedule</div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div><i class="fa-regular fa-calendar-days" style="margin-right: 6px;"></i>Start: <span id="dStart">—</span></div>
        <div><i class="fa-regular fa-calendar-check" style="margin-right: 6px;"></i>End: <span id="dEnd">—</span></div>
        <div><i class="fa-regular fa-hourglass-half" style="margin-right: 6px;"></i>Deadline: <span id="dDeadline">—</span></div>
        <div><i class="fa-regular fa-clock" style="margin-right: 6px;"></i>Duration: <span id="dDuration">—</span></div>
      </div>
    </div>

    <!-- NEW: full description in drawer -->
    <div class="drawer-section-title">Description</div>
    <div id="dDesc" class="drawer-desc">—</div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mt-4 justify-content-center text-center " id="drawerTabs" role="tablist" style="border-bottom: 1px solid #e2e8f0;">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" onclick=" document.getElementById('conversation').style.display='block';" id="conversation-tab" data-bs-toggle="tab" data-bs-target="#conversation" type="button" role="tab" aria-controls="conversation" aria-selected="true" style="font-size: 14px; font-weight: 600; padding: 12px 16px; border: none; background: none; color: #64748b;">
          <i class="fa-regular fa-message" style="margin-right: 6px;"></i>Conversation
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" onclick=" document.getElementById('conversation').style.display='none';" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab" aria-controls="expenses" aria-selected="false" style="font-size: 14px; font-weight: 600; padding: 12px 16px; border: none; background: none; color: #64748b;">
          <i class="fa-regular fa-credit-card" style="margin-right: 6px;"></i>Expenses
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-3" id="drawerTabContent">
      <!-- Conversation Tab -->
      <div class="tab-pane fade show active" id="conversation" role="tabpanel" aria-labelledby="conversation-tab">
        <div class="drawer-section-title">Conversation</div>

        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
          <button class="btn btn-secondary" id="btnLoadOlder" disabled style="height: 38px; padding: 0 16px;">
            <span id="olderSpin" class="spinner-border" style="display: none; margin-right: 6px;"></span>
            Load older
          </button>
          </div>

        <div id="msgBusy" style="display: none; margin-bottom: 12px;">
          <div style="display: flex; align-items: center; gap: 8px; color: #64748b;">
            <div class="spinner-border"></div>
            <span>Loading messages...</span>
          </div>
        </div>

        <div id="chat" class="chat-box" aria-live="polite"></div>

        <div id="msgEmpty" style="display: none; text-align: center; color: #94a3b8; font-size: 13px; padding: 20px;">
          No messages yet.
        </div>
        <div class="composer-sticky">
          <div class="chat-composer">
            <button class="icon-btn" id="btnAttach" title="Attach files">
              <i class="fa fa-paperclip"></i>
            </button>
            <div id="composer" class="form-control" contenteditable="true" data-placeholder="Type a message" aria-label="Message composer"></div>
            <button class="icon-btn primary" id="btnSend" title="Send">
              <i class="fa fa-paper-plane"></i>
            </button>
          </div>
          <input id="attachInput" type="file" style="display: none;" multiple>
          <div id="attachChips" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;"></div>
          <div style="font-size: 11px; color: #64748b; margin-top: 6px; margin-bottom: 6px">
            Tip: Press <strong>Enter</strong> to send, <strong>Shift + Enter</strong> for new line.
          </div>
        </div>
      </div>
      <!-- Expenses Tab -->
    <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
    <div class="drawer-section-title">Expenses</div>

    <!-- Add Expense Button -->
    <div style="display: flex; justify-content: center; margin-bottom: 12px;">
        <button class="btn btn-primary" id="btnAddExpense" style="height: 38px; padding: 0 16px;">
            <i class="fa fa-plus" style="margin-right: 8px"></i>Add Expense
        </button>
    </div>

    <!-- Expense Form (initially hidden) -->
    <div id="expenseForm" style="display: none; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; background: var(--surface); margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
                <label class="form-label">Expense Head</label>
                <select id="expenseHead" class="form-select" style="height: 38px;">
                    <option value="">Select expense head</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div>
                <label class="form-label">Expense Date</label>
                <input type="date" id="expenseDate" class="form-control" style="height: 38px;">
            </div>
        </div>
        <div style="margin-bottom: 12px;">
            <label class="form-label">Amount</label>
            <input type="number" id="expenseAmount" class="form-control" placeholder="0.00 INR" style="height: 38px;">
        </div>
        <div style="margin-bottom: 12px;">
            <label class="form-label">Note</label>
            <textarea id="expenseNote" class="form-control" rows="3" placeholder="Add notes about this expense..."></textarea>
        </div>
        <div style="margin-bottom: 16px;">
            <label class="form-label">Upload File</label>
            <input type="file" id="expenseFile" class="form-control" multiple>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Supported formats: PDF, JPG, PNG (Max 10MB)</div>
        </div>
        <div style="display: flex; gap: 8px; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" id="btnCancelExpense" style="height: 38px; padding: 0 16px;">Cancel</button>
            <button type="button" class="btn btn-primary" id="btnSaveExpense" style="height: 38px; padding: 0 16px;">
                <span class="spinner-border" id="expenseSaveSpin" style="display: none; margin-right: 6px;"></span>
                Save Expense
            </button>
        </div>
    </div>

    <!-- Expenses List -->
    <div id="expensesList" class="expenses-list" aria-live="polite">
        <!-- Expenses will be loaded here dynamically -->
    </div>

    <!-- Empty state for expenses -->
    <div id="expensesEmpty" style="text-align: center; padding: 40px 20px; color: #94a3b8;">
        <i class="fa-regular fa-credit-card" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
        <h4 style="font-size: 16px; font-weight: 600; color: #64748b; margin-bottom: 8px;">No expenses yet</h4>
        <p style="font-size: 14px; margin: 0;">Click "Add Expense" to track your first expense.</p>
    </div>
</div>
{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastInfo" class="toast align-items-center text-bg-primary border-0 mt-2" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastInfoText">Info</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  </div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(()=>{
/* ================== HELPERS ================== */
const forcedRole = @json($jobPortalRole);
const roleFromPath = window.location.pathname.startsWith('/client-user/')
  ? 'client_user'
  : (window.location.pathname.startsWith('/assignee/') ? 'assignee' : '');
const EXPECTED_ROLE = (forcedRole || roleFromPath).toLowerCase();
let TOKEN = '';
let role = EXPECTED_ROLE;
let IS_ASSIGNEE = false;
let IS_CLIENT_USER = false;
let IS_READ_ONLY = false;
const LOGIN_REDIRECT = EXPECTED_ROLE === 'client_user'
  ? '/client-user/login'
  : (EXPECTED_ROLE === 'assignee' ? '/assignee/login' : '/');
const H={
  get Authorization(){ return TOKEN ? 'Bearer ' + TOKEN : ''; },
  Accept:'application/json'
};
const byId=(id)=>document.getElementById(id);
const esc=(s='')=>String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
const ok =(m)=>{byId('toastSuccessText').textContent=m||'Done';new bootstrap.Toast('#toastSuccess').show()};
const err=(m)=>{byId('toastErrorText').textContent  =m||'Something went wrong';new bootstrap.Toast('#toastError').show()};
const info=(m)=>{byId('toastInfoText').textContent =m||'Info';new bootstrap.Toast('#toastInfo').show()};
const fmt=(iso,dt)=>{if(!iso)return'—';const d=new Date(iso);if(isNaN(d))return'—';return dt?d.toLocaleString():d.toLocaleDateString()};
const fmtDate     =(iso)=>fmt(iso,0);
thefmtDateTime =(iso)=>fmt(iso,1); // (typo-safe alias kept, unused)
const fmtDateTime =(iso)=>fmt(iso,1);
const sevenDaysAgo=()=>{const d=new Date();d.setDate(d.getDate()-7);return d};
const dayKey=(iso)=>{const d=new Date(iso);return isNaN(d)?'':`${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}`};
const dateChipHTML=(iso)=>{const d=new Date(iso);return `<div style="display:flex;justify-content:center;margin:12px 0"><span style="font-size:11px;padding:4px 12px;border-radius:999px;border:1px solid #e2e8f0;background:var(--light-color);color:#64748b;">${d.toLocaleDateString()}</span></div>`};
const badgeClass=(t,v)=>`badge ${String(v||'').toLowerCase().replace(/ /g,'_')}`;
const badgeLabel=(v)=>esc(String(v||'-').replaceAll('_',' '));
const within1min=(iso)=>{const t=new Date(iso).getTime();return!isNaN(t)&&(Date.now()-t)<=60000};
const parseFilename=(cd)=>{if(!cd)return null;const m=cd.match(/filename\*=UTF-8''([^;]+)|filename="([^"]+)"|filename=([^;]+)/i);return m?decodeURIComponent(m[1]||m[2]||m[3]||'').replace(/["']/g,''):null};
// === Contact & initials helpers ===
function safeTrim(v){ return (v==null)?'':String(v).trim(); }
function initialsFrom(name,email,role){
  const n = safeTrim(name);
  if(n){ return n.split(/\s+/).slice(0,2).map(s=>s[0]).join('').toUpperCase(); }
  const e = safeTrim(email);
  if(e){ return e[0].toUpperCase(); }
  return String(role||'?').slice(0,1).toUpperCase();
}
function contactLine(name,email,phone){
  const parts = [];
  if(safeTrim(name)) parts.push(safeTrim(name));
  if(safeTrim(email)) parts.push(safeTrim(email));
  if(safeTrim(phone)) parts.push(safeTrim(phone));
  return parts.join(' · ');
}

function clearStoredAuth(){
  try{
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('role');
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    localStorage.removeItem('type');
  }catch(e){}
}

async function fetchAuthContextForToken(candidate){
  if(!candidate) return null;
  const res = await fetch('/api/auth/context', {
    headers: {
      'Authorization': 'Bearer ' + candidate,
      'Accept': 'application/json',
      'Cache-Control': 'no-cache',
      'Pragma': 'no-cache',
    },
    cache: 'no-store',
  });
  if(!res.ok) return null;
  const j = await res.json().catch(()=>({}));
  return j?.data?.role ? { token: candidate, data: j.data } : null;
}

async function ensurePortalAuth(){
  const sessionToken = sessionStorage.getItem('token') || '';
  const localToken = localStorage.getItem('token') || '';
  const candidates = [];
  if(sessionToken) candidates.push(sessionToken);
  if(localToken && localToken !== sessionToken) candidates.push(localToken);

  let fallback = null;
  for (const candidate of candidates) {
    try{
      const ctx = await fetchAuthContextForToken(candidate);
      if(!ctx) continue;
      if(!fallback) fallback = ctx;
      if(!EXPECTED_ROLE || String(ctx.data.role).toLowerCase() === EXPECTED_ROLE){
        TOKEN = candidate;
        role = String(ctx.data.role || EXPECTED_ROLE || '').toLowerCase();
        IS_ASSIGNEE = role === 'assignee';
        IS_CLIENT_USER = role === 'client_user';
        IS_READ_ONLY = IS_ASSIGNEE || IS_CLIENT_USER;
        return ctx.data;
      }
    }catch(e){}
  }

  if (fallback && !EXPECTED_ROLE) {
    TOKEN = fallback.token;
    role = String(fallback.data.role || '').toLowerCase();
    IS_ASSIGNEE = role === 'assignee';
    IS_CLIENT_USER = role === 'client_user';
    IS_READ_ONLY = IS_ASSIGNEE || IS_CLIENT_USER;
    return fallback.data;
  }

  clearStoredAuth();
  await Swal.fire('Auth Required','Please login with the correct portal account.','warning');
  location.href = LOGIN_REDIRECT;
  throw new Error('Unauthenticated');
}

async function GET(u){
  const r = await fetch(u, { headers: H, cache: 'no-store' }); // ⬅️ add cache: 'no-store'
  const ct=(r.headers.get('content-type')||'').toLowerCase();
  const j = ct.includes('application/json') ? await r.json().catch(()=>({})) : {message:await r.text()};
  if(!r.ok) throw new Error(j.message||('HTTP '+r.status));
  return j;
}

async function JSONreq(u,m,p){
  const r=await fetch(u,{method:m,headers:{...H,'Content-Type':'application/json'},body:JSON.stringify(p||{})});
  const j=await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(j.message||('HTTP '+r.status));
  return j;
}

/* ================== ENHANCED PDF EXPORT (unchanged APIs) ================== */
async function exportAsPDF(jobId, type = 'report') {
  const { jsPDF } = window.jspdf;
  const prev = byId('btnExport') ? byId('btnExport').innerHTML : '';
  if(byId('btnExport')){ byId('btnExport').disabled = true; byId('btnExport').innerHTML = `<span class="spinner-border" style="width:16px;height:16px;margin-right:8px"></span>Generating PDF...`; }
  try {
    const endpoint = type === 'report'
      ? `${API.exportReport(jobId)}?format=pdf`
      : `${API.messages(jobId).replace('/messages', '/export-chats')}?format=pdf&rolewise=1`;
    const response = await fetch(endpoint, { method:'GET', headers:{'Authorization': H.Authorization,'Accept':'text/html'} });
    if (!response.ok) throw new Error(`Failed to generate PDF (HTTP ${response.status})`);
    const htmlContent = await response.text();
    await generateEnhancedPDF(htmlContent, jobId, type);
    ok('PDF generated successfully');
  } catch (error) {
    console.error('PDF generation failed:', error);
    err(error.message || 'PDF generation failed');
    if (confirm('PDF generation failed. Open the report in a new tab for printing?')) {
      const endpoint = type === 'report'
        ? `${API.exportReport(jobId)}?format=pdf`
        : `${API.messages(jobId).replace('/messages', '/export-chats')}?format=pdf&rolewise=1`;
      window.open(endpoint, '_blank');
    }
  } finally {
    if(byId('btnExport')){ byId('btnExport').disabled = false; byId('btnExport').innerHTML = prev; }
  }
}

async function generateEnhancedPDF(htmlContent, jobId, type) {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'mm', 'a4');
  const margin = 15, pageWidth = pdf.internal.pageSize.getWidth(), pageHeight = pdf.internal.pageSize.getHeight();
  const contentWidthMM = pageWidth - margin * 2, contentHeightMM = pageHeight - margin * 2;

  const iframe = document.createElement('iframe');
  iframe.style.cssText = 'position:absolute; left:-9999px; top:-9999px; width:210mm; border:none;';
  document.body.appendChild(iframe);
  const iframeWin = iframe.contentWindow;
  const iframeDoc = iframe.contentDocument || iframeWin.document;
  iframeDoc.open(); iframeDoc.write(htmlContent); iframeDoc.close();
  await new Promise((resolve)=>{ iframe.onload = resolve; setTimeout(resolve, 1000); });

  const canvas = await html2canvas(iframeDoc.body,{
    scale:2, useCORS:true, allowTaint:false, backgroundColor:'#ffffff',
    scrollX:0, scrollY:0, windowWidth:iframeDoc.body.scrollWidth, windowHeight:iframeDoc.body.scrollHeight,
    onclone:(clonedDoc)=>{ clonedDoc.querySelectorAll('a').forEach(link=>{ const url=link.href; if(url && !url.startsWith('javascript:')){ link.style.color='#3b82f6'; link.style.textDecoration='underline'; link.title=url; } }); }
  });

  const pxToMm = contentWidthMM / canvas.width;
  const docWidthCssPx = iframeDoc.body.scrollWidth || iframeDoc.documentElement.scrollWidth;
  const scaleFactor = canvas.width / (docWidthCssPx || canvas.width);

  const contentHeightCanvasPx = contentHeightMM / pxToMm;
  const pageHeightPx = Math.floor(contentHeightCanvasPx);

  const links = Array.from(iframeDoc.querySelectorAll('a')).map(el=>{
    const rect = el.getBoundingClientRect();
    const docLeft = rect.left + (iframeWin.pageXOffset || iframeDoc.documentElement.scrollLeft || 0);
    const docTop  = rect.top  + (iframeWin.pageYOffset || iframeDoc.documentElement.scrollTop  || 0);
    const widthCss = rect.width || el.offsetWidth || 0;
    const heightCss= rect.height|| el.offsetHeight|| 0;
    return { href:el.href, leftCss:docLeft, topCss:docTop, widthCss, heightCss };
  });

  try{
    const totalPages = Math.max(1, Math.ceil((canvas.height)/pageHeightPx));
    for(let p=0;p<totalPages;p++){
      const sliceTopCanvasPx = p * pageHeightPx;
      const sliceHeightCanvasPx = Math.min(pageHeightPx, canvas.height - sliceTopCanvasPx);
      const sliceBottomCanvasPx = sliceTopCanvasPx + sliceHeightCanvasPx;

      const tmp = document.createElement('canvas');
      tmp.width = canvas.width; tmp.height = sliceHeightCanvasPx;
      const tctx = tmp.getContext('2d');
      tctx.drawImage(canvas, 0, sliceTopCanvasPx, canvas.width, sliceHeightCanvasPx, 0, 0, canvas.width, sliceHeightCanvasPx);

      const imgData = tmp.toDataURL('image/jpeg', 0.95);
      const imgHeightMM = (sliceHeightCanvasPx * pxToMm);
      pdf.addImage(imgData, 'JPEG', margin, margin, contentWidthMM, imgHeightMM);

      links.forEach(link=>{
        const linkTopCanvasPx = link.topCss * scaleFactor;
        const linkBottomCanvasPx = (link.topCss + link.heightCss) * scaleFactor;
        if (linkBottomCanvasPx <= sliceTopCanvasPx || linkTopCanvasPx >= sliceBottomCanvasPx) return;
        const visibleTopCanvasPx = Math.max(linkTopCanvasPx, sliceTopCanvasPx);
        const visibleBottomCanvasPx = Math.min(linkBottomCanvasPx, sliceBottomCanvasPx);
        const visibleHeightCanvasPx = visibleBottomCanvasPx - visibleTopCanvasPx;
        const relTopCanvasPx = visibleTopCanvasPx - sliceTopCanvasPx;
        const relLeftCanvasPx = link.leftCss * scaleFactor;

        const x_mm = margin + relLeftCanvasPx * pxToMm;
        const y_mm = margin + relTopCanvasPx  * pxToMm;
        const w_mm = Math.max(1, link.widthCss * scaleFactor * pxToMm);
        const h_mm = Math.max(1, visibleHeightCanvasPx * pxToMm);

        try{ pdf.link(x_mm, y_mm, w_mm, h_mm, { url: link.href }); }catch(e){ console.warn('Link add failed',e); }
      });

      if (p < totalPages - 1) pdf.addPage();
    }

    const pageCount = pdf.internal.getNumberOfPages();
    for (let i=1;i<=pageCount;i++){
      pdf.setPage(i); pdf.setFontSize(8); pdf.setTextColor(100,100,100);
      pdf.text(`Page ${i} of ${pageCount} - Generated on ${new Date().toLocaleDateString()}`, pageWidth/2, pageHeight-10, {align:'center'});
    }
    const timestamp = new Date().toISOString().split('T')[0];
    const filename = `job_${jobId}_${type}_${timestamp}.pdf`;
    pdf.save(filename);
  } finally {
    document.body.removeChild(iframe);
  }
}

/* ================== ROLE / ENUMS / CLIENTS ================== */
function applyRoleVisibility(){
  if(IS_ASSIGNEE){
    const c=byId('filterClient'); if(c) c.closest('.filter-field').style.display='none';
  }
  if(IS_READ_ONLY){
    const a=document.querySelector('a.btn.btn-primary[href="/admin/jobs/add"]'); if(a) a.style.display='none';
    const composerWrap = document.querySelector('.composer-sticky'); if (composerWrap) composerWrap.style.display = 'none';
    if (typeof btnAddExpense !== 'undefined' && btnAddExpense) btnAddExpense.style.display = 'none';
    if (typeof expenseForm !== 'undefined' && expenseForm) expenseForm.style.display = 'none';
    if (typeof dSaveStatus !== 'undefined' && dSaveStatus) dSaveStatus.style.display = 'none';
    if (typeof dStatus !== 'undefined' && dStatus) dStatus.disabled = true;
  }
}

const tbody   = byId('jTbody'),
      pager   = byId('pager'),
      infoEl  = byId('resultsInfo');

let roots=[], viewRows=[], page=1, perPage=10, q='',
    fClient='', fType='', fPriority='', fStatus='',
    sortKey='created', sortDir='desc';

const childrenCache=new Map(), expanded=new Set(), jobsById=new Map();
const filterClientModal = new bootstrap.Modal(byId('filterClientModal'));
const filterClientTreeEl = byId('filterClientTree');
const filterClientLoadEl = byId('filterClientLoad');
const filterClientCurrent = byId('filterClientCurrent');
const btnPickFilterClient = byId('btnPickFilterClient');
const btnClearFilterClient = byId('clearFilterClient');
let clientRowsCache = [];
let selectedFilterClient = null;

const API={
  jobs:'/api/job-details',
  myJobs:'/api/assignedpeople/my-jobs',
  enums:'/api/job-details/enums',
  clients:'/api/clients/all?status=active&sort=asc',
  show:(id)=>`/api/job-details/${id}`,
   statusChange: (id)=>`/api/job-details/${id}/status`,
  assignees:(id)=>`/api/job-details/${id}/assignees`,
  allPeople:'/api/assigned-people',
  assign:(id)=>`/api/job-details/${id}/assign`,
  unassign:(id)=>`/api/job-details/${id}/unassign`,
  update:(id)=>`/api/job-details/${id}`,
  messages:(id)=>`/api/job-details/${id}/messages`,
  messageUpdate:(jobId,msgId)=>`/api/job-details/messages/${msgId}`,
  exportReport:(id)=>`/api/job-details/${id}/export-report`,
  expenseHeads: '/api/expense-heads/all',
  expenses: (id) => `/api/job-details/${id}/expenses`,
  expenseStore: (id) => `/api/job-details/${id}/expenses`
};

const filterClient   = byId('filterClient'),
      filterType     = byId('filterType'),
      filterPriority = byId('filterPriority'),
      filterStatus   = byId('filterStatus');

/* ========== URL filter handling ========== */
// Read friendly filter from URL like ?filter=assigned or ?filter=in_progress
/* ========== URL FILTER HANDLING (DASHBOARD QUICK LINKS) ========== */
function getUrlFilter() {
  try {
    const sp = new URLSearchParams(window.location.search);
    const f = sp.get('filter');
    return f ? String(f).trim().toLowerCase() : '';
  } catch {
    return '';
  }
}

let appliedFilter = getUrlFilter(); // e.g. 'completed', 'pending', 'assigned', etc.

// Render a visible pill only if you added the HTML earlier. If not, this function is no-op.
function renderAppliedFilterPill() {
  const pillWrap = document.getElementById('appliedFilterPill');
  const pillText = document.getElementById('appliedFilterText');
  const clearBtn = document.getElementById('clearAppliedFilter');
  if (!pillWrap || !pillText || !clearBtn) return;
  if (!appliedFilter) {
    pillWrap.style.display = 'none'; pillText.textContent = '';
    clearBtn.onclick = null;
    return;
  }
  pillText.textContent = `Filter: ${appliedFilter.replaceAll('_',' ')}`;
  pillWrap.style.display = 'flex';
  clearBtn.onclick = (ev) => {
    ev.preventDefault();
    appliedFilter = '';
    // Remove from URL without reload
    try { const url = new URL(window.location.href); url.searchParams.delete('filter'); window.history.replaceState({}, '', url.toString()); } catch(e){}
    // reset selects (so UI shows All)
    if(filterStatus) filterStatus.value = '';
    if(filterClient) filterClient.value = '';
    if(filterType) filterType.value = '';
    if(filterPriority) filterPriority.value = '';
    syncFilterClientLabel();
    loadJobs();
    renderAppliedFilterPill();
  };
}

function fillOpts(sel,arr){
  sel.innerHTML=(arr||[]).map(v=>v===''?'<option value="">All</option>':`<option value="${esc(v)}">${esc(v.replaceAll('_',' '))}</option>`).join('')
}
function getSelectedFilterClientName(){
  const idx = filterClient ? filterClient.selectedIndex : -1;
  return (idx >= 0 && filterClient.options[idx]) ? String(filterClient.options[idx].textContent || '').trim() : '';
}
function syncFilterClientLabel(){
  filterClientCurrent.textContent = getSelectedFilterClientName() || 'All Clients';
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
function renderFilterClientTree(nodes, container){
  container.innerHTML = '';
  const liRoot = document.createElement('li');
  const itemRoot = document.createElement('div'); itemRoot.className='picker-item';
  const fakeT = document.createElement('button'); fakeT.type='button'; fakeT.className='picker-toggle'; fakeT.style.visibility='hidden'; fakeT.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
  const radioRoot = document.createElement('input'); radioRoot.type='radio'; radioRoot.name='filterClientPick'; radioRoot.value='';
  if (!filterClient.value) radioRoot.checked = true;
  const titleRoot = document.createElement('div'); titleRoot.className='picker-title';
  titleRoot.innerHTML='<strong>All Clients</strong><small>Clear the client filter</small>';
  itemRoot.appendChild(fakeT); itemRoot.appendChild(radioRoot); itemRoot.appendChild(titleRoot);
  liRoot.appendChild(itemRoot); container.appendChild(liRoot);
  radioRoot.addEventListener('change', ()=>{ selectedFilterClient = null; });
  nodes.forEach(node=> container.appendChild(renderFilterClientNode(node)));
}
function renderFilterClientNode(node){
  const li = document.createElement('li');
  const item = document.createElement('div'); item.className='picker-item';
  const toggle = document.createElement('button'); toggle.type='button'; toggle.className='picker-toggle'; toggle.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
  if (!node.children || !node.children.length) toggle.style.visibility='hidden';
  const radio = document.createElement('input'); radio.type='radio'; radio.name='filterClientPick'; radio.value=String(node.id);
  if (filterClient.value && String(node.id) === String(filterClient.value)) radio.checked = true;
  const title = document.createElement('div'); title.className='picker-title';
  title.innerHTML = `<strong>${esc(node.title)}</strong><small>#${node.id}${node.parent_id ? ' • child' : ''}</small>`;
  item.appendChild(toggle); item.appendChild(radio); item.appendChild(title); li.appendChild(item);
  const kids = document.createElement('ul'); kids.className='picker-children picker-tree'; li.appendChild(kids);
  if (node.children && node.children.length){
    node.children.forEach(ch=> kids.appendChild(renderFilterClientNode(ch)));
    toggle.addEventListener('click', ()=>{
      const open = kids.style.display === 'block';
      kids.style.display = open ? 'none' : 'block';
      toggle.classList.toggle('open', !open);
    });
  }
  radio.addEventListener('change', ()=>{ selectedFilterClient = { id: node.id, title: node.title }; });
  return li;
}
async function loadEnumsAndClients(){
  try{
    const ej=await GET(API.enums), e=ej?.data||{};
    fillOpts(filterType,[''].concat(e.types||[]));
    fillOpts(filterPriority,[''].concat(e.priority||[]));
    fillOpts(filterStatus,[''].concat(e.status||[]));
    byId('dStatus').innerHTML=(e.status||[]).map(s=>`<option value="${esc(s)}">${esc(s.replaceAll('_',' '))}</option>`).join('');
  }catch{}
  try{
    const cj=await GET(API.clients), rows=Array.isArray(cj.data)?cj.data:[];
    clientRowsCache = rows;
    filterClient.innerHTML=['<option value="">All Clients</option>'].concat(rows.map(c=>`<option value="${c.id}">${esc(c.name||('Client #'+c.id))}</option>`)).join('');
    syncFilterClientLabel();
  }catch{}
}

/* ================== LOAD + FILTER ================== */
async function loadJobs(){
  tbody.innerHTML=`<tr><td class="text-center" colspan="8" style="padding:40px"><div style="display:flex;align-items:center;justify-content:center;gap:10px;color:#94a3b8"><div class="spinner-border"></div><span>Loading jobs...</span></div></td></tr>`;
  
  // Build query params
  const qp = new URLSearchParams({ page: '1', per_page: '100', ...(q ? { q } : {}) });

  if (fClient) qp.set('client_id', fClient);
  if (fType) qp.set('type', fType);
  if (fPriority) qp.set('priority', fPriority);
  if (fStatus) qp.set('status', fStatus);
  if (appliedFilter) qp.set('filter', appliedFilter);
  qp.set('sort', sortDir === 'asc' ? 'asc' : 'desc');

  try{
    const endpoint = IS_ASSIGNEE ? API.myJobs : API.jobs;
    let activeEndpoint = endpoint;
    let first = null;
    try {
      first = await GET(`${endpoint}?${qp.toString()}`);
    } catch (primaryErr) {
      // For assignee flows, fallback to generic endpoint if my-jobs fails.
      if (IS_ASSIGNEE && endpoint !== API.jobs) {
        console.warn('Primary assignee jobs endpoint failed, using /api/job-details', primaryErr);
        activeEndpoint = API.jobs;
        first = await GET(`${API.jobs}?${qp.toString()}`);
      } else {
        throw primaryErr;
      }
    }

    let rows = Array.isArray(first?.data) ? first.data : [];
    // Assignee APIs may omit assignees_count; keep hierarchy logic stable.
    if (IS_ASSIGNEE) {
      rows = rows.map(r => ({ ...r, assignees_count: Number(r.assignees_count ?? 1) }));
    }
    const totalPages = Number(first?.meta?.total_pages || 1);
    for(let p=2;p<=totalPages;p++){
      const nx = await GET(`${activeEndpoint}?${new URLSearchParams({...Object.fromEntries(qp),page:String(p)})}`);
      const nxRows = Array.isArray(nx?.data) ? nx.data : [];
      rows = rows.concat(IS_ASSIGNEE ? nxRows.map(r => ({ ...r, assignees_count: Number(r.assignees_count ?? 1) })) : nxRows);
    }

    // Special client-side handling for assigned / unassigned / pending dashboard quick filters:
    if (appliedFilter === 'assigned') {
      rows = rows.filter(r => Number(r.assignees_count || 0) > 0);
    } else if (appliedFilter === 'unassigned') {
      rows = rows.filter(r => Number(r.assignees_count || 0) === 0);
    } else if (appliedFilter === 'pending') {
  rows = rows.filter(r => {
    const st = String(r.status || '').toLowerCase().replace(/\s+/g,'_');
    return st === 'pending'; // Only show jobs with 'pending' status
  });
    }

    // SPECIAL HANDLING FOR ASSIGNEE ROLE - Hierarchy adjustments
    // SPECIAL HANDLING FOR ASSIGNEE ROLE - Hierarchy adjustments
    if (IS_ASSIGNEE) {
      // Create a map for quick lookup
      const jobMap = new Map();
      rows.forEach(r => jobMap.set(Number(r.id), r));
      
      // Build parent-child relationships from all rows
      const jobChildren = new Map();
      rows.forEach(r => {
        const parentId = Number(r.parent_id);
        if (parentId) {
          if (!jobChildren.has(parentId)) {
            jobChildren.set(parentId, []);
          }
          jobChildren.get(parentId).push(r);
        }
      });
      
      // Determine which jobs should appear as roots for assignee
      roots = rows.filter(r => {
        const parentId = Number(r.parent_id);
        const childAssigned = Number(r.assignees_count || 0) > 0;
        
        // If no parent, show as root only if assigned
        if (!parentId) {
          return childAssigned;
        }
        
        // Has parent - check if parent is in our rows (assigned to user)
        const parent = jobMap.get(parentId);
        
        // If parent doesn't exist in rows (not assigned), and this child is assigned, promote to root
        if (!parent && childAssigned) {
          return true;
        }
        
        // If parent exists and is assigned, this will be shown as its child (not a root)
        if (parent && Number(parent.assignees_count || 0) > 0) {
          return false;
        }
        
        // If parent exists but not assigned, and child is assigned, promote to root
        if (parent && childAssigned) {
          return true;
        }
        
        return false;
      });

      // Pre-populate children cache for assigned parents
      childrenCache.clear();
      roots.forEach(root => {
        const rootId = Number(root.id);
        const children = jobChildren.get(rootId) || [];
        
        // Only cache children that are assigned to current user
        const assignedChildren = children.filter(child => 
          Number(child.assignees_count || 0) > 0
        );
        
        if (assignedChildren.length > 0) {
  childrenCache.set(rootId, assignedChildren);
  // keep parent job's child_count accurate for UI
  const parentJob = jobsById.get(rootId);
  if (parentJob) parentJob.child_count = assignedChildren.length;
}

      });

    } else {
      // Admin - normal hierarchy
      roots = rows.filter(r => !r.parent_id);
    }
    jobsById.clear(); 
    rows.forEach(r => jobsById.set(Number(r.id), r));
    // ---------- compute child counts ----------
const childCountMap = new Map();
rows.forEach(r => {
  const pid = Number(r.parent_id) || 0;
  if (pid) childCountMap.set(pid, (childCountMap.get(pid) || 0) + 1);
});
// attach child_count to each row (default 0)
rows.forEach(r => { r.child_count = Number(childCountMap.get(Number(r.id)) || 0); });
// ensure any cached jobsById later sees the value
rows.forEach(r => jobsById.set(Number(r.id), r));

    applyFilters();

    // Update visible pill
    renderAppliedFilterPill();
    
    // Debug log for assignee
    if (IS_ASSIGNEE) {
      console.log('Assignee view - Roots:', roots.length, 'Total jobs:', rows.length);
      roots.forEach(r => {
        console.log(`Root job ${r.id}: "${r.title}", parent: ${r.parent_id}, assignees: ${r.assignees_count}`);
      });
    }
    
  } catch(e) {
    err(e.message||'Failed to load jobs');
    tbody.innerHTML=`<tr><td class="text-center" colspan="8" style="padding:40px;color:#94a3b8">Failed to load jobs</td></tr>`
  }
}
function applyFilters(){
  const nq=q.toLowerCase();
  viewRows=roots.filter(r=>!nq||[r.title||'',r.description||''].join(' ').toLowerCase().includes(nq));
  const S={
    title:(a,b)=> (a.title||'').localeCompare(b.title||''),
    priority:(a,b)=> (a.priority||'').localeCompare(b.priority||''),
    status:(a,b)=> (a.status||'').localeCompare(b.status||''),
    created:(a,b)=> new Date(a.created_at||0)-new Date(b.created_at||0)
  };
  viewRows.sort(S[sortKey]); if(sortDir==='desc') viewRows.reverse();
  const total=viewRows.length;
  page=Math.max(1,Math.min(page,Math.ceil(total/perPage)||1));
  renderPage(); updateSortIcons();
}
function renderPage(){
  const total=viewRows.length, tp=Math.max(1,Math.ceil(total/perPage)),
        start=(page-1)*perPage, slice=viewRows.slice(start,start+perPage);
  renderTable(slice); renderPager(tp);
  infoEl.textContent=`Showing ${total?(start+1):0}–${Math.min(start+perPage,total)} of ${total} jobs · page ${page}/${tp}`;
}
function renderPager(tp){
  const item=(p,l,dis,act)=>`<button class="page-btn ${dis?'':''} ${act?'active':''}" data-page="${p}" ${dis?'disabled':''}>${l}</button>`;
  let h=''; h+=item(Math.max(1,page-1),'Previous',page<=1,false);
  const s=Math.max(1,page-2), e=Math.min(tp,page+2);
  for(let p=s;p<=e;p++) h+=item(p,p,false,p===page);
  h+=item(Math.min(tp,page+1),'Next',page>=tp,false);
  pager.innerHTML=h;
}
function updateSortIcons(){
  const map={title:'iconSortTitle',priority:'iconSortPriority',status:'iconSortStatus',created:'iconSortCreated'};
  Object.values(map).forEach(id=>{ try{ byId(id).className='fa fa-sort tiny-sort-icon' }catch(e){} });
  const ic=byId(map[sortKey]);
  if(ic){ ic.classList.add('active'); ic.classList.remove('fa-sort'); ic.classList.add(sortDir==='asc'?'fa-sort-up':'fa-sort-down'); }
}

/* ================== ACTIONS MENU (3-DOTS) ================== */
/* ================== ACTIONS MENU (3-DOTS) - REPLACEMENT ================== */
let actionsMenuEl=null, actionsMenuForId=null;
function ensureActionsMenu(){
  if(actionsMenuEl) return actionsMenuEl;
  const el=document.createElement('div');
  el.id='rowActionsMenu';
  // Use fixed positioning (viewport-based) and sensible defaults
  el.style.cssText =
    'position:fixed;z-index:1400;min-width:220px;max-width:360px;max-height:calc(100vh - 32px);overflow:auto;' +
    'background:var(--surface);border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.12);padding:6px;display:none;';
  el.setAttribute('role','menu');
  document.body.appendChild(el);
  actionsMenuEl=el;

  // close on outside click
  document.addEventListener('click', e => {
    if(!actionsMenuEl || actionsMenuEl.style.display === 'none') return;
    const btnMatch = e.target.closest('.btn-actions');
    const insideMenu = e.target.closest('#rowActionsMenu');
    if(!insideMenu && !btnMatch) { closeActionsMenu(); }
  }, true);

  // close on resize/scroll (keeps behavior but menu is fixed so it won't jump)
  window.addEventListener('resize', closeActionsMenu);
  // window.addEventListener('scroll', closeActionsMenu, true);

  // delegate menu actions (keeps your existing handler)
  actionsMenuEl.addEventListener('click', async (e) => {
    const it = e.target.closest('[data-am-act]'); if(!it) return;
    e.preventDefault(); const id = Number(it.dataset.id || actionsMenuForId);
    closeActionsMenu(); if(!id) return;
    const act = it.dataset.amAct;
    if(act==='open') return openDrawer(id);
    if(act==='view') return openViewer(id);
    if(act==='viewdoc') return viewDocument(id);
    if(act==='assign'){ if(IS_READ_ONLY){info('You do not have permission for this action.');return}
      const tr = tbody.querySelector(`tr[data-id="${id}"]`); return openAssignModal(id, tr?.dataset?.title||(`#${id}`))
    }
    if(act==='export'){ if(IS_READ_ONLY){info('You do not have permission for this action.');return}
      return exportReport(id);
    }
    if(act==='edit'){ if(IS_READ_ONLY){info('You do not have permission for this action.');return}
      location.href=`/admin/jobs/edit/${id}`; return;
    }
    if(act==='delete'){ if(IS_READ_ONLY){info('You do not have permission for this action.');return}
      const ask=await Swal.fire({title:'Delete Job?',text:'This job will be permanently removed (including children).',icon:'warning',showCancelButton:true,confirmButtonText:'Delete',confirmButtonColor:'#dc2626'});
      if(!ask.isConfirmed) return;
      try{
        await JSONreq(`${API.update(id)}`,'DELETE',{});
        ok('Job deleted successfully');
        roots=roots.filter(r=>String(r.id)!==String(id));
        viewRows=viewRows.filter(r=>String(r.id)!==String(id));
        renderPage();
      }catch(ex){err(ex.message||'Delete failed')}
    }
  });
  return el;
}

function openActionsMenu(btn, id){
  const el = ensureActionsMenu();
  actionsMenuForId = id;

  // Get job data to check if document exists
  const job = jobsById.get(Number(id));
  const hasDocument = job && job.document_id;

  // build menu items
  const list = [];
  list.push(`<a href="#" data-am-act="open"  data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-up-right-from-square"></i><span>Open drawer</span></a>`);
  list.push(`<a href="#" data-am-act="view"  data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-eye"></i><span>View details</span></a>`);
  
  // **NEW: Add View Document option if document exists**
  if (hasDocument) {
    list.push(`<a href="#" data-am-act="viewdoc" data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-file-pdf"></i><span>View document</span></a>`);
  }
  
  if(!IS_READ_ONLY){
    list.push(`<a href="#" data-am-act="assign" data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-user-plus"></i><span>Assign people</span></a>`);
    list.push(`<a href="#" data-am-act="export" data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-download"></i><span>Export report</span></a>`);
    list.push(`<a href="/admin/jobs/edit/${id}" data-am-act="edit" data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:var(--text-color);text-decoration:none"><i class="fa fa-pen"></i><span>Edit</span></a>`);
    list.push(`<a href="#" data-am-act="delete" data-id="${id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;color:#dc2626;text-decoration:none"><i class="fa fa-trash"></i><span>Delete</span></a>`);
  }
  el.innerHTML = list.join('');
  
  // show temporarily so offsetWidth/offsetHeight are available
  el.style.display = 'block';
  el.style.opacity = '0'; // hide visually while we compute placement
  el.style.pointerEvents = 'none';

  // measure & compute placement relative to viewport
  const r = btn.getBoundingClientRect();
  const pad = 8;
  const menuW = Math.min(el.offsetWidth || 240, window.innerWidth - 16);
  const menuH = el.offsetHeight || 200;
  // Preferred: below the button
  let top = r.bottom + pad;
  // If not enough space below, flip above
  if (top + menuH > window.innerHeight && (r.top - pad - menuH) >= 0) {
    top = r.top - pad - menuH;
  }
  // compute left: prefer right-align to the button, but stay within viewport
  let left = r.right - menuW;
  if (left < 8) left = Math.max(8, r.left);
  if (left + menuW > window.innerWidth - 8) left = Math.max(8, window.innerWidth - menuW - 8);

  el.style.width = menuW + 'px';
  el.style.top = Math.round(top) + 'px';
  el.style.left = Math.round(left) + 'px';
  el.style.opacity = '1';
  el.style.pointerEvents = '';
  // ensure vertical scrollbar shows inside the menu if needed
  el.style.maxHeight = 'calc(100vh - 32px)';
  el.style.overflow = 'auto';
}
function closeActionsMenu(){ if(actionsMenuEl){ actionsMenuEl.style.display='none'; actionsMenuForId=null } }

/* ================== TABLE RENDERING ================== */
function rowActionsButtonHTML(r){
  return `<button class="btn-icon btn-actions" data-id="${r.id}" title="Actions" aria-haspopup="true" aria-expanded="false"><i class="fa fa-ellipsis-vertical"></i></button>`;
}
function rowHTML(r, level=0){
  const planned=(r.planned_start_at||r.planned_end_at)?`${fmtDate(r.planned_start_at)} → ${fmtDate(r.planned_end_at)}`:'—';
  const badge=`<span class="${badgeClass('status',r.status)}">${badgeLabel(r.status)}</span>`;
  const priorityBadge=`<span class="${badgeClass('priority',r.priority)}">${badgeLabel(r.priority)}</span>`;

  // NEW: expander with badge (shows only when r.child_count > 0)
// canonical child count comes from jobsById (freshly computed by refreshChildCounts)
const canonical = jobsById.get(Number(r.id)) || r || {};
const childCount = Number(canonical.child_count || 0);
  const displayCount = childCount > 9 ? '9+' : childCount; // cap if you want
  const expanderWithBadge = `
    <div class="expander-wrap" style="display:inline-flex;align-items:center;gap:8px;position:relative;">
      <button class="expander js-expand" data-id="${r.id}" data-level="${level}" aria-expanded="${expanded.has(r.id)?'true':'false'}" title="Expand/collapse children">
        <i class="fa ${expanded.has(r.id)?'fa-caret-down':'fa-caret-right'}"></i>
      </button>
      ${childCount > 0 ? `<span class="expander-child-badge" title="${childCount} child${childCount>1?'ren':''}">${displayCount}</span>` : ''}
    </div>
  `;

  return `
  <tr data-id="${r.id}" data-level="${level}" data-title="${esc(r.title||'')}" style="--indent:${16*level}px" class="${level>0?'child-row':''}">
    <td style="width:48px;white-space:nowrap">
      ${expanderWithBadge}
    </td>
    <td>
      <div style="display:flex;align-items:center;gap:8px;cursor:pointer" class="js-open">
        <span style="color:#3b82f6;text-decoration:underline">${esc(r.title||'—')}</span>
        ${r.document_name?`<span style="font-size:11px;padding:3px 8px;border-radius:6px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0">${esc(r.document_name)}</span>`:''}
      </div>
      <div style="font-size:12px;color:#94a3b8;margin-top:4px">${esc(r.type||'task')} · ${r.assignees_count||0} assignee(s)</div>
    </td>
    <td>${esc(r.client_name||'-')}</td>
    <td>${priorityBadge}</td>
    <td>${badge}</td>
    <td>${planned}</td>
    <td style="font-size:13px;color:#64748b">${fmtDateTime(r.created_at)}</td>
    <td style="white-space:nowrap">${rowActionsButtonHTML(r)}</td>
  </tr>`;
}

function renderTable(rows){
  tbody.innerHTML=rows.length?rows.map(r=>rowHTML(r,0)).join(''):``
  + `<tr><td colspan="8"><div class="empty-state">
  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 7h-9M14 17H5M17 12H3" stroke-width="2" stroke-linecap="round"/><circle cx="17" cy="7" r="3" stroke-width="2"/><circle cx="7" cy="17" r="3" stroke-width="2"/></svg>
  <h3>No jobs found</h3><p>Try adjusting your filters or search query</p></div></td></tr>`;
}
// Recompute child counts from jobsById (covers nested relationships)
function refreshChildCounts(){
  const map = new Map();
  for (const job of jobsById.values()){
    const pid = Number(job.parent_id) || 0;
    if (pid) map.set(pid, (map.get(pid) || 0) + 1);
  }
  for (const [id, job] of jobsById.entries()){
    const c = map.get(Number(id)) || 0;
    job.child_count = c;
    jobsById.set(Number(id), job);
  }
}
async function checkGrandchildrenExistence(childIds, opts = {}) {
  if (!Array.isArray(childIds) || childIds.length === 0) return;
  const batchSize = opts.batchSize || 6; // tune if needed
  const work = [...new Set(childIds.map(n => Number(n)).filter(Boolean))];

  // small batch runner
  for (let i = 0; i < work.length; i += batchSize) {
    const batch = work.slice(i, i + batchSize);
    await Promise.all(batch.map(async (cid) => {
      try {
        const qp = new URLSearchParams({ page: '1', per_page: '1', parent_id: String(cid) });
        if (fClient) qp.set('client_id', fClient);
        if (fType)   qp.set('type', fType);
        if (fPriority) qp.set('priority', fPriority);
        if (appliedFilter) qp.set('filter', appliedFilter);
        if (fStatus) qp.set('status', fStatus);

        const res = await GET(`${API.jobs}?${qp.toString()}`);
        // Prefer meta.total if present otherwise check data length (data is 0 or 1 here)
        const count = (res.meta && (res.meta.total || res.meta.total_items || res.meta.total_count)) 
                      ? Number(res.meta.total || res.meta.total_items || res.meta.total_count)
                      : (Array.isArray(res.data) ? res.data.length : 0);

        // If api returns just 1 record and no meta.total, we can't know the full count, but 1 means at least 1 child.
        const job = jobsById.get(Number(cid)) || {};
        job.child_count = Number(count || 0);
        jobsById.set(Number(cid), job);
      } catch (e) {
        // On error, assume 0 (safe), but don't fail the whole batch
        const job = jobsById.get(Number(cid)) || {};
        job.child_count = Number(job.child_count || 0);
        jobsById.set(Number(cid), job);
        console.warn('checkGrandchildrenExistence failed for', cid, e);
      }
    }));
    // small pause to be polite (optional)
    await new Promise(r => setTimeout(r, 60));
  }
}

/* ================== EXPORT (menu) ================== */
async function exportReport(jobId){
  const {value:format} = await Swal.fire({
    title: 'Export job report',
    input: 'radio',
    inputOptions: { json: 'JSON (raw)', word: 'Word (.doc)', pdf: 'PDF (.pdf)' },
    inputValidator: (v) => !v && 'Select a format',
    showCancelButton: true,
    confirmButtonText: 'Download',
    cancelButtonText: 'Cancel',
  });
  if (!format) return;
  if (format === 'pdf') return await exportAsPDF(jobId, 'report');

  const extMap = { json: 'json', word: 'doc' };
  const qformat = format, def = `job_${jobId}_report.${extMap[format] || 'dat'}`;
  try{
    const url = `${API.exportReport(jobId)}?format=${encodeURIComponent(qformat)}`;
    const resp = await fetch(url, { method:'GET', headers:{ 'Authorization': H.Authorization, 'Accept':'*/*' } });
    if (!resp.ok) {
      let em = `Export failed (HTTP ${resp.status})`;
      try { const j = await resp.json(); if (j && j.message) em = j.message } catch {}
      throw new Error(em);
    }
    const blob = await resp.blob();
    const cd = resp.headers.get('content-disposition');
    const fname = parseFilename(cd) || def;
    const u = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href=u; a.download=fname; document.body.appendChild(a); a.click(); a.remove();
    setTimeout(()=>URL.revokeObjectURL(u), 5e3);
    ok('Download started');
  }catch(ex){ err(ex.message||'Export failed') }
}

/* ================== EVENTS: FILTERS / SORT / PAGINATION ================== */
let sTO=null;
byId('searchInput').addEventListener('input',e=>{
  clearTimeout(sTO);
  sTO=setTimeout(()=>{
    q=e.target.value.trim();
    appliedFilter = '';  // cancel dashboard filter when searching
    page=1;applyFilters();
  },300);
});


[filterClient,filterType,filterPriority,filterStatus].forEach(el=>el.addEventListener('change',()=>{
  // user manually changed a filter — cancel dashboard filter
  appliedFilter = '';
  fClient=filterClient.value||''; fType=filterType.value||''; fPriority=filterPriority.value||''; fStatus=filterStatus.value||'';
  syncFilterClientLabel();
  page=1; expanded.clear(); childrenCache.clear(); loadJobs();
}));

btnPickFilterClient?.addEventListener('click', async ()=>{
  try{
    if (!clientRowsCache.length) {
      filterClientLoadEl.style.display = 'block';
      await loadEnumsAndClients();
    }
    selectedFilterClient = filterClient.value ? { id: filterClient.value, title: getSelectedFilterClientName() } : null;
    renderFilterClientTree(toClientTree(clientRowsCache), filterClientTreeEl);
    filterClientModal.show();
  } finally {
    filterClientLoadEl.style.display = 'none';
  }
});

btnClearFilterClient?.addEventListener('click', ()=>{
  filterClient.value = '';
  filterClient.dispatchEvent(new Event('change'));
});

byId('btnSaveFilterClient')?.addEventListener('click', ()=>{
  filterClient.value = selectedFilterClient ? String(selectedFilterClient.id) : '';
  filterClient.dispatchEvent(new Event('change'));
  filterClientModal.hide();
});

byId('perPage').addEventListener('change',e=>{perPage=parseInt(e.target.value,10)||10;page=1;renderPage()});
document.querySelectorAll('th.sortable').forEach(th=>th.addEventListener('click',()=>{
  const k=th.dataset.sort;
  if(sortKey!==k){sortKey=k;sortDir=(k==='created')?'desc':'asc'} else sortDir=(sortDir==='asc')?'desc':'asc';
  applyFilters();
}));
pager.addEventListener('click',e=>{
  const b=e.target.closest('.page-btn'); if(!b||b.disabled) return;
  e.preventDefault(); const p=parseInt(b.dataset.page,10);
  if(!Number.isNaN(p)){ page=p; renderPage() }
});

/* ================== TABLE CLICK HANDLER ================== */
tbody.addEventListener('click',async e=>{
  const tr=e.target.closest('tr'); if(!tr) return;
  const id=parseInt(tr.dataset.id,10), level=parseInt(tr.dataset.level||'0',10);

  const actBtn=e.target.closest('.btn-actions');
  if(actBtn){ e.preventDefault(); openActionsMenu(actBtn, id); return }

  if(IS_READ_ONLY&&(e.target.closest('.js-assign')||e.target.closest('.js-del')||e.target.closest('.btn-edit'))){
    e.preventDefault(); info('You do not have permission for this action.'); return;
  }
  if(e.target.closest('.js-expand')){
    const b=e.target.closest('.js-expand'); b.setAttribute('aria-busy','true');
    await toggleChildren(id,tr,level);
    b.querySelector('i').className='fa '+(expanded.has(id)?'fa-caret-down':'fa-caret-right');
    b.setAttribute('aria-expanded',expanded.has(id)?'true':'false');
    b.removeAttribute('aria-busy'); return;
  }
  if(e.target.closest('.js-view')){ openViewer(id); return }
  if(e.target.closest('.js-open')){ openDrawer(id); return }
});

/* ================== CHILDREN ================== */
// ---------- updated fetchChildren ----------
async function fetchChildren(pid){
  const parentId = Number(pid);

  // helper to update parent child_count (safe)
  async function setParentCount(count){
    try {
      const parent = jobsById.get(parentId);
      if (parent) {
        parent.child_count = count;
        jobsById.set(parentId, parent);
        return;
      }
      // fallback: try to fetch parent job from server and set count (best-effort)
      const j = await GET(API.show(parentId));
      if (j && j.data) {
        j.data.child_count = count;
        jobsById.set(parentId, j.data);
      }
    } catch (e) {
      // ignore failures — it's non-critical
      console.warn('setParentCount fallback failed', e);
    }
  }

  // small wrapper to index rows into jobsById and refresh counts
  function indexRows(rows){
    (rows || []).forEach(r => jobsById.set(Number(r.id), r));
    // ensure canonical counts are recomputed
    refreshChildCounts();
  }

  if (IS_ASSIGNEE) {
    // For assignee, use the pre-built cache first
    if (childrenCache.has(parentId)) {
      const cached = childrenCache.get(parentId);
      // ensure parent count reflects cache
      setParentCount(Array.isArray(cached) ? cached.length : 0).catch(()=>{});
      // also ensure jobsById has entries
      indexRows(cached);
      return cached;
    }
    
    // If not in cache, fetch children for this specific parent
    const qp = new URLSearchParams({
      page: '1', 
      per_page: '100', 
      parent_id: String(parentId)
    });
    
    if (fClient) qp.set('client_id', fClient);
    if (fType) qp.set('type', fType);
    if (fPriority) qp.set('priority', fPriority);
    if (appliedFilter) qp.set('filter', appliedFilter);
    if (fStatus) qp.set('status', fStatus);
    
    try {
      const j = await GET(`${API.jobs}?${qp.toString()}`);
      let rows = j.data || [];
      
      // Filter to only show children assigned to current user
      rows = rows.filter(child => Number(child.assignees_count || 0) > 0);
      
      // cache + index
      childrenCache.set(parentId, rows);
      indexRows(rows);

      // update parent's child_count (assignee view shows only assigned children)
      await setParentCount(rows.length);

      // --- lightweight check: see which of these children themselves have children ---
      const needCheck = rows.filter(c => !Number(c.child_count)).map(c => Number(c.id));
      if (needCheck.length) {
        await checkGrandchildrenExistence(needCheck);
        // recompute global counts after marking grandchildren existence
        refreshChildCounts();
      }

      return rows;
    } catch (error) {
      console.error('Error fetching children:', error);
      // ensure parent count = 0 on error (best-effort)
      setParentCount(0).catch(()=>{});
      return [];
    }
  }
  
  // Admin - original logic (with parent count update)
  if (childrenCache.has(parentId)) {
    const cached = childrenCache.get(parentId);
    setParentCount(Array.isArray(cached) ? cached.length : 0).catch(()=>{});
    indexRows(cached);
    return cached;
  }
  
  const qp = new URLSearchParams({page: '1', per_page: '100', parent_id: String(parentId)});
  if (fClient) qp.set('client_id', fClient);
  if (fType) qp.set('type', fType);
  if (fPriority) qp.set('priority', fPriority);
  if (appliedFilter) qp.set('filter', appliedFilter);
  if (fStatus) qp.set('status', fStatus);
  
  try {
    const j = await GET(`${API.jobs}?${qp.toString()}`); 
    const rows = j.data || [];
    rows.forEach(r => jobsById.set(Number(r.id), r));
    childrenCache.set(parentId, rows);

    // update parent's child_count (admin sees all children)
    await setParentCount(rows.length);

    // check grandchildren existence for the returned children (only if child_count not provided)
    const needCheckAdmin = rows.filter(c => !Number(c.child_count)).map(c => Number(c.id));
    if (needCheckAdmin.length) {
      await checkGrandchildrenExistence(needCheckAdmin);
      refreshChildCounts();
    }

    // ensure global counts are up-to-date
    refreshChildCounts();

    return rows;
  } catch (err) {
    console.error('Error fetching children (admin):', err);
    setParentCount(0).catch(()=>{});
    return [];
  }
}
async function toggleChildren(pid,row,level){
  if(expanded.has(pid)){
    let el=row.nextElementSibling;
    while(el&&el.classList.contains('child-row')&&parseInt(el.dataset.level||'0',10)>level){
      const n=el.nextElementSibling; el.remove(); el=n
    }
    expanded.delete(pid); return;
  }
  expanded.add(pid);
  const kids=await fetchChildren(pid);
  if(!kids.length){ info('No child jobs found'); expanded.delete(pid); return }
  let cur=row; kids.forEach(k=>{cur.insertAdjacentHTML('afterend',rowHTML(k,level+1)); cur=cur.nextElementSibling})
}

/* ================== ASSIGN MODAL ================== */
let assignModal,assignJobId=null,assignJobTitle='',allPeople=[],selectedSet=new Set(),beforeSet=new Set();
const assignListEl=byId('assignList'),assignSearchEl=byId('assignSearch'),assignSelectAllEl=byId('assignSelectAll'),assignSaveBtn=byId('assignSaveBtn'),assignBusy=byId('assignBusy');
assignModal=new bootstrap.Modal('#assignModal');
const assignModalLoading = document.getElementById('assignModalLoading');
const showAssignOverlay = (on)=>{ if(assignModalLoading){ assignModalLoading.style.display = on ? 'flex' : 'none'; } };

// Replace the old loadPeople() with this
async function loadPeople() {
  if (allPeople.length) return;

  assignBusy.style.display = 'block';
  const pageSize = 200; // big page size to reduce round-trips
  let p = 1, totalPages = 1, collected = [];

  try {
    while (p <= totalPages) {
      // fetch a page
      const url = `${API.allPeople}?page=${p}&per_page=${pageSize}`;
      const resp = await GET(url);

      const rows = Array.isArray(resp.data) ? resp.data : [];
      collected = collected.concat(rows);

      // read total pages from meta, fall back if missing
      const meta = resp.meta || {};
      totalPages = Number(meta.total_pages || totalPages);
      if (!totalPages || Number.isNaN(totalPages)) {
        // If backend doesn't send meta, stop after first big page
        totalPages = 1;
      }
      p += 1;
    }

    // de-dupe by id just in case and sort nicely
    const seen = new Set();
    allPeople = collected.filter(u => {
      const id = Number(u.id);
      if (seen.has(id)) return false;
      seen.add(id);
      return true;
    }).sort((a, b) => (a.name || '').localeCompare(b.name || ''));

  } finally {
    assignBusy.style.display = 'none';
  }
}
async function openAssignModal(jobId, title){
  assignJobId = Number(jobId);
  assignJobTitle = title;
  byId('assignJobTitle').textContent = title;

  assignSearchEl.value='';
  assignSelectAllEl.checked=false;
  assignSelectAllEl.indeterminate=false;
  assignListEl.innerHTML='';
  assignBusy.style.display='none';

  assignModal.show();
  showAssignOverlay(true);
  assignBusy.style.display='block';

  try {
    await loadPeople();

    // ✅ RESET local sets first
    selectedSet = new Set();
    beforeSet   = new Set();

    // ✅ GET current assignees from server and APPLY - only include ACTIVE assignments
    const cur = await GET(API.assignees(jobId));
    const ids = (cur.data || [])
      .filter(r => String(r.map_status || '').toLowerCase() === 'active')
      .map(r => Number(r.id));
    selectedSet = new Set(ids);
    beforeSet   = new Set(ids);

    // ✅ render UI
    renderAssignList();

  } finally {
    assignBusy.style.display='none';
    showAssignOverlay(false);
  }
}
function filteredPeople(){
  const f=(assignSearchEl.value||'').toLowerCase();
  return f?allPeople.filter(p=>[p.name||'',p.email||''].join(' ').toLowerCase().includes(f)):allPeople
}
function renderAssignList(){
  const items=filteredPeople();
  if(!items.length){
    assignListEl.innerHTML=`<div style="padding:20px;text-align:center;color:#94a3b8">No people match your search.</div>`;
    assignSelectAllEl.indeterminate=false; assignSelectAllEl.checked=false; return;
  }
  assignListEl.innerHTML=items.map(p=>{
    const c=selectedSet.has(Number(p.id))?'checked':'';
    return `<div class="assign-item">
      <div><strong>${esc(p.name||('Person #'+p.id))}</strong> ${p.email?`<span style="font-size:12px;color:#94a3b8">· ${esc(p.email)}</span>`:''}</div>
      <div class="form-check form-switch" style="margin:0">
        <input type="checkbox" class="form-check-input assign-check" data-id="${p.id}" ${c}>
      </div>
    </div>`
  }).join('');
  const visibleIds=items.map(p=>Number(p.id)),
        selCount=visibleIds.filter(id=>selectedSet.has(id)).length;
  if(selCount===0){ assignSelectAllEl.indeterminate=false; assignSelectAllEl.checked=false }
  else if(selCount===visibleIds.length){ assignSelectAllEl.indeterminate=false; assignSelectAllEl.checked=true }
  else { assignSelectAllEl.indeterminate=true; assignSelectAllEl.checked=false }
}
assignSearchEl.addEventListener('input',()=>renderAssignList());
assignSelectAllEl.addEventListener('change',()=>{
  const items=filteredPeople(); if(!items.length) return;
  if(assignSelectAllEl.indeterminate){ assignSelectAllEl.indeterminate=false; assignSelectAllEl.checked=true }
  const check=assignSelectAllEl.checked;
  for(const p of items){ const id=Number(p.id); if(check)selectedSet.add(id); else selectedSet.delete(id) }
  renderAssignList();
});
assignListEl.addEventListener('input', (e) => {
  const ch = e.target.closest('.assign-check');
  if (!ch) return;
  const id = Number(ch.dataset.id);
  if (ch.checked) selectedSet.add(id);
  else selectedSet.delete(id);
  renderAssignList(); // keeps “Select all” in sync
});

assignSaveBtn.addEventListener('click', async () => {
  if (!assignJobId) return;

  const after  = Array.from(selectedSet.values()).sort((a,b)=>a-b);
  const before = Array.from(beforeSet.values()).sort((a,b)=>a-b);
  const toAssign   = after.filter(x => !before.includes(x));
  const toUnassign = before.filter(x => !after.includes(x));

  assignSaveBtn.disabled = true;
  const prev = assignSaveBtn.innerHTML;
  assignSaveBtn.innerHTML = `<span class="spinner-border" style="width:16px;height:16px;margin-right:6px"></span>Saving...`;

  try {
    if (toAssign.length)   await JSONreq(API.assign(assignJobId), 'POST',  { assigned_person_ids: toAssign });
    if (toUnassign.length) await JSONreq(API.unassign(assignJobId), 'PATCH', { assigned_person_ids: toUnassign });

    // ✅ 1) Rebase local state to the new truth
    beforeSet   = new Set(after);
    selectedSet = new Set(after);

    // ✅ 2) (Recommended) Re-fetch from server to guarantee consistency
    const cur = await GET(API.assignees(assignJobId));
    const ids = (cur.data || []).map(r => Number(r.id));
    beforeSet   = new Set(ids);
    selectedSet = new Set(ids);

    // If you prefer to keep modal open, re-render:
    // renderAssignList();
    // Otherwise hide modal:
    assignModal.hide();

    // Update table count + refresh visible rows
    const row = roots.find(r => Number(r.id) === Number(assignJobId));
    if (row) row.assignees_count = ids.length;
    childrenCache.clear();
    applyFilters();

    ok('Assignments updated');
  } catch (ex) {
    err(ex.message || 'Save failed');
  } finally {
    assignSaveBtn.disabled = false;
    assignSaveBtn.innerHTML = prev;
  }
});

/* ================== FULL VIEWER MODAL ================== */
const viewer=new bootstrap.Modal('#jobViewModal');
const vId=byId('vId'),vTitle=byId('vTitle'),vClient=byId('vClient'),vType=byId('vType'),vPriority=byId('vPriority'),vStatus=byId('vStatus'),vStart=byId('vStart'),vEnd=byId('vEnd'),vDeadline=byId('vDeadline'),vDuration=byId('vDuration'),vDesc=byId('vDesc'),vAssignees=byId('vAssignees'),vMedia=byId('vMedia');
/* ================== VIEW DOCUMENT ================== */
function toAbsUrl(u){
  if (!u) return '';
  // already absolute (http/https)
  if (/^https?:\/\//i.test(u)) return u;
  // make relative url absolute
  return `${window.location.origin}/${String(u).replace(/^\/+/, '')}`;
}

async function viewDocument(jobId){
  try {
    let job = jobsById.get(Number(jobId));
    if (!job) {
      const j = await GET(API.show(jobId));
      job = j.data;
    }

    // Prefer nested document (your updated jobs index now sends job.document)
    let doc = job.document || null;

    // Fallback: fetch document by document_id using DocumentController
    const docId = job.document_id || doc?.id || null;
    if (!doc && docId) {
      const d = await GET(`/api/documents/${docId}`);
      doc = d.data;
    }

    const fileUrl = doc?.file_url ? toAbsUrl(doc.file_url) : null;

    if (!fileUrl) {
      info('No document attached to this job');
      return;
    }

    const newTab = window.open(fileUrl, '_blank', 'noopener,noreferrer');

    if (!newTab) {
      err('Popup blocked. Please allow popups for this site.');
      setTimeout(() => {
        const name = doc?.doc_name || job.document_name || 'file';
        if (confirm(`Popup blocked. Open document "${name}" in this tab?`)) {
          window.location.href = fileUrl;
        }
      }, 100);
    }

  } catch(ex) {
    console.error('Failed to open document:', ex);
    err(ex.message || 'Failed to open document');
  }
}

async function openViewer(id){
  [vId,vTitle,vClient,vType,vPriority,vStatus].forEach(el=>el.textContent='—');
  [vStart,vEnd,vDeadline,vDuration].forEach(el=>el.textContent='—');
  vDesc.innerHTML='—'; vAssignees.textContent='—'; vMedia.textContent='—';
  viewer.show();
  try{
    const j=await GET(API.show(id)), d=j.data||{};
    vId.textContent=d.id; vTitle.textContent=d.title||'—';
    vClient.textContent=j.data.client_name||'—';
    vType.textContent=d.type||'task';
    vPriority.textContent=(d.priority||'normal').replaceAll('_',' ');
    vStatus.textContent=(d.status||'planned').replaceAll('_',' ');
    vStart.textContent=fmtDate(d.planned_start_at); vEnd.textContent=fmtDate(d.planned_end_at);
    vDeadline.textContent=fmtDate(d.planned_deadline_at);
    if(d.planned_start_at&&d.planned_end_at){
      const sd=new Date(d.planned_start_at), ed=new Date(d.planned_end_at);
      vDuration.textContent=Math.max(0,Math.round((ed-sd)/86400000))+' day(s)';
    }
    vDesc.innerHTML=d.description||'<span style="font-size:13px;color:#94a3b8">No description</span>';
    const ass=Array.isArray(j.assignees)?j.assignees:[]; 
    vAssignees.innerHTML=ass.length?ass.map(a=>`<span class="chip">${esc(a.name||a.email||('Person #'+a.id))}</span>`).join(' '):'<span style="font-size:13px;color:#94a3b8">None</span>';
    const media=Array.isArray(j.media)?j.media:[]; 
    vMedia.innerHTML=media.length?media.map(m=>`<div style="margin:6px 0"><i class="fa-regular fa-image" style="margin-right:6px"></i><a href="${esc(m.absolute_url)}" target="_blank" style="color:#3b82f6">${esc(m.title||m.absolute_url)}</a></div>`).join(''):'<span style="font-size:13px;color:#94a3b8">None</span>';
  }catch(ex){err(ex.message||'Failed to load details')}
}

/* ================== DRAWER + MESSAGES ================== */
const drawer=new bootstrap.Offcanvas('#jobDrawer');
const dTitle=byId('dTitle'),dJobId=byId('dJobId'),dClient=byId('dClient'),dChipType=byId('dChipType'),dChipPriority=byId('dChipPriority'),dChipAssignees=byId('dChipAssignees'),dStatus=byId('dStatus'),dSaveStatus=byId('dSaveStatus'),dStatusSpin=byId('dStatusSpin'),dStart=byId('dStart'),dEnd=byId('dEnd'),dDeadline=byId('dDeadline'),dDuration=byId('dDuration'),dDesc=byId('dDesc'),chat=byId('chat'),btnLoadOlder=byId('btnLoadOlder'),olderSpin=byId('olderSpin'),composer=byId('composer'),btnAttach=byId('btnAttach'),attachInput=byId('attachInput'),attachChips=byId('attachChips'),drawerBusy=byId('drawerBusy'),msgBusy=byId('msgBusy'),msgEmpty=byId('msgEmpty');
// === ADD EXPENSE VARIABLES HERE ===
const expenseForm = byId('expenseForm');
const expensesList = byId('expensesList');
const expensesEmpty = byId('expensesEmpty');
const btnAddExpense = byId('btnAddExpense');
const btnCancelExpense = byId('btnCancelExpense');
const btnSaveExpense = byId('btnSaveExpense');
const expenseSaveSpin = byId('expenseSaveSpin');

// Expense form elements
const expenseHead = byId('expenseHead');
const expenseDate = byId('expenseDate');
const expenseAmount = byId('expenseAmount');
const expenseNote = byId('expenseNote');
const expenseFile = byId('expenseFile');

(function(){ if(role!=='admin')return;
  const b=document.createElement('button');
  b.className='btn btn-secondary'; b.id='btnExport'; b.style='height:38px;padding:0 16px;margin-left:8px'; b.title='Export conversation';
  b.innerHTML=`<i class="fa fa-download" style="margin-right:8px"></i>Export`;
  btnLoadOlder.parentNode.insertBefore(b,btnLoadOlder.nextSibling);
  b.addEventListener('click', async function(){
    if(!dJob){ info('Open a job first'); return }
    const {value:format} = await Swal.fire({
      title: 'Choose export format',
      input: 'radio',
      inputOptions: { csv: 'CSV (spreadsheet)', word: 'Word (.docx)', pdf: 'PDF (.pdf)' },
      inputValidator: (v) => !v && 'You need to choose a format',
      showCancelButton: true,
      confirmButtonText: 'Download',
      cancelButtonText: 'Cancel',
    });
    if (!format) return;
    if (format === 'pdf') return await exportAsPDF(dJob.id, 'messages');

    const map = { csv: 'excel', word: 'word' };
    const qf = map[format] || format;
    const prev = b.innerHTML; b.disabled = true; b.innerHTML = `<span class="spinner-border" style="width:16px;height:16px;margin-right:8px"></span>Preparing...`;
    try {
      const url = `${API.messages(dJob.id).replace('/messages','/export-chats')}?format=${encodeURIComponent(qf)}&rolewise=1`,
            resp = await fetch(url, { method: 'GET', headers: { 'Authorization': H.Authorization, 'Accept': '*/*' } });
      if (!resp.ok) {
        let em = `Export failed (HTTP ${resp.status})`;
        try { const j = await resp.json(); if (j && j.message) em = j.message } catch {} 
        throw new Error(em);
      }
      const cd = resp.headers.get('content-disposition'),
            name = parseFilename(cd) || `job_${dJob.id}_messages.${format === 'word' ? 'docx' : 'csv'}`,
            bl = await resp.blob(), u = URL.createObjectURL(bl), a = document.createElement('a');
      a.href = u; a.download = name; document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(u), 5e3);
      ok('Download started');
    } catch (ex) { err(ex.message || 'Export failed') }
    finally { b.disabled = false; b.innerHTML = prev }
  });
})();

let dJob=null, msgPage=1, msgTotalPages=1, attachmentPreviews=[], editMode=false, editMsgId=null, editOrigHtml='', editOrigAttachments=[], editRemovedAttachmentIds=new Set(), editNewFiles=[];
const formatFileSize=(n)=>{if(n===0)return'0 Bytes';const k=1024,u=['Bytes','KB','MB','GB'],i=Math.floor(Math.log(n)/Math.log(k));return parseFloat((n/Math.pow(k,i)).toFixed(2))+' '+u[i]};
function handleFileSelection(){
  const files=Array.from(attachInput.files||[]);
  if(editMode){ editNewFiles.push(...files); attachInput.value=''; renderAttachChips(); ok(`${files.length} file${files.length>1?'s':''} added`) }
  else{
    files.forEach(f=>{ attachmentPreviews.push({file:f,id:'preview-'+Date.now()+Math.random(),url:f.type.startsWith('image/')?URL.createObjectURL(f):null}) });
    attachInput.value=''; renderAttachChips(); renderAttachmentPreviews();
    const img=files.filter(f=>f.type.startsWith('image/')).length, oth=files.length-img;
    let t=[]; if(img)t.push(`${img} image${img>1?'s':''}`); if(oth)t.push(`${oth} file${oth>1?'s':''}`); ok(t.join(' & ')+' uploaded')
  }
}
function renderAttachmentPreviews(){
  if(editMode) return;
  let c=byId('attachmentPreviews');
  if(!c){ c=document.createElement('div'); c.id='attachmentPreviews'; c.className='attach-preview'; attachChips.parentNode.insertBefore(c,attachChips) }
  c.innerHTML='';
  attachmentPreviews.forEach((p,i)=>{
    const el=document.createElement('div'); el.className='preview-item';
    el.innerHTML=p.url?`<img src="${p.url}" alt="Preview" class="preview-image"><button type="button" class="remove-preview" data-index="${i}"><i class="fa fa-times"></i></button>`:`<div class="preview-file"><i class="fa fa-file"></i><div class="preview-info"><div class="preview-name">${esc(p.file.name)}</div><div class="preview-size">${formatFileSize(p.file.size)}</div></div></div><button type="button" class="remove-preview" data-index="${i}"><i class="fa fa-times"></i></button>`;
    c.appendChild(el)
  });
  c.querySelectorAll('.remove-preview').forEach(b=>b.addEventListener('click',e=>{e.preventDefault();removeAttachmentPreview(parseInt(b.dataset.index))}))
}
function removeAttachmentPreview(i){
  if(attachmentPreviews[i]?.url) URL.revokeObjectURL(attachmentPreviews[i].url);
  attachmentPreviews.splice(i,1); renderAttachmentPreviews(); renderAttachChips();
}
function clearEditState(){
  editMode=false; editMsgId=null; editOrigHtml=''; editOrigAttachments=[]; editRemovedAttachmentIds=new Set(); editNewFiles=[];
  attachmentPreviews.forEach(p=>{if(p.url)URL.revokeObjectURL(p.url)}); attachmentPreviews=[];
  const pc=byId('attachmentPreviews'); if(pc)pc.remove();
  const b=byId('editBanner'); if(b)b.remove();
  const s=byId('btnSend'); s.classList.remove('editing'); s.title='Send'; s.innerHTML=`<i class="fa fa-paper-plane"></i>`;
  attachInput.value=''; renderAttachChips();
}
function showEditBanner(){
  const rm=editRemovedAttachmentIds.size, nf=editNewFiles.length;
  const w=document.createElement('div'); w.id='editBanner';
  w.style='display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:10px;background:#fff6f3;border:1px solid #fde1d0;margin-bottom:8px';
  w.innerHTML=`<div style="display:flex;align-items:center;gap:10px"><i class="fa fa-pen" style="color:#d97706"></i><div><div style="font-size:13px;font-weight:600;color:#92400e">Editing message</div><div style="font-size:12px;color:#7c2d12">${rm>0?`${rm} attachment(s) marked for removal · `:''}${nf>0?`${nf} new attachment(s) · `:''}Your changes will update the original message</div></div></div><div style="display:flex;gap:8px;align-items:center"><button id="cancelEditBtn" class="btn btn-secondary" style="height:34px;padding:6px 10px">Cancel</button></div>`;
  document.querySelector('.composer-sticky').insertBefore(w,document.querySelector('.composer-sticky').firstChild);
  byId('cancelEditBtn').addEventListener('click',()=>{
    Swal.fire({title:'Cancel editing?',text:'All changes to the message and attachments will be lost.',icon:'warning',showCancelButton:true,confirmButtonText:'Yes, cancel',cancelButtonText:'Continue editing'}).then(r=>{
      if(r.isConfirmed){ composer.innerHTML=''; attachInput.value=''; editNewFiles=[]; editRemovedAttachmentIds=new Set(); renderAttachChips(); clearEditState() }
    })
  })
}
btnAttach.addEventListener('click',()=>attachInput.click());
attachInput.addEventListener('change',handleFileSelection);

function renderAttachChips(){
  let exHtml='';
  if(editMode&&Array.isArray(editOrigAttachments)&&editOrigAttachments.length){
    exHtml=editOrigAttachments.map(a=>{
      const removed=editRemovedAttachmentIds.has(String(a.id));
      const icon=a.kind==='image'?(removed?'fa-regular fa-image text-muted':'fa-regular fa-image text-primary'):(removed?'fa-regular fa-file text-muted':'fa-regular fa-file text-primary');
      return `<span class="chip existing-att ${removed?'removed border-danger':''}" data-att-id="${esc(a.id)}" style="position:relative;padding-right:28px;cursor:pointer"><i class="fa ${icon}"></i><span class="ms-1 ${removed?'text-decoration-line-through text-muted':'text-dark'}">${esc(a.original_name)}</span><i class="fa ${removed?'fa-rotate-left text-success':'fa-xmark text-danger'} js-remove-existing" title="${removed?'Restore attachment':'Remove attachment'}" style="position:absolute;right:6px"></i></span>`
    }).join('')
  }
  const newFilesHtml=(editNewFiles||[]).map((f,i)=>`<span class="chip border-primary" data-new-i="${i}" style="position:relative;padding-right:28px"><i class="fa fa-paperclip text-primary"></i><span class="ms-1">${esc(f.name)}</span><i class="fa fa-xmark text-danger js-remove-new" style="position:absolute;right:8px;cursor:pointer" title="Remove"></i></span>`).join('');
  const curHtml=attachmentPreviews.map((p,i)=>`<span class="chip border-primary" data-preview-i="${i}" style="position:relative;padding-right:28px"><i class="fa ${p.url?'fa-image text-green-500':'fa-paperclip text-primary'}"></i><span class="ms-1">${esc(p.file.name)}</span><i class="fa fa-xmark text-danger js-remove-preview" style="position:absolute;right:8px;cursor:pointer" title="Remove"></i></span>`).join('');
  attachChips.innerHTML=`${exHtml?`<div class="mb-2"><small class="text-muted">Existing attachments:</small><div class="d-flex flex-wrap gap-2 mt-1">${exHtml}</div></div>`:''}${editMode?(newFilesHtml?`<div><small class="text-muted">New attachments:</small><div class="d-flex flex-wrap gap-2 mt-1">${newFilesHtml}</div></div>`:''):''}${!editMode&&curHtml?`<div><small class="text-muted">Current attachments:</small><div class="d-flex flex-wrap gap-2 mt-1">${curHtml}</div></div>`:''}`;
  attachChips.addEventListener('click',handleAttachChipClicks)
}
function handleAttachChipClicks(e){
  const rx=e.target.closest('.js-remove-existing');
  if(rx){
    e.preventDefault(); e.stopPropagation();
    const chip=rx.closest('[data-att-id]'), id=chip?.dataset?.attId; if(!id)return;
    const removed=editRemovedAttachmentIds.has(String(id));
    removed?editRemovedAttachmentIds.delete(String(id)):editRemovedAttachmentIds.add(String(id));
    info(removed?'Restored attachment':'Attachment marked for removal - save to confirm'); renderAttachChips(); return;
  }
  const rn=e.target.closest('.js-remove-new');
  if(rn){
    e.preventDefault(); e.stopPropagation();
    const chip=rn.closest('[data-new-i]'), idx=Number(chip?.dataset?.newI);
    if(!Number.isNaN(idx)){ editNewFiles.splice(idx,1); renderAttachChips(); info('New attachment removed') }
    return;
  }
  const rp=e.target.closest('.js-remove-preview');
  if(rp){
    e.preventDefault(); e.stopPropagation();
    const chip=rp.closest('[data-preview-i]'), idx=Number(chip?.dataset?.previewI);
    if(!Number.isNaN(idx)){ removeAttachmentPreview(idx); info('Attachment removed') }
    return;
  }
  const ex=e.target.closest('.existing-att');
  if(ex&&!e.target.closest('.js-remove-existing')){
    const id=ex.dataset.attId,a=editOrigAttachments.find(a=>String(a.id)===String(id));
    if(a&&!editRemovedAttachmentIds.has(String(id))) window.open(a.absolute_url,'_blank')
  }
}
function buildAttachmentsHTML(att){
  if(!Array.isArray(att)||!att.length) return'';
  return `<div class="msg-atts" style="margin-top:8px;font-size:12px">${att.map(a=>a.kind==='image'
    ?`<div data-att-id="${esc(a.id||'')}"><i class="fa-regular fa-image" style="margin-right:4px"></i><a href="${esc(a.absolute_url)}" target="_blank" style="color:#3b82f6">${esc(a.original_name||'image')}</a></div>`
    :`<div data-att-id="${esc(a.id||'')}"><i class="fa-regular fa-file" style="margin-right:4px"></i><a href="${esc(a.absolute_url)}" target="_blank" style="color:#3b82f6">${esc(a.original_name||'file')}</a></div>`).join('')}</div>`
}
function canEditMessage(m){ return String(m.sender_role||'').toLowerCase()===role&&within1min(m.created_at)&&role==='admin' }

function renderMessages(rows, prepend, withDates){
  const frag=document.createDocumentFragment(); let lastDay=null;
  rows.slice().reverse().forEach(r=>{
const isMe = !!r.__forceMe || (r.sender_email && r.my_email &&
              r.sender_email.trim().toLowerCase() === r.my_email.trim().toLowerCase());

    const allowEdit = canEditMessage(r);

    if(withDates){
      const dk=dayKey(r.created_at);
      if(dk && dk!==lastDay){
        const sep=document.createElement('div');
        sep.innerHTML = dateChipHTML(r.created_at);
        frag.appendChild(sep.firstChild);
        lastDay=dk;
      }
    }

    // contact bits (from API)
    const sName  = safeTrim(r.sender_name);
    const sEmail = safeTrim(r.sender_email);
    const sPhone = safeTrim(r.sender_phone);

    const initials   = initialsFrom(sName, sEmail, r.sender_role);
// Show only the sender's name; fallback to role if name missing
const whoLine = sName || esc(r.sender_role || '');
    const footerWho  = whoLine; // show name/email/phone even for own messages (matches assignee view)

    const att = r.attachments_json ? (JSON.parse(r.attachments_json)||[]) : [];
    const attHtml = buildAttachmentsHTML(att);

    const w = document.createElement('div');
    w.className = 'msg' + (isMe ? ' me' : '');
    w.dataset.messageId = r.id;
    w.dataset.createdAt = r.created_at || '';
    w.dataset.attachments = JSON.stringify(att||[]);

    const actionsHtml = allowEdit
      ? `<div class="msg-actions" style="margin-top:6px; display:flex; gap:8px;">
           <button class="icon-btn js-edit-msg" title="Edit message" style="width:28px;height:28px;border-radius:8px;">
             <i class="fa fa-pen"></i>
           </button>
         </div>`
      : '';

    w.innerHTML = `
      <div class="avatar" title="${whoLine}">${esc(initials)}</div>
      <div class="bubble">
        <div class="msg-body" data-msg-id="${r.id}">
          ${(r.message_html && r.message_html.trim())
            ? r.message_html
            : (r.message_text && r.message_text.trim() ? esc(r.message_text) : '')}
          ${attHtml}
        </div>
        <div style="font-size:11px;color:${isMe?'rgba(255,255,255,.85)':'#94a3b8'};margin-top:6px">
          ${fmtDateTime(r.created_at)} · ${footerWho}
        </div>
        ${actionsHtml}
      </div>`;
    frag.appendChild(w);
  });

  if(prepend) chat.prepend(frag); else { chat.appendChild(frag); chat.scrollTop = chat.scrollHeight; }
}
btnLoadOlder.addEventListener('click',async()=>{
  if(!dJob||msgPage>=msgTotalPages) return;
  olderSpin.style.display='inline-block'; btnLoadOlder.disabled=true;
  try{
    const top=chat.scrollHeight;
    msgPage+=1;
    const j=await GET(`${API.messages(dJob.id)}?page=${msgPage}&per_page=20`), rows=Array.isArray(j.data)?j.data:[];
    renderMessages(rows,true,true);
    btnLoadOlder.disabled=(msgPage>=(j.meta?.total_pages||msgPage));
    const after=chat.scrollHeight; chat.scrollTop=after-top;
  }finally{ olderSpin.style.display='none'; if(msgPage<msgTotalPages) btnLoadOlder.disabled=false }
});
function enterEditMode(w){
  if(!w) return;
  const id=w.dataset.messageId, body=w.querySelector('.msg-body'); if(!body) return;
  if(editMode&&editMsgId===id) return;
  editMode=true; editMsgId=id; editOrigHtml=body.innerHTML||'';
  try{ editOrigAttachments=JSON.parse(w.dataset.attachments||'[]') }catch{ editOrigAttachments=[] }
  editRemovedAttachmentIds=new Set(); editNewFiles=[];
  const t=document.createElement('div'); t.innerHTML=editOrigHtml; composer.innerHTML=t.innerHTML||''; composer.focus();
  showEditBanner();
  const s=byId('btnSend'); s.classList.add('editing'); s.title='Save changes'; s.innerHTML=`<i class="fa fa-check"></i>`;
  renderAttachChips();
}
chat.addEventListener('click',e=>{
  const b=e.target.closest('.js-edit-msg'); if(!b) return;
  enterEditMode(b.closest('.msg'))
});
byId('btnSend').addEventListener('click',sendMessage);
composer.addEventListener('keydown',e=>{ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); sendMessage() }});

function addTempMessage(html, files){
  const id = 'temp-' + Date.now();
  const m = {
    id,
    message_html: html,
    sender_role: role,
    created_at: new Date().toISOString(),
    attachments: files.map(f => ({ original_name: f.name, kind: 'file' })),
    __forceMe: true // ✅ tell the renderer to put this on the right
  };
  renderMessages([m], false, false);
  return id;
}

function removeTempMessage(id){ const el=chat.querySelector(`[data-message-id="${id}"]`); if(el)el.remove() }
function showSendingState(){
  const s=byId('btnSend'), a=byId('btnAttach'), c=byId('composer');
  s.disabled=true; s.style.opacity='0.7'; s.innerHTML=`<i class="fa fa-spinner fa-spin"></i>`;
  a.disabled=true; c.setAttribute('contenteditable','false');
}
function hideSendingState(){
  const s=byId('btnSend'), a=byId('btnAttach'), c=byId('composer');
  s.disabled=false; s.style.opacity='1';
  s.innerHTML=editMode?`<i class="fa fa-check"></i>`:`<i class="fa fa-paper-plane"></i>`;
  a.disabled=false; c.setAttribute('contenteditable','true');
}
async function sendMessage(){
  if (IS_CLIENT_USER) {
    info('You do not have permission for this action.');
    return;
  }
  if(!dJob) return;
  const html=(composer.innerHTML||'').trim();
  const files=editMode?editNewFiles.slice():attachmentPreviews.map(p=>p.file);
  if(editMode){
    const norm=h=>h.replace(/\s+/g,' ').replace(/<br\s*\/?>/gi,'\n').replace(/<div>/gi,'\n').replace(/<\/div>/gi,'').replace(/&nbsp;/gi,' ').trim();
    const changed=norm(editOrigHtml||'')!==norm(html||''), rm=(editRemovedAttachmentIds&&editRemovedAttachmentIds.size>0), nf=(editNewFiles&&editNewFiles.length>0);
    if(!changed&&!rm&&!nf){ info('No changes to save'); return }
  }else{ if(!html&&files.length===0){ info('Type a message or attach files'); return } }

  if(!editMode){
    const tmpId=addTempMessage(html,files); 
    showSendingState();
    const fd=new FormData(); if(html)fd.append('message_html',html); files.forEach(f=>fd.append('attachments[]',f));
    try{
      const r=await fetch(API.messages(dJob.id),{method:'POST',headers:H,body:fd}), j=await r.json().catch(()=>({}));
      if(!r.ok) throw new Error(j.message||('HTTP '+r.status));
      if(j&&j.data){ if(!j.data.sender_role)j.data.sender_role=role; if(!j.data.created_at)j.data.created_at=new Date().toISOString() }
      removeTempMessage(tmpId); msgEmpty.style.display='none'; renderMessages([j.data],false,true);
      const attCount=(j.data&&(j.data.attachments_json?(JSON.parse(j.data.attachments_json)||[]).length:(j.data.attachments||[]).length))||0;
      ok(attCount>0?`Message sent — ${attCount} attachment${attCount>1?'s':''} uploaded`:'Message sent');
      composer.innerHTML=''; attachmentPreviews.forEach(p=>{if(p.url)URL.revokeObjectURL(p.url)}); attachmentPreviews=[]; renderAttachChips(); renderAttachmentPreviews(); hideSendingState();
    }catch(ex){
      removeTempMessage(tmpId); err(ex.message||'Send failed'); composer.innerHTML=html; hideSendingState()
    }
    return;
  }

  // Edit mode
  if(!editMode||!editMsgId) return;
  showSendingState();
  const cancelBtn=byId('cancelEditBtn'); if(cancelBtn) cancelBtn.disabled=true;
  const fd=new FormData(); fd.append('message_html',html||''); fd.append('_method','PATCH');
  (editNewFiles||[]).forEach(f=>fd.append('attachments[]',f));
  Array.from(editRemovedAttachmentIds).forEach(id=>fd.append('remove_attachment_ids[]',id));
  try{
    const r=await fetch(API.messageUpdate(dJob.id,editMsgId),{method:'POST',headers:{'Authorization':'Bearer '+TOKEN,'Accept':'application/json'},body:fd}),
          j=await r.json().catch(()=>({}));
    if(!r.ok) throw new Error(j.message||j.error||('HTTP '+r.status));
    const u=j.data||{}, uHtml=u.message_html||html, uAtt=u.attachments_json?(JSON.parse(u.attachments_json)||[]):(u.attachments||[]),
          w=chat.querySelector(`[data-message-id="${editMsgId}"]`);
    if(w){
      const body=w.querySelector('.msg-body'); if(body){ body.innerHTML=uHtml; const nh=buildAttachmentsHTML(uAtt), old=body.querySelector('.msg-atts'); if(old)old.remove(); if(nh)body.insertAdjacentHTML('beforeend',nh); w.dataset.attachments=JSON.stringify(uAtt) }
      const allow=canEditMessage(u), act=allow?`<div class="msg-actions" style="margin-top:6px; display:flex; gap:8px;"><button class="icon-btn js-edit-msg" title="Edit message" style="width:28px;height:28px;border-radius:8px;"><i class="fa fa-pen"></i></button></div>`:'';
      const a=w.querySelector('.msg-actions'); if(a)a.outerHTML=act; else if(act)w.querySelector('.bubble').insertAdjacentHTML('beforeend',act)
    }
    ok('Message updated');
  }catch(ex){ err(ex.message||'Update failed') }
  finally{
    if(cancelBtn) cancelBtn.disabled=false;
    clearEditState(); composer.innerHTML=''; attachInput.value=''; renderAttachChips(); hideSendingState()
  }
}
/* ================== EXPENSE FUNCTIONALITY ================== */
if (expenseDate) {
  expenseDate.value = new Date().toISOString().split('T')[0];
}

// Event listeners for expense functionality
if (btnAddExpense) {
  btnAddExpense.addEventListener('click', () => {
    if (IS_CLIENT_USER) {
      info('You do not have permission for this action.');
      return;
    }
    expenseForm.style.display = 'block';
    expensesList.style.display = 'none';
    expensesEmpty.style.display = 'none';
    btnAddExpense.style.display = 'none';
  });
}

if (btnCancelExpense) {
  btnCancelExpense.addEventListener('click', () => {
    resetExpenseForm();
    showExpensesList();
  });
}

if (btnSaveExpense) {
  btnSaveExpense.addEventListener('click', saveExpense);
}

let allExpenses = [];
let shownCount = 0;
const EXPENSES_PER_PAGE = 10;
let isLoadingMore = false;

// server-side pagination tracking (added)
let serverTotal = null;    // total items as reported by server (if present)
let nextPageUrl = null;    // next page URL from server (if present)
let currentPage = 1;       // current page we have loaded (1-based)

// Global references to prevent recreation issues
let viewMoreBtn = null;
let endOfListMessage = null;

// Helper: create or reuse the "View More" button with spinner
function ensureViewMoreBtn() {
  if (viewMoreBtn && viewMoreBtn.parentNode) return viewMoreBtn;

  // Remove existing if found but orphaned
  const existingBtn = document.getElementById('btnViewMoreExpenses');
  if (existingBtn && existingBtn.parentNode) {
    existingBtn.parentNode.removeChild(existingBtn);
  }

  viewMoreBtn = document.createElement('button');
  viewMoreBtn.id = 'btnViewMoreExpenses';
  viewMoreBtn.type = 'button';
  viewMoreBtn.className = 'btn btn-outline-secondary w-100 mt-2 d-flex justify-content-center align-items-center';
  viewMoreBtn.style.display = 'none';
  viewMoreBtn.style.gap = '8px';

  const spinner = document.createElement('span');
  spinner.className = 'spinner-border spinner-border-sm';
  spinner.style.display = 'none';
  spinner.role = 'status';
  spinner.ariaHidden = true;

  const label = document.createElement('span');
  label.className = 'vm-label';
  label.textContent = 'View more';

  viewMoreBtn.appendChild(spinner);
  viewMoreBtn.appendChild(label);

  viewMoreBtn.addEventListener('click', async () => {
    await loadMoreExpenses();
  });

  expensesList.parentElement.appendChild(viewMoreBtn);
  return viewMoreBtn;
}

// Helper: create or reuse the "End of list" message
function ensureEndOfListMessage() {
  if (endOfListMessage && endOfListMessage.parentNode) return endOfListMessage;

  // Remove existing if found but orphaned
  const existingMsg = document.getElementById('endOfExpensesList');
  if (existingMsg && existingMsg.parentNode) {
    existingMsg.parentNode.removeChild(existingMsg);
  }

  endOfListMessage = document.createElement('div');
  endOfListMessage.id = 'endOfExpensesList';
  endOfListMessage.className = 'text-center text-muted py-3';
  endOfListMessage.style.display = 'none';
  endOfListMessage.innerHTML = '<em>You have reached the end of the list</em>';

  expensesList.parentElement.appendChild(endOfListMessage);
  return endOfListMessage;
}

// Core: show current slice of expenses
// Replace existing renderExpenses with this: always render all locally-available items
function renderExpenses(expenses) {
  if (!Array.isArray(expenses)) return;

  // clear current list
  expensesList.innerHTML = '';

  // Render ALL locally-available expenses (no slicing)
  expenses.forEach(exp => addExpenseToDOM(exp));

  // Show/hide the empty state
  if (expenses.length === 0) {
    expensesEmpty.style.display = 'block';
    expensesList.style.display = 'none';
  } else {
    expensesEmpty.style.display = 'none';
    expensesList.style.display = 'block';
  }

  // Ensure viewMore/end-of-list UI elements exist
  const viewMoreBtn = ensureViewMoreBtn();
  const endOfListMessage = ensureEndOfListMessage();

  // Since we're rendering everything locally, hide the View More button
  // but keep the end-of-list message logic (show it when there are items)
  viewMoreBtn.style.display = 'none';
  viewMoreBtn.disabled = true;

  if (expenses.length === 0) {
    endOfListMessage.style.display = 'none';
    window.removeEventListener('scroll', handleScrollLoadMore);
  } else {
    endOfListMessage.style.display = 'block';
    window.removeEventListener('scroll', handleScrollLoadMore);
  }

  // Special handling for Assignee mode — keep previous behaviour (hide view more)
  if (IS_ASSIGNEE) {
    viewMoreBtn.style.display = 'none';
    viewMoreBtn.disabled = true;
  }
}

// Load expense heads
async function loadExpenseHeads() {
  if (!expenseHead) return;
  
  try {
    const response = await GET(API.expenseHeads);
    const heads = response.data || [];
    
    expenseHead.innerHTML = '<option value="">Select expense head</option>' +
      heads.map(head => `<option value="${head.id}">${esc(head.title)}</option>`).join('');
  } catch (error) {
    console.error('Failed to load expense heads:', error);
    expenseHead.innerHTML = '<option value="">Failed to load expense heads</option>';
  }
}

function resetExpenseForm() {
  if (!expenseForm) return;
  
  expenseForm.style.display = 'none';
  if (expenseHead) expenseHead.value = '';
  if (expenseDate) expenseDate.value = new Date().toISOString().split('T')[0];
  if (expenseAmount) expenseAmount.value = '';
  if (expenseNote) expenseNote.value = '';
  if (expenseFile) {
    try { expenseFile.value = ''; } catch(e) { /* ignore readonly file inputs */ }
  }
}

function showExpensesList() {
  if (!expenseForm || !expensesList || !btnAddExpense || !expensesEmpty) return;
  
  expenseForm.style.display = 'none';
  expensesList.style.display = 'block';
  btnAddExpense.style.display = 'block';
  
  // Show empty state if no expenses
  if (expensesList.children.length === 0) {
    expensesEmpty.style.display = 'block';
    expensesList.style.display = 'none';
  } else {
    expensesEmpty.style.display = 'none';
    expensesList.style.display = 'block';
  }
}

async function saveExpense() {
  if (!dJob || !btnSaveExpense) return;

  // Basic validation
  if (!expenseHead.value) {
    err('Please select an expense head');
    return;
  }
  if (!expenseDate.value) {
    err('Please select an expense date');
    return;
  }
  if (!expenseAmount.value || parseFloat(expenseAmount.value) <= 0) {
    err('Please enter a valid amount');
    return;
  }

  // Client-side file size check (10 MB per file)
  const MAX_BYTES = 10 * 1024 * 1024;
  if (expenseFile && expenseFile.files && expenseFile.files.length) {
    for (let i = 0; i < expenseFile.files.length; i++) {
      const f = expenseFile.files[i];
      if (f.size > MAX_BYTES) {
        err(`Attachment "${f.name}" exceeds the 10 MB limit`);
        return;
      }
    }
  }

  btnSaveExpense.disabled = true;
  if (expenseSaveSpin) expenseSaveSpin.style.display = 'inline-block';

  try {
    const formData = new FormData();
    formData.append('expense_head_id', expenseHead.value);
    formData.append('expense_date', expenseDate.value);
    formData.append('amount', expenseAmount.value);
    formData.append('note', expenseNote.value || '');

    // append files (supports multiple) — controller expects attachments[] 
    if (expenseFile && expenseFile.files && expenseFile.files.length) {
      for (let i = 0; i < expenseFile.files.length; i++) {
        formData.append('attachments[]', expenseFile.files[i]);
      }
    }

    const response = await fetch(API.expenseStore(dJob.id), {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + TOKEN,
        'Accept': 'application/json',
      },
      body: formData
    });

    if (!response.ok) {
      // try to show validation messages from server
      const errorData = await response.json().catch(() => ({}));
      // If Laravel validation errors exist, join them for display
      if (errorData && errorData.errors) {
        const all = Object.values(errorData.errors).flat().join(' · ');
        throw new Error(all || errorData.message || 'Failed to save expense');
      }
      throw new Error(errorData.message || 'Failed to save expense');
    }

    const result = await response.json();
    // Prefer server canonical data: reload list so attachments/urls/creator info are exactly as backend produced
    if (result && result.data) {
      await loadExpenses(dJob.id);
    } else {
      const expense = result.data || result;
      addExpenseToDOM(expense);
    }

    resetExpenseForm();
    showExpensesList();
    ok('Expense saved successfully');
    
  } catch (error) {
    console.error('saveExpense error', error);
    err(error.message || 'Failed to save expense');
  } finally {
    btnSaveExpense.disabled = false;
    if (expenseSaveSpin) expenseSaveSpin.style.display = 'none';
  }
}

/**
 * Strip HTML and truncate to `limit` characters, ending with an ellipsis.
 * Keeps words intact where possible.
 */
function truncateText(input, limit = 140) {
  if (input == null) return '';
  // remove HTML tags and collapse whitespace
  const text = String(input).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  if (text.length <= limit) return text;
  // truncate without cutting the last word awkwardly
  const cut = text.slice(0, limit);
  const lastSpace = cut.lastIndexOf(' ');
  return (lastSpace > Math.floor(limit * 0.6) ? cut.slice(0, lastSpace) : cut) + '…';
}

function addExpenseToDOM(expense) {
  if (!expensesList) return;

  const expenseElement = document.createElement('div');
  expenseElement.className = 'expense-bubble';
  expenseElement.style.cursor = 'pointer';

  const headTitle = expense.expense_head_title || expense.expense_head || 'Expense';
  const amount = parseFloat(expense.amount || 0).toFixed(2);

  // Build attachments HTML (show ALL attachments if present)
  let attachmentsHtml = '';
  try {
    const atts = expense.attachments_json ? (typeof expense.attachments_json === 'string' ? JSON.parse(expense.attachments_json || '[]') : (expense.attachments_json || [])) : [];
    if (Array.isArray(atts) && atts.length) {
      attachmentsHtml = `<div class="expense-docs" style="margin-top:8px">` +
        atts.map(a => {
          const url = a.absolute_url || a.relative_url || a.url || a.absoluteUrl || '';
          const name = a.original_name || a.title || a.stored_name || 'Attachment';
          return `<div style="margin-top:6px"><i class="fa fa-paperclip" style="margin-right:8px"></i><a href="${esc(url)}" target="_blank">${esc(name)}</a></div>`;
        }).join('') +
      `</div>`;
    } else if (expense.attachment_url) {
      const fileName = expense.original_filename || 'Attachment';
      attachmentsHtml = `<div class="expense-docs" style="margin-top:8px"><div><i class="fa fa-paperclip" style="margin-right:8px"></i><a href="${esc(expense.attachment_url)}" target="_blank">${esc(fileName)}</a></div></div>`;
    }
  } catch (e) {
    attachmentsHtml = ''; // fail silently
  }

  // Note HTML (escaped, short preview in list)
  const noteHtml = expense.note
    ? `<div class="expense-note" style="margin-top:8px;color:#334155;line-height:1.4">${esc(truncateText(expense.note, 300))}</div>`
    : '';

  // creator & times
  const creatorName  = expense.creator_name ? String(expense.creator_name).trim() : '';
  const creatorEmail = expense.creator_email ? String(expense.creator_email).trim() : '';
  const creatorDisplayName = creatorName || creatorEmail || 'Unknown';
  const createdAt = expense.created_at ? fmtDateTime(expense.created_at) : (expense.created_at_display || '');

  // footer: show name in bold, email on next line (mailto if available), and created time on the right
  const creatorHtml = creatorName
    ? `<div style="display:flex;flex-direction:column;gap:2px"><strong>${esc(creatorName)}</strong>${creatorEmail ? `<a href="mailto:${esc(creatorEmail)}" style="font-size:12px;color:#64748b;text-decoration:underline">${esc(creatorEmail)}</a>` : ''}</div>`
    : `<div><strong>${esc(creatorDisplayName)}</strong></div>`;

  expenseElement.innerHTML = `
    <div class="expense-head" style="font-weight:600">${esc(headTitle)}</div>

    <div class="expense-meta" style="display:flex;gap:12px;align-items:center;margin-top:6px">
      <div class="expense-date" style="font-size:13px;color:#64748b">${fmtDate(expense.expense_date)}</div>
      <div class="expense-amount" style="font-size:15px;font-weight:700">₹${amount}</div>
    </div>

    ${attachmentsHtml}

    ${noteHtml}

    <div class="expense-footer" style="font-size:13px;color:#64748b;border-top:1px solid #e2e8f0;padding-top:8px;margin-top:10px;display:flex;gap:8px;align-items:center;justify-content:space-between">
      <div class="expense-creator" style="display:flex;flex-direction:column;align-items:flex-start;">
        ${creatorHtml}
        ${expense.creator_phone ? `<div style="font-size:12px;color:#94a3b8;margin-top:4px">${esc(expense.creator_phone)}</div>` : ''}
      </div>
      <div style="color:#94a3b8;font-size:12px">${esc(createdAt)}</div>
    </div>
  `;

  // optional: click to open full details (if you have showExpenseDetails)
  if (typeof showExpenseDetails === 'function') {
    expenseElement.addEventListener('click', () => showExpenseDetails(expense));
  }

  expensesList.appendChild(expenseElement);
  if (expensesEmpty) expensesEmpty.style.display = 'none';
  expensesList.style.display = 'block';
}

async function loadMoreExpenses() {
  // Safety: prevent concurrent loads
  if (isLoadingMore) return;

  const viewMoreBtn = ensureViewMoreBtn();
  const spinner = viewMoreBtn.querySelector('.spinner-border');
  const label = viewMoreBtn.querySelector('.vm-label');

  // If we've shown all local items but server reports more, fetch next page
  const localExhausted = shownCount >= allExpenses.length;
  const serverHasMore = serverTotal ? (allExpenses.length < serverTotal) : false;

  if (localExhausted && serverHasMore) {
    // attempt to fetch next page from server
    // construct fetch URL robustly
    let fetchUrl = null;
    if (nextPageUrl) {
      fetchUrl = nextPageUrl;
    } else {
      const nextPage = (currentPage || 1) + 1;
      try {
        const base = typeof API.expenses === 'function' ? API.expenses(dJob.id) : API.expenses;
        fetchUrl = base + (String(base).includes('?') ? '&' : '?') + 'page=' + nextPage;
      } catch (e) {
        fetchUrl = (typeof API.expenses === 'function' ? API.expenses(dJob.id) : API.expenses) + '?page=' + nextPage;
      }
    }

    isLoadingMore = true;
    viewMoreBtn.disabled = true;
    spinner.style.display = 'inline-block';
    label.textContent = 'Loading...';

    try {
      const resp = await GET(fetchUrl);
      const newItems = Array.isArray(resp.data) ? resp.data : [];
      // update pagination trackers if provided
      if (resp.meta && typeof resp.meta.total !== 'undefined') serverTotal = Number(resp.meta.total);
      if (resp.meta && resp.meta.current_page) currentPage = Number(resp.meta.current_page);
      if (resp.meta && resp.meta.next_page_url) nextPageUrl = resp.meta.next_page_url;
      if (resp.links && resp.links.next) nextPageUrl = resp.links.next;

      // append and re-sort by created_at desc to preserve ordering
      allExpenses = allExpenses.concat(newItems || []);
      allExpenses.sort((a, b) => {
        const aT = new Date(a.created_at || 0).getTime();
        const bT = new Date(b.created_at || 0).getTime();
        return bT - aT;
      });

      // expand shownCount so the newly fetched items are visible
      const prevShown = shownCount;
      shownCount = Math.min(allExpenses.length, shownCount + EXPENSES_PER_PAGE);

      renderExpenses(allExpenses);

      // scroll to first newly added item if present
      const items = expensesList.querySelectorAll('.expense-bubble');
      if (items.length > prevShown) {
        const el = items[prevShown];
        if (el && typeof el.scrollIntoView === 'function') {
          el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    } catch (err) {
      console.error('Failed to fetch next expenses page', err);
    } finally {
      spinner.style.display = 'none';
      label.textContent = 'View more';
      isLoadingMore = false;

      // Decide visibility after fetching
      if (shownCount >= allExpenses.length) {
        viewMoreBtn.style.display = 'none';
        viewMoreBtn.disabled = true;
        window.removeEventListener('scroll', handleScrollLoadMore);
        const endMsg = ensureEndOfListMessage();
        endMsg.style.display = 'block';
      } else {
        viewMoreBtn.disabled = false;
        viewMoreBtn.style.display = 'flex';
        window.addEventListener('scroll', handleScrollLoadMore);
      }

      if (allExpenses.length === 0) {
        viewMoreBtn.style.display = 'none';
        viewMoreBtn.disabled = true;
        window.removeEventListener('scroll', handleScrollLoadMore);
      }
    }
    return;
  }

  // If we get here, either we still have local items to reveal or there is no server more
  // Original local-only behavior preserved below.

  // Safety: prevent loading when everything is already shown
  if (isLoadingMore || shownCount >= allExpenses.length) {
    viewMoreBtn.style.display = 'none';
    return;
  }

  isLoadingMore = true;
  viewMoreBtn.disabled = true;
  spinner.style.display = 'inline-block';
  label.textContent = 'Loading...';

  // small delay so spinner is visible for a moment
  await new Promise(res => setTimeout(res, 300));

  try {
    const prevShown = shownCount;
    shownCount = Math.min(allExpenses.length, shownCount + EXPENSES_PER_PAGE);

    // re-render the list with the new shownCount
    renderExpenses(allExpenses);

    // smooth scroll to the first newly added item (if present)
    const items = expensesList.querySelectorAll('.expense-bubble');
    if (items.length > prevShown) {
      const el = items[prevShown];
      if (el && typeof el.scrollIntoView === 'function') {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  } catch (err) {
    console.error('loadMoreExpenses error', err);
  } finally {
    // restore spinner/label state
    spinner.style.display = 'none';
    label.textContent = 'View more';
    isLoadingMore = false;

    // If all expenses are shown, hide and disable the button and remove scroll listener
    if (shownCount >= allExpenses.length) {
      viewMoreBtn.style.display = 'none';
      viewMoreBtn.disabled = true;
      window.removeEventListener('scroll', handleScrollLoadMore);
      // show end-of-list message (renderExpenses will already handle this, but ensure)
      const endMsg = ensureEndOfListMessage();
      endMsg.style.display = 'block';
    } else {
      // still more to load — enable the button
      viewMoreBtn.disabled = false;
      viewMoreBtn.style.display = 'flex';
      window.addEventListener('scroll', handleScrollLoadMore);
    }

    // Special case: if there are zero expenses (safety)
    if (allExpenses.length === 0) {
      viewMoreBtn.style.display = 'none';
      viewMoreBtn.disabled = true;
      window.removeEventListener('scroll', handleScrollLoadMore);
    }
  }
}


// Auto "View More" when scrolled to bottom
function handleScrollLoadMore() {
  const nearBottom =
    window.innerHeight + window.scrollY >= document.body.offsetHeight - 100;
  if (nearBottom && !isLoadingMore && shownCount < allExpenses.length) {
    loadMoreExpenses();
  }
}

// Main loader — fetch all server pages and render ALL items locally
async function loadExpenses(jobId) {
  if (!expensesList || !expensesEmpty) return;

  try {
    // first page
    const firstResp = await GET(API.expenses(jobId));
    let expensesPage = Array.isArray(firstResp.data) ? firstResp.data : [];

    // store pagination info
    serverTotal = (firstResp.meta && typeof firstResp.meta.total !== 'undefined') ? Number(firstResp.meta.total) : null;
    if (firstResp.meta && firstResp.meta.current_page) currentPage = Number(firstResp.meta.current_page);
    if (firstResp.meta && firstResp.meta.next_page_url) nextPageUrl = firstResp.meta.next_page_url;
    if (firstResp.links && firstResp.links.next) nextPageUrl = firstResp.links.next;

    // Begin with first page items
    let combined = expensesPage.slice();

    // If serverTotal indicates more items than returned here, fetch subsequent pages
    // Keep fetching as long as there's a nextPageUrl OR combined.length < serverTotal
    // Defensive: limit loop to avoid infinite loops (max 100 pages)
    let attempts = 0;
    while (true) {
      attempts++;
      if (attempts > 100) break; // safety stop

      // stop if no indication of more items
      if (!nextPageUrl && !(serverTotal && combined.length < serverTotal)) break;

      // compute next fetch URL
      let fetchUrl = nextPageUrl;
      if (!fetchUrl) {
        const nextPage = (currentPage || 1) + 1;
        try {
          const base = typeof API.expenses === 'function' ? API.expenses(jobId) : API.expenses;
          fetchUrl = base + (String(base).includes('?') ? '&' : '?') + 'page=' + nextPage;
        } catch (e) {
          fetchUrl = (typeof API.expenses === 'function' ? API.expenses(jobId) : API.expenses) + '?page=' + nextPage;
        }
      }

      // attempt fetch
      let resp;
      try {
        resp = await GET(fetchUrl);
      } catch (err) {
        console.error('Failed to fetch next expenses page while collecting all pages', { fetchUrl, err });
        break;
      }

      const pageItems = Array.isArray(resp.data) ? resp.data : [];
      if (!pageItems.length) {
        // nothing returned — stop to avoid infinite loop
        if (resp.meta && resp.meta.current_page) {
          currentPage = Number(resp.meta.current_page);
        }
        if (resp.meta && resp.meta.next_page_url) nextPageUrl = resp.meta.next_page_url;
        if (resp.links && resp.links.next) nextPageUrl = resp.links.next;
        break;
      }

      // append
      combined = combined.concat(pageItems);

      // update trackers from this response
      if (resp.meta && typeof resp.meta.total !== 'undefined') serverTotal = Number(resp.meta.total);
      if (resp.meta && resp.meta.current_page) currentPage = Number(resp.meta.current_page);
      if (resp.meta && resp.meta.next_page_url) nextPageUrl = resp.meta.next_page_url;
      if (resp.links && resp.links.next) nextPageUrl = resp.links.next;

      // if we've gathered all known items, stop
      if (serverTotal && combined.length >= serverTotal) break;

      // otherwise continue loop (nextPageUrl may guide it)
    }

    // Final combined array: sort by created_at desc
    combined.sort((a, b) => {
      const aT = new Date(a.created_at || 0).getTime();
      const bT = new Date(b.created_at || 0).getTime();
      return bT - aT;
    });

    // Actor email from controller (preserve existing code)
    const actorEmail = (
      firstResp.actor_email ||
      (firstResp.meta && firstResp.meta.actor_email) ||
      ''
    ).toString().trim().toLowerCase();

    // Apply role-based filter (same as before)
    let visibleExpenses = combined;
    if (IS_ASSIGNEE) {
      if (actorEmail) {
        visibleExpenses = combined.filter(exp => {
          const creatorEmail = (exp.creator_email || '').toString().trim().toLowerCase();
          return creatorEmail === actorEmail;
        });
      } else {
        console.warn('Assignee mode: actor_email missing, hiding all expenses.');
        visibleExpenses = [];
      }
    }

    // Store locally and render ALL of them
    allExpenses = visibleExpenses;
    // Show all items locally — regardless of being multiple of 10 or not
    shownCount = allExpenses.length;
    renderExpenses(allExpenses);

  } catch (error) {
    console.error('Failed to load expenses:', error);
    showExpensesList();
  }
}

async function openDrawer(id){
  dJob=null; dJobId.textContent='—'; dTitle.textContent='—'; dClient.textContent='—';
  [dStart,dEnd,dDeadline,dDuration].forEach(el=>el.textContent='—');
  dChipType.textContent='—'; dChipPriority.textContent='—'; dChipAssignees.textContent='—';
  dDesc.innerHTML='—';
  chat.innerHTML=''; msgEmpty.style.display='none';
  attachmentPreviews.forEach(p=>{if(p.url)URL.revokeObjectURL(p.url)}); attachmentPreviews=[];
  attachInput.value=''; renderAttachChips(); renderAttachmentPreviews();
  btnLoadOlder.disabled=true; byId('dTitleSk').style.display='block'; drawerBusy.style.display='flex'; drawer.show();
  
  try{
    const j=await GET(API.show(id)); dJob=j.data;
    dJobId.textContent=dJob.id; dTitle.textContent=dJob.title||'—'; dClient.textContent=j.data.client_name||'—';
    dChipType.textContent=(dJob.type||'task');
    dChipPriority.textContent=(dJob.priority||'normal').replaceAll('_',' ');
    dChipAssignees.textContent=(j.assignees?j.assignees.length:(dJob.assignees_count||0))+' assignee(s)';
    dStart.textContent=fmtDate(dJob.planned_start_at); dEnd.textContent=fmtDate(dJob.planned_end_at); dDeadline.textContent=fmtDate(dJob.planned_deadline_at);
    if(dJob.planned_start_at&&dJob.planned_end_at){ const sd=new Date(dJob.planned_start_at), ed=new Date(dJob.planned_end_at); dDuration.textContent=Math.max(0,Math.round((ed-sd)/86400000))+' day(s)' }
    dStatus.value=(dJob.status||'planned');
    dDesc.innerHTML = dJob.description || '<span style="font-size:13px;color:#94a3b8">No description</span>';
    
    // Reset expense form and load expenses
    resetExpenseForm();
    showExpensesList();
    await loadExpenses(id);
    
    await loadMessages(true);
  }catch(ex){ 
    err(ex.message||'Failed to open') 
  }finally{ 
    byId('dTitleSk').style.display='none'; 
    drawerBusy.style.display='none' 
  }
}
/* ===== Assignees dropdown (no modal) ===== */
/* ===== Assignees dropdown (no modal) ===== */
dChipAssignees.classList.add('clickable');
dChipAssignees.title = 'Click to view assignees';

let ddEl = null;           // the dropdown element
let ddOpenForJobId = null; // which job it's showing for
let ddOutsideHandler = null;

// --- viewport reposition RAF handle (added globally once) ---
let ddRaf = null;
function onViewportScroll(){
  if (!ddEl || ddEl.style.display === 'none') return;
  if (ddRaf) return;
  ddRaf = requestAnimationFrame(() => {
    ddRaf = null;
    // If anchor is offscreen completely, close; otherwise reposition
    try {
      const rect = dChipAssignees.getBoundingClientRect();
      const offscreen = rect.bottom < 0 || rect.top > window.innerHeight || rect.right < 0 || rect.left > window.innerWidth;
      if (offscreen) {
        closeAssigneesDD();
      } else {
        positionDD(dChipAssignees);
      }
    } catch (e) {
      // defensive: if element not present, close
      closeAssigneesDD();
    }
  });
}
// ----------------------------------------------------------------

function ensureAssigneesDD(){
  if(ddEl) return ddEl;
  ddEl = document.createElement('div');
  ddEl.className = 'assignees-dd';
  ddEl.setAttribute('role','menu');
  document.body.appendChild(ddEl);

  // prevent dropdown internal scrolling from bubbling up and triggering page-level handlers
  ddEl.addEventListener('wheel', e => e.stopPropagation(), { passive:true });
  ddEl.addEventListener('touchmove', e => e.stopPropagation(), { passive:true });
  ddEl.addEventListener('scroll', e => e.stopPropagation(), { passive:true });

  return ddEl;
}

function positionDD(anchorEl){
  const rect = anchorEl.getBoundingClientRect();
  const pad = 8;
  const w   = Math.min(250, window.innerWidth - 16);
  ddEl.style.width = w + 'px';

  // Prefer below, flip above if not enough space
  let top = rect.bottom + pad;
  const estH = Math.min(320, ddEl.scrollHeight || 240);
  if (top + estH > window.innerHeight - 8 && (rect.top - pad - estH) >= 0) {
    top = rect.top - pad - estH;
  }

  // Right-align to chip, keep within viewport
  let left = rect.right - w;
  if (left < 8) left = Math.max(8, rect.left);
  if (left + w > window.innerWidth - 8) left = Math.max(8, window.innerWidth - w - 8);

  ddEl.style.top  = Math.round(top) + 'px';
  ddEl.style.left = Math.round(left) + 'px';
}

function closeAssigneesDD(){
  if(!ddEl) return;
  ddEl.style.display = 'none';
  ddEl.innerHTML = '';
  ddOpenForJobId = null;

  // remove the dedicated handlers we added (reposition & outside click)
  window.removeEventListener('resize', onViewportScroll, { passive:true });
  window.removeEventListener('scroll', onViewportScroll, { passive:true });
  document.removeEventListener('click', ddOutsideHandler, true);
}

async function openAssigneesDD(){
  if(!dJob) return;
  const jobId = dJob.id;
  const el = ensureAssigneesDD();

  // toggle if already open for this job
  if (ddOpenForJobId === jobId && el.style.display !== 'none') {
    closeAssigneesDD();
    return;
  }

  ddOpenForJobId = jobId;
  el.innerHTML = `
    <div style="display:flex;align-items:center;gap:8px;color:#64748b;padding:10px 12px">
      <div class="spinner-border"></div><span>Loading assignees...</span>
    </div>`;
  el.style.display = 'block';
  positionDD(dChipAssignees);

  ddOutsideHandler = (e) => {
    if (!ddEl || ddEl.style.display === 'none') return;

    const insideDD = e.target.closest('.assignees-dd');
    const onChip   = e.target === dChipAssignees || e.target.closest('#dChipAssignees');
    if (insideDD || onChip) return;
    closeAssigneesDD();
  };

  // listen for outside clicks (capture) and reposition on viewport scroll/resize
  document.addEventListener('click', ddOutsideHandler, true);
  window.addEventListener('resize', onViewportScroll, { passive:true });
  window.addEventListener('scroll', onViewportScroll, { passive:true });

  // also close when drawer hides
  document.getElementById('jobDrawer').addEventListener('hidden.bs.offcanvas', closeAssigneesDD, { once:true });

  try{
    const j = await GET(API.assignees(jobId));
    const rows = Array.isArray(j.data) ? j.data : [];

    if (!rows.length) {
      el.innerHTML = `<div class="dd-empty">No assignees for this job.</div>`;
      positionDD(dChipAssignees);
      return;
    }

    el.innerHTML = rows.map(r=>{
      const name  = esc(r.name || r.email || ('Person #'+r.id));
      const email = r.email ? ` <span style="color:#94a3b8;font-size:12px">· ${esc(r.email)}</span>` : '';
      const status = r.map_status
        ? `<span class="badge ${badgeClass('status', r.map_status)}" style="margin-left:6px;">${badgeLabel(r.map_status)}</span>`
        : '';
      return `<div class="dd-item"><div><strong>${name}</strong>${email}</div><div>${status}</div></div>`;
    }).join('');
    positionDD(dChipAssignees);
  } catch(ex){
    el.innerHTML = `<div class="dd-empty" style="color:#dc2626">${esc(ex.message || 'Failed to load assignees')}</div>`;
    positionDD(dChipAssignees);
  }
}

// click to toggle dropdown
dChipAssignees.addEventListener('pointerdown', (e) => {
  // Don't let any capture-phase document listeners preempt us
  e.preventDefault();
  e.stopImmediatePropagation(); // stronger than stopPropagation
  openAssigneesDD();
}, { capture: true });

async function loadMessages(initial=false){

  if(!dJob) return;

  msgBusy.style.display='block';

  try{

    msgPage=1;

    const j=await GET(`${API.messages(dJob.id)}?page=${msgPage}&per_page=20`), rows=Array.isArray(j.data)?j.data:[];

    msgTotalPages=j.meta?.total_pages||1;

    // SIMPLY USE ALL ROWS - NO DATE FILTERING:

    const rr = rows; // Just assign rows directly

    chat.innerHTML='';

    if(!rr.length) msgEmpty.style.display='block'; else{ msgEmpty.style.display='none'; renderMessages(rr,false,true) }

    btnLoadOlder.disabled=(msgPage>=msgTotalPages);

  }finally{ msgBusy.style.display='none' }

}
 
dSaveStatus.addEventListener('click',async()=>{
  if (IS_CLIENT_USER) {
    info('You do not have permission for this action.');
    return;
  }
  if(!dJob) return;
  dSaveStatus.disabled=true; dStatus.disabled=true; dStatusSpin.style.display='inline-block';
  const row=tbody.querySelector(`tr[data-id="${dJob.id}"]`); let cell=null, prev='';
  if(row){
    row.dataset.updating='1'; row.style.opacity='0.85'; row.style.pointerEvents='none'; row.classList.add('row-updating');
    cell=row.querySelectorAll('td')[4]; if(cell){ prev=cell.innerHTML; cell.innerHTML=`<span class="skel-pill"></span>` }
  }
  try{
   await JSONreq(API.statusChange(dJob.id),'PATCH',{status:dStatus.value});
     ok('Status updated'); dJob.status=dStatus.value;
    if(cell) cell.innerHTML=`<span class="${badgeClass('status',dStatus.value)}">${badgeLabel(dStatus.value)}</span>`;
  }catch(ex){ err(ex.message||'Update failed'); if(cell&&prev) cell.innerHTML=prev }
  finally{
    dSaveStatus.disabled=false; dStatus.disabled=false; dStatusSpin.style.display='none';
    if(row){ row.style.opacity=''; row.style.pointerEvents=''; delete row.dataset.updating; row.classList.remove('row-updating') }
  }
});
/* minor: keep existing bindings */
byId('btnLoadOlder');
chat.addEventListener('click',e=>{const b=e.target.closest('.js-edit-msg'); if(!b)return; enterEditMode(b.closest('.msg'))});
// --- Tab highlight helpers ---
// call after drawer/tab elements exist (put near other event-binding code)
(function initTabHighlighting(){
  const convBtn = document.getElementById('conversation-tab');
  const expBtn  = document.getElementById('expenses-tab');
  const convPane = document.getElementById('conversation');
  const expPane  = document.getElementById('expenses');

  if(!convBtn || !expBtn || !convPane || !expPane) return;

  function setActiveTab(tabName){
    if(tabName === 'conversation'){
      // visual
      convBtn.classList.add('active'); convBtn.setAttribute('aria-selected','true');
      expBtn.classList.remove('active'); expBtn.setAttribute('aria-selected','false');
      // content
      convPane.classList.add('show','active'); convPane.style.display = 'block';
      expPane.classList.remove('show','active'); expPane.style.display = 'none';
    } else {
      expBtn.classList.add('active'); expBtn.setAttribute('aria-selected','true');
      convBtn.classList.remove('active'); convBtn.setAttribute('aria-selected','false');
      expPane.classList.add('show','active'); expPane.style.display = 'block';
      convPane.classList.remove('show','active'); convPane.style.display = 'none';
    }
  }

  // ensure clicks set active state (preserve any existing onclick inline handlers)
  convBtn.addEventListener('click', (ev)=>{
    // allow original handler to run first — then enforce active visuals
    setTimeout(()=>setActiveTab('conversation'), 0);
  });
  expBtn.addEventListener('click', (ev)=>{
    setTimeout(()=>setActiveTab('expenses'), 0);
  });

  // When drawer opens, ensure the currently visible pane's tab is highlighted
  document.getElementById('jobDrawer')?.addEventListener('shown.bs.offcanvas', ()=>{
    // choose active based on currently visible pane
    if(convPane.classList.contains('show') || convPane.style.display === 'block') setActiveTab('conversation');
    else setActiveTab('expenses');
  });

  // initialize right away (keeps the first tab active as in your markup)
  setActiveTab(convBtn.classList.contains('active') ? 'conversation' : 'conversation');
})();

/* ================== EXPENSES EXPORT (UI + handlers) ================== */
/**
 * Adds an Export button next to the Add Expense button, prompts for format,
 * and downloads expenses as CSV (Excel), Word (.doc), or PDF.
 *
 * Relies on helpers in your file:
 * - GET, esc, ok, err, info, generateEnhancedPDF, parseFilename, API.expenses, TOKEN, dJob
 * - allExpenses / loadExpenses() — if allExpenses is empty we fetch first
 */

(function setupExpenseExport() {

  // safe esc fallback (uses existing esc if present)
  const esc = (typeof window !== 'undefined' && typeof window.esc === 'function')
    ? window.esc
    : (s => {
        if (s === null || typeof s === 'undefined') return '';
        return String(s)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      });

  // ---------- helper: compute grand totals ----------
  function computePersonTotals(expenses) {
    const totals = {}; // { personName: { CUR: amount, ... }, ... }
    if (!Array.isArray(expenses)) return totals;

    for (const e of expenses) {
      let name = e && (e.creator_name || e.creator_email) ? (e.creator_name || e.creator_email) : `#${Number(e && e.created_by || 0)}`;
      name = String(name);
      const currency = (e && (e.currency || 'INR') || 'INR').toUpperCase();
      const amount = Number(e && e.amount || 0);

      if (!totals[name]) totals[name] = {};
      if (!totals[name][currency]) totals[name][currency] = 0;
      totals[name][currency] += amount;
    }

    // sort keys by descending grand sum
    const sorted = {};
    Object.keys(totals)
      .sort((a, b) => {
        const sa = Object.values(totals[a]).reduce((s, v) => s + v, 0);
        const sb = Object.values(totals[b]).reduce((s, v) => s + v, 0);
        return sb - sa;
      })
      .forEach(k => { sorted[k] = totals[k]; });

    return sorted;
  }

  // --- create Export button ---
  const exportBtn = document.createElement('button');
  exportBtn.id = 'btnExportExpenses';
  exportBtn.type = 'button';
  exportBtn.className = 'btn btn-secondary';
  exportBtn.style = 'height:38px;padding:0 12px;margin-left:8px;display:inline-flex;align-items:center;gap:8px';
  exportBtn.innerHTML = `<i class="fa fa-download"></i> Export`;

  if (typeof btnAddExpense !== 'undefined' && btnAddExpense && btnAddExpense.parentNode) {
    btnAddExpense.parentNode.insertBefore(exportBtn, btnAddExpense.nextSibling);
  } else {
    const toolbar = document.querySelector('.toolbar') || document.body;
    toolbar.appendChild(exportBtn);
  }

  // --- click handler ---
  exportBtn.addEventListener('click', async () => {
    if (!dJob || !dJob.id) { info('Open a job first'); return; }

    const { value: format } = await Swal.fire({
      title: 'Export expenses',
      input: 'radio',
      inputOptions: { excel: 'Excel (CSV)', word: 'Word (.doc)', pdf: 'PDF (.pdf)' },
      inputValidator: v => !v && 'Select a format',
      showCancelButton: true,
      confirmButtonText: 'Download',
      cancelButtonText: 'Cancel'
    });
    if (!format) return;

    try {
      if (!Array.isArray(allExpenses) || !allExpenses.length) {
        await loadExpenses(dJob.id);
      }
      const expenses = Array.isArray(allExpenses) ? allExpenses.slice() : [];

      if (format === 'excel') return downloadExpensesCSV(expenses, dJob.id);
      if (format === 'word')  return downloadExpensesWord(expenses, dJob.id);
      if (format === 'pdf')   return exportExpensesAsPDF(expenses, dJob.id);
    } catch (ex) {
      console.error('Export failed', ex);
      err(ex.message || 'Export failed');
    }
  });

  /* ---------- CSV (Excel) ---------- */
 /* ---------- CSV (Excel) ---------- */
function downloadExpensesCSV(expenses, jobId) {
  expenses = Array.isArray(expenses) ? expenses : [];

  const cols = ['ID','Expense Date','Expense Head','Amount','Currency','Note','Creator','Created At','Attachments'];
  const rows = expenses.map(e => {
    let atts = '';
    try {
      const a = e.attachments_json ? (typeof e.attachments_json === 'string' ? JSON.parse(e.attachments_json||'[]') : e.attachments_json || []) : [];
      atts = a.map(x => x.absolute_url || x.relative_url || '').filter(Boolean).join(' | ');
    } catch {}
    const note = (e.note || '').replace(/\r\n|\r|\n/g,' ').replace(/"/g,'""');
    return [
      `"${String(e.id||'')}"`,
      `"${String(e.expense_date||'')}"`,
      `"${String(e.expense_head||'')}"`,
      Number(e.amount||0).toFixed(2),
      `"${String(e.currency||'')}"`,
      `"${note}"`,
      `"${String(e.creator_name || e.creator_email || '')}"`,
      `"${String(e.created_at || '')}"`,
      `"${String(atts).replace(/"/g,'""')}"`
    ].join(',');
  });

  // compute totals (person -> currency -> amount)
  const personTotals = typeof computePersonTotals === 'function' ? computePersonTotals(expenses) : {};

  // compute overall totalExpense as sum of all person/currency totals
  let totalExpense = 0;
  for (const p of Object.keys(personTotals)) {
    const cmap = personTotals[p] || {};
    for (const cur of Object.keys(cmap)) {
      totalExpense += Number(cmap[cur]) || 0;
    }
  }

  // build CSV lines
  const lines = [];
  lines.push(cols.join(','));
  lines.push(...rows);
  lines.push(''); // blank line

  // add Total Expense line (single combined number)
  lines.push(`Total Expense, , , ${totalExpense.toFixed(2)}, , , , , `);

  lines.push(''); // blank line
  lines.push('Grand totals by person');
  lines.push(['Person','Currency','Amount'].join(','));
  for (const person of Object.keys(personTotals)) {
    const cmap = personTotals[person] || {};
    for (const cur of Object.keys(cmap)) {
      // quote person to be safe
      lines.push([`"${String(person).replace(/"/g,'""')}"`, `"${String(cur).replace(/"/g,'""')}"`, Number(cmap[cur]).toFixed(2)].join(','));
    }
  }

  const csv = lines.join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `job_${jobId}_expenses_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  // cleanup
  setTimeout(() => {
    URL.revokeObjectURL(a.href);
    a.remove();
  }, 250);

  ok('CSV exported');
}
  /* ---------- Word (.doc) ---------- */
function downloadExpensesWord(expenses, jobId) {
  // safety defaults
  expenses = Array.isArray(expenses) ? expenses : [];
  const personTotals = typeof computePersonTotals === 'function' ? computePersonTotals(expenses) : {};

  // compute overall total (sum of all person/currency totals)
  let totalExpense = 0;
  for (const p of Object.keys(personTotals)) {
    const cmap = personTotals[p] || {};
    for (const cur of Object.keys(cmap)) {
      totalExpense += Number(cmap[cur]) || 0;
    }
  }

  // build totals table HTML (if any)
  let totalsHtml = '';
  if (Object.keys(personTotals).length) {
    // total expense line (before per-person totals)
    totalsHtml += `<div style="margin-top:8px;margin-bottom:6px;font-weight:600;font-size:20px;color:#0369a1;">Total Expense: <strong>${totalExpense.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</strong></div>`;

    totalsHtml += '<h2>Expense Summary</h2>';
    totalsHtml += '<table class="totals"><thead><tr><th>Person</th><th>Currency</th><th style="text-align:right">Total</th></tr></thead><tbody>';
    for (const person of Object.keys(personTotals)) {
      const cmap = personTotals[person] || {};
      for (const cur of Object.keys(cmap)) {
        totalsHtml += `<tr><td>${esc(person)}</td><td>${esc(cur)}</td><td style="text-align:right">${Number(cmap[cur]).toFixed(2)}</td></tr>`;
      }
    }
    totalsHtml += '</tbody></table><br/>';
  } else {
    // still show totalExpense (zero when no personTotals)
    totalsHtml += `<div style="margin-top:8px;margin-bottom:6px;font-weight:600;color:#0369a1;">Total Expense: <strong>${totalExpense.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</strong></div>`;
  }
  // Word-friendly HTML + CSS. Prepend BOM below when creating the Blob.
  const html = `<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="utf-8" />
<title>Job ${esc(jobId)} — Expenses</title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#333;margin:12px;}
  h1{color:#0369a1;margin:0 0 6px;}
  h2{color:#0369a1;margin-top:14px;}
  .meta{font-size:11px;color:#64748b;margin-bottom:8px;}
  table{width:100%;border-collapse:collapse;margin-top:10px;mso-table-lspace:0pt;mso-table-rspace:0pt;}
  th,td{border:1px solid #ddd;padding:6px 8px;vertical-align:top;}
  th{background:#f3f4f6;font-weight:700;text-align:left;}
  td.right{text-align:right;}
  a{color:#0b5dd7;word-break:break-word;text-decoration:underline;}
  td, th { mso-line-height-rule:exactly; }
  table.totals th, table.totals td { padding:6px 8px; }
</style>
</head>
<body>
  <h1>Job ${esc(jobId)} — Expenses</h1>
  <div class="meta">Generated: ${new Date().toLocaleString()}</div>

  ${totalsHtml}
  <h2> Expense Data </h2>
  <table>
    <thead>
      <tr>
        <th style="width:6%;">ID</th>
        <th style="width:12%;">Date</th>
        <th style="width:30%;">Head</th>
        <th style="width:12%;text-align:right">Amount</th>
        <th style="width:8%;">Currency</th>
        <th style="width:18%;">Note</th>
        <th style="width:10%;">Creator</th>
        <th style="width:6%;">Attachments</th>
      </tr>
    </thead>
    <tbody>
      ${expenses.map(e => {
        // attachments -> links
        let attLinks = '';
        try {
          const arr = e.attachments_json ? (typeof e.attachments_json === 'string' ? JSON.parse(e.attachments_json||'[]') : e.attachments_json || []) : [];
          attLinks = (Array.isArray(arr) ? arr : []).map(x => {
            const url = esc(x.absolute_url || x.relative_url || '');
            const name = esc(x.original_name || x.stored_name || 'file');
            return url ? `<a href="${url}">${name}</a>` : `${name}`;
          }).join('<br>');
        } catch (err) {
          attLinks = '';
        }

        // note: strip tags then preserve new lines
        const rawNote = String(e.note || '');
        const notePlain = esc(rawNote.replace(/<[^>]*>/g, ''));
        const noteWithBreaks = notePlain.replace(/\r\n|\r|\n/g, '<br>');

        return `<tr>
          <td>${esc(e.id||'')}</td>
          <td>${esc(e.expense_date||'')}</td>
          <td>${esc(e.expense_head||'')}</td>
          <td style="text-align:right">${esc(Number(e.amount||0).toFixed(2))}</td>
          <td>${esc(e.currency||'')}</td>
          <td>${noteWithBreaks}</td>
          <td>${esc(e.creator_name||e.creator_email||'')}</td>
          <td>${attLinks}</td>
        </tr>`;
      }).join('')}
    </tbody>
  </table>
</body>
</html>`;

  // Prepend UTF-8 BOM so Word reliably detects encoding (fixes garbled characters)
  const bomPrefixed = '\uFEFF' + html;

  // Create blob and download
  const blob = new Blob([bomPrefixed], { type: 'application/msword;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `job_${jobId}_expenses_${new Date().toISOString().split('T')[0]}.doc`;
  document.body.appendChild(a);
  a.click();
  // cleanup
  setTimeout(() => {
    URL.revokeObjectURL(url);
    a.remove();
  }, 250);

  ok('Word exported');
}
  /* ---------- PDF (links-only attachments) ---------- */
  async function exportExpensesAsPDF(expenses, jobId) {
  if (!Array.isArray(expenses)) expenses = [];

  // Minimal CSS for the PDF HTML (keeps styling similar to your other templates)
  const css = `
    <style>
      body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#334155;padding:12px}
      header{border-bottom:1px solid #d1d5db;margin-bottom:10px;padding-bottom:6px}
      h1{color:#0369a1;margin:0;font-size:18px}
      .meta{font-size:11px;color:#64748b;margin-top:6px}
      .section{margin-top:12px}
      .expense{border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:10px;background:#fff}
      .expense .meta{font-size:11px;color:#64748b;margin-bottom:6px}
      .note{white-space:pre-wrap;margin-top:6px}
      .attachments{font-size:11px;margin-top:6px}
      .attachments a{display:block;color:#0b5dd7;text-decoration:underline;word-break:break-all}
      table{width:100%;border-collapse:collapse;margin-top:12px}
      th,td{border:1px solid #ddd;padding:8px;vertical-align:top}
      .totals-table th, .totals-table td { border:1px solid #ddd; padding:6px; text-align:left; }
      h2.section-title { color:#0369a1; margin-top:16px; font-size:14px; }
      .summary-line{font-weight:600;margin-top:8px;color:#0369a1;}
    </style>
  `;

  // Build HTML body
  let bodyHtml = `<div><header><h1>Job ${esc(jobId)} — Expenses</h1><div class="meta">Generated: ${new Date().toLocaleString()}</div></header>`;

  // --- compute totals ---
  const personTotals = computePersonTotals(expenses);

  // compute overall total (sum of all amounts)
  let totalExpense = 0;
  for (const person of Object.keys(personTotals)) {
    const cmap = personTotals[person];
    for (const cur of Object.keys(cmap)) {
      totalExpense += Number(cmap[cur]) || 0;
    }
  }

  // --- total expense line ---
  bodyHtml += `<div class="summary-line" style="font-size:20px">Total Expense: <strong>${totalExpense.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</strong></div>`;

  // --- grand totals by person ---
  if (Object.keys(personTotals).length) {
    let totalsBlock = `<section class="section"><h2>Expense Summary</h2><table class="totals-table"><thead><tr><th>Person</th><th>Currency</th><th style="text-align:right">Total</th></tr></thead><tbody>`;
    for (const person of Object.keys(personTotals)) {
      const cmap = personTotals[person];
      for (const cur of Object.keys(cmap)) {
        totalsBlock += `<tr><td>${esc(person)}</td><td>${esc(cur)}</td><td style="text-align:right">${Number(cmap[cur]).toFixed(2)}</td></tr>`;
      }
    }
    totalsBlock += `</tbody></table></section>`;
    bodyHtml += totalsBlock;

    // Insert the requested H2 "Expense Data" after the totals and before the expense details
    bodyHtml += `<h2 class="section-title">Expense Data</h2>`;
  } else {
    // If no totals, still place the Expense Data heading before details
    bodyHtml += `<h2 class="section-title">Expense Data</h2>`;
  }

  // --- expense details ---
  if (!expenses.length) {
    bodyHtml += `<div style="color:#64748b;padding:20px;text-align:center">No expenses available</div>`;
  } else {
    for (const e of expenses) {
      let attsHtml = '';
      try {
        const a = e.attachments_json
          ? (typeof e.attachments_json === 'string'
              ? JSON.parse(e.attachments_json || '[]')
              : e.attachments_json || [])
          : [];
        if (Array.isArray(a) && a.length) {
          attsHtml = `<div class="attachments">` + a.map(x => {
            const url = esc(x.absolute_url || x.relative_url || x.url || '');
            const name = esc(x.original_name || x.stored_name || x.title || 'Attachment');
            const meta = (x.size ? ` (${Number(x.size).toLocaleString()} bytes)` : '') + (x.mime ? ` · ${esc(x.mime)}` : '');
            return url ? `<a href="${url}" target="_blank" rel="noopener noreferrer">${name}${meta}</a>` : `<div>${name}${meta}</div>`;
          }).join('') + `</div>`;
        }
      } catch (err) {
        attsHtml = '';
      }

      bodyHtml += `<div class="expense">
        <div class="meta"><strong>#${esc(e.id||'')}</strong> · ${esc(e.expense_date||'')} · 
          <strong>${esc(typeof e.amount!=='undefined'?Number(e.amount).toFixed(2):'')}</strong> ${esc(e.currency||'')}</div>
        <div><strong>${esc(e.expense_head||e.expense_head_title||'')}</strong></div>
        <div class="note">${esc((e.note||'').replace(/<[^>]*>/g,''))}</div>
        ${attsHtml}
        <div style="border-top:1px solid #e2e8f0;margin-top:8px;padding-top:8px;font-size:11px;color:#64748b">
          ${esc(e.creator_name || e.creator_email || '')} · ${esc(e.created_at || '')}
        </div>
      </div>`;
    }
  }

  bodyHtml += `</div>`;

  const htmlContent = `<!doctype html><html><head><meta charset="utf-8">${css}</head><body>${bodyHtml}</body></html>`;

  // call your existing generateEnhancedPDF routine
  try {
    await generateEnhancedPDF(htmlContent, jobId, 'expenses');
    ok('PDF generated');
  } catch (e) {
    console.error('PDF export failed', e);
    if (confirm('PDF generation failed. Open HTML in new tab for printing?')) {
      const w = window.open('', '_blank');
      w.document.write(htmlContent);
      w.document.close();
    }
    throw e;
  }
}

})();

(async function init(){
  await ensurePortalAuth();
  applyRoleVisibility();
  await loadEnumsAndClients();

  // If page was opened with ?filter=..., apply it to the status filter
  // (set both the UI select and internal fStatus so loadJobs() sends the right param)
  if (appliedFilter) {
    try {
      // set UI (only if option exists), and internal filter value
      // using exactly the status string passed in the URL (e.g. 'completed' or 'in_progress')
      if (filterStatus) {
        // if the option exists (loadEnumsAndClients populated the options), set it
        const opt = Array.from(filterStatus.options).find(o => String(o.value).toLowerCase() === String(appliedFilter).toLowerCase());
        if (opt) filterStatus.value = opt.value;
      }
      fStatus = appliedFilter;
    } catch (e) { /* ignore errors */ }
  }

  // render pill (if UI has it)
  renderAppliedFilterPill();

  if (!IS_READ_ONLY) {
    await loadExpenseHeads();
  }

  await loadJobs();
})();
})();
</script>
@endpush
