<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>@yield('title','Dashboard — Structure 2 (Hallienz)')</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">

  <!-- Bootstrap / Icons / SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>

  <!-- Main theme (tokens come from here) -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">
  <!-- Shared UI components -->
  <link rel="stylesheet" href="{{ asset('/assets/css/common/components.css') }}">

  <!-- Structure 2 (refined) -->
  <style>
     :root{
      --radius: var(--radius-md,12px);
      --rail-w: 78px;
      --drawer-w: 292px;
      --elev-1: var(--shadow-sm, 0 1px 2px rgba(0,0,0,.06));
      --elev-2: 0 6px 20px rgba(0,0,0,.12);
      --ease: cubic-bezier(.2,.7,.2,1);
    }

    /* base */
    body{
      margin:0;
      font-family: var(--font-sans, Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif);
      background: var(--bg-body, var(--light-color,#f5f7fa));
      color: var(--text-color,#0f172a);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
    }
    body.no-scroll{ overflow:hidden; }
    .layout{ display:grid; grid-template-columns: var(--rail-w) 1fr; min-height:100svh; }

    /* ===== REMOS-STYLE: Dark Navy Rail ===== */
    .rail{
      position:sticky; top:0; height:100svh;
      background: #1b2240;
      border-right: none;
      display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 10px;
      z-index:1001;
    }
    .rail .logo{ width:48px; height:48px; display:flex; align-items:center; justify-content:center; margin-bottom:4px; }
    .rail .rail-nav{ display:flex; flex-direction:column; gap:4px; margin-top:6px; width:100%; }
    .rail .rail-btn{
      width:100%; height:42px; border:0; background:transparent; color:rgba(255,255,255,.55);
      border-radius:10px; display:flex; align-items:center; justify-content:center;
      transition: background .18s var(--ease), color .18s var(--ease), transform .12s var(--ease);
      position:relative; outline:none;
    }
    .rail .rail-btn:hover{ background: rgba(255,255,255,.1); color:#fff; }
    .rail .rail-btn:hover .fa{ color: #fff; }
    .rail .rail-btn:focus-visible{ outline:2px solid rgba(255,255,255,.4); outline-offset:2px; }
    .rail .rail-btn.active{
      background: rgba(35,119,252,.28);
      color: #fff;
    }
    .rail .rail-btn.active::before{
      content:""; position:absolute; left:0; top:8px; bottom:8px; width:3px;
      border-radius:0 4px 4px 0; background: var(--primary-color,#2377fc);
    }
    .rail .rail-btn .fa{ font-size:15px; color: rgba(255,255,255,.55); transition: color .18s var(--ease); }
    .rail .rail-btn.active .fa{ color: #fff; }
    .rail .spacer{ flex:1; }
    .rail .rail-bottom{ display:flex; flex-direction:column; gap:4px; width:100%; padding-top:8px; border-top:1px solid rgba(255,255,255,.08); }

    /* ===== REMOS-STYLE: Dark Navy Drawer ===== */
    .drawer{
      position:fixed; top:0; left:var(--rail-w); height:100svh; width:var(--drawer-w);
      background:#222849; border-right: none;
      box-shadow: 4px 0 24px rgba(0,0,0,.22);
      transform: translateX(-100%); opacity:0; visibility:hidden;
      transition: transform .16s var(--ease), opacity .16s var(--ease), visibility .16s var(--ease);
      z-index:1000; display:flex; flex-direction:column;
    }
    .drawer.open{ transform:none; opacity:1; visibility:visible; }
    .drawer[aria-hidden="true"]{ pointer-events:none; }
    /* CSS hover — ensures drawer opens reliably on hover */
    .rail:hover ~ .drawer,
    .drawer:hover {
      transform: none !important;
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
    }
    /* Shift panel content when sidebar is hover-open */
    .rail:hover ~ .panel,
    .drawer:hover ~ .panel {
      margin-left: var(--drawer-w);
    }

    .drawer .drawer-head{
      padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.06);
      display:flex; align-items:center; justify-content:space-between; gap:8px;
    }
    .fa-times{margin:0 !important;}

    .drawer .nav-scroll{ flex:1; overflow:auto; padding:10px 10px 14px; display:flex; flex-direction:column; gap:2px; }
    .drawer .nav-scroll::-webkit-scrollbar{ width:4px; }
    .drawer .nav-scroll::-webkit-scrollbar-thumb{ background: rgba(255,255,255,.12); border-radius:4px; }
    .drawer .nav-section-title{
      padding: 16px 12px 5px; font-size: 10px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1.2px; color: rgba(255,255,255,.35);
    }

    /* Primary links + submenu */
    .drawer .nav-link{
      display:flex; align-items:center; gap:10px;
      padding: 9px 12px; border-radius:10px; color:rgba(255,255,255,.72);
      text-decoration:none; transition: background .14s var(--ease), color .14s var(--ease);
      border:none !important; position:relative;
      font-size:13px; font-weight:500;
    }
    .drawer .nav-link i{ color: rgba(255,255,255,.45); min-width:16px; text-align:center; font-size: 13px; transition: color .14s var(--ease); }
    .drawer .nav-link:hover{ background: rgba(255,255,255,.07); color: #fff; }
    .drawer .nav-link:hover i{ color: rgba(255,255,255,.85); }
    .drawer .nav-link:focus-visible{ outline:2px solid rgba(255,255,255,.3); outline-offset:2px; }
    .drawer .nav-link.active{
      background: rgba(35,119,252,.25);
      color: #fff; font-weight:600;
    }
    .drawer .nav-link.active i{ color: #fff; }
    .drawer .nav-link.active::before{
      content:""; position:absolute; left:0; top:6px; bottom:6px; width:3px; border-radius:0 4px 4px 0;
      background: var(--primary-color,#2377fc);
    }

    .nav-group{ display:flex; flex-direction:column; gap:2px; }
    .group-toggle{ display:flex; align-items:center; gap:10px; cursor:pointer; user-select:none; }
    .group-toggle .chev{ margin-left:auto; color:rgba(255,255,255,.35); transition: transform .18s var(--ease); font-size: 10px; }
    .group-toggle.open .chev{ transform: rotate(180deg); color: rgba(255,255,255,.75); }

    .submenu{
      display:none; flex-direction:column; gap:1px; margin-left: 12px; padding-left: 10px;
      border-left: 1.5px solid rgba(255,255,255,.08); margin-top: 2px;
    }
    .submenu.open{ display:flex; animation:fadeIn .2s var(--ease); }
    .submenu .nav-link{ font-size:12px; padding: 7px 10px 7px 12px; border-radius:8px; font-weight: 400; color:rgba(255,255,255,.6); }
    .submenu .nav-link::before{ display:none; }
    .submenu .nav-link:hover{ color:#fff; background: rgba(255,255,255,.07); }
    .submenu .nav-link.active{ background: rgba(35,119,252,.2); color:#fff; font-weight:600; }

    /* Pin button */
    .drawer-pin-btn{
      width:30px; height:30px; border-radius: 8px;
      border: 1px solid rgba(255,255,255,.12); background: transparent;
      color: rgba(255,255,255,.45); display:inline-flex; align-items:center; justify-content:center;
      cursor: pointer; transition: var(--transition); font-size: 12px;
    }
    .drawer-pin-btn:hover{ background: rgba(255,255,255,.1); color: #fff; }
    .drawer-pin-btn.pinned{
      background: rgba(35,119,252,.25);
      color: #fff; border-color: rgba(35,119,252,.4);
    }
    .drawer-pin-btn.pinned i{ transform: rotate(-25deg); }
    .drawer-pin-btn i{ transition: transform .2s var(--ease); margin: 0 !important; }

    .layout.is-pinned .panel{ margin-left: var(--drawer-w); }
    .layout.is-pinned .drawer{ box-shadow: none; }

    @keyframes fadeIn{ from{opacity:0; transform:translateY(-4px);} to{opacity:1; transform:translateY(0);} }

    /* ===== Right side ===== */
    .panel{ min-width:0; display:flex; flex-direction:column; min-height:100svh; transition: margin-left .22s var(--ease); }
    .panel.shifted{ margin-left: var(--drawer-w); }

    .admin-header{
      min-height:62px; background:#fff; border-bottom:1px solid var(--border-color,#e5e7eb);
      position:sticky; top:0; z-index:900; display:flex; align-items:center; gap:10px; padding:0 20px;
      box-shadow: 0 1px 0 var(--border-color,#e5e7eb);
    }
    .admin-header .btn.btn-link{ color: var(--text-color); text-decoration:none; }
    .admin-header .btn.btn-link:hover{ color: var(--accent-color); }
    .admin-header h6{ margin:0; font-family: var(--font-head); font-weight:600; }

    /* Theme toggle in header */
    .header-theme-toggle{
      width:36px; height:36px; border-radius:10px; border:1px solid var(--border-color,#e5e7eb);
      background:transparent; color:var(--text-color); display:inline-flex; align-items:center;
      justify-content:center; cursor:pointer; transition: var(--transition); font-size:15px;
    }
    .header-theme-toggle:hover{ background: var(--light-color,#f4f7fb); color:var(--primary-color); border-color: rgba(35,119,252,.3); }
    .header-theme-toggle i{ pointer-events:none; transition: transform .3s var(--ease); }
    .header-theme-toggle:hover i{ transform: rotate(20deg); }

    .page-head{
      display:flex; align-items:center; justify-content:space-between; gap:8px;
      background:linear-gradient(180deg, #ffffff, #f8fbff); border:1px solid var(--border-color); border-radius: 18px;
      padding:14px 16px; margin:14px;
      box-shadow: var(--shadow-sm);
    }
    .page-head .actions{ display:flex; gap:8px; flex-wrap:wrap; }

    .main-content{ flex:1; padding: 0 12px 24px; }

    /* ===== Overlay under drawer (mobile) ===== */
    .overlay{
      position:fixed; inset:0; background: rgba(0,0,0,.45);
      z-index: 950; opacity:0; visibility:hidden; transition: .18s var(--ease); backdrop-filter: blur(2px);
    }
    .overlay.active{ opacity:1; visibility:visible; }

    /* ===== Mobile ===== */
    @media (max-width: 991px){
      .layout{ grid-template-columns: 1fr; }
      .rail{ display:none !important; }
      .drawer{ left:0; width:280px; }
      .panel{ margin-left:0 !important; }
      .admin-header .brand-title{ display:none; }
    }

    /* ===== Dark mode ===== */
    html.theme-dark{
      --bg-body:#0d1117; --text-color:#e5e7eb; --light-color:#161b27; --border-color:#1e2a3a; --surface:#1a2235;
    }
    html.theme-dark body{ background: var(--bg-body); color: var(--text-color); }
    html.theme-dark .admin-header{ background: #161b27 !important; border-bottom-color: #1e2a3a !important; }
    html.theme-dark .page-head,
    html.theme-dark .dropdown-menu,
    html.theme-dark .modal-content,
    html.theme-dark .card,
    html.theme-dark .offcanvas,
    html.theme-dark .toast{ background: var(--light-color) !important; color: var(--text-color) !important; border-color: var(--border-color) !important; }
    /* Rail + Drawer keep dark navy regardless of theme */
    .rail{ background: #1b2240 !important; }
    .drawer{ background: #222849 !important; }
    html.theme-dark .header-theme-toggle{ border-color: #1e2a3a; color:#e5e7eb; }
    html.theme-dark .header-theme-toggle:hover{ background:#1e2a3a; color:#60a5fa; }

    /* Reduce motion pref */
    @media (prefers-reduced-motion: reduce){
      *{ transition:none !important; animation:none !important; }
    }

    /* --- Logout & "Logged in" area --- */
    .drawer .drawer-foot,
    .sidebar-auth{
      margin-top:auto; border-top:1px solid rgba(255,255,255,.07);
      padding:12px; background:transparent; position:sticky; bottom:0; z-index:1;
    }

    .login-state{ display:flex; align-items:center; gap:8px; font-size:12px; color: rgba(255,255,255,.45); margin-bottom:8px; }
    .login-state .fa{ font-size:12px; color: #34d399; }

    .auth-link{
      display:flex; align-items:center; justify-content:center; gap:.5rem;
      height:36px; border:1px solid rgba(255,255,255,.12);
      border-radius:10px;
      color: rgba(255,255,255,.7);
      font-weight:600; font-size:13px; text-decoration:none;
      background: transparent;
      transition: background .15s ease, border-color .15s ease, color .15s ease, transform .08s ease;
    }
    .auth-link:hover{ background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.45); color:#fca5a5; }
    .auth-link:active{ transform: translateY(1px); }

    .drawer .drawer-foot,
    .sidebar-auth{ padding-bottom: calc(12px + env(safe-area-inset-bottom, 0)); }
    .ah_usericon:hover { background: var(--primary-color); color: var(--surface) !important;}

    .fa-bars { margin: 0 !important; }


    /* ===========================
       Notifications Drawer (Right)
       =========================== */
    .notif-drawer {
      position: fixed; top: 0; right: 0; height: 100svh; width: 360px;
      background: #fff; border-left: 1px solid var(--border-color, #e5e7eb);
      box-shadow: 0 8px 30px rgba(0,0,0,.18);
      transform: translateX(100%); opacity: 0; visibility: hidden;
      transition: transform .22s var(--ease), opacity .22s var(--ease), visibility .22s var(--ease);
      z-index: 1100; display: flex; flex-direction: column;
      backdrop-filter: saturate(1.4) blur(4px);
    }
    .notif-drawer.open { transform: none; opacity: 1; visibility: visible; }
    .notif-drawer[aria-hidden="true"] { pointer-events: none; }
    .notif-drawer-head {
      padding: 12px 14px; border-bottom: 1px solid var(--border-color, #e5e7eb);
      display: flex; align-items: center; justify-content: space-between; background: #fff;
    }
    .notif-drawer-list { flex: 1; overflow: auto; padding: 8px; background: var(--bg-body, #fafbfc); }
    .notif-drawer-foot { border-top: 1px solid var(--border-color, #e5e7eb); padding: 10px; background: #fff; position: sticky; bottom: 0; }
    .notif-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.45);
      z-index: 1050; opacity: 0; visibility: hidden; transition: .18s var(--ease); backdrop-filter: blur(2px);
    }
    .notif-overlay.active { opacity: 1; visibility: visible; }
    .notif-item { border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; box-shadow: var(--elev-1); }
    .notif-item + .notif-item { margin-top: 8px; }

    html.theme-dark .notif-drawer,
    html.theme-dark .notif-drawer-head,
    html.theme-dark .notif-drawer-foot { background: var(--light-color, #161b27); border-color: var(--border-color, #1e2a3a); }
    html.theme-dark .notif-drawer-list { background: var(--bg-body, #0d1117); }
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
<div class="layout" id="layoutRoot">
  <!-- ===== Left: Icon Rail ===== -->
  <aside class="rail" id="rail" aria-label="Icon rail">
    <a href="/dashboard" class="logo mt-1" aria-label="Home">
      <img id="railLogo" src="{{ asset('/assets/media/images/legmedlogo_small.webp') }}" alt="Logo" style="max-height:32px;width:auto;">
    </a>

    <nav class="rail-nav" role="navigation" aria-label="Primary">
      <a class="rail-btn" data-open="drawer" href="/dashboard" title="Dashboard" id="railDashboard">
        <i class="fa-solid fa-gauge"></i>
      </a>
      <!-- Kickers -->
      <button class="rail-btn group-kicker" data-section="clients" type="button" title="Client"><i class="fa-solid fa-users"></i></button>
      <button class="rail-btn group-kicker" data-section="doc-types" type="button" title="Document Types"><i class="fa-regular fa-file-lines"></i></button>
      <button class="rail-btn group-kicker" data-section="documents" type="button" title="Documents"><i class="fa-regular fa-folder-open"></i></button>
      <button class="rail-btn group-kicker" data-section="jobs" type="button" title="Jobs"><i class="fa-solid fa-briefcase"></i></button>
      <button class="rail-btn group-kicker" data-section="assigned-people" type="button" title="Assigned People"><i class="fa-solid fa-user-tag"></i></button>
      <button class="rail-btn group-kicker" data-section="accounting" type="button" title="Accounting"><i class="fa-solid fa-file-invoice-dollar"></i></button>
      <button class="rail-btn group-kicker" data-section="expense-heads" type="button" title="Expense Heads">
  <i class="fa-solid fa-receipt"></i>
</button>

      <button class="rail-btn group-kicker" data-section="settings" type="button" title="Settings"><i class="fa-solid fa-gear"></i></button>
      
    </nav>

    <div class="spacer"></div>

    <div class="rail-bottom">
      <a class="rail-btn" href="#" id="logoutRail" title="Logout"><i class="fa fa-sign-out-alt"></i></a>
      <button class="rail-btn d-lg-none" id="openDrawerMobile" title="Open menu"><i class="fa fa-bars"></i></button>
    </div>
  </aside>

  <!-- ===== Drawer (labels + submenus) ===== -->
  <aside class="drawer" id="drawer" aria-label="Navigation drawer" aria-hidden="true">
    <div class="drawer-head">
      <a href="/dashboard" class="d-inline-flex align-items-center">
        <img id="drawerLogo" src="{{ asset('/assets/media/images/legmedlogo.png') }}" alt="Logo" style="max-height:34px;width:auto;">
      </a>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="drawer-pin-btn d-none d-lg-inline-flex" id="drawerPinBtn"
                title="Keep sidebar open" aria-label="Pin sidebar" aria-pressed="false">
          <i class="fa-solid fa-thumbtack"></i>
        </button>
        <button class="drawer-pin-btn d-lg-none" id="closeDrawer" aria-label="Close drawer"><i class="fa fa-times"></i></button>
      </div>
    </div>

    <div class="nav-scroll">
      <div class="nav-section-title">Main</div>
      <a href="/dashboard" class="nav-link"><i class="fa-solid fa-gauge"></i><span>Dashboard</span></a>

      <div class="nav-section-title">Clients</div>
      <div class="nav-group" data-section="clients">
        <a href="#" class="nav-link group-toggle" data-target="sm-clients" aria-expanded="false">
          <i class="fa-solid fa-users"></i><span>Clients</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-clients" class="submenu" role="group" aria-label="Clients submenu">
          <a href="/admin/client/add" class="nav-link">Create Client</a>
          <a href="/admin/client/manage" class="nav-link">Client Directory</a>
          <a href="/admin/client-users/manage" class="nav-link">Client Contacts</a>
        </div>
      </div>

      <div class="nav-group" data-section="doc-types">
        <a href="#" class="nav-link group-toggle" data-target="sm-doc-types" aria-expanded="false">
          <i class="fa-regular fa-file-lines"></i><span>Document Types</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-doc-types" class="submenu" role="group" aria-label="Document Types submenu">
          <a href="/admin/document-types/create" class="nav-link">Create Type</a>
          <a href="/admin/document-types" class="nav-link">Type Library</a>
        </div>
      </div>

      <div class="nav-group" data-section="documents">
        <a href="#" class="nav-link group-toggle" data-target="sm-documents" aria-expanded="false">
          <i class="fa-regular fa-folder-open"></i><span>Documents</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-documents" class="submenu" role="group" aria-label="Documents submenu">
          <a href="/documents/upload" class="nav-link">Upload Document</a>
          <a href="/admin/documents" class="nav-link">Document Library</a>
        </div>
      </div>

      <div class="nav-section-title">Jobs</div>
      <div class="nav-group" data-section="jobs">
        <a href="#" class="nav-link group-toggle" data-target="sm-jobs" aria-expanded="false">
          <i class="fa-solid fa-briefcase"></i><span>Jobs</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-jobs" class="submenu" role="group" aria-label="Jobs submenu">
          <a href="/admin/jobs/add" class="nav-link">Create Job</a>
          <a href="/admin/jobs/view" class="nav-link">Job Directory</a>
          <a href="/job-expense/claim/manage" class="nav-link">Expense Claims</a>

        </div>
      </div>

      <div class="nav-group" data-section="assigned-people">
        <a href="#" class="nav-link group-toggle" data-target="sm-assigned-people" aria-expanded="false">
          <i class="fa-solid fa-user-tag"></i><span>Team</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-assigned-people" class="submenu" role="group" aria-label="Team submenu">
          <a href="/admin/assignedpeople/manage" class="nav-link">Team Directory</a>
        </div>
      </div>

      <div class="nav-section-title">Accounting</div>
      <div class="nav-group" data-section="accounting">
        <a href="#" class="nav-link group-toggle" data-target="sm-accounting" aria-expanded="false">
          <i class="fa-solid fa-file-invoice-dollar"></i><span>Accounting</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-accounting" class="submenu" role="group" aria-label="Accounting submenu">
          <a href="/admin/accounting/client-bills" class="nav-link">Client Bills</a>
          <a href="/admin/accounting/repayments" class="nav-link">Repayments</a>
          <a href="/admin/accounting/bill-heads/create" class="nav-link">Create Bill Head</a>
          <a href="/admin/accounting/bill-heads/manage" class="nav-link">Bill Head Library</a>
          <a href="/admin/accountant-users/manage" class="nav-link">Accountants</a>
        </div>
      </div>

      <div class="nav-group" data-section="expense-heads">
        <a href="#" class="nav-link group-toggle" data-target="sm-expense-heads" aria-expanded="false">
          <i class="fa-solid fa-receipt"></i><span>Expense Heads</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-expense-heads" class="submenu" role="group" aria-label="Expense Heads submenu">
          <a href="/admin/expenseHead/create" class="nav-link">Create Expense Head</a>
          <a href="/admin/expenseHead/manage" class="nav-link">Expense Head Library</a>
        </div>
      </div>

      <div class="nav-section-title">System</div>
      <div class="nav-group" data-section="settings">
        <a href="#" class="nav-link group-toggle" data-target="sm-settings" aria-expanded="false">
          <i class="fa-solid fa-gear"></i><span>Settings</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="sm-settings" class="submenu" role="group" aria-label="Settings submenu">
          <a href="/admin/mailer" class="nav-link">Mailer</a>
          <a href="/admin/logs" class="nav-link">Activity Logs</a>
        </div>
      </div>
    </div>

    <div class="drawer-foot">
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

  <div id="overlay" class="overlay" aria-hidden="true"></div>

  <!-- ===== Right Panel ===== -->
  <div class="panel" id="panel">
    <header class="admin-header">
      <button class="btn btn-link d-lg-none" id="openDrawerMobileTop" aria-label="Open navigation"><i class="fa fa-bars fs-4"></i></button>

      <div class="ms-auto d-flex align-items-center gap-2 me-2" style="position:relative;">

        <!-- Theme Toggle (header) -->
        <button class="header-theme-toggle" id="toggleTheme" title="Toggle theme" aria-pressed="false">
          <i class="fa-regular fa-sun" id="themeIcon"></i>
        </button>

        <!-- Notification Bell (drawer trigger + badge) -->
        <div class="notif-bell">
          <button id="openNotifDrawer"
            class="position-relative d-flex align-items-center justify-content-center"
            aria-label="Open notifications"
            style="width:36px;height:36px;color:var(--primary-color);background:transparent;border:0;">
            <i class="fa-regular fa-bell fa-lg"></i>
            <span id="notifBadge"
              class="position-absolute translate-middle badge rounded-pill bg-danger"
              style="top:6px; right:-2px; display:none; font-size:10px; line-height:1; padding:.2rem .35rem;">0</span>
          </button>
        </div>

        <!-- Profile Icon -->
        <!-- Profile Dropdown (anchor kept) -->
<div class="dropdown">
  <a href="#" 
     class="ah_usericon"
     id="userDropdown"
     data-bs-toggle="dropdown"
     data-bs-auto-close="outside"
     aria-expanded="false"
     aria-label="User menu"
     role="button"
     style="border:2px solid var(--primary-color)!important;
            border-radius:50%;
            width:36px;height:36px;
            display:flex;align-items:center;justify-content:center;
            color:var(--primary-color);">
    <i class="fa fa-user"></i>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown" style="z-index:1400; min-width:180px;">
    <li class="dropdown-item-text small text-muted ps-3">Signed in</li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i>Profile</a></li>
    <!-- <li><a class="dropdown-item" href="/admin/settings"><i class="fa fa-gear me-2"></i>Settings</a></li> -->
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

<!-- Notifications Drawer + overlay -->
<aside id="notifDrawer" class="notif-drawer" role="dialog" aria-modal="true" aria-labelledby="notifDrawerTitle" aria-hidden="true">
  <div class="notif-drawer-head">
    <strong id="notifDrawerTitle">Notifications</strong>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-outline-secondary" id="notifDrawerRefreshBtn">Refresh</button>
      <button class="btn btn-sm btn-outline-secondary" id="notifDrawerMarkAllReadBtn">Mark all read</button>
      <button class="btn btn-sm btn-light" id="closeNotifDrawer" aria-label="Close notifications"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <div id="notifDrawerList" class="notif-drawer-list">
    <div class="p-3 text-center text-muted small">Loading…</div>
  </div>
  <div class="notif-drawer-foot">
    <button id="notifViewAllBtn" class="btn btn-sm btn-primary w-100">View all</button>
  </div>
</aside>
<div id="notifOverlay" class="notif-overlay" aria-hidden="true"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
@yield('scripts')
{{-- Inject the current user for JS --}}
<script>
  window.APP_USER = {
    id: {{ auth()->id() ?? 'null' }},
    role: @json(optional(auth()->user())->role)
  };
  console.log('[AppUser]', window.APP_USER);
</script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  console.log('[Notification Init]');

  const body       = document.body;
  const layoutRoot = document.getElementById('layoutRoot');
  const rail       = document.getElementById('rail');
  const drawer     = document.getElementById('drawer');
  const panel      = document.getElementById('panel');
  const overlay    = document.getElementById('overlay');
  const pinBtn     = document.getElementById('drawerPinBtn');

  const openDrawerMobile     = document.getElementById('openDrawerMobile');
  const openDrawerMobileTop  = document.getElementById('openDrawerMobileTop');
  const closeDrawerBtn       = document.getElementById('closeDrawer');

  /* ---- Pin / lock-open (persisted) ---- */
  const PIN_KEY = 'sidebarPinned:admin';
  let isPinned  = (localStorage.getItem(PIN_KEY) === '1');
  function applyPinState(){
    if (!layoutRoot) return;
    layoutRoot.classList.toggle('is-pinned', isPinned);
    if (pinBtn){
      pinBtn.classList.toggle('pinned', isPinned);
      pinBtn.setAttribute('aria-pressed', isPinned ? 'true' : 'false');
      pinBtn.setAttribute('title', isPinned ? 'Unpin sidebar (close on hover-out)' : 'Pin sidebar open');
    }
    if (isPinned){
      drawer?.classList.add('open');
      drawer?.setAttribute('aria-hidden','false');
    }
  }
  pinBtn?.addEventListener('click', (e)=>{
    e.preventDefault();
    isPinned = !isPinned;
    localStorage.setItem(PIN_KEY, isPinned ? '1' : '0');
    applyPinState();
  });

  const THEME_KEY   = 'theme';
  const themeIcon   = document.getElementById('themeIcon');
  const themeIconDrawer = document.getElementById('themeIconDrawer');
  const toggleTheme = document.getElementById('toggleTheme');
  const toggleThemeDrawer = document.getElementById('toggleThemeDrawer');

  const railLogoMask   = document.getElementById('railLogoMask');
  const drawerLogoMask = document.getElementById('drawerLogoMask');

  // ==== Notifications: elements (drawer variant) ====
  const notifBadge            = document.getElementById('notifBadge');
  const notifDrawer           = document.getElementById('notifDrawer');
  const notifOverlay          = document.getElementById('notifOverlay');
  const openNotifDrawerBtn    = document.getElementById('openNotifDrawer');
  const closeNotifDrawerBtn   = document.getElementById('closeNotifDrawer');
  const notifDrawerList       = document.getElementById('notifDrawerList');
  const notifDrawerRefreshBtn = document.getElementById('notifDrawerRefreshBtn');
  const notifDrawerMarkAllBtn = document.getElementById('notifDrawerMarkAllReadBtn');
  const notifViewAllBtn       = document.getElementById('notifViewAllBtn');

  const CURRENT_USER    = window.APP_USER || {};
  const CURRENT_USER_ID = Number(CURRENT_USER.id || localStorage.getItem('user_id') || 0) || 0;
  const CURRENT_ROLE    = String(CURRENT_USER.role || localStorage.getItem('user_role') || '');

  // helpers
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
    add(drawer,'open');
    drawer.setAttribute('aria-hidden','false');
    if(isDesktop() && !isPinned) add(panel,'shifted');
  }
  function closeDrawerDesktop(){
    if (isPinned) return; // never auto-close while pinned
    rem(drawer,'open');
    drawer.setAttribute('aria-hidden','true');
    rem(panel,'shifted');
  }

  function openNavMobile(){
    add(drawer,'open');
    drawer.setAttribute('aria-hidden','false');
    setOverlay(true);
  }
  function closeNavMobile(){
    rem(drawer,'open');
    drawer.setAttribute('aria-hidden','true');
    setOverlay(false);
  }

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
  if(path === '/dashboard'){ document.getElementById('railDashboard')?.classList.add('active'); }

  // Theme handling
  function setLogos(mode){
    const maskLight = "{{ asset('/assets/media/images/hallienzlogo_light.png') }}";
    const use = maskLight;
    railLogoMask?.style.setProperty('--logo', `url("${use}")`);
    drawerLogoMask?.style.setProperty('--logo', `url("${use}")`);
    if(themeIcon){
      if(mode==='dark'){ themeIcon.classList.replace('fa-moon','fa-sun'); themeIcon.classList.replace('fa-regular','fa-solid'); }
      else{ themeIcon.classList.replace('fa-sun','fa-moon'); themeIcon.classList.replace('fa-solid','fa-regular'); }
    }
    toggleTheme?.setAttribute('aria-pressed', mode==='dark' ? 'true' : 'false');
  }
  function applyTheme(mode){
    const isDark = (mode === 'dark');
    document.documentElement.classList.toggle('theme-dark', isDark);
    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    setLogos(mode);
    document.querySelectorAll('.table').forEach(el=> el.classList.toggle('table-dark', isDark));
  }
  const stored = localStorage.getItem(THEME_KEY);
  if(stored){ applyTheme(stored); }
  else{
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(prefersDark ? 'dark' : 'light');
  }
  toggleTheme?.addEventListener('click', ()=>{
    const next = document.documentElement.classList.contains('theme-dark') ? 'light':'dark';
    applyTheme(next);
  });

  // Esc to close
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      // Left nav or notif drawer
      if(document.getElementById('notifOverlay')?.classList.contains('active')) closeNotifDrawer();
      else if(overlay.classList.contains('active')) closeNavMobile();
      else closeDrawerDesktop();
    }
  });

  // Apply persisted pin state
  applyPinState();

  // Hover open/close (left drawer) — disabled while pinned
  let hoverTimer;
  function clearHoverTimer(){ if(hoverTimer){ clearTimeout(hoverTimer); hoverTimer=null; } }
  rail?.addEventListener('mouseenter', ()=>{
    if(isPinned) return;
    if(isFinePointer() && isDesktop()){ clearHoverTimer(); openDrawerDesktop(); }
  });
  rail?.addEventListener('mouseleave', ()=>{
    if(isPinned) return;
    if(isFinePointer() && isDesktop()){
      hoverTimer = setTimeout(()=>{ if(!overlay.classList.contains('active')) closeDrawerDesktop(); }, 400);
    }
  });
  drawer?.addEventListener('mouseenter', clearHoverTimer);
  drawer?.addEventListener('mouseleave', ()=>{
    if(isPinned) return;
    if(isFinePointer() && isDesktop()){
      hoverTimer = setTimeout(()=>{ if(!overlay.classList.contains('active')) closeDrawerDesktop(); }, 350);
    }
  });

  // Clicking rail group icons opens drawer and reveals group
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

  // ==============================
  // Notifications: client helpers
  // ==============================
  function apiHeaders() {
    const token = sessionStorage.getItem('token');
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    return headers;
  }

  function buildUrlCandidates(path) {
    if (!path.startsWith('/')) path = '/' + path;
    return ['/api' + path, path];
  }
async function doLogout(){
    Swal.fire({title:'Logging out...', didOpen:()=>Swal.showLoading(), allowOutsideClick:false});
    const endpoints = ['/api/assignee/logout','/api/admin/logout','/api/logout'];
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

  async function fetchWithFallback(method, path, body) {
    const urls = buildUrlCandidates(path);
    let lastErr;
    for (const url of urls) {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        const res = await fetch(url, {
          method,
          headers: apiHeaders(),
          body: body ? JSON.stringify(body) : undefined,
          signal: controller.signal
        });
        clearTimeout(timeoutId);
        if (res.ok) return res.json();
        if ([404, 405].includes(res.status)) { lastErr = new Error(`HTTP ${res.status}`); continue; }
        throw new Error(`HTTP ${res.status}`);
      } catch (e) {
        if (e.name === 'AbortError') {
          lastErr = new Error('Request timeout');
        } else {
          lastErr = e;
        }
      }
    }
    throw lastErr || new Error('Request failed');
  }

  async function apiGet(path){    return fetchWithFallback('GET',    path); }
  async function apiPost(path,b){ return fetchWithFallback('POST',   path, b); }
  async function apiPatch(path,b){return fetchWithFallback('PATCH',  path, b); }
  async function apiDelete(path){ return fetchWithFallback('DELETE', path); }

  function formatWhen(ts){
    try { const d = new Date(ts); return isNaN(d) ? '' : d.toLocaleString(); }
    catch { return ''; }
  }
  function priorityBadge(p) {
    const map = { urgent:'danger', high:'warning', normal:'secondary', low:'secondary' };
    const cls = map[(p||'normal')] || 'secondary';
    return `<span class="badge bg-${cls} text-uppercase" style="font-size:.65rem;">${(p||'normal')}</span>`;
  }

  async function fetchNotifications({ onlyUnread = false, limit = 20 } = {}){
    try {
      const qp = new URLSearchParams();
      qp.set('limit', String(limit));
      if (onlyUnread) qp.set('unread','1');
      if (CURRENT_USER_ID) qp.set('user_id', String(CURRENT_USER_ID));
      if (CURRENT_ROLE)   qp.set('role', CURRENT_ROLE);

      const data  = await apiGet(`/notifications/my?${qp.toString()}`);
      const items = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      return items;
    } catch (e) {
      console.error('[notifications] fetch failed', e);
      return [];
    }
  }

  async function refreshUnreadBadge(){
    const unread = await fetchNotifications({ onlyUnread: true, limit: 50 });
    const count  = unread.length;
    if (count > 0) {
      notifBadge.style.display = 'inline-block';
      notifBadge.textContent = count > 99 ? '99+' : String(count);
    } else {
      notifBadge.style.display = 'none';
      notifBadge.textContent = '0';
    }
  }
  // Initial badge (non-blocking)
  if (notifBadge) { refreshUnreadBadge(); }

  // ==============================
  // Right Drawer wiring
  // ==============================
  function setNotifOverlay(on){
    notifOverlay?.classList.toggle('active', !!on);
    notifOverlay?.setAttribute('aria-hidden', on ? 'false' : 'true');
    body.classList.toggle('no-scroll', !!on);
  }
  function openNotifDrawer(){
    if(!notifDrawer) return;
    notifDrawer.classList.add('open');
    notifDrawer.setAttribute('aria-hidden','false');
    setNotifOverlay(true);
    loadUnreadIntoDrawer();
  }
  function closeNotifDrawer(){
    if(!notifDrawer) return;
    notifDrawer.classList.remove('open');
    notifDrawer.setAttribute('aria-hidden','true');
    setNotifOverlay(false);
  }

  function renderNotificationsInto(container, items){
    if(!container) return;
    if (!Array.isArray(items) || items.length === 0) {
      container.innerHTML = '<div class="p-3 text-center text-muted small">No unread notifications</div>';
      return;
    }
    container.innerHTML = items.map(n => {
      const title   = n.title ?? 'Notification';
      const message = n.message ?? '';
      const when    = (function(){ try { const d=new Date(n.created_at||n.updated_at); return isNaN(d)?'':d.toLocaleString(); } catch { return ''; } })();
      const link    = n.link_url || null;
      const prioCls = (p=>({urgent:'danger', high:'warning', normal:'secondary', low:'secondary'})[p||'normal']||'secondary')(n.priority);
      return `
        <div class="notif-item d-flex align-items-start p-2" data-id="${n.id}">
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2">
              <strong class="me-1">${title}</strong>
              <span class="badge bg-${prioCls} text-uppercase" style="font-size:.65rem;">${(n.priority||'normal')}</span>
            </div>
            <div class="small text-muted mt-1">${message}</div>
            <div class="d-flex gap-2 mt-2">
              <button class="btn btn-sm btn-outline-primary notif-view" data-id="${n.id}" ${link?`data-link="${link}"`:''} style='display:none'>View</button>
              <button class="btn btn-sm btn-outline-danger notif-del" data-id="${n.id}"style='display:none'>Delete</button>
            </div>
            <div class="small text-muted mt-1">${when}</div>
          </div>
        </div>`;
    }).join('');

    // Delete
    container.querySelectorAll('.notif-del').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        e.preventDefault();
        const id = Number(btn.dataset.id);
        try {
          await apiDelete(`/notifications/${id}`);
          btn.closest('[data-id]')?.remove();
          await refreshUnreadBadge();
          if(!container.querySelector('[data-id]')) container.innerHTML = '<div class="p-3 text-center text-muted small">No unread notifications</div>';
        } catch(err){
          console.error(err);
          Swal.fire('Error', 'Delete failed', 'error');
        }
      });
    });

    // View (mark read + follow link)
    container.querySelectorAll('.notif-view').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        e.preventDefault();
        const id   = Number(btn.dataset.id);
        const link = btn.getAttribute('data-link');
        try{
          const body = { read: true };
          if (CURRENT_USER_ID) body.user_id = CURRENT_USER_ID;
          if (CURRENT_ROLE)    body.role    = CURRENT_ROLE;
          await apiPatch(`/notifications/${id}/read`, body);
          await refreshUnreadBadge();
        }catch(err){ console.warn('[notif-view] mark read failed (continuing)…', err); }
        if (link) window.location.href = link;
      });
    });
  }

  async function loadUnreadIntoDrawer(){
    if(!notifDrawerList) return;
    notifDrawerList.innerHTML = '<div class="p-3 text-center text-muted small">Loading…</div>';
    try{
      const items = await fetchNotifications({ onlyUnread: true, limit: 50 });
      renderNotificationsInto(notifDrawerList, items);
    }catch(e){
      console.error(e);
      notifDrawerList.innerHTML = '<div class="p-3 text-center text-danger small">Failed to load notifications</div>';
    }
  }

  // Wire drawer buttons
  openNotifDrawerBtn?.addEventListener('click', openNotifDrawer);
  closeNotifDrawerBtn?.addEventListener('click', closeNotifDrawer);
  notifOverlay?.addEventListener('click', closeNotifDrawer);
  notifViewAllBtn?.addEventListener('click', ()=>{ window.location.href = '/admin/notifications'; });
  notifDrawerRefreshBtn?.addEventListener('click', async ()=>{ await loadUnreadIntoDrawer(); await refreshUnreadBadge(); });
  notifDrawerMarkAllBtn?.addEventListener('click', async ()=>{
    try {
      const body = {};
      if (CURRENT_USER_ID) body.user_id = CURRENT_USER_ID;
      if (CURRENT_ROLE)    body.role    = CURRENT_ROLE;
      await apiPost('/notifications/mark-all-read', body);
      await loadUnreadIntoDrawer();
      await refreshUnreadBadge();
      Swal.fire({ icon:'success', title:'Marked all as read', timer:1000, showConfirmButton:false });
    } catch(e){
      console.error(e);
      Swal.fire('Error', 'Could not mark all as read', 'error');
    }
  });
});

// === Highlight active link, open its submenu, and light up the rail icon ===
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
      toggle?.classList.add('open', 'active');
      toggle?.setAttribute('aria-expanded', 'true');
      const section = group?.getAttribute('data-section');
      document.querySelector(`.rail .group-kicker[data-section="${section}"]`)?.classList.add('active');
    }
  }
});

if (path === '/dashboard') {
  document.getElementById('railDashboard')?.classList.add('active');
}
</script>

</body>
</html>
