<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RTS – Research Tracking System, Qatar University">

    <title>RTS – Research Tracking System</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --qu-primary: #8d1b3d;
            --qu-primary-dark: #63102b;
            --qu-primary-darker: #221f20;
            --qu-gold: #cf9a2f;
            --qu-gold-light: #e8c36a;
            --qu-sand: #ab8140;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f0f13;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }
        .welcome-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .welcome-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(141,27,61,0.25) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 0%, rgba(207,154,47,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 70% 40% at 50% 100%, rgba(141,27,61,0.15) 0%, transparent 50%);
        }
        .welcome-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .welcome-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .welcome-nav__brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .welcome-nav__brand i {
            font-size: 1.5rem;
            color: var(--qu-gold);
        }
        .welcome-nav__links {
            display: flex;
            gap: 0.75rem;
        }
        .welcome-nav__links .btn {
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-login {
            color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-login:hover { background: rgba(255,255,255,0.1); color: white; }
        .btn-register {
            background: var(--qu-primary);
            color: white;
        }
        .btn-register:hover { background: var(--qu-primary-dark); }

        .welcome-hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .welcome-hero__icon {
            font-size: 3.5rem;
            color: var(--qu-gold);
            margin-bottom: 1.5rem;
            display: block;
        }
        .welcome-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #fff 0%, var(--qu-gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .welcome-hero p {
            font-size: 1.125rem;
            color: rgba(255,255,255,0.6);
            max-width: 560px;
            margin: 0 auto 2rem;
        }
        .welcome-hero .btn-get-started {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: var(--qu-primary);
            color: white;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 1rem;
            box-shadow: 0 1px 2px rgba(22,19,26,.07), 0 0px 1px rgba(22,19,26,.06);
        }
        .welcome-hero .btn-get-started:hover {
            background: var(--qu-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(141,27,61,0.3);
        }

        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }
        .feature-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }
        .feature-card:hover {
            background: rgba(255,255,255,0.07);
            transform: translateY(-4px);
            border-color: rgba(207,154,47,0.2);
        }
        .feature-card i {
            font-size: 2rem;
            color: var(--qu-gold);
            margin-bottom: 0.75rem;
        }
        .feature-card h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .feature-card p {
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.5);
        }

        .welcome-footer {
            text-align: center;
            padding: 2rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.3);
        }

        @media (max-width: 640px) {
            .welcome-nav { padding: 1rem; flex-wrap: wrap; gap: 0.75rem; }
            .welcome-hero h1 { font-size: 2rem; }
            .welcome-hero p { font-size: 1rem; }
            .welcome-features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="welcome-wrapper">
    <div class="welcome-bg"></div>
    <div class="welcome-content">
        <nav class="welcome-nav">
            <a href="#" class="welcome-nav__brand">
                <i class="fas fa-flask"></i>
                <span>RTS</span>
            </a>
            <div class="welcome-nav__links">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/home') }}" class="btn btn-register">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-login">
                            <i class="fas fa-sign-in-alt"></i> Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <section class="welcome-hero">
            <div>
                <i class="fas fa-flask welcome-hero__icon"></i>
                <h1>Learning Resource<br>Tracking System</h1>
                <p>A centralized platform for managing student research projects, grants, cycles, and evaluations at Qatar University.</p>
                @auth
                    <a href="{{ url('/home') }}" class="btn-get-started">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-get-started">
                        <i class="fas fa-sign-in-alt"></i> Get Started
                    </a>
                @endauth
            </div>
        </section>

        <section class="welcome-features">
            <div class="feature-card">
                <i class="fas fa-project-diagram"></i>
                <h4>Project Management</h4>
                <p>Create and track research projects through complete lifecycle.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-sync-alt"></i>
                <h4>Cycle Tracking</h4>
                <p>Manage grant cycles and reporting deadlines.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-check-double"></i>
                <h4>Review & Grading</h4>
                <p>Streamlined review and grading workflow.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-file-alt"></i>
                <h4>Report Submission</h4>
                <p>Submit progress and final reports digitally.</p>
            </div>
        </section>

        <footer class="welcome-footer">
            &copy; {{ date('Y') }} Qatar University. All rights reserved.
        </footer>
    </div>
</div>
</body>
</html>
