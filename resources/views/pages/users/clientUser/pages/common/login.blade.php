<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Client Portal Login - Darpan</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root{
      --primary:#0f766e;
      --primary-dark:#0d5f59;
      --secondary:#14b8a6;
      --border:#e2e8f0;
      --muted:#64748b;
      --dark:#0f172a;
      --bg-light:#f8fafc;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body{
      font-family: 'Inter', sans-serif;
      background: var(--bg-light);
      color: var(--dark);
      overflow-x: hidden;
    }

    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-60px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(60px) scale(0.95); }
      to { opacity: 1; transform: translateX(0) scale(1); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .auth-wrap{
      min-height:100vh;
      display:grid;
      grid-template-columns: 1fr 1fr;
    }

    @media (max-width: 992px){
      .auth-wrap{grid-template-columns: 1fr;}
      .auth-illustration{display:none;}
    }

    .auth-illustration{
      background: linear-gradient(135deg, #062b2a 0%, #0b4a47 50%, #0f766e 100%);
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
      animation: slideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      opacity: 0;
    }

    .animated-bg {
      position: absolute;
      inset: 0;
      opacity: 0.4;
    }

    .floating-shape {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      animation: float-shape 15s ease-in-out infinite;
    }

    .shape-1 {
      width: 350px;
      height: 350px;
      background: radial-gradient(circle, rgba(20, 184, 166, 0.45) 0%, transparent 70%);
      top: -80px;
      left: -80px;
      animation-delay: 0s;
    }

    .shape-2 {
      width: 280px;
      height: 280px;
      background: radial-gradient(circle, rgba(45, 212, 191, 0.30) 0%, transparent 70%);
      bottom: -60px;
      right: -60px;
      animation-delay: 5s;
    }

    .shape-3 {
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(153, 246, 228, 0.20) 0%, transparent 70%);
      top: 45%;
      left: 35%;
      animation-delay: 10s;
    }

    @keyframes float-shape {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      33% { transform: translate(40px, -40px) rotate(120deg); }
      66% { transform: translate(-25px, 35px) rotate(240deg); }
    }

    .auth-illustration .content {
      position: relative;
      z-index: 2;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 60px;
      animation: fadeIn 0.6s ease-out 0.3s forwards;
      opacity: 0;
    }

    .logo-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 24px rgba(15, 118, 110, 0.4);
      position: relative;
    }

    .logo-icon::after {
      content: '';
      position: absolute;
      inset: -2px;
      background: linear-gradient(135deg, #0f766e, #14b8a6);
      border-radius: 14px;
      z-index: -1;
      opacity: 0.5;
      filter: blur(8px);
    }

    .logo-icon img {
      width: 28px;
      height: 28px;
      object-fit: contain;
      display: block;
      filter: brightness(0) invert(1);
    }

    .logo-text {
      display: flex;
      flex-direction: column;
    }

    .company-name {
      font-size: 12px;
      font-weight: 600;
      color: #99f6e4;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .app-name {
      font-family: 'Outfit', sans-serif;
      font-size: 26px;
      font-weight: 800;
      background: linear-gradient(135deg, #ffffff 0%, #ccfbf1 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.5px;
    }

    .illustration-panel {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      padding: 48px 40px;
      margin-bottom: 32px;
      backdrop-filter: blur(10px);
      animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
      opacity: 0;
    }

    .illustration-title {
      font-family: 'Outfit', sans-serif;
      font-size: 32px;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 16px;
      line-height: 1.2;
    }

    .illustration-text {
      color: #ccfbf1;
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 32px;
    }

    .features-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #e6fffb;
      font-size: 14px;
    }

    .feature-item i {
      width: 20px;
      color: #99f6e4;
      font-size: 16px;
    }

    .illustration-footer {
      position: relative;
      z-index: 2;
      color: rgba(255, 255, 255, 0.5);
      font-size: 13px;
      animation: fadeIn 0.6s ease-out 0.5s forwards;
      opacity: 0;
    }

    .auth-card{
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 40px;
      animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
      opacity: 0;
    }

    .card{
      width:100%;
      max-width: 480px;
      background:#fff;
      border:none;
      border-radius: 24px;
      box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.08),
        0 8px 16px rgba(0, 0, 0, 0.04);
      padding: 48px 40px;
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #0f766e, #14b8a6);
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 24px;
      transition: all 0.2s;
    }

    .back-link:hover {
      color: var(--primary);
      transform: translateX(-4px);
    }

    .portal-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
      color: var(--primary);
      padding: 8px 16px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 24px;
      border: 1px solid #99f6e4;
    }

    .auth-title{
      font-family: 'Outfit', sans-serif;
      font-size: 36px;
      font-weight: 800;
      color: var(--dark);
      margin-bottom: 12px;
      letter-spacing: -0.8px;
    }

    .auth-sub{
      color: var(--muted);
      font-size: 15px;
      line-height: 1.6;
      margin-bottom: 32px;
    }

    .form-label{
      font-size: 13px;
      font-weight: 600;
      color: #334155;
      margin-bottom: 8px;
    }

    .form-control{
      height: 52px;
      border: 2px solid var(--border);
      border-radius: 14px;
      padding: 0 18px;
      font-size: 15px;
      color: var(--dark);
      transition: all 0.2s;
      background: #fafbfc;
    }

    .form-control:focus{
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.10);
      background: #fff;
    }

    .form-control::placeholder {
      color: #94a3b8;
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .form-control {
      padding-right: 52px;
    }

    .password-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      transition: color 0.2s;
      padding: 4px;
    }

    .password-toggle:hover {
      color: var(--primary);
    }

    .btn-primary{
      height: 54px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border: none;
      border-radius: 14px;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.3px;
      transition: all 0.3s;
      box-shadow: 0 8px 20px rgba(15, 118, 110, 0.25);
      position: relative;
      overflow: hidden;
    }

    .btn-primary:hover:not(:disabled){
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(15, 118, 110, 0.35);
    }

    .btn-primary:disabled{
      opacity: 0.7;
      cursor: not-allowed;
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 16px;
      margin: 32px 0 24px;
    }

    .divider-line {
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    }

    .divider-text {
      color: var(--muted);
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div class="auth-wrap">
    <div class="auth-illustration">
      <div class="animated-bg">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
      </div>

      <div class="content">
        <div class="logo-section">
          <div class="logo-icon">
            <img src="{{ asset('/assets/media/images/legmedlogo.png') }}" alt="Legmed Logo">
          </div>
          <div class="logo-text">
            <span class="company-name">Legmed</span>
            <span class="app-name">Darpan</span>
          </div>
        </div>

        <div class="illustration-panel">
          <h1 class="illustration-title">View work across your assigned client scope.</h1>
          <p class="illustration-text">
            Clients can sign in to review jobs, deadlines, documents, and related activity for the client trees assigned to them.
          </p>

          <div class="features-list">
            <div class="feature-item">
              <i class="fas fa-diagram-project"></i>
              <span>Select parent clients or exact child clients as needed</span>
            </div>
            <div class="feature-item">
              <i class="fas fa-briefcase"></i>
              <span>See live job details without edit permissions</span>
            </div>
            <div class="feature-item">
              <i class="fas fa-folder-open"></i>
              <span>Review client-linked documents and updates in one place</span>
            </div>
          </div>
        </div>
      </div>

      <div class="illustration-footer">
        © 2025 Legmed. All rights reserved.
      </div>
    </div>

    <div class="auth-card">
      <div class="card">
        <a href="/" class="back-link">
          <i class="fas fa-arrow-left"></i>
          Back to Portal Selection
        </a>

        <div class="portal-badge">
          <i class="fas fa-building-user"></i>
          Client Portal
        </div>

        <h5 class="auth-title">Welcome Back</h5>
        <p class="auth-sub">Sign in to access your client dashboard</p>

        <div class="mb-3">
          <label for="identifier" class="form-label">Email or Username</label>
          <input type="text" id="identifier" class="form-control"
                 placeholder="Enter your email or username" autocomplete="username" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" class="form-control"
                   placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="password-toggle" id="togglePassword">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button id="loginBtn" class="btn btn-primary w-100">
          <span id="btnText">Sign In</span>
          <i class="fas fa-arrow-right ms-2" id="btnIcon"></i>
        </button>

        <div class="divider">
          <div class="divider-line"></div>
          <span class="divider-text">Need help?</span>
          <div class="divider-line"></div>
        </div>

        <div class="text-center">
          <small class="text-muted">
            Contact your system administrator for access issues
          </small>
        </div>
      </div>
    </div>
  </div>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      eyeIcon.classList.toggle('fa-eye');
      eyeIcon.classList.toggle('fa-eye-slash');
    });

    async function login(){
      const identifier = document.getElementById('identifier').value.trim();
      const passwordVal = document.getElementById('password').value;

      if(!identifier || !passwordVal){
        Swal.fire({
          icon:'warning',
          title:'Missing Fields',
          text:'Please enter both email/username and password',
          confirmButtonColor: '#0f766e'
        });
        return;
      }

      const btn = document.getElementById('loginBtn');
      const btnText = document.getElementById('btnText');
      const btnIcon = document.getElementById('btnIcon');

      btn.disabled = true;
      btnText.textContent = 'Signing in...';
      btnIcon.className = 'fas fa-spinner fa-spin ms-2';

      try{
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/client-users/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({
            identifier,
            password: passwordVal
          })
        });

        const data = await res.json().catch(() => ({}));

        btn.disabled = false;
        btnText.textContent = 'Sign In';
        btnIcon.className = 'fas fa-arrow-right ms-2';

        if(res.ok && data?.access_token){
          sessionStorage.removeItem('token');
          sessionStorage.removeItem('role');
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          localStorage.removeItem('type');

          sessionStorage.setItem('token', data.access_token);
          sessionStorage.setItem('role', data?.tokenable_type || 'client_user');

          Swal.fire({
            icon:'success',
            title:'Login Successful',
            text:'Redirecting to dashboard...',
            timer:1500,
            showConfirmButton:false,
            confirmButtonColor: '#0f766e'
          }).then(() => {
            window.location.href = '/client-user/dashboard';
          });
        } else {
          Swal.fire({
            icon:'error',
            title:'Login Failed',
            text: data?.message || 'Invalid credentials. Please try again.',
            confirmButtonColor: '#0f766e'
          });
        }

      } catch(err) {
        btn.disabled = false;
        btnText.textContent = 'Sign In';
        btnIcon.className = 'fas fa-arrow-right ms-2';

        Swal.fire({
          icon:'error',
          title:'Connection Error',
          text:'Something went wrong. Please try again.',
          confirmButtonColor: '#0f766e'
        });
        console.error(err);
      }
    }

    document.getElementById('loginBtn').addEventListener('click', login);
    document.getElementById('password').addEventListener('keyup', e=>{ if(e.key==='Enter') login(); });
    document.getElementById('identifier').addEventListener('keyup', e=>{ if(e.key==='Enter') login(); });
  </script>
</body>
</html>
