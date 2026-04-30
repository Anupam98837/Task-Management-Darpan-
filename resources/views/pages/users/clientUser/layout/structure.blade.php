<!DOCTYPE html>
@php
  $portalPrefix = $portalPrefix ?? 'assignee';
  $portalLabel = $portalLabel ?? 'Portal';
  $portalDashboardUrl = $portalDashboardUrl ?? '/' . $portalPrefix . '/dashboard';
  $portalJobsUrl = $portalJobsUrl ?? '/' . $portalPrefix . '/jobs/view';
  $portalDocumentsUrl = $portalDocumentsUrl ?? '/' . $portalPrefix . '/documents';
  $portalNotificationsUrl = $portalNotificationsUrl ?? '/' . $portalPrefix . '/notifications';
  $portalLoginUrl = $portalLoginUrl ?? '/' . $portalPrefix . '/login';
  $portalLogoutApi = $portalLogoutApi ?? ($portalPrefix === 'client-user' ? '/api/client-users/logout' : '/api/assignedpeople/logout');
  $portalThemeKey = $portalThemeKey ?? 'theme:' . $portalPrefix;
  $isClientUser = ($portalPrefix === 'client-user');
@endphp
<html lang="en" class="">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>@yield('title','Dashboard — Structure 2 (Hallienz)')</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">

  <!-- Bootstrap / Icons / SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/css/sweetalert2.min.css" rel="stylesheet"/>

  <!-- Main theme -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

  <style>
    :root{
      --radius: var(--radius-md,12px);
      --rail-w: 72px;
      --drawer-w: 260px;
      --elev-1: var(--shadow-sm, 0 1px 2px rgba(0,0,0,.06));
      --elev-2: 0 6px 20px rgba(0,0,0,.12);
      --ease: cubic-bezier(.2,.7,.2,1);
    }

    body{ margin:0; font-family: var(--font-sans, Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif);
      background: var(--bg-body, var(--light-color,#f5f7fa)); color: var(--text-color,#0f172a); }
    body.no-scroll{ overflow:hidden; }
    .layout{ display:grid; grid-template-columns: var(--rail-w) 1fr; min-height:100svh; }

    /* Rail — refined, more pro */
    .rail{ position:sticky; top:0; height:100svh;
      background: linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
      border-right:1px solid var(--border-color,#e5e7eb);
      display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 10px; z-index:1001;
      box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .rail .logo{ width:48px; height:48px; display:flex; align-items:center; justify-content:center; margin-bottom: 4px; }
    .rail .rail-nav{ display:flex; flex-direction:column; gap:4px; margin-top:6px; width:100%; }
    .rail .rail-btn{ width:100%; height:42px; border:0; background:transparent; color:var(--text-color);
      border-radius:10px; display:flex; align-items:center; justify-content:center;
      transition: background .18s var(--ease), color .18s var(--ease), transform .12s var(--ease);
      outline:none; position:relative;
    }
    .rail .rail-btn:hover{ background: rgba(3,105,161,.08); color: var(--primary-color); transform: translateY(-1px); }
    .rail .rail-btn:hover .fa{ color: var(--primary-color); }
    .rail .rail-btn.active{
      background: linear-gradient(135deg, rgba(3,105,161,.12), rgba(14,165,233,.10));
      color: var(--primary-color);
      box-shadow: inset 0 0 0 1px rgba(3,105,161,.18);
    }
    .rail .rail-btn.active::before{
      content:""; position:absolute; left:-10px; top:8px; bottom:8px; width:3px;
      border-radius:0 4px 4px 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
    }
    .rail .rail-btn .fa{ font-size:16px; color: var(--muted-color, #64748b); transition: color .18s var(--ease); }
    .rail .rail-btn.active .fa{ color: var(--primary-color); }
    .rail .spacer{ flex:1; }
    .rail .rail-bottom{ display:flex; flex-direction:column; gap:4px; width:100%; padding-top: 8px; border-top: 1px dashed var(--border-color); }
    .rail .rail-divider{ width: 28px; height:1px; background: var(--border-color); margin: 4px 0; }

    /* Left nav drawer — refined */
    .drawer{ position:fixed; top:0; left:var(--rail-w); height:100svh; width:var(--drawer-w);
      background: #ffffff;
      border-right:1px solid var(--border-color);
      box-shadow: 0 8px 24px -8px rgba(15,23,42,.10);
      transform: translateX(-100%); opacity:0; visibility:hidden;
      transition: transform .24s var(--ease), opacity .24s var(--ease), visibility .24s var(--ease);
      z-index:1000; display:flex; flex-direction:column;
    }
    .drawer.open{ transform:none; opacity:1; visibility:visible; }
    .drawer[aria-hidden="true"]{ pointer-events:none; }
    .drawer .drawer-head{
      padding:12px 14px; border-bottom:1px solid var(--border-color);
      display:flex; align-items:center; justify-content:space-between; gap: 8px;
      background: linear-gradient(180deg, #ffffff, #fafbfd);
    }
    .drawer .nav-scroll{ flex:1; overflow:auto; padding:10px 10px 14px; display:flex; flex-direction:column; gap:3px; }
    .drawer .nav-section-title{
      padding: 10px 10px 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .8px; color: var(--muted-color);
    }

    .drawer .nav-link{
      display:flex; align-items:center; gap:10px; padding: 8px 11px;
      border-radius:8px; color:var(--text-color); text-decoration:none;
      transition: background .14s var(--ease), color .14s var(--ease);
      border:1px solid transparent !important; position:relative;
      font-size:13px; font-weight:500; letter-spacing: .1px;
    }
    .drawer .nav-link i{ color: var(--muted-color, #64748b); min-width:16px; text-align:center; font-size: 13px; transition: color .14s var(--ease); }
    .drawer .nav-link:hover{ background: rgba(3,105,161,.06); color: var(--primary-color); }
    .drawer .nav-link:hover i{ color: var(--primary-color); }
    .drawer .nav-link.active{
      background: linear-gradient(135deg, rgba(3,105,161,.10), rgba(14,165,233,.08));
      color: var(--primary-color); font-weight: 600;
    }
    .drawer .nav-link.active i{ color: var(--primary-color); }
    .drawer .nav-link.active::before{
      content:""; position:absolute; left:-10px; top:6px; bottom:6px; width:3px;
      border-radius:0 4px 4px 0; background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
    }

    /* Pin button (lock drawer open) */
    .drawer-pin-btn{
      width:30px; height:30px; border-radius: 8px;
      border: 1px solid var(--border-color); background: #fff;
      color: var(--muted-color); display:inline-flex; align-items:center; justify-content:center;
      cursor: pointer; transition: var(--transition); font-size: 12px;
    }
    .drawer-pin-btn:hover{ background: var(--light-color); color: var(--primary-color); border-color: rgba(3,105,161,.25); }
    .drawer-pin-btn.pinned{
      background: linear-gradient(135deg, rgba(3,105,161,.14), rgba(14,165,233,.10));
      color: var(--primary-color); border-color: rgba(3,105,161,.30);
      box-shadow: inset 0 0 0 1px rgba(3,105,161,.12);
    }
    .drawer-pin-btn.pinned i{ transform: rotate(-25deg); }
    .drawer-pin-btn i{ transition: transform .2s var(--ease); }

    /* When the layout is in pinned mode, leave room for the drawer */
    .layout.is-pinned .panel{ margin-left: var(--drawer-w); }
    .layout.is-pinned .drawer{ box-shadow: none; border-right: 1px solid var(--border-color); }

    .nav-group{ display:flex; flex-direction:column; gap:3px; }
    .group-toggle{ display:flex; align-items:center; gap:10px; cursor:pointer; user-select:none; }
    .group-toggle .chev{ margin-left:auto; color:var(--muted-color); transition: transform .18s var(--ease); font-size: 10px; }
    .group-toggle.open .chev{ transform: rotate(180deg); color: var(--primary-color); }

    .submenu{ display:none; flex-direction:column; gap:2px; margin-left: 14px; padding-left: 8px; border-left: 1.5px solid var(--border-color); margin-top: 2px; }
    .submenu.open{ display:flex; animation:fadeIn .2s var(--ease); }
    .submenu .nav-link{ font-size: 12px; padding: 6px 10px 6px 14px; border-radius:6px; font-weight: 500; }
    .submenu .nav-link::before{ display:none; }
    .submenu .nav-link.active{ background: rgba(3,105,161,.06); }
    @keyframes fadeIn{ from{opacity:0; transform:translateY(-4px);} to{opacity:1; transform:translateY(0);} }

    /* Right side */
    .panel{ min-width:0; display:flex; flex-direction:column; min-height:100svh; transition: margin-left .22s var(--ease); }
    .panel.shifted{ margin-left: var(--drawer-w); }

    .admin-header{ height:60px; background:#fff; border-bottom:1px solid var(--border-color);
      position:sticky; top:0; z-index:900; display:flex; align-items:center; gap:10px; padding:0 12px; }
    .admin-header .btn.btn-link{ color: var(--text-color); text-decoration:none; }
    .admin-header .btn.btn-link:hover{ color: var(--accent-color); }

    .page-head{ display:flex; align-items:center; justify-content:space-between; gap:8px; background:#fff; border:1px solid var(--border-color); border-radius: var(--radius);
      padding:12px 14px; margin:12px; }
    .main-content{ flex:1; 
      /* padding: 6px 12px 16px 12px;  */
    }

    .overlay{ position:fixed; inset:0; background: rgba(0,0,0,.45); z-index: 950; opacity:0; visibility:hidden; transition: .18s var(--ease); backdrop-filter: blur(2px); }
    .overlay.active{ opacity:1; visibility:visible; }

    @media (max-width: 991px){
      .layout{ grid-template-columns: 1fr; }
      .rail{ display:none !important; }
      .drawer{ left:0; width:280px; }
      .panel{ margin-left:0 !important; }
      .admin-header .brand-title{ display:none; }
    }

    /* Dark mode */
    html.theme-dark{
      --bg-body:#0b1220; --text-color:#e5e7eb; --light-color:#0f172a; --border-color:#273244;
    }
    html.theme-dark .rail, html.theme-dark .drawer, html.theme-dark .admin-header, html.theme-dark .page-head,
    html.theme-dark .dropdown-menu, html.theme-dark .modal-content, html.theme-dark .card,
    html.theme-dark .offcanvas
    /* ,
     html.theme-dark .toast */
    { background: var(--light-color) !important; color: var(--text-color) !important; border-color: var(--border-color) !important; }
    html.theme-dark .drawer .nav-link:hover{ background: rgba(99,102,241,.10); color: var(--accent-color,#6366f1); }
    html.theme-dark .drawer .nav-link.active{ background: rgba(99,102,241,.14); }

    /* Scrollbar */
    .drawer .nav-scroll::-webkit-scrollbar{ width:8px; }
    .drawer .nav-scroll::-webkit-scrollbar-thumb{ background: var(--border-color); border-radius:8px; }

    .drawer .drawer-foot{ margin-top:auto; border-top:1px solid var(--border-color, #e5e7eb); padding:12px; background:#fff; position:sticky; bottom:0; z-index:1; }
    .login-state{ display:flex; align-items:center; gap:8px; font-size:12px; color: var(--muted-color, #6b7280); margin-bottom:8px; }
    .login-state .fa{ font-size:12px; color: var(--success-color, #16a34a); }
    .auth-link{ display:flex; align-items:center; justify-content:center; gap:.5rem; height:36px; border:1px solid var(--border-color, #e5e7eb);
      border-radius: var(--radius, 12px); color: var(--danger-color, #dc2626); font-weight:600; font-size:14px; text-decoration:none;
      background: transparent; transition: background .15s ease, border-color .15s ease, color .15s ease, transform .08s ease; }
    .auth-link:hover{ background: rgba(220,38,38,.06); border-color: rgba(220,38,38,.35); color:#b91c1c; }

    .theme-toggle-drawer{ display:flex; align-items:center; justify-content:center; gap:.5rem; height:36px; border:1px solid var(--border-color, #e5e7eb);
      border-radius: var(--radius, 12px); color: var(--text-color); font-weight:600; font-size:14px; background: transparent;
      transition: background .15s ease, border-color .15s ease, color .15s ease, transform .08s ease; width:100%; margin-bottom:8px; cursor:pointer; }
    .theme-toggle-drawer:hover{ background: var(--light-color); border-color: var(--border-color); color: var(--accent-color); }

    html.theme-dark .drawer .drawer-foot{ background: var(--light-color, #0f172a); border-top-color: var(--border-color, #273244); }
    html.theme-dark .auth-link{ border-color: var(--border-color, #273244); }
    html.theme-dark .auth-link:hover{ background: rgba(220,38,38,.12); border-color: rgba(220,38,38,.45); }
    html.theme-dark .theme-toggle-drawer{ border-color: var(--border-color, #273244); }
    html.theme-dark .theme-toggle-drawer:hover{ background: rgba(99,102,241,.10); border-color: rgba(99,102,241,.35); }

    .ah_usericon:hover { background: var(--primary-color); color: var(--surface) !important;}
    .fa-bars { margin: 0 !important; }

    /* ===== Notification Drawer (right side) ===== */
    .notif-drawer{
      position:fixed; top:0; right:0; height:100svh; width:380px; max-width:100%;
      background:#fff; border-left:1px solid var(--border-color);
      box-shadow: var(--elev-2);
      transform: translateX(100%); opacity:0; visibility:hidden;
      transition: transform .22s var(--ease), opacity .22s var(--ease), visibility .22s var(--ease);
      z-index:1200; display:flex; flex-direction:column;
      backdrop-filter: saturate(1.2) blur(2px);
    }
    .notif-drawer.open{ transform:none; opacity:1; visibility:visible; }
    .nd-head{ padding:12px 14px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .nd-body {
  flex: 1;
  overflow-y: auto;   /* only vertical scrolling */
  overflow-x: hidden; /* hide horizontal scrollbar completely */
}
 
    .nd-foot{ padding:10px 12px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; gap:8px; }
 
    /* Dark */
    html.theme-dark .notif-drawer,
    html.theme-dark .notif-drawer-head,
    html.theme-dark .notif-drawer-foot { background: var(--light-color, #0f172a); border-color: var(--border-color, #273244); }
    html.theme-dark .notif-drawer-list { background: var(--bg-body, #0b1220); }
    /* Apply to the notif drawer body only */
.nd-body {
  /* ensure only vertical scrolling and reserve space for scrollbar */
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-gutter: stable; /* keeps layout from shifting when scrollbar appears */
}
 
/* -------- WebKit browsers (Chrome, Edge, Safari) -------- */
html.theme-dark .nd-body::-webkit-scrollbar {
  width: 10px;                /* thin but reachable like your example */
  height: 0;                  /* hide bottom horizontal scrollbar handle */
}
 
/* Track: slightly darker than drawer bg so it reads as subtle groove */
html.theme-dark .nd-body::-webkit-scrollbar-track {
  background: transparent;    /* use transparent to blend with drawer bg */
  border-left: 1px solid rgba(255,255,255,0.02); /* subtle edge separation */
  margin: 8px 0;              /* give vertical breathing at top/bottom (works in some browsers) */
}
 
/* Thumb: slim, rounded, slightly lighter than track, with subtle inset shadow */
html.theme-dark .nd-body::-webkit-scrollbar-thumb {
  background-color: rgba(148,163,184,0.18); /* pale bluish-gray — visible but soft */
  min-height: 28px;                          /* ensure easy grab on touch/trackpads */
  border-radius: 999px;                      /* pill / fully rounded */
  border: 2px solid transparent;             /* spacing illusion */
  box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02); /* very subtle inner highlight */
}
 
/* Hover state: slightly brighter thumb */
html.theme-dark .nd-body::-webkit-scrollbar-thumb:hover {
  background-color: rgba(148,163,184,0.28);
  box-shadow: inset 0 0 6px rgba(0,0,0,0.25);
}
 
/* Optional: hide the up/down buttons on the scrollbar if browser shows them */
html.theme-dark .nd-body::-webkit-scrollbar-button { display: none; }
 
/* Corner: match drawer bg */
html.theme-dark .nd-body::-webkit-scrollbar-corner {
  background: transparent;
}
 
/* -------- Firefox -------- */
/* thin + colors */
html.theme-dark .nd-body {
  scrollbar-width: thin;
  scrollbar-color: rgba(148,163,184,0.18) transparent; /* thumb | track */
}
 
/* -------- Optional: auto-hide until hover (sleek behavior) --------
   NOTE: This only visually hides by making the thumb transparent; it's optional.
*/
html.theme-dark .nd-body::-webkit-scrollbar-thumb {
  transition: background-color .18s ease, opacity .18s ease;
  opacity: 0.9;
}
html.theme-dark .nd-body:not(:hover)::-webkit-scrollbar-thumb { opacity: 0.6; }
 
      /* Bell hover */
    #notifBellOpen:hover{ color: var(--accent-color,#6366f1); transform: translateY(-1px); transition: color .18s ease, transform .18s ease; }
  /* Expense bubble (cards) */
.expense-bubble {
  border-radius: 12px;
  padding: 14px 16px;
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--border-color, #e6eef8);
  box-shadow: 0 8px 24px rgba(2,6,23,0.06);
  margin-bottom: 14px;
  color: var(--text-color, #0f172a);
}

/* header/title (if present) */
.expense-bubble .expense-title {
  color: rgba(15,23,42,0.12);
  font-weight:700;
  margin-bottom: 8px;
  color: var(--muted-title, #cbd5e1); /* subtle title tint */
  font-size: 15px;
}

/* meta row: date on left, amount on right */
.expense-meta {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-top:6px;
}

/* date */
.expense-date {
  font-size:13px;
  color: var(--muted, #64748b);
}

/* amount (right aligned, green) */
.expense-amount {
  font-size:15px;
  font-weight:700;
  color: var(--expense-amount-color, #10b981);
  white-space:nowrap;
  margin-left: auto;
}

/* attachment link */
.expense-docs {
  margin-top:10px;
}
.expense-docs a {
  color: var(--accent, #3b82f6);
  text-decoration: underline;
  word-break: break-word;
  display:inline-block;
  max-width:100%;
}

/* thin divider */
.expense-bubble hr.expense-sep {
  border: none;
  border-top: 1px solid var(--border-color, #e6eef8);
  margin: 12px 0;
  opacity: .9;
}

/* creator / timestamp row */
.expense-footer {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-top:6px;
  color: var(--muted, #64748b);
  font-size:13px;
}

/* creator block (name + email) */
.expense-footer .creator {
  display:flex;
  flex-direction:column;
  gap:4px;
}
.expense-footer .creator a { color: var(--accent, #3b82f6); text-decoration: underline; }

/* timestamp (right side) */
.expense-footer .ts { color: var(--muted, #64748b); font-size:12px; white-space:nowrap; }

/* small responsive tweaks */
@media (max-width:520px){
  .expense-meta { flex-direction:row; align-items:center; }
  .expense-amount { font-size:14px; }
  .expense-footer { flex-direction:column; align-items:flex-start; gap:6px; }
  .expense-footer .ts { align-self:flex-end; }
}

/* ===== Dark mode overrides (uses html.theme-dark already present) ===== */
html.theme-dark .expense-bubble {
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border-color: rgba(255,255,255,0.06);
  box-shadow: 0 12px 36px rgba(2,6,23,0.6);
  color: var(--text-color, #e6eef8);
}
html.theme-dark .expense-bubble .expense-date,
html.theme-dark .expense-bubble .expense-footer,
html.theme-dark .expense-bubble .expense-meta { color: rgba(230,238,248,0.75); }
html.theme-dark .expense-bubble .expense-amount { color: #34d399; }
html.theme-dark .expense-docs a { color: rgba(96,165,250,0.95); }

/* make card appear slightly inset on dark background */
html.theme-dark .expense-bubble { box-shadow: 0 8px 20px rgba(2,6,23,0.45); }

/* tiny accessibility / focus */
.expense-bubble:focus-within { outline: 2px solid rgba(59,130,246,0.12); outline-offset: 2px; }

/* Attach previews & chips */
html.theme-dark .preview-item {
  background: rgba(255,255,255,0.02);
  border-color: rgba(255,255,255,0.04);
  box-shadow: none;
}
html.theme-dark .preview-name { color: #e6eef8; }
html.theme-dark .preview-size { color: rgba(230,238,248,0.65); }
html.theme-dark .remove-preview { background: #dc2626; color: #fff; }
html.theme-dark .attach-chip { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.04); color: #e6eef8; }
html.theme-dark .attach-chip.removed { opacity:.55; }

/* Drawer / nav tabs (centered + active soft-blue) */
html.theme-dark #drawerTabs { border-bottom-color: rgba(255,255,255,0.04); }
html.theme-dark #drawerTabs .nav-link {
  color: rgba(230,238,248,0.78);
  background: transparent;
}
html.theme-dark #drawerTabs .nav-link:hover {
  background: rgba(255,255,255,0.02);
  color: #e6eef8;
}
html.theme-dark #drawerTabs .nav-link.active {
  background-color: rgba(96,165,250,0.12) !important; /* soft blue */
  color: #e6eef8 !important;
  border-bottom-color: rgba(96,165,250,0.9) !important;
  box-shadow: 0 -1px 10px rgba(96,165,250,0.06);
}

/* Ensure icons readable */
html.theme-dark #drawerTabs .nav-link i,
html.theme-dark #drawerTabs .nav-link svg { color: rgba(230,238,248,0.9); }

/* View More button */
html.theme-dark #btnViewMoreExpenses {
  background: rgba(255,255,255,0.02);
  border-color: rgba(255,255,255,0.06);
  color: #e6eef8;
  box-shadow: none;
}
html.theme-dark #btnViewMoreExpenses.primary {
  background: linear-gradient(180deg, rgba(96,165,250,0.06), rgba(96,165,250,0.02));
  border-color: rgba(96,165,250,0.18);
  color: var(--accent, #60a5fa);
  box-shadow: 0 6px 18px rgba(96,165,250,0.06);
}
html.theme-dark #btnViewMoreExpenses:hover { background: rgba(255,255,255,0.03); }

/* Chat / composer adjustments */
html.theme-dark .chat-box { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.04); }
html.theme-dark .msg .bubble { background: rgba(255,255,255,0.03); color: #e6eef8; border-color: rgba(255,255,255,0.04); }
html.theme-dark .msg.me .bubble { background: linear-gradient(90deg, rgba(96,165,250,0.14), rgba(59,130,246,0.12)); color: #071026; }

/* Minor UI tweaks for dark */
html.theme-dark .search-box input,
html.theme-dark .select-box,
html.theme-dark .form-control,
html.theme-dark .form-select {
  background: rgba(255,255,255,0.02);
  border-color: rgba(255,255,255,0.04);
  color: #e6eef8;
}
html.theme-dark .btn-secondary { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.04); color: #e6eef8; }

/* keep reduced motion respected */
@media (prefers-reduced-motion: reduce){
  html.theme-dark * { transition: none !important; animation: none !important; }
}
 
 </style>

  @stack('styles')
</head>
<body>
<div class="layout">

  <!-- ===== Left: Icon Rail ===== -->
  <aside class="rail" id="rail" aria-label="Icon rail">
    <a href="{{ $portalDashboardUrl }}" class="logo mt-1" aria-label="Home">
      <img id="railLogo" src="{{ asset('/assets/media/images/legmedlogo_small.webp') }}" alt="Logo" style="max-height:32px;width:auto;">
    </a>

    <nav class="rail-nav" role="navigation" aria-label="Primary">
      <a class="rail-btn" data-open="drawer" href="{{ $portalDashboardUrl }}" title="Dashboard" id="railDashboard">
        <i class="fa-solid fa-gauge"></i>
      </a>
      <!-- Jobs group kicker -->
      <button class="rail-btn group-kicker" data-section="jobs" type="button" title="Jobs">
        <i class="fa-solid fa-briefcase"></i>
      </button>
      <a class="rail-btn" href="{{ $portalDocumentsUrl }}" title="Documents" id="railDocuments">
        <i class="fa-regular fa-folder-open"></i>
      </a>
    </nav>

    <div class="spacer"></div>

    <div class="rail-bottom">
      <button class="rail-btn" id="toggleTheme" title="Toggle theme" aria-pressed="false">
        <i class="fa-regular fa-moon" id="themeIcon"></i>
      </button>
      <a class="rail-btn" href="#" id="logoutRail" title="Logout">
        <i class="fa fa-sign-out-alt"></i>
      </a>
      <button class="rail-btn d-lg-none" id="openDrawerMobile" title="Open menu">
        <i class="fa fa-bars"></i>
      </button>
    </div>
  </aside>

  <!-- ===== Left Drawer (nav) ===== -->
  <aside class="drawer" id="drawer" aria-label="Navigation drawer" aria-hidden="true">
    <div class="drawer-head">
      <a href="{{ $portalDashboardUrl }}" class="d-inline-flex align-items-center">
        <img id="drawerLogo" src="{{ asset('/assets/media/images/legmedlogo.png') }}" alt="Logo" style="max-height:36px;width:auto;">
      </a>
      <button class="btn btn-sm btn-light d-lg-none" id="closeDrawer" aria-label="Close drawer"><i class="fa fa-times"></i></button>
    </div>

    <div class="nav-scroll">
      <a href="{{ $portalDashboardUrl }}" class="nav-link"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
      <!-- Jobs -->
      <div class="nav-group" data-section="jobs">
        <a href="#" class="nav-link group-toggle" data-target="sm-jobs" aria-expanded="false">
          <i class="fa-solid fa-briefcase"></i><span>Jobs</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-jobs" class="submenu" role="group" aria-label="Jobs submenu">
          <a href="{{ $portalJobsUrl }}" class="nav-link">View Jobs</a>
        </div>
      </div>

      <a href="{{ $portalDocumentsUrl }}" class="nav-link" id="navDocuments">
        <i class="fa-regular fa-folder-open"></i><span>Documents</span>
      </a>
    </div>

    <div class="drawer-foot">
      <button class="theme-toggle-drawer" id="toggleThemeDrawer" type="button">
        <i class="fa-regular fa-moon" id="themeIconDrawer"></i>
        <span>Toggle Theme</span>
      </button>

      <div class="login-state">
        <i class="fa fa-circle-check" aria-hidden="true"></i>
        <span>Logged in</span>
      </div>
      <a href="#" id="logoutDrawer" class="auth-link">
        <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>

  <!-- Shared overlays -->
  <div id="overlay" class="overlay" aria-hidden="true"></div>
  <div id="notifOverlay" class="overlay" aria-hidden="true"></div>

  <!-- ===== Right Panel ===== -->
  <div class="panel" id="panel">
    <header class="admin-header">
      <button class="btn btn-link d-lg-none" id="openDrawerMobileTop" aria-label="Open navigation"><i class="fa fa-bars fs-4"></i></button>

      <div class="ms-auto d-flex align-items-center gap-3 me-2" style="position:relative;">
        <!-- 🔔 Notification Bell opens Drawer -->
        <a href="#" id="notifBellOpen"
           class="position-relative d-flex align-items-center justify-content-center"
           aria-controls="notifDrawer" aria-expanded="false" aria-label="Notifications"
           style="width:36px;height:36px;color:var(--primary-color);text-decoration:none;">
          <i class="fa-regular fa-bell fa-lg"></i>
          <span id="notifBadge"
                class="position-absolute translate-middle badge rounded-pill bg-danger"
                style="top:6px; right:-2px; display:none; font-size:10px; line-height:1; padding:.2rem .35rem;">
            0
          </span>
        </a>

        <!-- Profile -->
        <!-- Profile (anchor kept) -->
<div class="dropdown">
  <a href="#"
     class="ah_usericon"
     id="userDropdown"
     data-bs-toggle="dropdown"
     data-bs-auto-close="outside"
     aria-expanded="false"
     aria-label="User menu"
     role="button"
     style="border:2px solid var(--primary-color)!important;border-radius:50%;
            width:36px;height:36px;display:flex;align-items:center;justify-content:center;
            color:var(--primary-color); text-decoration:none;">
    <i class="fa fa-user" aria-hidden="true"></i>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown" id="userDropdownMenu">
    <li class="dropdown-item-text small text-muted ps-3">Signed in</li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i>Profile</a></li>
    <!-- <li><a class="dropdown-item" href="/assignee/settings"><i class="fa fa-gear me-2"></i>Settings</a></li> -->
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="#" id="logoutHeader"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
  </ul>
</div>

      </div>
    </header>

    <main class="main-content">
      @yield('content')
    </main>
  </div>
</div>

<!-- ===== Notification Drawer (right) ===== -->
<aside class="notif-drawer" id="notifDrawer" aria-hidden="true" aria-label="Notifications">
  <div class="nd-head">
    <strong class="me-2">Notifications</strong>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-sm btn-outline-secondary" id="notifMarkAllReadBtn">Mark all read</button>
      <button class="btn btn-sm btn-light" id="notifCloseBtn" aria-label="Close"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <div class="nd-body" id="notifList">
    <div class="p-3 text-center text-muted small">Loading…</div>
  </div>
  <div class="nd-foot">
    <a href="{{ $portalNotificationsUrl }}" class="btn btn-sm btn-primary" id="notifViewAllBtn">View all</a>
    <button class="btn btn-sm btn-outline-secondary" id="notifRefreshBtn">Refresh</button>
  </div>
</aside>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
@yield('scripts')

{{-- Optional: expose user to JS (helps if guard differs) --}}
<script>
  window.APP_USER = {
    id: {{ auth()->id() ?? 'null' }},
    role: @json(optional(auth()->user())->role)
  };

</script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const body    = document.body;
  const rail    = document.getElementById('rail');
  const drawer  = document.getElementById('drawer');
  const panel   = document.getElementById('panel');
  const overlay = document.getElementById('overlay');

  const openDrawerMobile     = document.getElementById('openDrawerMobile');
  const openDrawerMobileTop  = document.getElementById('openDrawerMobileTop');
  const closeDrawerBtn       = document.getElementById('closeDrawer');

  const THEME_KEY   = @json($portalThemeKey);
  const themeIcon   = document.getElementById('themeIcon');
  const themeIconDrawer = document.getElementById('themeIconDrawer');
  const toggleTheme = document.getElementById('toggleTheme');
  const toggleThemeDrawer = document.getElementById('toggleThemeDrawer');

  const railLogoMask   = document.getElementById('railLogoMask');
  const drawerLogoMask = document.getElementById('drawerLogoMask');

  const isFinePointer = () => window.matchMedia && window.matchMedia('(pointer:fine)').matches;
  const isDesktop     = () => window.matchMedia && window.matchMedia('(min-width: 992px)').matches;

  const add = (el, cls)=> el && el.classList.add(cls);
  const rem = (el, cls)=> el && el.classList.remove(cls);
  const tog = (el, cls, on)=> el && el.classList.toggle(cls, on);
  
  function lockScroll(on){ body.classList.toggle('no-scroll', !!on); }
  function setOverlay(on){
    tog(overlay, 'active', on);
    overlay.setAttribute('aria-hidden', on ? 'false' : 'true');
    lockScroll(on);
  }

  function openDrawerDesktop(){
    add(drawer,'open'); drawer.setAttribute('aria-hidden','false');
    if(isDesktop()) add(panel,'shifted');
  }
  function closeDrawerDesktop(){
    rem(drawer,'open'); drawer.setAttribute('aria-hidden','true'); rem(panel,'shifted');
  }
  function openNavMobile(){ add(drawer,'open'); drawer.setAttribute('aria-hidden','false'); setOverlay(true); }
  function closeNavMobile(){ rem(drawer,'open'); drawer.setAttribute('aria-hidden','true'); setOverlay(false); }

  openDrawerMobile?.addEventListener('click', openNavMobile);
  openDrawerMobileTop?.addEventListener('click', openNavMobile);
  closeDrawerBtn?.addEventListener('click', closeNavMobile);
  overlay?.addEventListener('click', closeNavMobile);

  // Submenu toggles
  document.querySelectorAll('.group-toggle').forEach(t=>{
    t.addEventListener('click', (e)=>{
      e.preventDefault();
      const id = t.dataset.target;
      const menu = document.getElementById(id);
      const isOpen = menu?.classList.toggle('open');
      t.classList.toggle('open', !!isOpen);
      t.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

  // Active link in drawer
  const path = window.location.pathname.replace(/\/+$/,'');
  document.querySelectorAll('.drawer .nav-link[href]').forEach(link=>{
    const href = (link.getAttribute('href')||'').replace(/\/+$/,'');
    if(href && href !== '#' && href === path){
      link.classList.add('active');
      const submenu = link.closest('.submenu');
      if(submenu){
        submenu.classList.add('open');
        const togg = submenu.previousElementSibling;
        togg?.classList.add('open');
        togg?.setAttribute('aria-expanded','true');
      }
    }
  });
  const DASHBOARD_PATH = @json(parse_url($portalDashboardUrl, PHP_URL_PATH) ?: $portalDashboardUrl);
  const DOCUMENTS_PATH = @json(parse_url($portalDocumentsUrl, PHP_URL_PATH) ?: $portalDocumentsUrl);
  if(path === DASHBOARD_PATH){ document.getElementById('railDashboard')?.classList.add('active'); }
  if(path === DOCUMENTS_PATH){ document.getElementById('railDocuments')?.classList.add('active'); }

  // Theme
  function setLogos(mode){
    const maskLight = "{{ asset('/assets/media/images/hallienzlogo_light.png') }}";
    railLogoMask?.style.setProperty('--logo', `url("${maskLight}")`);
    drawerLogoMask?.style.setProperty('--logo', `url("${maskLight}")`);
    const toSun = (mode==='dark');
    themeIcon?.classList.toggle('fa-moon', !toSun);
    themeIcon?.classList.toggle('fa-sun',  toSun);
    themeIconDrawer?.classList.toggle('fa-moon', !toSun);
    themeIconDrawer?.classList.toggle('fa-sun',  toSun);
    toggleTheme?.setAttribute('aria-pressed', toSun ? 'true' : 'false');
  }
  function applyTheme(mode){
    const isDark = (mode === 'dark');
    document.documentElement.classList.toggle('theme-dark', isDark);
    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    setLogos(mode);
    document.querySelectorAll('.table').forEach(el=> el.classList.toggle('table-dark', isDark));
  }
  const stored = localStorage.getItem(THEME_KEY);
  if(stored){ applyTheme(stored); } else {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(prefersDark ? 'dark' : 'light');
  }
  function toggleThemeNow(){
    const next = document.documentElement.classList.contains('theme-dark') ? 'light':'dark';
    applyTheme(next);
  }
  toggleTheme?.addEventListener('click', toggleThemeNow);
  toggleThemeDrawer?.addEventListener('click', toggleThemeNow);

  // Esc to close left drawer
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      if(document.getElementById('notifDrawer')?.classList.contains('open')) return; // notif drawer handles its own
      if(document.getElementById('overlay')?.classList.contains('active')) closeNavMobile();
      else closeDrawerDesktop();
    }
  });

  // Hover open/close left drawer
  let hoverTimer;
  function clearHoverTimer(){ if(hoverTimer){ clearTimeout(hoverTimer); hoverTimer=null; } }
  rail?.addEventListener('mouseenter', ()=>{ if(window.matchMedia('(pointer:fine)').matches && window.matchMedia('(min-width: 992px)').matches){ clearHoverTimer(); openDrawerDesktop(); }});
  rail?.addEventListener('mouseleave', ()=>{
    if(window.matchMedia('(pointer:fine)').matches && window.matchMedia('(min-width: 992px)').matches){
      hoverTimer = setTimeout(()=>{ if(!overlay.classList.contains('active')) closeDrawerDesktop(); }, 140);
    }
  });
  drawer?.addEventListener('mouseenter', clearHoverTimer);
  drawer?.addEventListener('mouseleave', ()=>{
    if(window.matchMedia('(pointer:fine)').matches && window.matchMedia('(min-width: 992px)').matches){
      hoverTimer = setTimeout(()=>{ if(!overlay.classList.contains('active')) closeDrawerDesktop(); }, 140);
    }
  });

  // Rail group kickers
  document.querySelectorAll('.group-kicker').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      openDrawerDesktop();
      const section = btn.getAttribute('data-section');
      const group = document.querySelector(`.nav-group[data-section="${section}"]`);
      const toggle = group?.querySelector('.group-toggle');
      const submenuId = toggle?.getAttribute('data-target');
      if(submenuId){
        const sm = document.getElementById(submenuId);
        sm?.classList.add('open');
        toggle?.classList.add('open');
        toggle?.setAttribute('aria-expanded','true');
      }
    });
  });
  // Logout (assignee-first)
  async function doLogout(){
    Swal.fire({title:'Logging out...', didOpen:()=>Swal.showLoading(), allowOutsideClick:false});
    const endpoints = [@json($portalLogoutApi), '/api/assignedpeople/logout', '/api/admin/logout', '/api/logout'];
    const token = sessionStorage.getItem('token');
    try{
      let ok = false, lastErr = null;
      for(const url of endpoints){
        try{
          const res = await fetch(url, { method:'POST', headers: { 'Authorization': token ? `Bearer ${token}` : '' }});
          if(res.ok){ ok = true; break; }
          lastErr = new Error(`Logout failed at ${url} (${res.status})`);
        }catch(e){ lastErr = e; }
      }
      Swal.close();
      if(!ok && lastErr) console.warn(lastErr);
      await Swal.fire({ icon:'success', title:'Logged out', timer:1000, showConfirmButton:false });
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('role');
      localStorage.removeItem('token');
      localStorage.removeItem('type');
      window.location.href = '/';
    }catch(err){
      Swal.close();
      Swal.fire('Error', err.message || 'Unable to logout', 'error');
    }
  }
  document.getElementById('logoutRail')  ?.addEventListener('click', e=>{ e.preventDefault(); doLogout(); });
  document.getElementById('logoutDrawer')?.addEventListener('click', e=>{ e.preventDefault(); doLogout(); });
  document.getElementById('logoutHeader')?.addEventListener('click', e=>{ e.preventDefault(); doLogout(); });

  // Resize behavior
  window.addEventListener('resize', ()=>{
    if(!window.matchMedia('(min-width: 992px)').matches){
      rem(panel,'shifted');
      if(!overlay.classList.contains('active')) closeDrawerDesktop();
    } else {
      if(overlay.classList.contains('active')) {
        // left nav overlay only
        // (notification drawer uses its own overlay)
      }
      if(drawer.classList.contains('open')) add(panel,'shifted'); else rem(panel,'shifted');
    }
  });
});

/* ===========================
   Notifications — Drawer
   =========================== */
(function(){
  const NOTIF_HISTORY_URL = @json($portalNotificationsUrl);
  const FORCE_ROLE_FILTER = false; // set true only if you require strict role scoping

  // Elements
  const notifDrawer   = document.getElementById('notifDrawer');
  const notifOverlay  = document.getElementById('notifOverlay');
  const notifOpenBtn  = document.getElementById('notifBellOpen');
  const notifCloseBtn = document.getElementById('notifCloseBtn');
  const notifListEl   = document.getElementById('notifList');
  const notifBadge    = document.getElementById('notifBadge');
  const notifRefresh  = document.getElementById('notifRefreshBtn');
  const notifMarkAll  = document.getElementById('notifMarkAllReadBtn');
  const notifViewAll  = document.getElementById('notifViewAllBtn');

  // Helpers
  function getRoleString(raw){
    if(!raw) return '';
    if(typeof raw === 'string') return raw;
    if(typeof raw === 'object') return raw.slug || raw.name || raw.code || '';
    return '';
  }
  function apiHeaders(){
    const token = sessionStorage.getItem('token');
    const h = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    if(token) h['Authorization'] = `Bearer ${token}`;
    return h;
  }
  async function fetchWithFallback(method, path, body){
    const candidates = (p)=>{ if(!p.startsWith('/')) p='/'+p; return ['/api'+p, p]; };
    let lastErr;
    for(const url of candidates(path)){
      try{
        const res = await fetch(url, {
          method,
          headers: apiHeaders(),
          body: body ? JSON.stringify(body) : undefined,
          credentials: 'same-origin',
        });
        if(!res.ok){
          if([404,405].includes(res.status)){ lastErr = new Error(`HTTP ${res.status}`); continue; }
          throw new Error(`HTTP ${res.status}`);
        }
        const ct = res.headers.get('content-type') || '';
        if(!ct.includes('application/json')) throw new Error('Non-JSON response');
        return await res.json();
      }catch(e){ lastErr = e; }
    }
    throw lastErr || new Error('Request failed');
  }
  const apiGet    = (p)=> fetchWithFallback('GET', p);
  const apiPost   = (p,b)=> fetchWithFallback('POST', p, b);
  const apiPatch  = (p,b)=> fetchWithFallback('PATCH', p, b);
  const apiDelete = (p)=> fetchWithFallback('DELETE', p);

  function buildBaseQuery(limit = 100){
    const qp = new URLSearchParams();
    const uid = Number((window.APP_USER?.id ?? 0)) || 0;
    const role = getRoleString(window.APP_USER?.role);
    qp.set('limit', String(Math.min(100, Math.max(10, limit))));
    if(uid > 0) qp.set('user_id', String(uid));
    if(FORCE_ROLE_FILTER && role) qp.set('role', role);
    return qp;
  }

  async function fetchUnreadCount(){
    const qp = buildBaseQuery(10);
    const res = await apiGet(`/notifications/unread-count?${qp.toString()}`);
    return Number(res?.unread ?? 0);
  }
  async function fetchNotifications({ onlyUnread=false, limit=100 }={}){
    const qp = buildBaseQuery(limit);
    if(onlyUnread) qp.set('unread','1');      // controller expects boolean('unread')
    // To match unread-count (active only), uncomment:
    // qp.set('status','active');
    const res = await apiGet(`/notifications/my?${qp.toString()}`);
    return Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
  }

  // Render
  function formatWhen(ts){
    try{ const d = new Date(ts); return isNaN(d)?'':d.toLocaleString(); }catch{ return ''; }
  }
  function priorityBadge(p){
    const map = { urgent:'danger', high:'warning', normal:'secondary', low:'secondary' };
    const cls = map[(p||'normal')] || 'secondary';
    return `<span class="badge bg-${cls} text-uppercase" style="font-size:.65rem;">${(p||'normal')}</span>`;
  }
  function renderNotificationsInto(el, items){
    if(!el) return;
    if(!Array.isArray(items) || !items.length){
      el.innerHTML = `<div class="p-3 text-center text-muted small">No notifications</div>`;
      return;
    }
    const uid  = Number((window.APP_USER?.id ?? 0)) || 0;
    const role = getRoleString(window.APP_USER?.role);

    el.innerHTML = items.map(n=>{
      const receivers = Array.isArray(n.receivers) ? n.receivers : [];
      const isRead = receivers.some(r => (uid ? Number(r.id)===uid : true) && (!role || r.role===role) && Number(r.read)===1);
      const title = n.title ?? 'Notification';
      const message = n.message ?? '';
      const when = formatWhen(n.created_at || n.updated_at);
      const link = n.link_url || null;
      const prio = priorityBadge(n.priority);
      return `
        <div class="d-flex align-items-start p-2 border-bottom ${isRead ? 'opacity-75' : ''}" data-id="${n.id}">
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2">
              <strong class="me-1">${title}</strong> ${prio}
            </div>
            <div class="small text-muted mt-1">${message}</div>
            <div class="d-flex gap-2 mt-2">
              <button class="btn btn-sm btn-outline-primary notif-view" data-id="${n.id}" ${link ? `data-link="${link}"` : ''} style="display:none">View</button>
              <button class="btn btn-sm btn-outline-danger notif-del" data-id="${n.id}" style="display:none">Delete</button>
            </div>
            <div class="small text-muted mt-1">${when}</div>
          </div>
        </div>
      `;
    }).join('');

    // wire actions
    el.querySelectorAll('.notif-del').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        e.preventDefault();
        const id = Number(btn.dataset.id);
        try{
          await apiDelete(`/notifications/${id}`);
          btn.closest('[data-id]')?.remove();
          await refreshUnreadBadge();
        }catch(err){
          console.error(err); Swal.fire?.('Error','Delete failed','error');
        }
      });
    });

    el.querySelectorAll('.notif-view').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        e.preventDefault();
        const id = Number(btn.dataset.id);
        const link = btn.getAttribute('data-link');
        const body = {};
        const uid = Number((window.APP_USER?.id ?? 0)) || 0;
        const role = getRoleString(window.APP_USER?.role);
        if(uid) body.user_id = uid;
        if(FORCE_ROLE_FILTER && role) body.role = role;
        body.read = true;
        try{
          await apiPatch(`/notifications/${id}/read`, body);
          btn.closest('[data-id]')?.classList.add('opacity-75');
          await refreshUnreadBadge();
          if(link) window.location.href = link;
        }catch(err){
          console.error(err);
          if(link) window.location.href = link; // still follow link even if markRead fails
        }
      });
    });
  }

  // Badge
  async function refreshUnreadBadge(){
    if(!notifBadge) return;
    try{
      const count = await fetchUnreadCount();
      if(count > 0){
        notifBadge.style.display = 'inline-block';
        notifBadge.textContent = count > 99 ? '99+' : String(count);
      }else{
        notifBadge.style.display = 'none';
        notifBadge.textContent = '0';
      }
    }catch(e){
      console.warn('[badge] fallback compute', e);
      const items = await fetchNotifications({ onlyUnread:true, limit:100 });
      const c = items.length;
      notifBadge.style.display = c>0 ? 'inline-block' : 'none';
      notifBadge.textContent = c>99 ? '99+' : String(c);
    }
  }

  // Drawer open/close
  function openNotifDrawer(){
    notifDrawer?.classList.add('open');
    notifDrawer?.setAttribute('aria-hidden','false');
    notifOverlay?.classList.add('active');
    notifOverlay?.setAttribute('aria-hidden','false');
    // load content
    loadDropdownList();
  }
  function closeNotifDrawer(){
    notifDrawer?.classList.remove('open');
    notifDrawer?.setAttribute('aria-hidden','true');
    notifOverlay?.classList.remove('active');
    notifOverlay?.setAttribute('aria-hidden','true');
  }

  async function loadDropdownList(){
    if(notifListEl) notifListEl.innerHTML = `<div class="p-3 text-center text-muted small">Loading…</div>`;
    const items = await fetchNotifications({ onlyUnread:true, limit:100 });
    renderNotificationsInto(notifListEl, items);
  }

  // Wire events
  notifOpenBtn?.addEventListener('click', (e)=>{ e.preventDefault(); openNotifDrawer(); });
  notifCloseBtn?.addEventListener('click', (e)=>{ e.preventDefault(); closeNotifDrawer(); });
  notifOverlay?.addEventListener('click', closeNotifDrawer);
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && notifDrawer?.classList.contains('open')) closeNotifDrawer(); });

  notifRefresh?.addEventListener('click', async ()=>{ await loadDropdownList(); await refreshUnreadBadge(); });
  notifMarkAll?.addEventListener('click', async ()=>{
    try{
      const body = {};
      const uid = Number((window.APP_USER?.id ?? 0)) || 0;
      const role = getRoleString(window.APP_USER?.role);
      if(uid) body.user_id = uid;
      if(FORCE_ROLE_FILTER && role) body.role = role;
      await apiPost('/notifications/mark-all-read', body);
      await loadDropdownList();
      await refreshUnreadBadge();
      Swal.fire?.({ icon:'success', title:'Marked all as read', timer:900, showConfirmButton:false });
    }catch(e){
      console.error(e);
      Swal.fire?.('Error','Could not mark all as read','error');
    }
  });

  // View all route (exact same behavior pattern as admin)
  if(notifViewAll){ notifViewAll.setAttribute('href', NOTIF_HISTORY_URL); }

  // Initial badge
  refreshUnreadBadge();
})();
</script>

<!-- Keep active link highlight -->
<script>
(function(){
  const path = window.location.pathname.replace(/\/+$/, '');
  const normalize = (s)=> (s || '').replace(/\/+$/, '');
  document.querySelectorAll('.drawer .nav-link[href]').forEach(link => {
    const href = normalize(link.getAttribute('href'));
    if (href && href !== '#' && href === path) {
      link.classList.add('active');
      const submenu = link.closest('.submenu');
      if (submenu) {
        submenu.classList.add('open');
        const group = submenu.closest('.nav-group');
        const toggle = group?.querySelector('.group-toggle');
        toggle?.classList.add('open','active');
        toggle?.setAttribute('aria-expanded','true');
        const section = group?.getAttribute('data-section');
        document.querySelector(`.rail .group-kicker[data-section="${section}"]`)?.classList.add('active');
      }
    }
  });
  if (path === DASHBOARD_PATH) { document.getElementById('railDashboard')?.classList.add('active'); }
  if (path === DOCUMENTS_PATH) { document.getElementById('railDocuments')?.classList.add('active'); }
})();
</script>
</body>
</html>
