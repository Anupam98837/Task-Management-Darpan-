<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Assignee Portal Login - Darpan</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root{
      --primary:#1273D1;
      --primary-dark:#0f5ba8;
      --secondary:#2493ff;
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

    /* ===== Animations ===== */
    @keyframes slideInLeft {
      from {
        opacity: 0;
        transform: translateX(-60px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(60px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateX(0) scale(1);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    /* ===== Layout ===== */
    .auth-wrap{
      min-height:100vh;
      display:grid;
      grid-template-columns: 1fr 1fr;
    }
    
    @media (max-width: 992px){
      .auth-wrap{grid-template-columns: 1fr;}
      .auth-illustration{display:none;}
    }

    /* ===== Left Side ===== */
    .auth-illustration{
      background: linear-gradient(135deg, #0a1929 0%, #0d2847 50%, #1273D1 100%);
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
      background: radial-gradient(circle, rgba(18, 115, 209, 0.5) 0%, transparent 70%);
      top: -80px;
      left: -80px;
      animation-delay: 0s;
    }

    .shape-2 {
      width: 280px;
      height: 280px;
      background: radial-gradient(circle, rgba(36, 147, 255, 0.4) 0%, transparent 70%);
      bottom: -60px;
      right: -60px;
      animation-delay: 5s;
    }

    .shape-3 {
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, transparent 70%);
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
      background: linear-gradient(135deg, #1273D1 0%, #2493ff 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 24px rgba(18, 115, 209, 0.4);
      position: relative;
    }

    .logo-icon::after {
      content: '';
      position: absolute;
      inset: -2px;
      background: linear-gradient(135deg, #1273D1, #2493ff);
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
      color: #94a3b8;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .app-name {
      font-family: 'Outfit', sans-serif;
      font-size: 26px;
      font-weight: 800;
      background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
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
      color: #94a3b8;
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
      color: #cbd5e1;
      font-size: 14px;
    }

    .feature-item i {
      color: #60a5fa;
      font-size: 16px;
      width: 20px;
    }

    .illustration-footer {
      text-align: center;
      color: #64748b;
      font-size: 13px;
      position: relative;
      z-index: 2;
    }

    /* ===== Right Side ===== */
    .auth-card{
      padding: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      position: relative;
    }

    .auth-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 20%, rgba(18, 115, 209, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(36, 147, 255, 0.03) 0%, transparent 50%);
    }

    .auth-card .card{
      width: 440px;
      max-width: 100%;
      padding: 40px;
      border-radius: 24px;
      border: 2px solid var(--border);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
      background: #ffffff;
      position: relative;
      z-index: 2;
      animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
      opacity: 0;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
      padding: 8px 12px;
      border-radius: 8px;
      animation: fadeIn 0.6s ease-out 0.2s forwards;
      opacity: 0;
    }

    .back-link:hover {
      color: var(--primary);
      background: rgba(18, 115, 209, 0.05);
    }

    .back-link i {
      font-size: 12px;
    }

    .portal-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
      color: var(--primary);
      padding: 8px 16px;
      border-radius: 100px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 24px;
      animation: fadeInDown 0.6s ease-out 0.3s forwards;
      opacity: 0;
    }

    .portal-badge i {
      font-size: 14px;
    }

    .auth-title{
      font-family: 'Outfit', sans-serif;
      font-size: 32px;
      font-weight: 800;
      color: var(--dark);
      margin: 0 0 8px;
      line-height: 1.2;
      animation: fadeInUp 0.6s ease-out 0.4s forwards;
      opacity: 0;
    }

    .auth-sub{
      color: var(--muted);
      margin: 0 0 32px;
      font-size: 15px;
      animation: fadeInUp 0.6s ease-out 0.5s forwards;
      opacity: 0;
    }

    /* ===== Form ===== */
    .form-label{
      font-size: 14px;
      color: var(--dark);
      font-weight: 600;
      margin-bottom: 8px;
    }

    .mb-3 {
      animation: fadeInUp 0.6s ease-out forwards;
      opacity: 0;
    }

    .mb-3:nth-of-type(1) {
      animation-delay: 0.6s;
    }

    .mb-3:nth-of-type(2) {
      animation-delay: 0.7s;
    }

    .form-control{
      border-radius: 12px;
      height: 48px;
      padding: 12px 16px;
      border: 2px solid var(--border);
      font-size: 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus{
      border-color: var(--primary);
      outline: 0;
      box-shadow: 0 0 0 4px rgba(18, 115, 209, 0.1);
    }

    .form-control::placeholder{
      color: #94a3b8;
    }

    .password-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 4px;
      transition: color 0.2s;
    }

    .password-toggle:hover {
      color: var(--primary);
    }

    .forgot-link-wrapper {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 24px;
      animation: fadeInUp 0.6s ease-out 0.8s forwards;
      opacity: 0;
    }

    .forgot{
      font-size: 14px;
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .forgot:hover{
      color: var(--primary-dark);
    }

    .btn-primary{
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border: none;
      height: 50px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 15px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(18, 115, 209, 0.3);
      animation: fadeInUp 0.6s ease-out 0.9s forwards;
      opacity: 0;
    }

    .btn-primary:hover{
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(18, 115, 209, 0.4);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 16px;
      margin: 24px 0;
      animation: fadeIn 0.6s ease-out 1s forwards;
      opacity: 0;
    }

    .divider-line {
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .divider-text {
      color: var(--muted);
      font-size: 13px;
      font-weight: 500;
    }

    .text-center {
      animation: fadeIn 0.6s ease-out 1.1s forwards;
      opacity: 0;
    }

    @media (max-width: 640px) {
      .auth-card {
        padding: 24px 16px;
      }

      .auth-card .card {
        padding: 32px 24px;
      }

      .auth-title {
        font-size: 26px;
      }
    }
  </style>
</head>
<body>
  <div class="auth-wrap">
    <!-- Left -->
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
          <h2 class="illustration-title">Assignee Portal Access</h2>
          <p class="illustration-text">
            Unlock your productivity and securely access your tasks. Collaborate with your team and stay organized, anywhere, anytime.
          </p>

          <div class="features-list">
            <div class="feature-item">
              <i class="fas fa-tasks"></i>
              <span>Access and manage assigned tasks efficiently</span>
            </div>
            <div class="feature-item">
              <i class="fas fa-clock"></i>
              <span>Track progress and meet deadlines with ease</span>
            </div>
            <div class="feature-item">
              <i class="fas fa-users"></i>
              <span>Seamless collaboration with team members</span>
            </div>
            <div class="feature-item">
              <i class="fas fa-shield-alt"></i>
              <span>Secure access to your personal workspace</span>
            </div>
          </div>
        </div>
      </div>

      <div class="illustration-footer">
        © 2025 Legmed. All rights reserved.
      </div>
    </div>

    <!-- Right -->
    <div class="auth-card">
      <div class="card">
        <a href="/" class="back-link">
          <i class="fas fa-arrow-left"></i>
          Back to Portal Selection
        </a>

        <div class="portal-badge">
          <i class="fas fa-user"></i>
          Assignee Portal
        </div>

        <h5 class="auth-title">Welcome Back</h5>
        <p class="auth-sub">Sign in to your Assignee Account</p>

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

        <div class="forgot-link-wrapper" style="display:none">
          <a class="forgot" href="/assignee/forgot-password">Forgot Password?</a>
        </div>

        <button id="loginBtn" class="btn btn-primary w-100">
          <span id="btnText">Log In</span>
          <i class="fas fa-arrow-right ms-2" id="btnIcon"></i>
        </button>

        <div class="divider">
          <div class="divider-line"></div>
          <span class="divider-text">Need help?</span>
          <div class="divider-line"></div>
        </div>

        <div class="text-center">
          <small class="text-muted">
            Contact your manager for account access issues
          </small>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      eyeIcon.classList.toggle('fa-eye');
      eyeIcon.classList.toggle('fa-eye-slash');
    });

    // ===== Config (original functionality preserved) =====
    const LOGIN_API = '/api/assignedpeople/login';
    const REDIRECT_URL = '/assignee/dashboard';
    const TOKEN_KEY = 'token';

    // ===== Login flow (original functionality preserved) =====
    async function login(){
      const identifier = document.getElementById('identifier').value.trim();
      const passwordVal = document.getElementById('password').value;

      if(!identifier || !passwordVal){
        Swal.fire({
          icon:'warning',
          title:'Missing Fields',
          text:'Please enter both email/username and password',
          confirmButtonColor: '#1273D1'
        });
        return;
      }

      // Show loading state
      const btn = document.getElementById('loginBtn');
      const btnText = document.getElementById('btnText');
      const btnIcon = document.getElementById('btnIcon');
      
      btn.disabled = true;
      btnText.textContent = 'Logging in...';
      btnIcon.className = 'fas fa-spinner fa-spin ms-2';

      try{
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch(LOGIN_API, {
          method: 'POST',
          headers: {
            'Content-Type':'application/json',
            'Accept':'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({ identifier, password: passwordVal })
        });

        const data = await res.json().catch(()=>({}));

        // Reset button state
        btn.disabled = false;
        btnText.textContent = 'Log In';
        btnIcon.className = 'fas fa-arrow-right ms-2';

        if(res.ok){
          sessionStorage.removeItem('token');
          sessionStorage.removeItem('role');
          localStorage.removeItem('token');
          localStorage.removeItem('role');
          localStorage.removeItem('type');

          if(data?.access_token){ 
            sessionStorage.setItem(TOKEN_KEY, data.access_token);
            if(data?.tokenable_type){ sessionStorage.setItem('role', data.tokenable_type); }
          }
          Swal.fire({ 
            icon:'success', 
            title:'Login Successful', 
            text:'Redirecting to dashboard...',
            timer:1500, 
            showConfirmButton:false,
            confirmButtonColor: '#1273D1'
          }).then(()=> window.location.href = REDIRECT_URL);
        }else{
          Swal.fire({ 
            icon:'error', 
            title:'Login Failed', 
            text: data?.message || 'Invalid credentials',
            confirmButtonColor: '#1273D1'
          });
        }
      }catch(err){
        // Reset button state
        btn.disabled = false;
        btnText.textContent = 'Log In';
        btnIcon.className = 'fas fa-arrow-right ms-2';
        
        Swal.fire({ 
          icon:'error', 
          title:'Connection Error', 
          text:'Something went wrong. Please try again.',
          confirmButtonColor: '#1273D1'
        });
        console.error(err);
      }
    }

    // Buttons & Enter key (original functionality preserved)
    document.getElementById('loginBtn').addEventListener('click', login);
    document.getElementById('password').addEventListener('keyup', e=>{ if(e.key==='Enter') login(); });
    document.getElementById('identifier').addEventListener('keyup', e=>{ if(e.key==='Enter') login(); });
  </script>
</body>
</html>
