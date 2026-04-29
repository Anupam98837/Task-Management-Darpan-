<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>@yield('title','Dashboard')</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicons/favicon.png') }}">

  {{-- Bootstrap / Icons / SweetAlert2 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>

  {{-- Main theme (place AFTER Bootstrap so overrides apply) --}}
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}">

  {{-- Layout styles (variables-first; no hardcoded project colors) --}}
  <style>
    :root{
      --radius: var(--radius-md, 12px);
    }

    body {
      margin: 0;
      font-family: var(--font-sans, Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif);
      background: var(--light-color, #f5f7fa);
      color: var(--text-color, #0f172a);
    }

    .layout { display:flex; min-height:100vh; }

    /* Sidebar */
    .dashboard-sidebar {
      width: 260px; background:#fff; color: var(--text-color);
      display:flex; flex-direction:column;
      position:fixed; inset:0 auto 0 0; z-index:1000;
      border-right:1px solid var(--border-color, #e5e7eb);
      box-shadow: var(--shadow-sm, 0 1px 2px rgba(0,0,0,.05));
      transition: transform .28s ease, width .28s ease, box-shadow .28s ease;
    }
    .sidebar-logo {
      padding: 20px 16px;
      border-bottom: 1px solid var(--border-color);
      display:flex; align-items:center; justify-content:center;
      background: #fff;
    }
    .sidebar-logo img {
      max-width: 130px; filter: drop-shadow(0 1px 2px rgba(0,0,0,.25));
    }

    .sidebar-nav { flex:1; padding:12px; display:flex; flex-direction:column; gap:6px; overflow-y:auto; }
    .sidebar-nav .nav-link{
      color: var(--text-color); padding:10px 12px; border-radius:8px;
      display:flex; align-items:center; gap:10px; font-size:14px; font-weight:500;
      border:1px solid transparent; transition: .18s ease; position:relative;
    }
    .sidebar-nav .nav-link i{ color: var(--primary-color, #4f46e5); min-width:18px; text-align:center; }
    .sidebar-nav .nav-link:hover{ background: var(--light-color); color: var(--accent-color, #4f46e5); border-color: var(--border-color); }
    .sidebar-nav .nav-link.active{
      background: rgba(79,70,229,.08); color: var(--accent-color); font-weight:600; border-color: var(--border-color);
      box-shadow: var(--shadow-sm);
    }
    .sidebar-nav .nav-link.active::before{
      content:""; position:absolute; left:-12px; top:8px; bottom:8px; width:4px; border-radius:6px; background: var(--accent-color);
    }

    .nav-group { display:flex; flex-direction:column; gap:6px; }
    .group-toggle { cursor:pointer; user-select:none; }
    .group-toggle .chev { margin-left:auto; transition: transform .18s ease; color: var(--text-color); }
    .group-toggle.open .chev{ transform: rotate(180deg); }

    .submenu{ display:none; flex-direction:column; gap:4px; margin-left:6px; padding-left:6px; border-left:1px dashed var(--border-color); }
    .submenu.open{ display:flex; animation:fadeIn .2s ease; }
    .submenu .nav-link{ font-size:13px; padding:8px 12px 8px 36px; }
    @keyframes fadeIn{ from{opacity:0; transform:translateY(-4px);} to{opacity:1; transform:translateY(0);} }

    .sidebar-auth{ border-top:1px solid var(--border-color); padding:12px; }
    .auth-link{ color: var(--danger-color, #dc2626); display:flex; align-items:center; gap:.5rem; font-weight:600; font-size:14px; }
    .auth-link:hover{ color:#b91c1c; }

    /* Right Panel / Header */
    .right-panel{ flex:1; margin-left:260px; display:flex; flex-direction:column; transition: margin .28s ease; }
    .admin-header{
      height:60px; background:#fff; border-bottom:1px solid var(--border-color);
      display:flex; align-items:center; padding:0 14px; position:sticky; top:0; z-index:900; gap:10px;
    }
    .admin-header h5{ margin:0; font-family: var(--font-head, Poppins, var(--font-sans)); color: var(--text-color); font-weight:600; font-size:16px; }
    .main-content{ flex:1; padding:18px; }

    .ah_usericon{ border:2px solid var(--primary-color); border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; color:var(--primary-color); background: var(--surface, #0f172a) transition:.15s; }
    .ah_usericon:hover{ background: var(--primary-color); color:#fff; }

    .theme-toggle{ display:inline-flex; align-items:center; gap:8px; padding:4px 8px; border:1px solid var(--border-color); border-radius:999px; background:#fff; }
    .theme-toggle .form-check-input{ cursor:pointer; }
    .theme-toggle .icon{ width:16px; height:16px; display:inline-flex; align-items:center; justify-content:center; }

    /* Mobile Sidebar */
    @media (max-width: 991px){
      .dashboard-sidebar{ transform: translateX(-100%); }
      .dashboard-sidebar.active{ transform: translateX(0); }
      .right-panel{ margin-left:0; }
      .right-panel.shifted{ margin-left:0; }

      .sidebar-overlay{
        position:fixed; inset:0; background: rgba(0,0,0,.45);
        z-index:900; opacity:0; visibility:hidden; transition:.25s ease; backdrop-filter: blur(2px);
      }
      .sidebar-overlay.active{ opacity:1; visibility:visible; }
    }

    /* Scrollbars */
    .sidebar-nav::-webkit-scrollbar{ width:8px; }
    .sidebar-nav::-webkit-scrollbar-thumb{ background: var(--border-color); border-radius:8px; }

    .page-head{ display:flex; align-items:center; justify-content:space-between; background:#fff; border:1px solid var(--border-color); border-radius: var(--radius); padding:12px 14px; margin-bottom:14px; }
    .page-head h6{ margin:0; font-weight:700; letter-spacing:.3px; }
    .page-head .actions{ display:flex; gap:8px; }

    /* Dark mode */
    html.theme-dark{
      --bg-body:#0b1220; --text-color:#e5e7eb; --light-color:#0f172a; --border-color:#273244;
    }
    html.theme-dark body{ background: var(--bg-body); color: var(--text-color); }
    html.theme-dark .dashboard-sidebar,
    html.theme-dark .admin-header,
    html.theme-dark .page-head,
    html.theme-dark .dropdown-menu,
    html.theme-dark .modal-content,
    html.theme-dark .card,
    html.theme-dark .offcanvas,
    html.theme-dark .toast{ background: var(--light-color) !important; color: var(--text-color) !important; border-color: var(--border-color) !important; }
    html.theme-dark .sidebar-logo{ background: var(--light-color); border-bottom-color: var(--border-color); }
    html.theme-dark .sidebar-nav .nav-link{ color: var(--text-color); border-color:transparent; }
    html.theme-dark .sidebar-nav .nav-link:hover{ background: rgba(99,102,241,.10); color: var(--accent-color, #6366f1); border-color: var(--border-color); }
    html.theme-dark .sidebar-nav .nav-link.active{ background: rgba(99,102,241,.14); color: var(--accent-color); border-color: var(--border-color); box-shadow:none; }

    /* Bootstrap switch knob visibility in dark */
    html.theme-dark .form-switch .form-check-input{
      background-color:#1e293b; border-color: var(--border-color); box-shadow:none;
      background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23e5e7eb'/%3e%3c/svg%3e");
      background-position:left center; background-repeat:no-repeat;
      transition: background-position .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
    }
    html.theme-dark .form-switch .form-check-input:checked{
      background-color: var(--accent-color, #6366f1); border-color: var(--accent-color);
      background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23ffffff'/%3e%3c/svg%3e");
      background-position:right center;
    }

    .form-switch { padding-left: 3.1em; }
    /* Color the PNG shape with var(--primary-color) using CSS mask */
.logo-mask{
  display:inline-block;
  width:130px;            /* same footprint as your max-width:130px */
  height:36px;            /* adjust if your logo is taller/shorter */
  background: var(--primary-color);
  filter: drop-shadow(0 1px 2px rgba(0,0,0,.25)) !important;
  /* mask using the PNG */
  -webkit-mask-image: var(--logo);
  mask-image: var(--logo);
  -webkit-mask-repeat: no-repeat;
  mask-repeat: no-repeat;
  -webkit-mask-position: center;
  mask-position: center;
  -webkit-mask-size: contain;
  mask-size: contain;
}

/* Dark surface remains identical to your structure */
html.theme-dark .sidebar-logo{
  background: var(--light-color);
  border-bottom-color: var(--border-color);
}

  </style>

  @stack('styles')
</head>
<body>
<div class="layout">
  {{-- Sidebar --}}
  <aside class="dashboard-sidebar" id="sidebar" aria-label="Sidebar">
    <button id="closeSidebar" class="btn btn-sm btn-light d-lg-none m-2" aria-label="Close sidebar">
      <i class="fa fa-times"></i>
    </button>

    <div class="sidebar-logo">
      <a href="/dashboard" class="d-inline-flex align-items-center">
        <span
  id="sidebarLogo"
  class="logo-mask"
  aria-label="Hallienz"
  style="--logo: url('{{ asset('/assets/media/images/hallienzlogo_light.png') }}')"
></span>

      </a>
    </div>

    <nav class="sidebar-nav" role="navigation">
      <a href="/dashboard" class="nav-link">
        <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a>

      {{-- Example Section: Clients --}}
      <div class="nav-group">
        <a href="#" class="nav-link group-toggle" data-target="menu-clients" aria-expanded="false">
          <i class="fa-solid fa-users"></i><span>Client</span>
          <i class="fa fa-chevron-down ms-auto chev"></i>
        </a>
        <div id="menu-clients" class="submenu" role="group" aria-label="Clients submenu">
          <a href="/admin/client/add" class="nav-link">Add Client</a>
          <a href="/admin/client/manage" class="nav-link">Manage Client</a>
          <!-- <a href="/admin/client-approver/manage" class="nav-link">Manage Approvers</a> -->
        </div>
      </div>

      <!-- Document Types -->
<div class="nav-group">
  <a href="#" class="nav-link group-toggle" data-target="menu-doc-types" aria-expanded="false">
    <i class="fa-regular fa-file-lines"></i><span>Document Types</span>
    <i class="fa fa-chevron-down ms-auto chev"></i>
  </a>
  <div id="menu-doc-types" class="submenu" role="group" aria-label="Document Types submenu">
    <a href="/admin/document-types/create" class="nav-link">Add Document Type</a>
    <a href="/admin/document-types" class="nav-link">Manage Document Types</a>
  </div>
</div>


<!-- Documents -->
<div class="nav-group">
  <a href="#" class="nav-link group-toggle" data-target="menu-documents" aria-expanded="false">
    <i class="fa-regular fa-folder-open"></i><span>Documents</span>
    <i class="fa fa-chevron-down ms-auto chev"></i>
  </a>
  <div id="menu-documents" class="submenu" role="group" aria-label="Documents submenu">
    <a href="/admin/documents/upload" class="nav-link">Upload New Documents</a>
  </div>
</div>

    </nav>

    <div class="sidebar-auth">
      <a href="#" id="logoutBtnSidebar" class="auth-link">
        <i class="fa fa-sign-out-alt"></i><span>Logout</span>
      </a>
    </div>
  </aside>

  <div id="sidebarOverlay" class="sidebar-overlay" aria-hidden="true"></div>

  {{-- Right Panel --}}
  <div class="right-panel" id="rightPanel">
    <header class="admin-header">
      <button id="toggleSidebar" class="btn btn-link d-lg-none me-2" aria-label="Open sidebar">
        <i class="fa fa-bars fs-4"></i>
      </button>

      {{-- Page Title (optional) --}}
      {{-- <h5>@yield('title','Dashboard')</h5> --}}

      <div class="ms-auto d-flex align-items-center gap-2">
        {{-- Theme toggle --}}
        <div class="theme-toggle">
          <span class="icon" aria-hidden="true"><i class="fa-regular fa-sun"></i></span>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" role="switch" id="themeSwitch" aria-label="Toggle dark mode">
          </div>
          <span class="icon" aria-hidden="true"><i class="fa-regular fa-moon"></i></span>
        </div>

        {{-- User dropdown --}}
        <div class="dropdown">
          <a href="#" class="ah_usericon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
            <i class="fa fa-user"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <!-- <li class="dropdown-header">Account</li>
            <li><a class="dropdown-item" href="/profile"><i class="fa fa-user me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="/settings"><i class="fa fa-cog me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li> -->
            <li><a class="dropdown-item" href="#" id="logoutBtnMobile"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </header>

    <main class="main-content">
      @yield('content')
    </main>
  </div>
</div>

{{-- Core Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
@yield('scripts')

<script>
document.addEventListener('DOMContentLoaded',() => {
  const sidebar     = document.getElementById('sidebar');
  const overlay     = document.getElementById('sidebarOverlay');
  const themeSwitch = document.getElementById('themeSwitch');
  const logoEl      = document.getElementById('sidebarLogo');

  const openSidebar  = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
  const closeSidebar = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); };

  document.getElementById('toggleSidebar')?.addEventListener('click', openSidebar);
  document.getElementById('closeSidebar')?.addEventListener('click', closeSidebar);
  overlay.addEventListener('click', closeSidebar);

  // Submenu toggle
  document.querySelectorAll('.group-toggle').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const menuId = btn.dataset.target;
      const menu   = document.getElementById(menuId);
      const isOpen = menu.classList.toggle('open');
      btn.classList.toggle('open', isOpen);
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

  // Active link highlight
  const path = window.location.pathname.replace(/\/+$/,'');
  document.querySelectorAll('.sidebar-nav .nav-link[href]').forEach(link => {
    const href = link.getAttribute('href').replace(/\/+$/,'');
    if(href && href !== '#' && href === path){
      link.classList.add('active');
      const submenu = link.closest('.submenu');
      if(submenu){
        submenu.classList.add('open');
        const toggle = submenu.previousElementSibling;
        toggle?.classList.add('open');
        toggle?.setAttribute('aria-expanded','true');
      }
    }
  });

  // Theme handling
  const THEME_KEY = 'theme';
  function updateLogo(mode){
  const el = document.getElementById('sidebarLogo');
  if(!el) return;
  // You can use one file for both since we paint the color via CSS.
  // If you prefer, swap to a different mask file per mode.
  const light = "{{ asset('/assets/media/images/hallienzlogo_light.png') }}";
  // const dark  = "{{ asset('/assets/media/images/hallienzlogo_dark.png') }}"; // optional
  el.style.setProperty('--logo', url("${light}"));
}

  function applyTheme(mode){
    const isDark = (mode === 'dark');
    document.documentElement.classList.toggle('theme-dark', isDark);
    themeSwitch.checked = isDark;
    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    updateLogo(mode);
  }
  const stored = localStorage.getItem(THEME_KEY);
  if(stored === 'dark' || stored === 'light'){ applyTheme(stored); }
  else { const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches; applyTheme(prefersDark ? 'dark' : 'light'); }
  themeSwitch.addEventListener('change', (e)=> applyTheme(e.target.checked ? 'dark' : 'light'));

  // Logout (expects token in sessionStorage as 'token')
  async function doLogout(){
    const token = sessionStorage.getItem('token');
    if(!token){
      Swal.fire('Not logged in','No token found in session','warning');
      return;
    }
    Swal.fire({title:'Logging out...', didOpen:()=>Swal.showLoading(), allowOutsideClick:false});
    try{
      const res = await fetch('/api/admin/logout', { method:'POST', headers:{ 'Authorization': Bearer ${token} }});
      Swal.close();
      if(!res.ok) throw new Error('Logout failed');
      await Swal.fire({ icon:'success', title:'Logged out', timer:1200, showConfirmButton:false });
      sessionStorage.removeItem('token'); window.location.href = '/admin/login';
    }catch(err){
      Swal.fire('Error', err.message || 'Something went wrong', 'error');
    }
  }
  document.getElementById('logoutBtnSidebar')?.addEventListener('click', e=>{ e.preventDefault(); doLogout(); });
  document.getElementById('logoutBtnMobile')?.addEventListener('click',  e=>{ e.preventDefault(); doLogout(); });
});
</script>


</body>
</html>