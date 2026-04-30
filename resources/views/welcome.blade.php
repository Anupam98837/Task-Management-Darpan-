<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Darpan - Legmed Task Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0e27;
            color: #ffffff;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        .main-container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
        }
        
        .left-section {
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #2a2f4a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 40px;
            position: relative;
            overflow: hidden;
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
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, transparent 70%);
            bottom: -80px;
            right: -80px;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, transparent 70%);
            top: 50%;
            left: 40%;
            animation-delay: 10s;
        }
        
        @keyframes float-shape {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(50px, -50px) rotate(120deg); }
            66% { transform: translate(-30px, 40px) rotate(240deg); }
        }
        
        .mesh-gradient {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(at 20% 30%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(at 80% 70%, rgba(168, 85, 247, 0.15) 0%, transparent 50%),
                radial-gradient(at 50% 50%, rgba(14, 165, 233, 0.1) 0%, transparent 50%);
        }
        
        .content-wrapper {
            position: relative;
            z-index: 2;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            position: relative;
        }
        
        .logo-icon::after {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 16px;
            z-index: -1;
            opacity: 0.5;
            filter: blur(8px);
        }
        
        .logo-icon img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            display: block;
            filter: brightness(0) invert(1);
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .app-name {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }
        
        .hero-content {
            margin-bottom: 24px;
        }
        
        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 42px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.5;
            max-width: 500px;
            font-weight: 400;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateX(8px);
        }
        
        .feature-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.2) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon i {
            color: #a78bfa;
            font-size: 16px;
        }

        .feature-text {
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
        }
        
        .right-section {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
        }
        
        .right-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.03) 0%, transparent 50%);
        }
        
        .login-container {
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 2;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 22px;
        }
        
        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 30px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 15px;
        }
        
        .portal-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .portal-card {
            text-decoration: none;
            display: block;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 16px 18px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        
        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6366f1 0%, #a855f7 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }
        
        .portal-card:hover {
            border-color: #6366f1;
            box-shadow: 
                0 20px 40px rgba(99, 102, 241, 0.15),
                0 0 0 1px rgba(99, 102, 241, 0.1) inset;
            transform: translateY(-4px) scale(1.01) !important;
        }
        
        .portal-card:hover::before {
            transform: scaleX(1);
        }
        
        .portal-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .portal-icon-wrapper {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #eef2ff 0%, #f3e8ff 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
            flex-shrink: 0;
        }
        
        .portal-card:hover .portal-icon-wrapper {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            transform: scale(1.05) rotate(5deg);
        }
        
        .portal-icon-wrapper i {
            font-size: 22px;
            color: #6366f1;
            transition: all 0.4s ease;
        }
        
        .portal-card:hover .portal-icon-wrapper i {
            color: white;
        }
        
        .portal-info {
            flex: 1;
        }
        
        .portal-name {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .portal-arrow {
            color: #6366f1;
            font-size: 18px;
            transition: transform 0.3s ease;
        }
        
        .portal-card:hover .portal-arrow {
            transform: translateX(5px);
        }
        
        .portal-description {
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 8px 0;
        }
        
        .divider-line {
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider-text {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-note p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        
        .footer-note i {
            color: #ef4444;
            margin: 0 4px;
            animation: heartbeat 1.5s ease-in-out infinite;
        }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.15); }
        }
        
        /* Page Load Animations */
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
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes fadeInShape {
            to {
                opacity: 1;
            }
        }
        
        .logo-section {
            animation: fadeInLeft 0.4s ease-out;
        }
        
        .hero-content {
            animation: fadeInLeft 0.4s ease-out 0.05s backwards;
        }
        
        .features-list {
            animation: fadeInLeft 0.4s ease-out 0.1s backwards;
        }
        
        .feature-item {
            opacity: 0;
            animation: fadeInLeft 0.3s ease-out forwards;
        }
        
        .feature-item:nth-child(1) {
            animation-delay: 0.15s;
        }
        
        .feature-item:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .feature-item:nth-child(3) {
            animation-delay: 0.25s;
        }
        
        .feature-item:nth-child(4) {
            animation-delay: 0.3s;
        }
        
        .login-header {
            animation: fadeInUp 0.4s ease-out 0.05s backwards;
        }
        
        .portal-cards {
            animation: fadeInUp 0.4s ease-out 0.1s backwards;
        }
        
        .portal-card {
            opacity: 0;
            animation: scaleIn 0.3s ease-out forwards;
        }
        
        .portal-card:nth-child(1) {
            animation-delay: 0.15s;
        }
        
        .portal-card:nth-child(3) {
            animation-delay: 0.25s;
        }

        .portal-card:nth-child(4) {
            animation-delay: 0.35s;
        }
        
        .divider {
            opacity: 0;
            animation: fadeInUp 0.3s ease-out 0.2s forwards;
        }
        
        .footer-note {
            animation: fadeInUp 0.4s ease-out 0.3s backwards;
        }
        
        .floating-shape {
            opacity: 0;
            animation: float-shape 15s ease-in-out infinite, fadeInShape 1.5s ease-out forwards;
        }
        
        .shape-1 {
            animation-delay: 0s, 0.3s;
        }
        
        .shape-2 {
            animation-delay: 5s, 0.6s;
        }
        
        .shape-3 {
            animation-delay: 10s, 0.9s;
        }
        
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            
            .left-section {
                padding: 42px 28px;
                min-height: 50vh;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .features-list {
                display: none;
            }
            
            .right-section {
                padding: 28px 24px;
            }
        }

        @media (max-width: 640px) {
            .left-section {
                padding: 40px 24px;
            }
            
            .right-section {
                padding: 24px 18px;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .login-title {
                font-size: 28px;
            }
            
            .portal-content {
                gap: 16px;
            }
            
            .portal-icon-wrapper {
                width: 60px;
                height: 60px;
            }
            
            .portal-icon-wrapper i {
                font-size: 26px;
            }
            
            .portal-name {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="left-section">
            <div class="animated-bg">
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
                <div class="mesh-gradient"></div>
            </div>
            
            <div class="content-wrapper">
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="{{ asset('/assets/media/images/legmedlogo_small.webp') }}" alt="Legmed Logo">
                    </div>
                    <div class="logo-text">
                        <span class="company-name">Legmed</span>
                        <span class="app-name">Darpan</span>
                    </div>
                </div>
                
                <div class="hero-content">
                    <h1 class="hero-title">Enterprise Task Management Reimagined</h1>
                    <p class="hero-subtitle">
                        Secure, scalable, and intelligent task management platform designed for modern enterprises
                    </p>
                </div>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="feature-text">Streamlined task assignment and tracking</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="feature-text">Real-time progress monitoring and deadlines</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="feature-text">Seamless collaboration across teams</span>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <span class="feature-text">Productivity analytics and performance insights</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right-section">
            <div class="login-container">
                <div class="login-header">
                    <h2 class="login-title">Choose Your Portal</h2>
                    <p class="login-subtitle">Select the appropriate portal to access your workspace</p>
                </div>
                
                <div class="portal-cards">
                    <a href="/admin/login" class="portal-card">
                        <div class="portal-content">
                            <div class="portal-icon-wrapper">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="portal-info">
                                <h3 class="portal-name">
                                    Admin Portal
                                    <i class="fas fa-arrow-right portal-arrow"></i>
                                </h3>
                                <p class="portal-description">
                                    Task assignment, team management, and performance tracking
                                </p>
                            </div>
                        </div>
                    </a>
                    
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">or</span>
                        <div class="divider-line"></div>
                    </div>
                    
                    <a href="/assignee/login" class="portal-card">
                        <div class="portal-content">
                            <div class="portal-icon-wrapper">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="portal-info">
                                <h3 class="portal-name">
                                    Assignee Portal
                                    <i class="fas fa-arrow-right portal-arrow"></i>
                                </h3>
                                <p class="portal-description">
                                    Access your assigned tasks, update progress, and collaborate
                                </p>
                            </div>
                        </div>
                    </a>

                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">or</span>
                        <div class="divider-line"></div>
                    </div>

                    <a href="/client-user/login" class="portal-card">
                        <div class="portal-content">
                            <div class="portal-icon-wrapper">
                                <i class="fas fa-building-user"></i>
                            </div>
                            <div class="portal-info">
                                <h3 class="portal-name">
                                    Client Portal
                                    <i class="fas fa-arrow-right portal-arrow"></i>
                                </h3>
                                <p class="portal-description">
                                    View jobs and client-related work inside your assigned client scope
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="footer-note">
                    <p>by Legmed © 2025</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
