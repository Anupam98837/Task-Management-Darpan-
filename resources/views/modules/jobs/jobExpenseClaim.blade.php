{{-- resources/views/modules/pages/manageJobExpenseClaims.blade.php --}}
@php
  $jeUid = 'je_' . \Illuminate\Support\Str::random(8);

  // APIs
  $apiJobs     = url('/api/job-details'); // JobDetailsController@index (your job list)
  $apiClaim    = url('/api/job-expense-claims/claim'); // POST { job_id, expense_id, message }

  // Expenses API pattern (ExpenseController@listExpenses)
  $apiExpensesPattern = url('/api/job-details') . '/{job}/expenses'; // GET /api/job-details/{job}/expenses

  // ✅ UPDATED: Unified Claims API (backend routes to same controller method)
  $apiMyClaims         = url('/api/job-expense-claims/my');     // GET my claims (scope=my)
  $apiAdminClaims      = url('/api/job-expense-claims/admin');  // GET admin claims (scope=admin)
  $apiAdminUpdateClaim = url('/api/job-expense-claims/admin/{id}'); // PATCH admin claim update

  // Try to pass a role hint from server (optional). JS also infers from APIs if empty.
  $roleHint = (string) (request()->attributes->get('auth_role') ?? (auth()->user()->role ?? auth()->user()->user_role ?? ''));
@endphp


@push('styles')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
  <style>
.je-att-btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;width:40px;height:40px;border-radius:12px;border:1px solid var(--border-color);background:var(--surface);color:var(--text-color);cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,background-color .15s ease,border-color .15s ease,color .15s ease;box-shadow:var(--shadow-sm);}
.je-att-btn i{font-size:16px;line-height:1;}
.je-who{display:inline-flex;align-items:flex-start;gap:10px;}
.je-who i{margin-top:2px;opacity:.85;}
.je-who-text{display:flex;flex-direction:column;line-height:1.2;}
.je-who-name{font-weight:700;color:var(--text-color);font-size:13px;}
.je-who-email{font-size:12px;color:var(--muted-color);margin-top:2px;word-break:break-word;}
.je-att-btn:hover{transform:translateY(-1px);border-color:var(--primary-color);background:var(--light-color);color:var(--primary-color);box-shadow:var(--shadow-md,0 10px 18px rgba(0,0,0,.08));}
.je-att-btn:active{transform:translateY(0);box-shadow:var(--shadow-sm);}
.je-att-btn:focus{outline:none;}
.je-att-btn:focus-visible{box-shadow:0 0 0 3px var(--ring);border-color:var(--primary-color);}
.je-att-btn:disabled{opacity:.55;cursor:not-allowed;transform:none;box-shadow:none;}
.je-att-btn.is-sm{width:34px;height:34px;border-radius:10px;}
.je-att-btn.is-sm i{font-size:14px;}
#{{ $jeUid }}{--line-strong:var(--border-color);--line-soft:color-mix(in oklab,var(--border-color) 70%,transparent);--shadow-1:var(--shadow-sm);--shadow-2:var(--shadow-md);--page-hover:color-mix(in oklab,var(--primary-color) 10%,transparent);--ink:var(--text-color);}
.theme-dark #{{ $jeUid }},html.theme-dark #{{ $jeUid }}{--line-strong:var(--border-color);--line-soft:color-mix(in oklab,var(--border-color) 70%,transparent);--page-hover:color-mix(in oklab,var(--primary-color) 14%,transparent);--ink:var(--text-color);}
#{{ $jeUid }}.cm-wrap{max-width:1240px;margin:16px auto 44px;padding:24px;background:var(--bg-body);overflow:visible;font-family:var(--font-sans,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Inter,sans-serif);}
#{{ $jeUid }} .panel2{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04);padding:14px;}
#{{ $jeUid }} .mfa-toolbar .form-control,#{{ $jeUid }} .mfa-toolbar .form-select{height:44px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--text-color);box-shadow:none;transition:all .2s;}
#{{ $jeUid }} .mfa-toolbar .form-control:focus,#{{ $jeUid }} .mfa-toolbar .form-select:focus{outline:none;border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab,var(--primary-color) 18%,transparent);}
#{{ $jeUid }} .mfa-toolbar .btn{display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 18px;border-radius:12px;font-weight:600;border:none;transition:all .2s;}
#{{ $jeUid }} .mfa-toolbar .btn.btn-primary{background:linear-gradient(135deg,var(--secondary-color) 0%,var(--primary-color) 100%);color:#fff;box-shadow:0 2px 8px color-mix(in oklab,var(--primary-color) 28%,transparent);}
#{{ $jeUid }} .mfa-toolbar .btn.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px color-mix(in oklab,var(--primary-color) 36%,transparent);}
#{{ $jeUid }} .mfa-toolbar .btn.btn-light{background:var(--surface);color:var(--text-color);border:1px solid var(--line-strong);box-shadow:none;}
#{{ $jeUid }} .mfa-toolbar .btn.btn-light:hover{background:var(--primary-color);border-color:var(--primary-color);color:var(--surface);}
#{{ $jeUid }} .btn.btn-sm{padding:0 12px;border-radius:10px;font-size:13px;height:34px;align-items:center;display:inline-flex;gap:8px;}
#{{ $jeUid }} .table-wrap.card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden;position:relative;}
#{{ $jeUid }} .table-wrap .card-body{overflow:visible;}
#{{ $jeUid }} .table-responsive{overflow-x:auto;}
#{{ $jeUid }} .table{width:100%;margin-bottom:0;--bs-table-bg:transparent;border-collapse:collapse;color:var(--text-color);}
#{{ $jeUid }} .table thead{background:var(--light-color);}
#{{ $jeUid }} .table thead th{padding:14px 18px;text-align:left;font-size:12px;font-weight:600;color:var(--muted-color);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--line-strong)!important;white-space:nowrap;background:var(--light-color);}
#{{ $jeUid }} .table thead.sticky-top{z-index:3;}
#{{ $jeUid }} .table tbody tr{border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:var(--surface);transition:background .15s;}
#{{ $jeUid }} .table tbody tr:hover{background:var(--page-hover);}
#{{ $jeUid }} .table tbody td{padding:16px 18px;font-size:14px;color:var(--text-color);vertical-align:middle;border-bottom:1px solid color-mix(in oklab,var(--border-color) 45%,transparent)!important;}
#{{ $jeUid }} .card-body > .d-flex{background:var(--light-color);border-top:1px solid color-mix(in oklab,var(--border-color) 45%,transparent);}
#{{ $jeUid }} .pagination{gap:6px;}
#{{ $jeUid }} .page-link{background:var(--surface);color:var(--text-color);border:1px solid var(--line-strong);border-radius:8px!important;padding:6px 10px;font-size:13px;transition:all .2s;}
#{{ $jeUid }} .page-link:hover{background:var(--primary-color);border-color:var(--primary-color);color:var(--surface);}
#{{ $jeUid }} .page-item.active .page-link{background:var(--primary-color);border-color:var(--primary-color);color:#fff;}
#{{ $jeUid }} .page-item.disabled .page-link{opacity:.45;cursor:not-allowed;}
#{{ $jeUid }} .small{font-size:12.5px;}
#{{ $jeUid }} .badge-soft{background:color-mix(in oklab,var(--muted-color) 12%,transparent);color:var(--ink);border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);}
#{{ $jeUid }} .empty{color:var(--muted-color);}
#{{ $jeUid }} .placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px;}
#{{ $jeUid }} .je-status{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:color-mix(in oklab,var(--muted-color) 9%,transparent);font-weight:700;font-size:12px;white-space:nowrap;}
#{{ $jeUid }} .je-status i{opacity:.8;}
#{{ $jeUid }} .je-who{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:color-mix(in oklab,var(--primary-color) 10%,transparent);color:var(--text-color);font-weight:700;font-size:12px;}
#{{ $jeUid }} .je-who i{opacity:.85;}
#{{ $jeUid }} .je-more-btn{width:38px;height:34px;padding:0;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:var(--surface);color:var(--text-color);border:1px solid var(--line-strong);box-shadow:none;transition:all .2s;}
#{{ $jeUid }} .je-more-btn:hover{background:color-mix(in oklab,var(--primary-color) 12%,transparent);border-color:color-mix(in oklab,var(--primary-color) 35%,var(--border-color));}
#{{ $jeUid }} .dropdown-menu{border-radius:14px;border:1px solid color-mix(in oklab,var(--border-color) 60%,transparent);box-shadow:0 14px 40px rgba(0,0,0,.14);padding:8px;min-width:220px;}
#{{ $jeUid }} .dropdown-item{border-radius:12px;padding:10px 10px;display:flex;gap:10px;align-items:center;font-weight:700;}
#{{ $jeUid }} .dropdown-item i{width:18px;opacity:.85;}
#{{ $jeUid }} .dropdown-item:active{background:color-mix(in oklab,var(--primary-color) 18%,transparent);color:var(--text-color);}
#{{ $jeUid }} .je-tree{padding:12px;}
#{{ $jeUid }} .je-list{list-style:none;margin:0;padding:0;}
#{{ $jeUid }} .je-item{margin:0;padding:0;}
#{{ $jeUid }} .je-row{--level:0;display:flex;align-items:flex-start;gap:10px;padding:10px 12px;padding-left:calc(12px + (var(--level) * 18px));border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);border-radius:14px;background:var(--surface);box-shadow:var(--shadow-1);margin-bottom:8px;}
#{{ $jeUid }} .je-toggle{width:30px;height:30px;border-radius:10px;border:1px solid var(--line-strong);background:var(--surface);display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;cursor:pointer;}
#{{ $jeUid }} .je-toggle i{transition:transform .15s ease;}
#{{ $jeUid }} .je-item.is-collapsed > .je-children{display:none;}
#{{ $jeUid }} .je-item.is-collapsed > .je-row .je-toggle i{transform:rotate(-90deg);}
#{{ $jeUid }} .je-toggle[disabled]{opacity:.45;cursor:default;}
#{{ $jeUid }} .je-main{min-width:0;flex:1 1 auto;}
#{{ $jeUid }} .je-title{font-weight:600;}
#{{ $jeUid }} .je-meta{font-size:12px;color:var(--muted-color);margin-top:2px;overflow-wrap:anywhere;word-break:break-word;}
#{{ $jeUid }} .modal-xl{max-width:1040px;}
.theme-dark #{{ $jeUid }} .panel2,.theme-dark #{{ $jeUid }} .table-wrap.card{background:var(--surface);border-color:var(--border-color);}
.theme-dark #{{ $jeUid }} .table thead{background:color-mix(in oklab,var(--surface) 90%,black);}
.theme-dark #{{ $jeUid }} .table thead th{background:color-mix(in oklab,var(--surface) 90%,black);color:var(--muted-color);border-bottom-color:var(--border-color)!important;}
#{{ $jeUid }},#{{ $jeUid }} .table-responsive,#{{ $jeUid }} .table-wrap,#{{ $jeUid }} .card,#{{ $jeUid }} .panel2{overflow:visible!important;transform:none!important;}
@media (max-width:768px){#{{ $jeUid }}.cm-wrap{padding:16px;}#{{ $jeUid }} .mfa-toolbar .position-relative{min-width:100%!important;}}
#{{ $jeUid }}_jobModal .modal-content{border-radius:18px;border:1px solid var(--border-color);background:var(--surface);box-shadow:0 20px 40px rgba(0,0,0,.15);}
#{{ $jeUid }}_jobModal .modal-header{padding:18px 22px;border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:var(--surface);}
#{{ $jeUid }}_jobModal .modal-title{font-weight:800;font-size:18px;color:var(--text-color);}
#{{ $jeUid }}_jobModal .modal-body{padding:0;}
#{{ $jeUid }}_jobModal .modal-body .border-bottom{background:var(--surface);border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent)!important;}
#{{ $jeUid }}_jobModal .form-control,#{{ $jeUid }}_jobModal .form-select{height:44px;border-radius:12px;border:1px solid var(--border-color);background:var(--surface);color:var(--text-color);box-shadow:none;transition:all .2s;}
#{{ $jeUid }}_jobModal .form-control:focus,#{{ $jeUid }}_jobModal .form-select:focus{outline:none;border-color:var(--primary-color);box-shadow:0 0 0 3px color-mix(in oklab,var(--primary-color) 18%,transparent);}
#{{ $jeUid }}_jobModal .position-relative i.fa-search{opacity:.55;pointer-events:none;}
#{{ $jeUid }}_jobModal .je-tree{padding:14px;background:var(--bg-body);}
#{{ $jeUid }}_jobModal .je-list{list-style:none;margin:0;padding:0;}
#{{ $jeUid }}_jobModal .je-row{--level:0;display:flex;align-items:flex-start;gap:12px;padding:12px 12px;padding-left:calc(12px + (var(--level) * 18px));border-radius:14px;border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:var(--surface);box-shadow:0 1px 3px rgba(0,0,0,.04);transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;margin-bottom:10px;}
#{{ $jeUid }}_jobModal .je-row:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(15,23,42,.10);border-color:color-mix(in oklab,var(--primary-color) 35%,var(--border-color));}
#{{ $jeUid }}_jobModal .je-toggle{width:34px;height:34px;border-radius:12px;border:1px solid color-mix(in oklab,var(--border-color) 65%,transparent);background:var(--surface);display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;cursor:pointer;transition:all .15s ease;}
#{{ $jeUid }}_jobModal .je-toggle:hover{border-color:color-mix(in oklab,var(--primary-color) 35%,var(--border-color));box-shadow:0 6px 14px rgba(0,0,0,.06);}
#{{ $jeUid }}_jobModal .je-toggle i{transition:transform .15s ease;font-size:13px;opacity:.75;}
#{{ $jeUid }}_jobModal .je-toggle[disabled]{opacity:.35;cursor:default;box-shadow:none;}
#{{ $jeUid }}_jobModal .je-item.is-collapsed > .je-children{display:none;}
#{{ $jeUid }}_jobModal .je-item.is-collapsed > .je-row .je-toggle i{transform:rotate(-90deg);}
#{{ $jeUid }}_jobModal .je-main{min-width:0;flex:1 1 auto;}
#{{ $jeUid }}_jobModal .je-title{font-weight:700;color:var(--text-color);display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
#{{ $jeUid }}_jobModal .je-meta{margin-top:4px;font-size:12.5px;color:var(--muted-color);overflow-wrap:anywhere;}
#{{ $jeUid }}_jobModal .je-actions{margin-left:auto;display:flex;gap:8px;align-items:center;flex:0 0 auto;}
#{{ $jeUid }}_jobModal .je-actions .btn{height:34px;border-radius:10px;font-weight:700;padding:0 12px;}
#{{ $jeUid }}_jobModal .je-actions .btn.btn-primary{background:linear-gradient(135deg,var(--secondary-color) 0%,var(--primary-color) 100%);border:none;color:#fff;box-shadow:0 2px 8px color-mix(in oklab,var(--primary-color) 28%,transparent);}
#{{ $jeUid }}_jobModal .je-actions .btn.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px color-mix(in oklab,var(--primary-color) 36%,transparent);}
#{{ $jeUid }}_jobModal .badge.badge-soft{background:color-mix(in oklab,var(--muted-color) 12%,transparent);color:var(--text-color);border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);}
#{{ $jeUid }}_jobModal .pagination{gap:6px;}
#{{ $jeUid }}_jobModal .page-link{background:var(--surface);color:var(--text-color);border:1px solid var(--border-color);border-radius:8px!important;padding:6px 10px;font-size:13px;transition:all .2s;}
#{{ $jeUid }}_jobModal .page-link:hover{background:var(--primary-color);border-color:var(--primary-color);color:var(--surface);}
#{{ $jeUid }}_jobModal .page-item.active .page-link{background:var(--primary-color);border-color:var(--primary-color);color:#fff;}
#{{ $jeUid }}_jobModal .page-item.disabled .page-link{opacity:.45;cursor:not-allowed;}
html.theme-dark #{{ $jeUid }}_jobModal .je-tree{background:var(--bg-body);}
html.theme-dark #{{ $jeUid }}_jobModal .je-row{border-color:color-mix(in oklab,var(--border-color) 70%,transparent);box-shadow:0 1px 3px rgba(0,0,0,.18);}
html.theme-dark #{{ $jeUid }}_jobModal .je-row:hover{box-shadow:0 10px 26px rgba(0,0,0,.32);}
#{{ $jeUid }}_payHistoryModal .modal-content,#{{ $jeUid }}_payEditModal .modal-content{border-radius:18px;border:1px solid color-mix(in oklab,var(--border-color) 70%,transparent);background:var(--surface);box-shadow:0 18px 50px rgba(0,0,0,.18);}
#{{ $jeUid }}_payHistoryModal .modal-header,#{{ $jeUid }}_payEditModal .modal-header{border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);padding:16px 20px;}
#{{ $jeUid }}_payHistoryModal .modal-title,#{{ $jeUid }}_payEditModal .modal-title{font-weight:900;font-size:16.5px;color:var(--text-color);}
#{{ $jeUid }}_payHistoryModal .modal-body,#{{ $jeUid }}_payEditModal .modal-body{padding:16px 20px;}
#{{ $jeUid }} .je-kv{display:flex;gap:10px;flex-wrap:wrap;align-items:center;color:var(--muted-color);font-size:12.5px;}
#{{ $jeUid }} .je-kv .pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:color-mix(in oklab,var(--muted-color) 10%,transparent);color:var(--text-color);font-weight:700;}
#{{ $jeUid }} .je-mini-table{border:1px solid color-mix(in oklab,var(--border-color) 65%,transparent);border-radius:14px;overflow:hidden;background:var(--surface);}
#{{ $jeUid }} .je-mini-table table{width:100%;margin:0;border-collapse:collapse;}
#{{ $jeUid }} .je-mini-table thead th{background:var(--light-color);color:var(--muted-color);font-size:12px;letter-spacing:.4px;text-transform:uppercase;padding:12px 14px;border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);white-space:nowrap;}
#{{ $jeUid }} .je-mini-table tbody td{padding:12px 14px;border-bottom:1px solid color-mix(in oklab,var(--border-color) 45%,transparent);vertical-align:top;color:var(--text-color);font-size:13.5px;}
#{{ $jeUid }} .je-mini-table tbody tr:last-child td{border-bottom:none;}
#{{ $jeUid }} .je-att-btn{width:34px;height:34px;border-radius:12px;border:1px solid color-mix(in oklab,var(--border-color) 65%,transparent);background:var(--surface);display:inline-flex;align-items:center;justify-content:center;transition:all .15s ease;}
#{{ $jeUid }} .je-att-btn:hover{border-color:color-mix(in oklab,var(--primary-color) 35%,var(--border-color));background:color-mix(in oklab,var(--primary-color) 10%,transparent);}
#{{ $jeUid }} .je-att-list{margin-top:10px;padding:12px;border-radius:14px;background:color-mix(in oklab,var(--muted-color) 8%,transparent);border:1px solid color-mix(in oklab,var(--border-color) 60%,transparent);display:none;}
#{{ $jeUid }} .je-att-list a{word-break:break-all;}
#{{ $jeUid }}_attachmentModal .modal-content{border-radius:18px;border:1px solid var(--border-color);background:var(--surface);box-shadow:0 20px 40px rgba(0,0,0,.18);}
#{{ $jeUid }}_attachmentModal .modal-header{padding:18px 22px;border-bottom:1px solid color-mix(in oklab,var(--border-color) 55%,transparent);background:var(--surface);}
#{{ $jeUid }}_attachmentModal .modal-title{font-weight:800;font-size:18px;color:var(--text-color);}
#{{ $jeUid }}_attachmentModal .js-att-preview-container{background:var(--bg-body);min-height:400px;display:flex;align-items:center;justify-content:center;}
#{{ $jeUid }} .table-responsive{overflow-x:auto!important;-webkit-overflow-scrolling:touch;width:100%;}
#{{ $jeUid }} .je-exp-table{min-width:1100px;}
#{{ $jeUid }} .je-exp-table th,#{{ $jeUid }} .je-exp-table td{white-space:nowrap;}
@media (max-width:768px){#{{ $jeUid }} .je-exp-table{min-width:980px;}#{{ $jeUid }} .je-exp-table td:first-child{white-space:normal;min-width:200px;}}


/* ✅ override ONLY note cell */
#{{ $jeUid }} .je-exp-table td.je-note-cell{
  white-space: normal !important;
  overflow-wrap: anywhere;
  word-break: break-word;
  max-width: 460px;          /* matches your NOTE column width */
  cursor: pointer;
}

/* ✅ show only small portion in table (multi-line clamp) */
#{{ $jeUid }} .je-note-preview,
#{{ $jeUid }} .je-note-clamp,
#{{ $jeUid }} .je-note-wrap{
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;     /* change to 3 if you want */
  overflow: hidden;
  white-space: normal !important;
  overflow-wrap: anywhere;
  word-break: break-word;
}

/* optional: nice hover hint */
#{{ $jeUid }} .je-exp-table td.je-note-cell:hover{
  text-decoration: underline;
}


  </style>
@endpush
@section('content')
  <div id="{{ $jeUid }}"
       class="cm-wrap"
       data-api-jobs="{{ $apiJobs }}"
       data-api-claim="{{ $apiClaim }}"
       data-api-expenses-pattern="{{ $apiExpensesPattern }}"
       data-api-my-claims="{{ $apiMyClaims }}"
       data-api-admin-claims="{{ $apiAdminClaims }}"
       data-api-admin-update="{{ $apiAdminUpdateClaim }}"
       data-auth-role="{{ strtolower(trim($roleHint)) }}">

    {{-- ===== Header toolbar ===== --}}
    <div class="row align-items-center g-2 mb-3 mfa-toolbar panel2">
      <div class="col-12 d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge badge-soft"><i class="fa-solid fa-money-bill-wave me-1"></i> Manage</span>
          <label class="text-muted small mb-0">Job Expenses • Claim Requests</label>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
          <div class="small text-muted">
            Selected Job:
            <span class="fw-semibold js-job-label">None</span>
          </div>
          <button class="btn btn-primary js-open-jobs">
            <i class="fa fa-sitemap me-1"></i> Choose Job
          </button>
        </div>
      </div>
    </div>

    {{-- ===== Filters + actions ===== --}}
    <div class="row align-items-center g-2 mb-3 mfa-toolbar panel2">
      <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Per page</label>
          <select class="form-select js-per" style="width:110px;">
            <option>10</option>
            <option selected>20</option>
            <option>30</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        <div class="position-relative" style="min-width: min(420px, 100%); flex: 1 1 420px;">
          <input type="text" class="form-control ps-5 js-q" placeholder="Search head / note / amount…">
          <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        </div>
      </div>

      <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
        <button class="btn btn-light js-refresh"><i class="fa fa-rotate me-1"></i>Refresh</button>
        <button class="btn btn-primary js-reset"><i class="fa fa-rotate-left me-1"></i>Reset</button>
      </div>
    </div>

    {{-- ===== Expenses table ===== --}}
    <div class="card table-wrap">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 je-exp-table">
            <thead class="sticky-top">
              <tr>
                <th>EXPENSE</th>
                <th style="width:180px;">CREATOR NAME</th>
                <th style="width:220px;">CREATOR EMAIL</th>
                <th style="width:460px;">NOTE</th>
                <th style="width:140px; display:none">DATE</th>
                <th style="width:140px;">AMOUNT</th>
              
                <th class="text-center" style="width:90px;">PROOF</th>

                <th style="width:190px;">PAYMENT STATUS</th>
                <th style="width:160px;">CREATED</th>
                <th class="text-end" style="width:220px;">ACTIONS</th>
              </tr>
            </thead>
            <tbody class="js-rows">
              <tr class="js-loader" style="display:none;">
                <td colspan="10" class="p-0">
                  <div class="p-4">
                     <div class="placeholder-wave">
                      <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr class="js-empty" style="display:none;">
                <td colspan="10" class="p-4 text-center empty">
                  <i class="fa fa-receipt mb-2" style="font-size:32px; opacity:.6;"></i>
                  <div>No expenses found for this job.</div>
                  <div class="small text-muted mt-1">Choose a job first, then you can manage claim/payment status per expense.</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
          <div class="text-muted small js-meta">—</div>
          <nav style="position:relative; z-index:1;">
            <ul class="pagination mb-0 js-pager"></ul>
          </nav>
        </div>
      </div>
    </div>

  </div>

  {{-- ===== Job chooser modal ===== --}}
  <div class="modal fade" id="{{ $jeUid }}_jobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content" style="border-radius:18px;">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa fa-sitemap me-2"></i> Choose Job
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-0">
          <div class="p-3 border-bottom" style="border-color:var(--line-strong)!important;">
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <div class="position-relative" style="min-width:min(520px,100%); flex: 1 1 520px;">
                <input type="text" class="form-control ps-5 js-job-q" placeholder="Search jobs…">
                <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
              </div>

              <div class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Per page</label>
                <select class="form-select js-job-per" style="width:110px;">
                  <option>20</option><option selected>50</option><option>100</option><option>200</option>
                </select>
              </div>

              <button class="btn btn-light js-job-refresh">
                <i class="fa fa-rotate me-1"></i> Refresh
              </button>
            </div>

            <div class="small text-muted mt-2">
              Tip: Your API already returns role-wise jobs. We render a tree if <code>parent_id</code> is present.
            </div>
          </div>

          <div class="je-tree">
            <div class="js-job-loader" style="display:none;">
              <div class="p-3">
                <div class="placeholder-wave">
                  <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                </div>
              </div>
            </div>

            <div class="js-job-empty empty p-4 text-center" style="display:none;">
              <i class="fa fa-sitemap mb-2" style="font-size:32px; opacity:.6;"></i>
              <div>No jobs found.</div>
            </div>

            <div class="js-job-tree"></div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 gap-2">
              <div class="text-muted small js-job-meta">—</div>
              <nav style="position:relative; z-index:1;">
                <ul class="pagination mb-0 js-job-pager"></ul>
              </nav>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Payment History Modal (Bootstrap, not Swal) ===== --}}
  <div class="modal fade" id="{{ $jeUid }}_payHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="fa fa-clock-rotate-left me-2"></i> Payment History
            </h5>
            <div class="je-kv mt-2">
              <span class="pill js-ph-job">Job: —</span>
              <span class="pill js-ph-exp">Expense: —</span>
              <span class="pill js-ph-amt">Amount: —</span>
              <span class="pill js-ph-status">Status: —</span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="js-ph-empty text-center empty p-4" style="display:none;">
            <i class="fa fa-file-circle-xmark mb-2" style="font-size:30px;opacity:.6;"></i>
            <div>No Payment record found for this expense.</div>
            <div class="small text-muted mt-1">Once admin updates payment, it will appear here.</div>
          </div>

          <div class="je-mini-table js-ph-table" style="display:none;">
            <table>
              <thead>
                <tr>
                  <th style="width:160px;">PAID AT</th>
                  <th style="width:120px;">PAID BY</th>
                  <th style="width:140px;">TOTAL</th>
                  <th style="width:150px;">REMAINING</th>
                  <th style="width:160px;">STATE</th>
                  <th class="text-end" style="width:90px;">ATT</th>
                </tr>
              </thead>
              <tbody class="js-ph-rows"></tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Admin Payment Edit Modal (Bootstrap, not Swal) ===== --}}
  <div class="modal fade" id="{{ $jeUid }}_payEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">
              <i class="fa fa-pen-to-square me-2"></i> Update Payment Details
            </h5>
            <div class="je-kv mt-2">
              <span class="pill js-pe-job">Job: —</span>
              <span class="pill js-pe-exp">Expense: —</span>
              <span class="pill js-pe-claim">Claim: —</span>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

       <div class="modal-body">
  <div class="alert alert-warning py-2 px-3 small js-pe-no-claim" style="display:none;">
    No claim request exists for this expense yet. Admin can update payment only after a claim is created.
  </div>

  <form class="js-pe-form">
    <input type="hidden" class="js-pe-claim-id" value="">
    <input type="hidden" class="js-pe-exp-id" value="">
    <input type="hidden" class="js-pe-job-id" value="">

    <div class="row g-2">
      <!-- Total (auto) -->
      <div class="col-12 col-md-4">
        <label class="text-muted small mb-1">Total Amount</label>
        <input type="text" class="form-control js-pe-total" readonly>
        <div class="small text-muted mt-1">Auto from expense amount</div>
      </div>

      <!-- Paid now (input) -->
      <div class="col-12 col-md-4">
        <label class="text-muted small mb-1">Paid Now</label>
        <input type="number" step="0.01" min="0" class="form-control js-pe-paid-now" placeholder="e.g. 500.00">
        <div class="small text-muted mt-1">Enter paid amount only</div>
      </div>

      <!-- Remaining (auto) -->
      <div class="col-12 col-md-4">
        <label class="text-muted small mb-1">Remaining</label>
        <input type="text" class="form-control js-pe-remaining" readonly>
        <div class="small text-muted mt-1">Auto calculated</div>
      </div>

      <div class="col-12 col-md-6">
        <label class="text-muted small mb-1">Paid at</label>
        <input type="datetime-local" class="form-control js-pe-paid-at" />
      </div>

      <div class="col-12 col-md-6">
        <label class="text-muted small mb-1">Computed Status</label>
        <input type="text" class="form-control js-pe-status-view" readonly value="pending">
        <div class="small text-muted mt-1">Auto from remaining</div>
      </div>

      <!-- Attachment mode -->
      <div class="col-12">
        <label class="text-muted small mb-1">Attachment Type</label>
        <div class="d-flex gap-2 flex-wrap">
          <label class="btn btn-light btn-sm">
            <input type="radio" name="pe_attach_mode" class="form-check-input me-2 js-pe-mode" value="upload" checked>
            Upload (drag/drop)
          </label>
          <label class="btn btn-light btn-sm">
            <input type="radio" name="pe_attach_mode" class="form-check-input me-2 js-pe-mode" value="link">
            Link
          </label>
        </div>
      </div>

      <!-- Upload mode -->
      <div class="col-12 js-pe-upload-wrap">
        <div class="p-3 rounded-3 border js-pe-dropzone"
             style="border-style:dashed!important; cursor:pointer; background:var(--surface);">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small">
              <div class="fw-semibold">Drag & drop files here</div>
              <div class="text-muted">or click to choose</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm js-pe-choose">
              <i class="fa fa-upload me-1"></i> Choose file
            </button>
          </div>
          <input type="file" class="d-none js-pe-files" multiple>
        </div>

        <div class="mt-2 small text-muted js-pe-filelist">No files selected</div>
        <div class="small text-muted mt-1">
          Note: If your backend doesn’t upload files yet, we store filenames only. (Links always work.)
        </div>
      </div>

      <!-- Link mode -->
      <div class="col-12 js-pe-link-wrap" style="display:none;">
        <label class="text-muted small mb-1">Attachment Link</label>
        <input type="url" class="form-control js-pe-link" placeholder="https://...">
        <div class="small text-muted mt-1">Paste a public URL</div>
      </div>

      <div class="col-12">
        <label class="text-muted small mb-1">Note (optional)</label>
        <textarea class="form-control js-pe-note" rows="2" placeholder="Payment note…"></textarea>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
      <button type="submit" class="btn btn-primary js-pe-save">
        <i class="fa fa-check me-1"></i> Save
      </button>
    </div>
  </form>
</div>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
    <div class="toast js-ok-toast text-bg-success border-0">
      <div class="d-flex">
        <div class="toast-body js-ok-msg">Done</div>
        <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div class="toast js-err-toast text-bg-danger border-0 mt-2">
      <div class="d-flex">
        <div class="toast-body js-err-msg">Something went wrong</div>
        <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    (function () {
      const ROOT = document.getElementById(@json($jeUid));
      if (!ROOT) return;

      if (ROOT.dataset.jeInit === '1') return;
      ROOT.dataset.jeInit = '1';

      const TOKEN =
        localStorage.getItem('token') ||
        sessionStorage.getItem('token') ||
        '';

      // ✅ No alert. If no token, redirect to /
      if (!TOKEN) { location.href = '/'; return; }

      const API_JOBS = ROOT.dataset.apiJobs;
      const API_CLAIM = ROOT.dataset.apiClaim;
      const API_EXP_PATTERN = ROOT.dataset.apiExpensesPattern;

      const API_MY_CLAIMS = ROOT.dataset.apiMyClaims;
      const API_ADMIN_CLAIMS = ROOT.dataset.apiAdminClaims;
      const API_ADMIN_UPDATE_PATTERN = ROOT.dataset.apiAdminUpdate; // .../admin/{id}

      const qs  = (sel) => ROOT.querySelector(sel);

      const esc = (s) => {
        const m = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
        return (s==null?'':String(s)).replace(/[&<>\"'`]/g, ch => m[ch]);
      };

      const fmtDate = (iso) => {
        if (!iso) return '-';
        const d = new Date(iso);
        if (isNaN(d)) return esc(iso);
        return d.toLocaleString(undefined, {year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
      };

      function redirectToLogin() {
        try {
          localStorage.removeItem('token');
          sessionStorage.removeItem('token');
        } catch(e){}
        location.href = '/';
      }

      const okToastEl  = document.querySelector('.js-ok-toast');
      const errToastEl = document.querySelector('.js-err-toast');
      const okMsgEl    = document.querySelector('.js-ok-msg');
      const errMsgEl   = document.querySelector('.js-err-msg');

      const okToast  = okToastEl  ? new bootstrap.Toast(okToastEl)  : null;
      const errToast = errToastEl ? new bootstrap.Toast(errToastEl) : null;

      const ok = (m) => {
        if (okMsgEl) okMsgEl.textContent = m || 'Done';
        if (okToast) okToast.show();
        else console.log('[OK]', m);
      };

      const err = (m) => {
        if (errMsgEl) errMsgEl.textContent = m || 'Something went wrong';
        if (errToast) errToast.show();
        else console.error('[ERR]', m);
      };

      async function fetchJSON(url, opts = {}) {
        const res = await fetch(url, {
          cache: 'no-store',
          ...opts,
          headers: {
            'Authorization': 'Bearer ' + TOKEN,
            'Accept': 'application/json',
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache',
            ...(opts.headers || {})
          }
        });

        // ✅ Redirect on auth fail (no alert)
        if ([401, 419].includes(res.status)) {
          redirectToLogin();
          throw new Error('Unauthenticated');
        }

        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.error || j?.message || 'Request failed');
        return j;
      }

      // =========================
      // Role detection
      // =========================
      let ROLE =
        (ROOT.dataset.authRole || '').toLowerCase().trim()
        || (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase().trim()
        || '';

      async function inferRoleIfNeeded() {
        if (ROLE === 'admin' || ROLE === 'assignee') return ROLE;

        // Try admin endpoint; if ok => admin else assignee
        try {
          await fetchJSON(API_ADMIN_CLAIMS + '?per_page=1&page=1&_ts=' + Date.now());
          ROLE = 'admin';
        } catch (e) {
          ROLE = 'assignee';
        }
        return ROLE;
      }

      // =========================
      // State
      // =========================
      let selectedJob = null; // {id,title,...}
      let expensesAll = [];
      let expPage = 1;
      let expPages = 1;

      // claim cache per job
      const claimsCache = new Map(); // jobId -> {ts, list, mapByExpenseId}
      let claimsMapForJob = new Map(); // current job claims by expense_id

      // =========================
      // Jobs modal
      // =========================
      const modalEl = document.getElementById(@json($jeUid.'_jobModal'));
      const jobModal = modalEl ? new bootstrap.Modal(modalEl) : null;

      const btnOpenJobs  = qs('.js-open-jobs');
      const jobTreeWrap  = modalEl?.querySelector('.js-job-tree');
      const jobLoader    = modalEl?.querySelector('.js-job-loader');
      const jobEmpty     = modalEl?.querySelector('.js-job-empty');
      const jobMeta      = modalEl?.querySelector('.js-job-meta');
      const jobPager     = modalEl?.querySelector('.js-job-pager');
      const jobQ         = modalEl?.querySelector('.js-job-q');
      const jobPerSel    = modalEl?.querySelector('.js-job-per');
      const jobRefresh   = modalEl?.querySelector('.js-job-refresh');

      const jobLabel     = qs('.js-job-label');

      let jobsRaw = [];
      let jobPage = 1;
      let jobPages = 1;
      let jobLoadPromise = null;

      function setJobLoading(v){ if (jobLoader) jobLoader.style.display = v ? '' : 'none'; }

      function buildPager(pagerEl, cur, pages, onPage){
        const li = (dis, act, label, t) =>
          `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
            <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
          </li>`;

        let html = '';
        html += li(cur<=1, false, 'Previous', cur-1);

        const w = 3;
        const s = Math.max(1, cur - w);
        const e = Math.min(pages, cur + w);

        if (s > 1) {
          html += li(false, false, 1, 1);
          if (s > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }

        for (let i = s; i <= e; i++) html += li(false, i===cur, i, i);

        if (e < pages) {
          if (e < pages-1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
          html += li(false, false, pages, pages);
        }

        html += li(cur>=pages, false, 'Next', cur+1);

        pagerEl.innerHTML = html;

        pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a => {
          a.addEventListener('click', () => {
            const t = Number(a.dataset.page);
            if (!t || t === cur) return;
            onPage(t);
          });
        });
      }

      function hasParentId(list){
        return (list || []).some(j => j && (j.parent_id !== null && j.parent_id !== undefined));
      }
      function notePlainFromHtml(noteHtml){
  const s = (noteHtml || '').toString();
  return s
    .replace(/<\s*br\s*\/?>/gi, '\n')
    .replace(/<\/p>/gi, '\n')
    .replace(/<[^>]*>/g, '')
    .replace(/\r\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
}

    // =========================
// Proof (Expense Attachment) helpers
// =========================
function getExpenseAttachments(r){
  // backend gives attachments_json as stringified JSON array
  const raw = r?.attachments_json ?? r?.attachments ?? null;
  const parsed = parseMaybeJson(raw);
  return Array.isArray(parsed) ? parsed : [];
}

function getFirstAttachmentUrl(r){
  const atts = getExpenseAttachments(r);
  if (!atts.length) return null;

  const a = atts[0];
  let url = '';

  if (typeof a === 'string') url = a;
  else url = a?.absolute_url || a?.url || a?.relative_url || '';

  if (!url) return null;

  // make relative url absolute
  try { return new URL(url, location.origin).toString(); }
  catch(e){ return url; }
}

function proofCell(r){
  const url = getFirstAttachmentUrl(r);
  const expId = (r?.id != null ? String(r.id) : '');

  if (!url) return `<span class="text-muted">—</span>`;

  // ✅ icon button that opens new tab
  return `
    <button type="button"
            class="je-att-btn is-sm js-proof"
            data-exp="${esc(expId)}"
            data-url="${esc(url)}"
            title="Open proof">
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
    </button>
  `;
}

      function buildTree(items, idKey='id', parentKey='parent_id'){
        const map = {};
        items.forEach(n => {
          const o = {...n, children: []};
          map[o[idKey]] = o;
        });

        const roots = [];
        Object.values(map).forEach(n => {
          const pid = n[parentKey];
          if (pid !== null && pid !== undefined && map[pid]) map[pid].children.push(n);
          else roots.push(n);
        });
        return roots;
      }

      function filterJobs(list, term){
        if (!term) return list;
        const t = term.toLowerCase();
        return (list || []).filter(j => {
          const hay = [
            j.title, j.name, j.status, j.client_name, j.slug
          ].filter(Boolean).join(' ').toLowerCase();
          return hay.includes(t);
        });
      }

      function renderJobNode(n, level){
        const hasKids = Array.isArray(n.children) && n.children.length > 0;

        const li = document.createElement('li');
        li.className = 'je-item';

        const row = document.createElement('div');
        row.className = 'je-row';
        row.style.setProperty('--level', level);

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'je-toggle';
        toggle.innerHTML = `<i class="fa fa-chevron-down"></i>`;
        if (!hasKids) toggle.disabled = true;

        const main = document.createElement('div');
        main.className = 'je-main';

        const title = n.title ?? n.name ?? ('Job #' + n.id);
        const status = n.status ? String(n.status) : '';
        const badge = status
          ? `<span class="badge badge-soft ms-2">${esc(status)}</span>`
          : '';

        main.innerHTML = `
          <div class="je-title">${esc(title)} ${badge}</div>
          <div class="je-meta">
            ${n.client_name ? `Client: ${esc(n.client_name)} • ` : ''}ID: #${esc(n.id)}
          </div>
        `;

        const actions = document.createElement('div');
        actions.className = 'je-actions';
        actions.innerHTML = `
          <button class="btn btn-primary btn-sm" data-pick="${esc(n.id)}">
            <i class="fa fa-check me-1"></i> Select
          </button>
        `;

        row.appendChild(toggle);
        row.appendChild(main);
        row.appendChild(actions);
        li.appendChild(row);

        const kids = document.createElement('ul');
        kids.className = 'je-list je-children';
        if (hasKids) n.children.forEach(ch => kids.appendChild(renderJobNode(ch, level+1)));

        toggle.addEventListener('click', () => {
          if (!hasKids) return;
          li.classList.toggle('is-collapsed');
        });

        li.appendChild(kids);
        return li;
      }

      function renderJobs() {
        if (!jobTreeWrap) return;

        const term = (jobQ?.value || '').trim();
        const per = Math.max(20, Number(jobPerSel?.value || 50));

        const filteredFlat = filterJobs(jobsRaw, term);
        const total = filteredFlat.length;

        const pages = Math.max(1, Math.ceil(total / per));
        jobPages = pages;
        if (jobPage > pages) jobPage = pages;

        const start = (jobPage - 1) * per;
        const pageSlice = filteredFlat.slice(start, start + per);

        const useTree = hasParentId(pageSlice);
        const tree = useTree ? buildTree(pageSlice) : pageSlice.map(x => ({...x, children: []}));

        jobTreeWrap.innerHTML = '';
        if (!tree.length) {
          if (jobEmpty) jobEmpty.style.display = '';
          if (jobMeta) jobMeta.textContent = '0 result(s)';
          if (jobPager) jobPager.innerHTML = '';
          return;
        }

        if (jobEmpty) jobEmpty.style.display = 'none';

        const rootUl = document.createElement('ul');
        rootUl.className = 'je-list';
        tree.forEach(n => rootUl.appendChild(renderJobNode(n, 0)));
        jobTreeWrap.appendChild(rootUl);

        if (jobMeta) jobMeta.textContent = `Showing page ${jobPage} of ${pages} — ${total} result(s)`;
        if (jobPager) buildPager(jobPager, jobPage, pages, (t) => {
          jobPage = Math.max(1, t);
          renderJobs();
        });
      }

      async function loadJobs() {
        if (jobLoadPromise) return jobLoadPromise;
        jobLoadPromise = (async () => {
          setJobLoading(true);
          try {
            const per = Math.max(20, Number(jobPerSel?.value || 50));
            const usp = new URLSearchParams();
            usp.set('per_page', per);
            usp.set('page', 1);
            usp.set('_ts', Date.now());

            const j = await fetchJSON(API_JOBS + '?' + usp.toString());

            const items =
              Array.isArray(j?.data) ? j.data :
              Array.isArray(j?.data?.data) ? j.data.data :
              Array.isArray(j) ? j :
              [];

            jobsRaw = items.map(x => {
              const rawId = x.id ?? x.job_id ?? x.uuid ?? x.job_uuid ?? x.ID;
              const rawPid = x.parent_id ?? null;
              return {
                id: rawId == null ? null : String(rawId),
                title: x.title ?? x.name ?? x.job_title,
                status: x.status ?? '',
                parent_id: rawPid == null ? null : String(rawPid),
                client_name: x.client_name ?? x.client?.name ?? '',
              };
            }).filter(x => x.id);

            jobPage = 1;
            renderJobs();

          } catch(e) {
            console.error(e);
            jobsRaw = [];
            if (jobTreeWrap) jobTreeWrap.innerHTML = '';
            if (jobEmpty) jobEmpty.style.display = '';
            if (jobMeta) jobMeta.textContent = 'Failed to load';
            err(e.message || 'Failed to load jobs');
          } finally {
            setJobLoading(false);
            jobLoadPromise = null;
          }
        })();
        return jobLoadPromise;
      }

      // Pick job from modal
      modalEl?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-pick]');
        if (!btn) return;

        const id = String(btn.getAttribute('data-pick'));
        const job = jobsRaw.find(x => String(x.id) === id);
        if (!job) return;

        selectedJob = { id: id, title: job.title || ('Job #' + id) };
if (jobLabel) jobLabel.textContent = selectedJob.title || '—';

        jobModal?.hide();

        // reset claim cache for this job view
        claimsMapForJob = new Map();
        expPage = 1;
        loadExpenses();
      });

      // Modal controls
      btnOpenJobs?.addEventListener('click', async () => {
        jobModal?.show();
        await loadJobs();
      });

      jobRefresh?.addEventListener('click', () => loadJobs());

      let jobQTimer;
      jobQ?.addEventListener('input', () => {
        clearTimeout(jobQTimer);
        jobQTimer = setTimeout(() => { jobPage = 1; renderJobs(); }, 200);
      });

      jobPerSel?.addEventListener('change', () => {
        jobPage = 1;
        renderJobs();
      });

      // =========================
      // Claims helpers (UPDATED for new payment_breakdown format)
      // =========================
      function parseMaybeJson(v) {
        if (v == null) return null;
        if (typeof v === 'object') return v;
        if (typeof v === 'string') {
          const s = v.trim();
          if (!s) return null;
          try { return JSON.parse(s); } catch(e) { return null; }
        }
        return null;
      }

      function normalizeAttachments(att) {
        if (!att) return [];
        if (Array.isArray(att)) return att;
        return [att];
      }

      function n2(v){
        const num = Number(v);
        return isNaN(num) ? 0 : num;
      }

      function getBreakdownSummary(pb, fallbackTotal=null) {
  const o = parseMaybeJson(pb);
  if (!o) return { total: fallbackTotal, remaining: null, last_paid_at: null, last_paid_by: null };

  const total =
    (o.total_amount != null ? Number(o.total_amount) :
    (o.total != null ? Number(o.total) :
    (o.amount != null ? Number(o.amount) : fallbackTotal)));

  const remaining = (o.remaining != null ? Number(o.remaining) : null);

  let last_paid_at = o.paid_at ?? null;

  // ✅ prefer name, fallback to id
  let last_paid_by = o.paid_by_name ?? (o.paid_by ?? null);

  const hist = Array.isArray(o.history) ? o.history : (Array.isArray(o.payments) ? o.payments : null);
  if (hist && hist.length) {
    const last = hist[hist.length - 1];
    last_paid_at = last?.paid_at ?? last_paid_at;
    last_paid_by = last?.paid_by_name ?? (last?.paid_by ?? last_paid_by);
  }

  return {
    total: isNaN(total) ? fallbackTotal : total,
    remaining: isNaN(remaining) ? null : remaining,
    last_paid_at,
    last_paid_by
  };
}


      // Payment breakdown accepted formats:
      // A) { total_amount, remaining, history:[{amount(paid), remaining, paid_at,...}, ...] }
      // B) { history:[...] } or { payments:[...] }
      // C) [ ... ] (array)
      // D) old single object { amount(total), remaining, paid_at, ... } => convert to one history row with paid = total - remaining
      function extractPaymentHistory(pb, fallbackTotal=null) {
        const o = parseMaybeJson(pb);
        if (!o) return [];

        if (Array.isArray(o)) return o;
        if (Array.isArray(o.history)) return o.history;
        if (Array.isArray(o.payments)) return o.payments;

        // Old single object
        const sum = getBreakdownSummary(o, fallbackTotal);
        const total = n2(sum.total);
        const rem = (sum.remaining != null ? n2(sum.remaining) : null);
        const paid = (rem == null ? 0 : Math.max(0, total - rem));

        return [{
          amount: paid,                 // ✅ paid amount
          paid_by: o.paid_by ?? null,
          paid_at: o.paid_at ?? null,
          attachments: o.attachments ?? [],
          remaining: (rem == null ? total : rem)
        }];
      }

      function statusMeta(claim, fallbackTotal=null) {
        const stRaw = (claim?.status || '').toString().toLowerCase().trim();
        const pb = getBreakdownSummary(claim?.payment_breakdown, fallbackTotal);

        const total = (pb.total != null ? pb.total : fallbackTotal);
        const rem = (pb.remaining != null ? pb.remaining : null);

        let label = stRaw || 'not requested';
        let icon = 'fa-circle-minus';

        if (!claim) {
          label = 'not requested';
          icon = 'fa-circle-minus';
        } else if (rem != null && total != null) {
          if (rem <= 0) { label = 'paid'; icon = 'fa-circle-check'; }
          else if (rem < total) { label = 'partially paid'; icon = 'fa-circle-half-stroke'; }
          else { label = stRaw || 'pending'; icon = 'fa-hourglass-half'; }
        } else {
          if (stRaw === 'paid') icon = 'fa-circle-check';
          else if (stRaw === 'partially paid') icon = 'fa-circle-half-stroke';
          else if (stRaw === 'failed') icon = 'fa-circle-xmark';
          else icon = 'fa-hourglass-half';
        }

        return { label, icon, total, remaining: rem };
      }

      async function fetchAllPages(baseUrl, params, maxPages = 10) {
        const out = [];
        let page = 1;
        let pages = 1;

        while (page <= pages && page <= maxPages) {
          const usp = new URLSearchParams(params || {});
          usp.set('page', page);
          usp.set('_ts', Date.now());

          const j = await fetchJSON(baseUrl + '?' + usp.toString());
          const items = Array.isArray(j?.data) ? j.data : [];
          out.push(...items);

          const meta = j?.meta || {};
          pages = Number(meta.total_pages || 1);
          page++;
        }
        return out;
      }

      async function ensureClaimsForJob(jobId) {
        const key = String(jobId || '');
        if (!key) return new Map();

        const cached = claimsCache.get(key);
        if (cached && (Date.now() - cached.ts) < 45_000) {
          claimsMapForJob = cached.mapByExpenseId;
          return claimsMapForJob;
        }

        await inferRoleIfNeeded();

        let list = [];
        try {
          if (ROLE === 'admin') {
            list = await fetchAllPages(API_ADMIN_CLAIMS, { per_page: 200, job_id: key }, 10);
          } else {
            const mine = await fetchAllPages(API_MY_CLAIMS, { per_page: 200 }, 10);
            list = (mine || []).filter(x => String(x?.job_id || '') === key);
          }
        } catch (e) {
          console.warn('Claims load failed', e);
          list = [];
        }

        const map = new Map();
        (list || []).forEach(c => {
          const expId = c?.expense_id != null ? String(c.expense_id) : '';
          if (!expId) return;

          const prev = map.get(expId);
          if (!prev) map.set(expId, c);
          else {
            // if ids are numeric, keep max; otherwise keep latest by requested_at
            const prevId = Number(prev.id || 0);
            const curId  = Number(c.id || 0);

            if (!isNaN(prevId) && !isNaN(curId)) {
              if (curId >= prevId) map.set(expId, c);
            } else {
              const p = new Date(prev.requested_at || prev.updated_at || 0).getTime();
              const n = new Date(c.requested_at || c.updated_at || 0).getTime();
              if (n >= p) map.set(expId, c);
            }
          }
        });

        claimsCache.set(key, { ts: Date.now(), list, mapByExpenseId: map });
        claimsMapForJob = map;
        return map;
      }

      function bustClaimsCache(jobId) {
        const key = String(jobId || '');
        if (!key) return;
        claimsCache.delete(key);
      }
function bsBadgeClass(label){
  const t = (label || '').toString().toLowerCase().trim();

  if (!t || t === 'not requested') return 'bg-secondary';
  if (t.includes('paid') && !t.includes('partial')) return 'bg-success';
  if (t.includes('partial')) return 'bg-info';
  if (t.includes('fail') || t.includes('reject') || t.includes('cancel')) return 'bg-danger';

  return 'bg-warning'; // pending / requested
}

function renderStatusBadge(label, icon){
  const cls = bsBadgeClass(label);
  return `
    <span class="badge rounded-pill ${cls}">
      <i class="fa ${icon || 'fa-circle-minus'} me-1"></i>${esc(label)}
    </span>
  `;
}

      // =========================
      // Payment modals logic (UPDATED)
      // =========================
      const phModalEl = document.getElementById(@json($jeUid.'_payHistoryModal'));
      const phModal = phModalEl ? new bootstrap.Modal(phModalEl) : null;

      const peModalEl = document.getElementById(@json($jeUid.'_payEditModal'));
      const peModal = peModalEl ? new bootstrap.Modal(peModalEl) : null;

      function setText(el, text){ if (el) el.textContent = (text == null ? '—' : String(text)); }

      function fmtMoney(n, currency) {
        const num = Number(n);
        if (isNaN(num)) return '-';
        return num.toFixed(2) + (currency ? (' ' + currency) : '');
      }

      function toLocalDatetimeValue(isoOrSql) {
        if (!isoOrSql) return '';
        const d = new Date(isoOrSql);
        if (isNaN(d)) return '';
        const pad = (x) => String(x).padStart(2,'0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
      }

      function fromLocalDatetimeValue(v) {
        if (!v) return null;
        const d = new Date(v);
        if (isNaN(d)) return v;
        return d.toISOString();
      }
function buildAttachmentBlock(attachments) {
  const arr = normalizeAttachments(attachments);
  if (!arr.length) return '';

  const items = arr.map((a, i) => {
    const url = (typeof a === 'string')
      ? a
      : (a.absolute_url || a.url || a.relative_url || '');

    const name = (typeof a === 'string')
      ? ('Attachment #' + (i+1))
      : (a.original_name || a.name || a.stored_name || ('Attachment #' + (i+1)));

    const kind = (typeof a === 'object' && a !== null) ? (a.kind || '') : '';

    // ✅ If URL exists -> create clickable link
    if (url) {
      return `<div class="mb-2">
<i class="fa-solid fa-arrow-up-right-from-square"></i>
        <a href="${esc(url)}" target="_blank" rel="noopener noreferrer" class="js-att-link">
          ${esc(name)}
        </a>
      </div>`;
    }

    // ✅ If no URL but has original_name (uploaded file) -> show name only with icon
    if (name && name !== 'Attachment #' + (i+1)) {
      return `<div class="mb-2">
        <i class="fa ${kind === 'file' ? 'fa-file' : 'fa-paperclip'} me-1"></i>
        <span class="text-muted">${esc(name)}</span>
        <small class="text-muted ms-2">(File stored on server)</small>
      </div>`;
    }

    // ✅ Fallback for attachments with no URL and no name
    return `<div class="mb-2 text-muted">
     <i class="fa-solid fa-arrow-up-right-from-square"></i>
${esc(name)}
    </div>`;
  }).join('');

  return `<div class="je-att-list js-att-list" style="display:none;">${items}</div>`;
}
      function openPaymentHistory(expense) {
        const jobPill = phModalEl?.querySelector('.js-ph-job');
        const expPill = phModalEl?.querySelector('.js-ph-exp');
        const amtPill = phModalEl?.querySelector('.js-ph-amt');
        const stPill  = phModalEl?.querySelector('.js-ph-status');

        const emptyBox = phModalEl?.querySelector('.js-ph-empty');
        const tableBox = phModalEl?.querySelector('.js-ph-table');
        const rowsBox  = phModalEl?.querySelector('.js-ph-rows');

        const expId = expense?.id != null ? String(expense.id) : '';
        const claim = claimsMapForJob.get(expId);

const titleHead = (expense?.expense_head && String(expense.expense_head).trim())
  ? expense.expense_head
  : 'Expense';
        const currency = (expense?.currency || 'INR');
        const totalAmount = Number(expense?.amount ?? 0);

setText(jobPill, `Job: ${selectedJob?.title || '—'}`);
setText(expPill, `Expense: ${titleHead || '—'}`);
        setText(amtPill, `Amount: ${fmtMoney(totalAmount, currency)}`);

        const st = statusMeta(claim, totalAmount);
        setText(stPill, `Status: ${st.label}`);

        if (!claim) {
          if (emptyBox) emptyBox.style.display = '';
          if (tableBox) tableBox.style.display = 'none';
          if (rowsBox) rowsBox.innerHTML = '';
          phModal?.show();
          return;
        }

        const history = extractPaymentHistory(claim.payment_breakdown, totalAmount);

        const rows = (history || []).map((h, idx) => {
          const paid = (h?.amount != null ? Number(h.amount) : 0); // ✅ paid amount
          const rem  = (h?.remaining != null ? Number(h.remaining) : null);
          const paidAt = h?.paid_at || null;
const paidBy = paidByLabel(h, claim);

          let state = 'pending';
          if (rem != null) {
            if (rem <= 0) state = 'fully paid';
            else state = 'partially paid';
          } else {
            state = (claim.status || 'pending');
          }

          const atts = normalizeAttachments(h?.attachments);
          const attBtn = atts.length
            ? `<button type="button" class="je-att-btn js-pay-att" data-idx="${idx}" title="View attachments"><i class="fa-solid fa-arrow-up-right-from-square"></i>
</i></button>`
            : `<span class="text-muted small">—</span>`;

          const attBlock = atts.length ? buildAttachmentBlock(atts) : '';

          return `
            <tr>
              <td>
                <div class="fw-semibold">${esc(fmtDate(paidAt))}</div>
                <div class="small text-muted mt-1">Requested: ${esc(fmtDate(claim.requested_at))}</div>
              </td>
              <td>${esc(paidBy)}</td>
              <td>${esc(fmtMoney(paid, currency))}</td>
              <td>${rem == null ? '-' : esc(fmtMoney(rem, currency))}</td>
              <td>
               ${renderStatusBadge(
  state,
  state.includes('fully') ? 'fa-circle-check'
  : (state.includes('partial') ? 'fa-circle-half-stroke' : 'fa-hourglass-half')
)}

                <div class="small text-muted mt-1">Claim: ${esc(claim.status || '-')}</div>
              </td>
              <td class="text-end">
                ${attBtn}
                ${attBlock}
              </td>
            </tr>
          `;
        }).join('');

        if (!rows.trim()) {
          if (emptyBox) emptyBox.style.display = '';
          if (tableBox) tableBox.style.display = 'none';
          if (rowsBox) rowsBox.innerHTML = '';
          phModal?.show();
          return;
        }

        if (emptyBox) emptyBox.style.display = 'none';
        if (tableBox) tableBox.style.display = '';
        if (rowsBox) rowsBox.innerHTML = rows;

        phModal?.show();
      }
    // Toggle attachments list inside payment history modal
   // ✅ Payment History Modal - Handle attachment clicks (open in new tab)
phModalEl?.addEventListener('click', (e) => {
  // ✅ Click on paperclip button - open attachments in new tab
  const btn = e.target.closest('.js-pay-att');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  const tr = btn.closest('tr');
  if (!tr) return;

  const list = tr.querySelector('.js-att-list');
  if (!list) return;

  // Collect attachment links in that row
  const links = Array.from(list.querySelectorAll('.js-att-link'));
  
  if (links.length === 0) {
    err('No attachments found');
    return;
  }

  // ✅ If single attachment -> open directly in new tab
  if (links.length === 1) {
    const url = links[0].getAttribute('href');
    if (url) {
      window.open(url, '_blank', 'noopener,noreferrer');
    }
    return;
  }

  // ✅ If multiple attachments -> toggle the list so user can click individual links
  const isOpen = list.style.display === 'block';
  list.style.display = isOpen ? 'none' : 'block';
});

      // ===== Payment Edit (NEW UI) =====
      let peCtx = null;   // { total, prevRemaining, currency, claimId, existingPB }
      let peFiles = [];

      function hasNewPayUI(){
        return !!peModalEl?.querySelector('.js-pe-paid-now') && !!peModalEl?.querySelector('.js-pe-total');
      }

      function currentAttachMode(){
        const radios = peModalEl?.querySelectorAll('.js-pe-mode') || [];
        const checked = Array.from(radios).find(r => r.checked);
        return checked ? checked.value : 'upload';
      }

      function setFiles(files) {
        peFiles = Array.from(files || []);
        const listEl = peModalEl?.querySelector('.js-pe-filelist');
        if (!listEl) return;
        if (!peFiles.length) { listEl.textContent = 'No files selected'; return; }
        listEl.textContent = peFiles.map(f => `${f.name} (${Math.round(f.size/1024)} KB)`).join(', ');
      }

      function updateAttachModeUI(){
        const mode = currentAttachMode();
        const up = peModalEl?.querySelector('.js-pe-upload-wrap');
        const lk = peModalEl?.querySelector('.js-pe-link-wrap');
        if (up) up.style.display = (mode === 'upload') ? '' : 'none';
        if (lk) lk.style.display = (mode === 'link') ? '' : 'none';
      }

      // dropzone wiring (safe even if elements missing)
     (function initDropzone(){
  const dzEl = peModalEl?.querySelector('.js-pe-dropzone');
  const fileInputEl = peModalEl?.querySelector('.js-pe-files');
  const chooseBtnEl = peModalEl?.querySelector('.js-pe-choose');
  if (!dzEl || !fileInputEl) return;

  // ✅ prevent double-binding if script loads twice
  if (dzEl.dataset.inited === '1') return;
  dzEl.dataset.inited = '1';

  // ✅ Choose button should NOT bubble to dropzone click
  chooseBtnEl?.addEventListener('click', (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    fileInputEl.click();
  });

  fileInputEl.addEventListener('click', (ev) => {
    // ✅ just to be extra safe
    ev.stopPropagation();
  });

  fileInputEl.addEventListener('change', (e) => setFiles(e.target.files));

  function prevent(e){ e.preventDefault(); e.stopPropagation(); }
  dzEl.addEventListener('dragenter', prevent);
  dzEl.addEventListener('dragover', (e) => { prevent(e); dzEl.classList.add('border-primary'); });
  dzEl.addEventListener('dragleave', (e) => { prevent(e); dzEl.classList.remove('border-primary'); });
  dzEl.addEventListener('drop', (e) => {
    prevent(e);
    dzEl.classList.remove('border-primary');
    if (e.dataTransfer?.files?.length) setFiles(e.dataTransfer.files);
  });

  // ✅ Clicking dropzone opens file picker, BUT ignore clicks on choose/input
  dzEl.addEventListener('click', (e) => {
    if (e.target.closest('.js-pe-choose')) return;
    if (e.target.closest('.js-pe-files')) return;
    fileInputEl.click();
  });

  peModalEl?.querySelectorAll('.js-pe-mode')?.forEach(r => r.addEventListener('change', updateAttachModeUI));
})();

      function recomputeRemainingPreview() {
        if (!peCtx) return;

        const paidNowEl = peModalEl?.querySelector('.js-pe-paid-now');
        const totalEl   = peModalEl?.querySelector('.js-pe-total');
        const remEl     = peModalEl?.querySelector('.js-pe-remaining');
        const stView    = peModalEl?.querySelector('.js-pe-status-view');

        const paidNow = n2(paidNowEl?.value);
        const total = n2(peCtx.total);
        const prevRem = n2(peCtx.prevRemaining);
        const newRem = Math.max(0, prevRem - paidNow);

        if (totalEl) totalEl.value = fmtMoney(total, peCtx.currency);
        if (remEl) remEl.value = fmtMoney(newRem, peCtx.currency);

        const computedStatus = (newRem <= 0) ? 'paid' : (paidNow > 0 ? 'partially paid' : 'pending');
        if (stView) stView.value = computedStatus;
      }

      peModalEl?.addEventListener('input', (e) => {
        if (e.target && e.target.classList && e.target.classList.contains('js-pe-paid-now')) {
          recomputeRemainingPreview();
        }
      }); 

      function openPaymentEdit(expense) {
        // If modal html not updated yet, show an error toast (prevents silent failure)
        if (!hasNewPayUI()) {
          err('Pay Edit modal HTML not updated. Add the new fields (Paid Now / Total readonly / Attachment mode).');
          return;
        }

        const noClaim = peModalEl?.querySelector('.js-pe-no-claim');
        const form = peModalEl?.querySelector('.js-pe-form');

        const jobPill = peModalEl?.querySelector('.js-pe-job');
        const expPill = peModalEl?.querySelector('.js-pe-exp');
        const clPill  = peModalEl?.querySelector('.js-pe-claim');

        const claimIdEl = peModalEl?.querySelector('.js-pe-claim-id');
        const expIdEl   = peModalEl?.querySelector('.js-pe-exp-id');
        const jobIdEl   = peModalEl?.querySelector('.js-pe-job-id');

        const paidAtEl  = peModalEl?.querySelector('.js-pe-paid-at');
        const paidNowEl = peModalEl?.querySelector('.js-pe-paid-now');
        const linkEl    = peModalEl?.querySelector('.js-pe-link');
        const noteEl    = peModalEl?.querySelector('.js-pe-note');

        const expId = expense?.id != null ? String(expense.id) : '';
        const claim = claimsMapForJob.get(expId);

        const titleHead = expense?.expense_head || ('Head #' + (expense?.expense_head_id ?? '-'));
        const currency = (expense?.currency || 'INR');
        const totalAmount = n2(expense?.amount ?? 0);

        setText(jobPill, `Job: ${selectedJob?.title || '—'} (#${selectedJob?.id || '—'})`);
        setText(expPill, `Expense: ${titleHead} (#${expId || '—'})`);

        // reset inputs
        if (paidNowEl) paidNowEl.value = '';
        if (paidAtEl) paidAtEl.value = toLocalDatetimeValue(new Date());
        if (linkEl) linkEl.value = '';
        if (noteEl) noteEl.value = '';
        setFiles([]);
        updateAttachModeUI();

        if (!claim) {
          setText(clPill, 'Claim: —');
          if (noClaim) noClaim.style.display = '';
          if (form) form.style.opacity = '.55';

          if (claimIdEl) claimIdEl.value = '';
          if (expIdEl) expIdEl.value = expId || '';
          if (jobIdEl) jobIdEl.value = selectedJob?.id || '';

          peCtx = { total: totalAmount, prevRemaining: totalAmount, currency, claimId: '', existingPB: {} };
          recomputeRemainingPreview();
          peModal?.show();
          return;
        }

        if (noClaim) noClaim.style.display = 'none';
        if (form) form.style.opacity = '1';

        setText(clPill, `Claim: #${claim.id}`);

        if (claimIdEl) claimIdEl.value = String(claim.id);
        if (expIdEl) expIdEl.value = expId || '';
        if (jobIdEl) jobIdEl.value = selectedJob?.id || '';

        const sum = getBreakdownSummary(claim.payment_breakdown, totalAmount);
        const prevRemaining = (sum.remaining != null ? sum.remaining : totalAmount);

        peCtx = {
          total: totalAmount,
          prevRemaining,
          currency,
          claimId: String(claim.id),
          existingPB: parseMaybeJson(claim.payment_breakdown) || {}
        };

        recomputeRemainingPreview();
        peModal?.show();
      }

      // Submit admin payment update (PATCH) - NEW behavior
      peModalEl?.querySelector('.js-pe-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        await inferRoleIfNeeded();
        if (ROLE !== 'admin') return err('Only admin can update payment details');

        if (!hasNewPayUI()) return err('Pay Edit modal HTML not updated.');

        const claimId = peModalEl?.querySelector('.js-pe-claim-id')?.value || '';
        if (!claimId) return err('No claim to update');

        const paidNow = n2(peModalEl?.querySelector('.js-pe-paid-now')?.value);
        if (paidNow <= 0) return err('Enter a valid paid amount');

        const paidAtRaw = peModalEl?.querySelector('.js-pe-paid-at')?.value || '';
        const paidAt = fromLocalDatetimeValue(paidAtRaw);

        const mode = currentAttachMode();
        const link = (peModalEl?.querySelector('.js-pe-link')?.value || '').trim();
        const note = (peModalEl?.querySelector('.js-pe-note')?.value || '').trim() || null;

        const total = n2(peCtx?.total);
        const prevRem = n2(peCtx?.prevRemaining);
        const newRem = Math.max(0, prevRem - paidNow);

        let attachments = [];
        if (mode === 'link') {
          if (link) attachments = [link];
        } else {
          attachments = (peFiles || []).map(f => ({ original_name: f.name, kind: 'file', url: null }));
        }

        const existing = (peCtx?.existingPB && typeof peCtx.existingPB === 'object') ? peCtx.existingPB : {};
        let prevHistory = [];

        if (Array.isArray(existing.history)) prevHistory = existing.history.slice();
        else if (Array.isArray(existing.payments)) prevHistory = existing.payments.slice();
        else if (Array.isArray(existing)) prevHistory = existing.slice();
        else if (existing && (existing.paid_at || existing.attachments || existing.remaining != null)) {
          // convert old object to one row
          const converted = extractPaymentHistory(existing, total);
          if (converted.length) prevHistory = prevHistory.concat(converted);
        }

        const paymentEntry = {
          amount: paidNow,         // ✅ paid amount only
          paid_by: null,
          paid_at: paidAt,
          attachments,
          remaining: newRem,
          note
        };

        const nextPB = {
          total_amount: total,
          amount: total,           // keep for backward compatibility
          remaining: newRem,
          history: prevHistory.concat([paymentEntry])
        };

        const nextStatus = (newRem <= 0) ? 'paid' : 'partially paid';

        const url = API_ADMIN_UPDATE_PATTERN.replace('{id}', encodeURIComponent(claimId));

        try {
          peModalEl?.querySelector('.js-pe-save')?.setAttribute('disabled', 'disabled');
    // ✅ If upload mode -> send multipart with _method=PATCH so Laravel receives files
    if (mode === 'upload') {
      const fd = new FormData();
      fd.append('_method', 'PATCH');
      fd.append('status', nextStatus);
      fd.append('payment_breakdown', JSON.stringify(nextPB));

      // IMPORTANT: backend expects attachments_files[]
      (peFiles || []).forEach(f => fd.append('attachments_files[]', f));

      await fetchJSON(url, {
        method: 'POST',   // ✅ multipart
        body: fd,
        headers: {}       // ✅ do not set Content-Type manually
      });
    } else {
      // ✅ Link mode can stay JSON PATCH (no files)
      await fetchJSON(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          status: nextStatus,
          payment_breakdown: nextPB
        })
      });
    }

          ok('Payment updated');
          peModal?.hide();

          if (selectedJob?.id) bustClaimsCache(selectedJob.id);
          await loadExpenses();

        } catch (e2) {
          console.error(e2);
          err(e2.message || 'Failed to update payment');
        } finally {
          peModalEl?.querySelector('.js-pe-save')?.removeAttribute('disabled');
        }
      });

      // =========================
      // Expenses table
      // =========================
      const perSel = qs('.js-per');
      const qInput = qs('.js-q');
      const btnRefresh = qs('.js-refresh');
      const btnReset = qs('.js-reset');

      const rowsTbody = qs('.js-rows');
      const loaderRow = ROOT.querySelector('.js-loader');
      const emptyRow  = ROOT.querySelector('.js-empty');
      const metaEl    = qs('.js-meta');
      const pagerEl   = qs('.js-pager');

      function setExpLoading(v){
        if (loaderRow) loaderRow.style.display = v ? '' : 'none';
      }

      function clearExpenseRows(){
        Array.from(rowsTbody.querySelectorAll('tr')).forEach(tr => {
          if (tr.classList.contains('js-loader')) return;
          if (tr.classList.contains('js-empty')) return;
          tr.remove();
        });
      }
function paidByLabel(h, claim=null){
  // Prefer name
  const name =
    h?.paid_by_name ??
    claim?.payment_breakdown_paid_by_name ?? // optional if you ever extract it
    null;

  if (name) return String(name);

  // Fallback to id
  const id = (h?.paid_by != null ? String(h.paid_by) : null);
  return id ? ('ID #' + id) : '-';
}

      function applyExpenseSearch(list){
  const term = (qInput.value || '').trim().toLowerCase();
  if (!term) return list;

  return (list || []).filter(r => {
    const head = (r.expense_head || '').toLowerCase();
    const note = ((r.note || '') + '').replace(/<[^>]*>/g,'').toLowerCase();
    const amt  = String(r.amount ?? '').toLowerCase();
    const cur  = String(r.currency ?? '').toLowerCase();

    const nm   = String(creatorName(r) || '').toLowerCase();
    const em   = String(creatorEmail(r) || '').toLowerCase();

    return (head + ' ' + note + ' ' + amt + ' ' + cur + ' ' + nm + ' ' + em).includes(term);
  });
}

function creatorName(r){
  return (
    r.creator_name ??
    r.created_by_name ??
    r.created_by_person_name ??
    r.created_by_user_name ??
    r.created_by_label ??
    (r.created_by != null ? ('ID #' + String(r.created_by)) : null) ??
    '—'
  );
}

function creatorEmail(r){
  return (
    r.creator_email ??
    r.created_by_email ??
    r.email ??
    '—'
  );
}

      function statusCell(exp){
        const expId = exp?.id != null ? String(exp.id) : '';
        const claim = claimsMapForJob.get(expId);
        const fallbackTotal = Number(exp?.amount ?? 0);

        const st = statusMeta(claim, fallbackTotal);
        const currency = exp?.currency || 'INR';

        const remText = (st.remaining != null ? `Remaining: ${fmtMoney(st.remaining, currency)}` : '');
        const reqAt = claim?.requested_at ? `Requested: ${fmtDate(claim.requested_at)}` : '';

        return `
          <div>
${renderStatusBadge(st.label, st.icon)}
            <div class="small text-muted mt-2">
              ${remText ? esc(remText) : ''}${remText && reqAt ? ' • ' : ''}${reqAt ? esc(reqAt) : ''}
            </div>
          </div>
        `;
      }

      function rowActionsHtml(exp){
        const expId = exp?.id != null ? String(exp.id) : '';
        const claim = claimsMapForJob.get(expId);
        const hasClaim = !!claim;

        const isAssignee = (ROLE === 'assignee');
        const isAdmin = (ROLE === 'admin');

        const canRequest = isAssignee && !hasClaim;

        const btnReq = isAssignee ? `
          <button class="btn btn-primary btn-sm js-claim" data-exp="${esc(expId)}" ${canRequest ? '' : 'disabled'}>
            <i class="fa fa-hand-holding-dollar me-1"></i>
            ${canRequest ? 'Request Claim' : 'Requested'}
          </button>
        ` : '';

        const more = `
          <div class="dropdown">
            <button class="je-more-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item js-pay-history" href="javascript:void(0)" data-exp="${esc(expId)}">
                  <i class="fa fa-clock-rotate-left"></i> Payment History
                </a>
              </li>

              ${isAdmin ? `
                <li>
                  <a class="dropdown-item js-pay-edit ${hasClaim ? '' : 'disabled'}" href="javascript:void(0)" data-exp="${esc(expId)}" ${hasClaim ? '' : 'tabindex="-1" aria-disabled="true"'}>
                    <i class="fa fa-pen-to-square"></i> Add / Update Payment
                  </a>
                </li>
              ` : ''}
            </ul>
          </div>
        `;

        return `
          <div class="d-inline-flex gap-2 align-items-center justify-content-end">
            ${btnReq}
            ${more}
          </div>
        `;
      }
function rowHtml(r){
  const head = r.expense_head || ('Head #' + (r.expense_head_id ?? '-'));

  const amount = Number(r.amount ?? 0);
  const currency = (r.currency || 'INR');
  const createdAt = fmtDate(r.created_at);

  const cName  = creatorName(r);
  const cEmail = creatorEmail(r);

  const expId = (r?.id != null ? String(r.id) : '');
  const notePlain = notePlainFromHtml(r?.note);

  // ✅ show only small portion in table
  const NOTE_PREVIEW_CHARS = 120; // change as you want
  const noteShort = (notePlain && notePlain.length > NOTE_PREVIEW_CHARS)
    ? (notePlain.slice(0, NOTE_PREVIEW_CHARS) + '…')
    : (notePlain || '—');

  return `
    <tr>
      <td><div class="fw-semibold">${esc(head)}</div></td>

      <td><div class="fw-semibold">${esc(cName)}</div></td>

      <td><div class="small">${esc(cEmail)}</div></td>

      <!-- ✅ Note preview only -->
      <td class="je-note-cell" data-exp="${esc(expId)}" title="Click to view full note">
        <div class="small text-muted je-note-preview">${esc(noteShort)}</div>
      </td>

      <td>
        <div class="fw-semibold">${esc(isNaN(amount) ? '0.00' : amount.toFixed(2))}</div>
        <div class="small text-muted">${esc(currency)}</div>
      </td>

      <td class="text-center">${proofCell(r)}</td>
      <td>${statusCell(r)}</td>
      <td>${createdAt}</td>
      <td class="text-end">${rowActionsHtml(r)}</td>
    </tr>
  `;
}

      function buildExpensePager(cur, pages){
        if (!pagerEl) return;
        buildPager(pagerEl, cur, pages, (t) => {
          expPage = Math.max(1, t);
          loadExpenses();
          window.scrollTo({top:0, behavior:'smooth'});
        });
      }

      async function loadExpenses(){
        clearExpenseRows();

        await inferRoleIfNeeded();

        if (!selectedJob?.id) {
          if (emptyRow) emptyRow.style.display = '';
          if (metaEl) metaEl.textContent = 'Choose a job to view expenses';
          if (pagerEl) pagerEl.innerHTML = '';
          return;
        }

        setExpLoading(true);
        if (emptyRow) emptyRow.style.display = 'none';

        try {
          const per = Math.max(10, Number(perSel.value || 20));

          const url = API_EXP_PATTERN
            .replace('{job}', encodeURIComponent(selectedJob.id))
            + '?page=' + encodeURIComponent(expPage)
            + '&per_page=' + encodeURIComponent(per)
            + '&_ts=' + Date.now();

          const j = await fetchJSON(url);

          const items = Array.isArray(j?.data) ? j.data : [];
          const meta = j?.meta || {};
          const total = Number(meta.total || items.length || 0);

          expensesAll = items;

          // ✅ load claim map for this job (for payment status + menus)
          await ensureClaimsForJob(selectedJob.id);

          const shown = applyExpenseSearch(expensesAll);

          if (!shown.length) {
            if (emptyRow) emptyRow.style.display = '';
            if (metaEl) metaEl.textContent = total ? `No match found in this page. Total expenses: ${total}` : 'No expenses found';
            if (pagerEl) pagerEl.innerHTML = '';
            return;
          }

          const frag = document.createElement('tbody');
          frag.innerHTML = shown.map(rowHtml).join('');
          Array.from(frag.children).forEach(tr => rowsTbody.appendChild(tr));

          expPages = Number(meta.total_pages || 1);
          buildExpensePager(Number(meta.page || expPage), expPages);

          if (metaEl) {
            const from = meta.from ?? null;
            const to = meta.to ?? null;
            metaEl.textContent = (from && to)
              ? `Showing ${from}–${to} of ${total} expense(s) • Job #${selectedJob.id} • Role: ${ROLE}`
              : `Total: ${total} expense(s) • Job #${selectedJob.id} • Role: ${ROLE}`;
          }

        } catch(e) {
          console.error(e);
          err(e.message || 'Failed to load expenses');
          if (emptyRow) emptyRow.style.display = '';
          if (metaEl) metaEl.textContent = 'Failed to load';
          if (pagerEl) pagerEl.innerHTML = '';
        } finally {
          setExpLoading(false);
        }
      }

      // =========================
      // Row actions (Request Claim + 3-dot menu)
      // =========================
      ROOT.addEventListener('click', async (e) => {
          const noteCell = e.target.closest('td.je-note-cell');
  if (noteCell && rowsTbody.contains(noteCell)) {
    const expId = String(noteCell.getAttribute('data-exp') || '');
    const row = (expensesAll || []).find(x => String(x.id) === expId);

    const head = row?.expense_head || ('Head #' + (row?.expense_head_id ?? '-'));
    const noteText = notePlainFromHtml(row?.note);

    await Swal.fire({
      title: head,
      html: esc(noteText || '—'),
      customClass: { htmlContainer: 'je-note-swal' },
      confirmButtonText: 'Close',
      showCloseButton: true,
      width: 760,
    });
    return;
  }


        const proofBtn = e.target.closest('.js-proof');
  if (proofBtn) {
    const url = proofBtn.getAttribute('data-url') || '';
    if (url) window.open(url, '_blank', 'noopener,noreferrer');
    return;
  }
        const claimBtn = e.target.closest('.js-claim');
        const payHistoryBtn = e.target.closest('.js-pay-history');
        const payEditBtn = e.target.closest('.js-pay-edit');

        if (payHistoryBtn) {
          const expId = String(payHistoryBtn.getAttribute('data-exp') || '');
          const row = (expensesAll || []).find(x => String(x.id) === expId);
          if (!row) return;
          openPaymentHistory(row);
          return;
        }

        if (payEditBtn) {
          await inferRoleIfNeeded();
          if (ROLE !== 'admin') return;

          if (payEditBtn.classList.contains('disabled')) return;

          const expId = String(payEditBtn.getAttribute('data-exp') || '');
          const row = (expensesAll || []).find(x => String(x.id) === expId);
          if (!row) return;
          openPaymentEdit(row);
          return;
        }

        if (claimBtn) {
          await inferRoleIfNeeded();
          if (ROLE !== 'assignee') return;

          const expId = String(claimBtn.getAttribute('data-exp') || '');
          if (!selectedJob?.id || !expId) return;

          const claimAlready = claimsMapForJob.get(expId);
          if (claimAlready) return;

          const { isConfirmed, value } = await Swal.fire({
            title: 'Request expense claim',
            input: 'textarea',
            inputLabel: 'Message (optional)',
            inputPlaceholder: 'Write a short note for admin…',
            inputAttributes: { 'aria-label': 'Claim message' },
            showCancelButton: true,
            confirmButtonText: 'Submit request',
          });

          if (!isConfirmed) return;

          try {
            claimBtn.disabled = true;

            await fetchJSON(API_CLAIM, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                job_id: selectedJob.id,
                expense_id: expId, // ✅ keep safe for uuid/numeric
                message: (value || '').trim() || null,
              })
            });

            ok('Claim request submitted');

            if (selectedJob?.id) bustClaimsCache(selectedJob.id);
            await loadExpenses();

          } catch(e2) {
            console.error(e2);
            err(e2.message || 'Failed to create claim request');
            claimBtn.disabled = false;
          }
          return;
        }
      });

      // Filters
      let qTimer;
      qInput.addEventListener('input', () => {
        clearTimeout(qTimer);
        qTimer = setTimeout(() => loadExpenses(), 200);
      });

      perSel.addEventListener('change', () => {
        expPage = 1;
        loadExpenses();
      });

      btnRefresh.addEventListener('click', () => loadExpenses());

      btnReset.addEventListener('click', () => {
        qInput.value = '';
        perSel.value = '20';
        expPage = 1;
        loadExpenses();
      });

      // Initial UI state
      if (jobLabel) jobLabel.textContent = 'None';

      inferRoleIfNeeded().finally(() => {
        loadExpenses();
      });

    })();
  </script>
@endpush 