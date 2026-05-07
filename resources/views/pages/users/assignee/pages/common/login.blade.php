<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Assignee Portal — Legmed Darpan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    :root {
      --brand:#0369a1; --brand-dark:#025f8e; --brand-mid:#0ea5e9; --brand-pale:#e0f2fe;
      --text:#0f172a; --muted:#64748b; --border:#e2e8f0; --surface:#ffffff; --radius:12px;
    }
    body { font-family:'Inter',system-ui,sans-serif; background:#f0f4f8; color:var(--text); min-height:100vh; overflow-x:hidden; -webkit-font-smoothing:antialiased; }

    .auth-wrap { min-height:100vh; display:grid; grid-template-columns:1fr 1fr; }
    @media(max-width:900px){ .auth-wrap{grid-template-columns:1fr;} .auth-panel{display:none!important;} }

    .auth-panel {
      background:linear-gradient(155deg,#07234a 0%,#0a3560 40%,#0256a0 100%);
      padding:48px 52px; display:flex; flex-direction:column; justify-content:space-between;
      position:relative; overflow:hidden;
    }
    .auth-panel::before,.auth-panel::after { content:''; position:absolute; border-radius:50%; filter:blur(80px); pointer-events:none; }
    .auth-panel::before { width:400px;height:400px; background:radial-gradient(circle,rgba(56,189,248,.22) 0%,transparent 70%); top:-80px;right:-80px; }
    .auth-panel::after  { width:300px;height:300px; background:radial-gradient(circle,rgba(14,165,233,.18) 0%,transparent 70%); bottom:-70px;left:-50px; }
    .grid-pattern { position:absolute;inset:0; background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px); background-size:40px 40px; pointer-events:none; }
    .panel-content,.panel-footer { position:relative;z-index:1; }

    .brand-mark { display:flex;align-items:center;gap:14px;margin-bottom:52px; }
    .brand-logo { width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;overflow:hidden;padding:6px; }
    .brand-logo img { max-width:100%;max-height:100%;object-fit:contain; }
    .brand-name .company { font-family:'Plus Jakarta Sans',sans-serif;font-size:20px;font-weight:800;color:#fff;display:block;line-height:1; }
    .brand-name .tagline { font-size:12px;color:rgba(255,255,255,.5);margin-top:2px;display:block; }

    .panel-headline { font-family:'Plus Jakarta Sans',sans-serif;font-size:34px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:14px;letter-spacing:-.5px; }
    .panel-headline span { color:#7dd3fc; }
    .panel-sub { font-size:15px;color:rgba(255,255,255,.6);line-height:1.65;margin-bottom:44px;max-width:380px; }

    .feature-list { display:flex;flex-direction:column;gap:14px; }
    .feature-item { display:flex;align-items:center;gap:14px; }
    .feature-icon { width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;color:#7dd3fc;font-size:15px;flex-shrink:0; }
    .feature-text { font-size:14px;color:rgba(255,255,255,.8);font-weight:500; }
    .panel-footer-text { font-size:12px;color:rgba(255,255,255,.3); }

    .auth-form-side { display:flex;align-items:center;justify-content:center;padding:40px 24px;background:#f0f4f8; }
    .auth-card { width:100%;max-width:420px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:36px 36px 32px;box-shadow:0 4px 24px rgba(15,23,42,.08),0 1px 4px rgba(15,23,42,.05); }

    .portal-badge { display:inline-flex;align-items:center;gap:6px;background:var(--brand-pale);color:var(--brand);border:1px solid rgba(3,105,161,.2);border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:700;margin-bottom:20px; }
    .auth-title { font-family:'Plus Jakarta Sans',sans-serif;font-size:26px;font-weight:800;color:var(--text);margin-bottom:4px;letter-spacing:-.3px; }
    .auth-sub { font-size:14px;color:var(--muted);margin-bottom:28px; }

    .field-group { margin-bottom:18px; }
    .field-group label { display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:7px; }
    .field-input-wrap { position:relative; }
    .field-icon { position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;pointer-events:none; }
    .field-input-wrap input { width:100%;height:46px;padding:0 42px 0 40px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:14.5px;color:var(--text);background:#f8fafc;transition:all .18s;outline:none; }
    .field-input-wrap input:focus { border-color:var(--brand);background:#fff;box-shadow:0 0 0 3px rgba(3,105,161,.13); }
    .field-input-wrap input::placeholder { color:#94a3b8; }
    .pwd-toggle { position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;padding:4px;transition:color .15s; }
    .pwd-toggle:hover { color:var(--brand); }

    .remember-row { display:flex;align-items:center;justify-content:space-between;margin-bottom:22px; }
    .remember-row .form-check-label { font-size:13.5px;color:#374151;cursor:pointer; }
    .form-check-input:checked { background-color:var(--brand);border-color:var(--brand); }

    .btn-login { width:100%;height:48px;background:linear-gradient(135deg,var(--brand) 0%,var(--brand-mid) 100%);color:#fff;border:none;border-radius:var(--radius);font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 14px rgba(3,105,161,.3); }
    .btn-login:hover:not(:disabled) { transform:translateY(-1px);box-shadow:0 6px 20px rgba(3,105,161,.4); }
    .btn-login:disabled { opacity:.65;cursor:not-allowed; }
    .btn-login .spin { width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite; }
    @keyframes spin { to{transform:rotate(360deg);} }

    .auth-divider { display:flex;align-items:center;gap:12px;margin:22px 0 16px; }
    .auth-divider span { font-size:12.5px;color:var(--muted);white-space:nowrap; }
    .auth-divider::before,.auth-divider::after { content:'';flex:1;height:1px;background:var(--border); }
    .help-note { font-size:12.5px;color:var(--muted);text-align:center;line-height:1.55; }
    .back-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:20px;transition:color .15s; }
    .back-link:hover { color:var(--brand); }
  </style>
</head>
<body>
<div class="auth-wrap">

  <!-- Left Panel -->
  <div class="auth-panel">
    <div class="grid-pattern"></div>
    <div class="panel-content">
      <div class="brand-mark">
        <div class="brand-logo">
          <img src="{{ asset('/assets/media/images/legmedlogo.png') }}" alt="Legmed">
        </div>
        <div class="brand-name">
          <span class="company">Legmed Darpan</span>
          <span class="tagline">Task Management Platform</span>
        </div>
      </div>
      <h1 class="panel-headline">Stay on top of<br><span>every task</span></h1>
      <p class="panel-sub">View your assigned jobs, track progress, log expenses, and communicate with your team — all in one place.</p>
      <div class="feature-list">
        <div class="feature-item"><div class="feature-icon"><i class="fas fa-briefcase"></i></div><span class="feature-text">View & manage your assigned jobs</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="fas fa-receipt"></i></div><span class="feature-text">Submit & track expense claims</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="fas fa-comments"></i></div><span class="feature-text">Real-time job messaging & updates</span></div>
        <div class="feature-item"><div class="feature-icon"><i class="fas fa-bell"></i></div><span class="feature-text">Instant notifications & alerts</span></div>
      </div>
    </div>
    <div class="panel-footer"><p class="panel-footer-text">© {{ date('Y') }} Legmed. All rights reserved.</p></div>
  </div>

  <!-- Right: Form -->
  <div class="auth-form-side">
    <div class="auth-card">
      <a href="/" class="back-link"><i class="fas fa-arrow-left"></i> Back to portal selection</a>
      <div class="portal-badge"><i class="fas fa-user-tag"></i> Assignee Portal</div>
      <h2 class="auth-title">Welcome back</h2>
      <p class="auth-sub">Sign in to your assignee account</p>

      <div class="field-group">
        <label for="identifier">Email or Username</label>
        <div class="field-input-wrap">
          <i class="fas fa-envelope field-icon"></i>
          <input type="text" id="identifier" placeholder="Enter your email or username" autocomplete="username">
        </div>
      </div>

      <div class="field-group">
        <label for="password">Password</label>
        <div class="field-input-wrap">
          <i class="fas fa-lock field-icon"></i>
          <input type="password" id="password" placeholder="Enter your password" autocomplete="current-password">
          <button type="button" class="pwd-toggle" id="togglePassword" tabindex="-1"><i class="fas fa-eye" id="eyeIcon"></i></button>
        </div>
      </div>

      <div class="remember-row">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
      </div>

      <button id="loginBtn" class="btn-login">
        <span id="btnText">Sign In</span>
        <i class="fas fa-arrow-right" id="btnIcon"></i>
      </button>

      <div class="auth-divider"><span>Need help?</span></div>
      <p class="help-note">Contact your administrator if you're having trouble accessing your account.</p>
    </div>
  </div>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('click',function(){
    const i=document.getElementById('password'),c=document.getElementById('eyeIcon');
    i.type=i.type==='password'?'text':'password';
    c.className=i.type==='text'?'fas fa-eye-slash':'fas fa-eye';
  });

  async function login(){
    const identifier=document.getElementById('identifier').value.trim();
    const passwordVal=document.getElementById('password').value;
    const remember=document.getElementById('rememberMe').checked;
    if(!identifier||!passwordVal){
      Swal.fire({icon:'warning',title:'Missing Fields',text:'Please enter both email/username and password.',confirmButtonColor:'#0369a1'});
      return;
    }
    const btn=document.getElementById('loginBtn');
    btn.disabled=true;
    btn.innerHTML='<span class="spin"></span> Signing in…';
    try{
      const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
      const res=await fetch('/api/assignedpeople/login',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({identifier,password:passwordVal,remember})});
      const data=await res.json().catch(()=>({}));
      btn.disabled=false;
      btn.innerHTML='<span id="btnText">Sign In</span><i class="fas fa-arrow-right" id="btnIcon"></i>';
      if(res.ok&&data?.access_token){
        ['token','role'].forEach(k=>{sessionStorage.removeItem(k);localStorage.removeItem(k);});
        localStorage.removeItem('type');
        sessionStorage.removeItem('type');
        const store = remember ? localStorage : sessionStorage;
        store.setItem('token',data.access_token);
        if(data?.tokenable_type){
          store.setItem('role',data.tokenable_type);
          store.setItem('type',data.tokenable_type);
        }
        Swal.fire({icon:'success',title:'Login Successful',text:'Redirecting…',timer:1200,showConfirmButton:false}).then(()=>window.location.href='/assignee/dashboard');
      }else{
        Swal.fire({icon:'error',title:'Login Failed',text:data?.message||'Invalid credentials.',confirmButtonColor:'#0369a1'});
      }
    }catch(err){
      btn.disabled=false;
      btn.innerHTML='<span id="btnText">Sign In</span><i class="fas fa-arrow-right" id="btnIcon"></i>';
      Swal.fire({icon:'error',title:'Connection Error',text:'Unable to connect. Please try again.',confirmButtonColor:'#0369a1'});
    }
  }

  document.getElementById('loginBtn').addEventListener('click',login);
  ['password','identifier'].forEach(id=>document.getElementById(id).addEventListener('keyup',e=>{if(e.key==='Enter')login();}));
</script>
</body>
</html>
