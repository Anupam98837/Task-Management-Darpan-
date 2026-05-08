<!DOCTYPE html>
<html lang="en">
<head>
  @php
    $developerRoles = $developerRoles ?? [];
  @endphp
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Developer Access — Legmed Darpan</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    :root {
      --brand:#0369a1; --brand-mid:#0ea5e9; --brand-soft:#e0f2fe; --brand-deep:#082f49;
      --text:#0f172a; --muted:#64748b; --border:#dbe5ef; --surface:#ffffff; --radius:14px;
    }
    body {
      font-family:'Inter',system-ui,sans-serif; min-height:100vh; color:var(--text);
      background:
        radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 30%),
        linear-gradient(160deg, #edf5fb 0%, #f8fbfe 48%, #eef6ff 100%);
      -webkit-font-smoothing:antialiased;
    }
    .dev-shell { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:32px 18px; }
    .dev-card {
      width:100%; max-width:1100px; display:grid; grid-template-columns: 1.15fr .85fr; overflow:hidden;
      border:1px solid rgba(14, 116, 144, .14); border-radius:28px; background:#fff;
      box-shadow:0 24px 70px rgba(15,23,42,.12), 0 8px 22px rgba(14,116,144,.08);
    }
    @media (max-width: 940px) {
      .dev-card { grid-template-columns:1fr; }
    }
    .dev-panel {
      position:relative; padding:48px 46px; background:linear-gradient(160deg, #08253f 0%, #0b3d69 56%, #0c5f98 100%);
      color:#fff; overflow:hidden;
    }
    .dev-panel::before,
    .dev-panel::after {
      content:''; position:absolute; border-radius:999px; filter:blur(70px); pointer-events:none;
    }
    .dev-panel::before { width:280px; height:280px; background:rgba(56,189,248,.18); top:-80px; right:-30px; }
    .dev-panel::after { width:240px; height:240px; background:rgba(125,211,252,.16); bottom:-80px; left:-40px; }
    .dev-grid {
      position:absolute; inset:0;
      background-image:linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
      background-size:40px 40px; pointer-events:none;
    }
    .dev-panel-content,
    .dev-panel-footer { position:relative; z-index:1; }
    .dev-badge {
      display:inline-flex; align-items:center; gap:10px; padding:8px 14px; border-radius:999px;
      background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); font-size:12px; font-weight:700;
      letter-spacing:.06em; text-transform:uppercase; color:#d7eefb; margin-bottom:22px;
    }
    .dev-title {
      font-family:'Plus Jakarta Sans',sans-serif; font-size:38px; line-height:1.12; font-weight:800; letter-spacing:-.03em; margin-bottom:14px;
    }
    .dev-title span { color:#8bd7ff; }
    .dev-sub {
      max-width:460px; color:rgba(255,255,255,.72); font-size:15px; line-height:1.75; margin-bottom:34px;
    }
    .dev-points { display:grid; gap:14px; }
    .dev-point {
      display:flex; align-items:flex-start; gap:14px; padding:14px 16px; border-radius:18px;
      background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.11);
    }
    .dev-point-icon {
      width:40px; height:40px; border-radius:14px; flex:0 0 40px; display:flex; align-items:center; justify-content:center;
      background:rgba(255,255,255,.12); color:#8bd7ff;
    }
    .dev-point strong { display:block; font-size:14px; font-weight:700; margin-bottom:3px; }
    .dev-point span { display:block; font-size:13px; color:rgba(255,255,255,.68); line-height:1.55; }
    .dev-panel-footer { margin-top:28px; font-size:12px; color:rgba(255,255,255,.38); }

    .dev-form-wrap { padding:42px 40px 38px; display:flex; align-items:center; justify-content:center; background:#f8fbfe; }
    .dev-form-card {
      width:100%; max-width:430px; background:#fff; border:1px solid var(--border); border-radius:24px;
      padding:32px; box-shadow:0 10px 30px rgba(15,23,42,.06);
    }
    .dev-form-badge {
      display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px;
      background:var(--brand-soft); border:1px solid rgba(3,105,161,.14); color:var(--brand); font-size:12px; font-weight:700;
      margin-bottom:18px;
    }
    .dev-form-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:28px; font-weight:800; letter-spacing:-.03em; margin-bottom:6px; }
    .dev-form-sub { font-size:14px; color:var(--muted); line-height:1.6; margin-bottom:24px; }
    .field-group { margin-bottom:18px; }
    .field-group label { display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:#334155; }
    .field-wrap { position:relative; }
    .field-icon {
      position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;
    }
    .field-wrap input,
    .field-wrap select {
      width:100%; min-height:48px; border:1.5px solid var(--border); border-radius:14px; background:#f8fbff;
      padding:0 16px 0 42px; font-size:14px; color:var(--text); outline:none; transition:all .18s ease;
    }
    .field-wrap select { appearance:none; }
    .field-wrap input:focus,
    .field-wrap select:focus {
      border-color:var(--brand); background:#fff; box-shadow:0 0 0 4px rgba(3,105,161,.12);
    }
    .field-wrap input::placeholder { color:#94a3b8; }
    .field-action {
      position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:transparent; color:#94a3b8; padding:6px;
    }
    .dev-inline-note {
      margin-top:8px; font-size:12px; color:#64748b; line-height:1.5;
    }
    .dev-remember {
      display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:22px;
      font-size:13px; color:#475569;
    }
    .dev-remember .form-check-input:checked { background-color:var(--brand); border-color:var(--brand); }
    .dev-submit {
      width:100%; min-height:50px; border:none; border-radius:15px;
      background:linear-gradient(135deg, var(--brand) 0%, var(--brand-mid) 100%); color:#fff;
      font-family:'Plus Jakarta Sans',sans-serif; font-size:15px; font-weight:800; letter-spacing:.01em;
      display:flex; align-items:center; justify-content:center; gap:10px;
      box-shadow:0 10px 20px rgba(3,105,161,.22); transition:transform .18s ease, box-shadow .18s ease, opacity .18s ease;
    }
    .dev-submit:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 14px 28px rgba(3,105,161,.28); }
    .dev-submit:disabled { opacity:.72; cursor:not-allowed; }
    .dev-submit .spin {
      width:16px; height:16px; border:2px solid rgba(255,255,255,.35); border-top-color:#fff; border-radius:50%;
      animation:dev-spin .75s linear infinite;
    }
    @keyframes dev-spin { to { transform:rotate(360deg); } }
  </style>
</head>
<body>
  <div class="dev-shell">
    <div class="dev-card">
      <section class="dev-panel">
        <div class="dev-grid"></div>
        <div class="dev-panel-content">
          <div class="dev-badge"><i class="fas fa-user-secret"></i> Hidden Developer Access</div>
          <h1 class="dev-title">Switch into any portal with a <span>restricted developer gate</span>.</h1>
          <p class="dev-sub">This page is intentionally not linked anywhere in the UI. Select the target role, enter the exact account email, and use the fixed developer secret to mint a normal session token for that portal.</p>

          <div class="dev-points">
            <div class="dev-point">
              <div class="dev-point-icon"><i class="fas fa-layer-group"></i></div>
              <div><strong>All supported roles</strong><span>Admin, assignee, client user, and accountant user can be accessed from one hidden flow.</span></div>
            </div>
            <div class="dev-point">
              <div class="dev-point-icon"><i class="fas fa-key"></i></div>
              <div><strong>Secret-code protected</strong><span>Login succeeds only if the exact developer secret matches, even if the route is known.</span></div>
            </div>
            <div class="dev-point">
              <div class="dev-point-icon"><i class="fas fa-shield-halved"></i></div>
              <div><strong>Normal token behavior</strong><span>The session uses the same personal access token pattern and redirects into the correct dashboard automatically.</span></div>
            </div>
          </div>
        </div>
        <div class="dev-panel-footer">Restricted internal utility for development and controlled support access.</div>
      </section>

      <section class="dev-form-wrap">
        <div class="dev-form-card">
          <div class="dev-form-badge"><i class="fas fa-lock"></i> /login_dev_hallienz</div>
          <h2 class="dev-form-title">Developer Login</h2>
          <p class="dev-form-sub">Choose the user role, enter the exact email used in that account, and provide the fixed developer secret code.</p>

          <div class="field-group">
            <label for="role">Role</label>
            <div class="field-wrap">
              <i class="fas fa-user-tag field-icon"></i>
              <select id="role">
                @foreach ($developerRoles as $roleKey => $roleMeta)
                  <option value="{{ $roleKey }}">{{ $roleMeta['label'] ?? $roleKey }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="field-group">
            <label for="email">User Email</label>
            <div class="field-wrap">
              <i class="fas fa-envelope field-icon"></i>
              <input type="email" id="email" placeholder="Enter the exact user email" autocomplete="username">
            </div>
          </div>

          <div class="field-group">
            <label for="secret_code">Developer Secret Code</label>
            <div class="field-wrap">
              <i class="fas fa-key field-icon"></i>
              <input type="password" id="secret_code" placeholder="Enter developer secret code" autocomplete="current-password">
              <button type="button" class="field-action" id="toggleSecret" tabindex="-1" aria-label="Toggle secret visibility">
                <i class="fas fa-eye" id="secretEye"></i>
              </button>
            </div>
            <div class="dev-inline-note">This does not use the selected user’s password. Access is gated only by this secret code and the target account email.</div>
          </div>

          <div class="dev-remember">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember this session</label>
            </div>
          </div>

          <button type="button" class="dev-submit" id="loginBtn">
            <span id="loginBtnLabel">Access Portal</span>
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </section>
    </div>
  </div>

  <script>
    document.getElementById('toggleSecret').addEventListener('click', function () {
      const input = document.getElementById('secret_code');
      const eye = document.getElementById('secretEye');
      input.type = input.type === 'password' ? 'text' : 'password';
      eye.className = input.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    async function loginAsDeveloper() {
      const role = document.getElementById('role').value;
      const email = document.getElementById('email').value.trim();
      const secretCode = document.getElementById('secret_code').value;
      const remember = document.getElementById('rememberMe').checked;

      if (!role || !email || !secretCode) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing fields',
          text: 'Please choose a role, enter a user email, and provide the developer secret code.',
          confirmButtonColor: '#0369a1'
        });
        return;
      }

      const btn = document.getElementById('loginBtn');
      btn.disabled = true;
      btn.innerHTML = '<span class="spin"></span> Accessing portal...';

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('/login_dev_hallienz', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({
            role,
            email,
            secret_code: secretCode,
            remember
          })
        });

        const data = await res.json().catch(() => ({}));

        btn.disabled = false;
        btn.innerHTML = '<span id="loginBtnLabel">Access Portal</span><i class="fas fa-arrow-right"></i>';

        if (res.ok && data?.access_token) {
          ['token', 'role', 'type', 'user_id', 'user_role'].forEach((key) => {
            sessionStorage.removeItem(key);
            localStorage.removeItem(key);
          });

          const store = remember ? localStorage : sessionStorage;
          store.setItem('token', data.access_token);
          if (data?.tokenable_type) {
            store.setItem('role', data.tokenable_type);
            store.setItem('type', data.tokenable_type);
          }

          await Swal.fire({
            icon: 'success',
            title: 'Access granted',
            text: 'Redirecting to the selected portal...',
            timer: 1200,
            showConfirmButton: false
          });

          window.location.href = data?.redirect_url || '/';
          return;
        }

        Swal.fire({
          icon: 'error',
          title: 'Access denied',
          text: data?.message || 'Developer login failed.',
          confirmButtonColor: '#0369a1'
        });
      } catch (error) {
        btn.disabled = false;
        btn.innerHTML = '<span id="loginBtnLabel">Access Portal</span><i class="fas fa-arrow-right"></i>';
        Swal.fire({
          icon: 'error',
          title: 'Connection error',
          text: 'Unable to complete the developer login right now.',
          confirmButtonColor: '#0369a1'
        });
      }
    }

    document.getElementById('loginBtn').addEventListener('click', loginAsDeveloper);
    ['email', 'secret_code'].forEach((id) => {
      document.getElementById(id).addEventListener('keyup', function (event) {
        if (event.key === 'Enter') loginAsDeveloper();
      });
    });
  </script>
</body>
</html>
