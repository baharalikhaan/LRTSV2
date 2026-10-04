<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Research Tracking System (RTS) — Qatar University's internal grant lifecycle platform: proposals, progress reports, grading and research outcomes in one system.">
    <title>Research Tracking System (RTS) — Qatar University</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/qu-theme.css">

    <style>
        /* ─── Base ─── */
        html { scroll-behavior: smooth; }
        body {
            font-family: "Inter", system-ui, sans-serif;
            color: var(--ink-800);
            background: #fff;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3 { font-family: "Fraunces", Georgia, serif; }
        a { color: var(--brand-600); }

        .wk-skip {
            position: absolute; left: -9999px; top: 0; z-index: 200;
            background: var(--brand-800); color: #fff; padding: 10px 18px;
            font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 0 0 8px 0;
        }
        .wk-skip:focus { left: 0; }

        /* ─── Sticky header ─── */
        .wk-nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--ink-100);
        }
        .wk-nav-inner {
            max-width: 1180px; margin: 0 auto;
            padding: 10px clamp(18px, 4vw, 40px);
            display: flex; align-items: center; gap: 18px;
        }
        .wk-brand { display: flex; align-items: center; gap: 14px; text-decoration: none; min-width: 0; }
        .wk-brand img { width: clamp(130px, 16vw, 176px); height: auto; display: block; }
        .wk-brand-text { line-height: 1.15; min-width: 0; }
        .wk-brand-text strong {
            display: block; font-size: 14.5px; font-weight: 700; color: var(--ink-900);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .wk-brand-text span { display: block; font-size: 10.5px; color: var(--ink-500); letter-spacing: .04em; }
        .wk-nav-links { display: flex; align-items: center; gap: clamp(10px, 2vw, 26px); margin-left: auto; }
        .wk-nav-links a {
            font-size: 13.5px; font-weight: 600; color: var(--ink-600); text-decoration: none;
            padding: 8px 2px; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s;
        }
        .wk-nav-links a:hover { color: var(--brand-700); border-bottom-color: var(--gold-500); }
        .wk-nav-cta {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand-700); color: #fff !important;
            padding: 9px 20px !important; border-radius: var(--fluent-radius-sm);
            border-bottom: none !important;
            font-size: 13.5px; font-weight: 700; text-decoration: none;
            transition: background .15s;
        }
        .wk-nav-cta:hover { background: var(--brand-800); }
        .wk-burger {
            display: none; margin-left: auto;
            background: none; border: 1px solid var(--ink-200); border-radius: 8px;
            width: 40px; height: 38px; font-size: 16px; color: var(--ink-700); cursor: pointer;
        }

        /* ─── Hero ─── */
        .wk-hero {
            position: relative; overflow: hidden;
            background:
                linear-gradient(115deg, rgba(76,12,33,.32), rgba(122,22,54,.20) 55%, rgba(141,27,61,.06)),
                url('/images/welcome-bg.svg') center / cover no-repeat var(--brand-800);
            color: #fff;
        }
        .wk-hero-inner {
            max-width: 1180px; margin: 0 auto;
            padding: clamp(48px, 8vh, 92px) clamp(18px, 4vw, 40px) clamp(30px, 5vh, 48px);
            display: flex; align-items: center; gap: clamp(24px, 5vw, 64px);
        }
        .wk-hero-text { flex: 1 1 54%; min-width: 0; }
        .wk-kicker {
            display: inline-flex; align-items: center; gap: 10px;
            font-size: 11.5px; font-weight: 700; letter-spacing: 2.6px; text-transform: uppercase;
            color: var(--gold-400); margin-bottom: 18px;
        }
        .wk-kicker::before { content: ''; width: 36px; height: 2px; background: currentColor; opacity: .75; }
        .wk-hero h1 {
            font-weight: 600; font-size: clamp(32px, 5vw, 56px); line-height: 1.1;
            letter-spacing: -.6px; margin: 0;
        }
        .wk-hero h1 em { font-style: normal; color: var(--gold-400); }
        .wk-slogans { display: flex; flex-wrap: wrap; gap: 9px; margin: 20px 0 0; padding: 0; list-style: none; }
        .wk-slogans li {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12.5px; font-weight: 600; color: rgba(250,247,240,.94);
            background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.20);
            padding: 7px 14px; border-radius: 999px;
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        }
        .wk-slogans li i { color: var(--gold-400); font-size: 11px; }
        .wk-hero p.lead {
            font-size: clamp(14px, 1.6vw, 16px); line-height: 1.75;
            color: rgba(250,247,240,.86); max-width: 560px;
            margin: 20px 0 0;
        }
        .wk-hero-ctas { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .wk-btn {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 12px 26px; border-radius: var(--fluent-radius-sm);
            font-size: 14px; font-weight: 700; text-decoration: none;
            transition: transform .15s, background .15s, border-color .15s;
        }
        .wk-btn:hover { transform: translateY(-1px); }
        .wk-btn-gold { background: var(--gold-500); color: #3b1220 !important; border: 1px solid var(--gold-500); }
        .wk-btn-gold:hover { background: var(--gold-400); border-color: var(--gold-400); }
        .wk-btn-ghost { background: transparent; color: #fff !important; border: 1px solid rgba(255,255,255,.45); }
        .wk-btn-ghost:hover { background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.7); }

        .wk-hero-art { flex: 1 1 40%; min-width: 0; display: flex; align-items: flex-end; justify-content: center; }
        .wk-hero-art img { width: 100%; max-width: 480px; height: auto; display: block; }

        /* ─── Sections ─── */
        .wk-sec { padding: clamp(52px, 8vh, 84px) clamp(18px, 4vw, 40px); }
        .wk-sec-alt { background: var(--ink-50); }
        .wk-sec-inner { max-width: 1180px; margin: 0 auto; }
        .wk-sec-head { text-align: center; max-width: 660px; margin: 0 auto clamp(30px, 5vh, 46px); }
        .wk-sec-kicker {
            display: inline-flex; align-items: center; gap: 9px;
            font-size: 11px; font-weight: 800; letter-spacing: 2.2px; text-transform: uppercase;
            color: var(--brand-600); margin-bottom: 12px;
        }
        .wk-sec-kicker::before, .wk-sec-kicker::after { content: ''; width: 26px; height: 2px; background: var(--gold-500); }
        .wk-sec-head h2 {
            font-size: clamp(24px, 3.4vw, 34px); font-weight: 600; color: var(--ink-900);
            margin: 0 0 10px; letter-spacing: -.3px;
        }
        .wk-sec-head p { font-size: 14.5px; line-height: 1.7; color: var(--ink-500); margin: 0; }

        /* feature cards */
        .wk-grid { display: grid; gap: 18px; }
        .wk-grid-4 { grid-template-columns: repeat(4, 1fr); }
        .wk-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .wk-card {
            background: #fff; border: 1px solid var(--ink-100); border-radius: var(--fluent-radius-md);
            padding: 26px 22px; box-shadow: var(--fluent-depth-2);
            transition: transform .18s, box-shadow .18s;
        }
        .wk-card:hover { transform: translateY(-3px); box-shadow: var(--fluent-depth-8); }
        .wk-card-ico {
            width: 46px; height: 46px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; margin-bottom: 16px;
        }
        .wk-card h3 { font-size: 16.5px; font-weight: 600; color: var(--ink-900); margin: 0 0 8px; }
        .wk-card p { font-size: 13.5px; line-height: 1.65; color: var(--ink-500); margin: 0; }
        .wk-ico-brand { background: #fdf2f4; color: var(--brand-600); }
        .wk-ico-info  { background: #eff6ff; color: #1d4ed8; }
        .wk-ico-gold  { background: #fffbeb; color: #b45309; }
        .wk-ico-ok    { background: #ecfdf5; color: #047857; }

        /* ─── Login section ─── */
        .wk-login-wrap {
            display: grid; grid-template-columns: minmax(0, 430px) minmax(0, 1fr);
            gap: clamp(22px, 4vw, 44px); align-items: start;
        }
        .wk-login-card {
            background: #fff; border: 1px solid var(--ink-100); border-radius: var(--fluent-radius-md);
            box-shadow: var(--fluent-depth-8); overflow: hidden;
        }
        .wk-login-card-top {
            background: linear-gradient(135deg, var(--brand-800), var(--brand-600));
            color: #fff; padding: 24px 28px; text-align: center;
        }
        .wk-login-card-top h3 { font-size: 20px; font-weight: 600; margin: 0; }
        .wk-login-card-top p { font-size: 12.5px; color: rgba(255,255,255,.8); margin: 6px 0 0; }
        .wk-login-card-body { padding: 26px 28px 28px; }
        .wk-login-card label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-700); margin-bottom: 6px; }
        .wk-login-card .form-control { width: 100%; }
        .wk-login-row { display: flex; justify-content: space-between; align-items: center; margin: 4px 0 18px; }
        .wk-login-row label { display: flex; align-items: center; gap: 8px; font-weight: 400; font-size: 13px; cursor: pointer; margin: 0; }
        .wk-login-row label input { width: 15px; height: 15px; accent-color: var(--brand-500); }
        .wk-login-row a { font-size: 12.5px; font-weight: 500; }
        .wk-or { display: flex; align-items: center; gap: 12px; margin: 18px 0; }
        .wk-or::before, .wk-or::after { content: ''; flex: 1; height: 1px; background: var(--ink-100); }
        .wk-or span { font-size: 10.5px; color: var(--ink-400); text-transform: uppercase; letter-spacing: 1px; }

        .wk-info-block { margin-bottom: 26px; }
        .wk-info-block h3 {
            font-family: "Inter", sans-serif;
            display: flex; align-items: center; gap: 10px;
            font-size: 15.5px; font-weight: 700; color: var(--ink-900); margin: 0 0 14px;
        }
        .wk-info-block h3 i { color: var(--brand-600); font-size: 15px; }
        .wk-way {
            display: flex; gap: 14px; background: #fff; border: 1px solid var(--ink-100);
            border-radius: var(--fluent-radius-md); padding: 16px 18px; margin-bottom: 12px;
        }
        .wk-way-ico {
            width: 38px; height: 38px; border-radius: 9px; flex-shrink: 0;
            background: #fdf2f4; color: var(--brand-600);
            display: flex; align-items: center; justify-content: center; font-size: 15px;
        }
        .wk-way b { display: block; font-size: 13.5px; color: var(--ink-800); margin-bottom: 3px; }
        .wk-way p { font-size: 12.5px; line-height: 1.6; color: var(--ink-500); margin: 0; }
        .wk-role-row {
            display: flex; gap: 14px; align-items: flex-start;
            padding: 13px 0; border-bottom: 1px dashed var(--ink-100);
        }
        .wk-role-row:last-child { border-bottom: none; }
        .wk-role-tag {
            flex-shrink: 0; font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            padding: 5px 10px; border-radius: 999px; min-width: 86px; text-align: center;
        }
        .wk-role-admin { background: #fdf2f4; color: var(--brand-700); }
        .wk-role-lpi   { background: #eff6ff; color: #1d4ed8; }
        .wk-role-rev   { background: #fffbeb; color: #b45309; }
        .wk-role-row p { font-size: 13px; line-height: 1.6; color: var(--ink-500); margin: 0; }
        .wk-role-row p b { color: var(--ink-800); }

        /* ─── Team ─── */
        .wk-team-card {
            background: #fff; border: 1px solid var(--ink-100); border-radius: var(--fluent-radius-md);
            padding: 24px 20px; text-align: center; box-shadow: var(--fluent-depth-2);
            display: flex; flex-direction: column; align-items: center;
            transition: transform .18s, box-shadow .18s;
        }
        .wk-team-card:hover { transform: translateY(-3px); box-shadow: var(--fluent-depth-8); }
        .wk-avatar {
            width: 60px; height: 60px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 18px; margin-bottom: 14px;
        }
        .wk-team-card h3 { font-family: "Inter", sans-serif; font-size: 15.5px; font-weight: 700; color: var(--ink-900); margin: 0 0 3px; }
        .wk-team-title { font-size: 12.5px; font-weight: 600; color: var(--brand-600); margin: 0 0 12px; }
        .wk-team-meta { list-style: none; padding: 0; margin: auto 0 0; display: grid; gap: 7px; }
        .wk-team-meta li { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 12px; color: var(--ink-500); }
        .wk-team-meta li i { color: var(--ink-400); width: 13px; font-size: 11.5px; }
        .wk-team-meta a { color: var(--ink-600); text-decoration: none; }
        .wk-team-meta a:hover { color: var(--brand-600); text-decoration: underline; }

        /* ─── Contact ─── */
        .wk-contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; }
        .wk-contact-card {
            background: #fff; border: 1px solid var(--ink-100); border-radius: var(--fluent-radius-md);
            padding: 26px 22px; text-align: center; box-shadow: var(--fluent-depth-2);
        }
        .wk-contact-card .wk-card-ico { margin: 0 auto 14px; }
        .wk-contact-card h3 { font-family: "Inter", sans-serif; font-size: 14.5px; font-weight: 700; color: var(--ink-900); margin: 0 0 7px; }
        .wk-contact-card p { font-size: 13px; line-height: 1.6; color: var(--ink-500); margin: 0; }
        .wk-contact-card a { color: var(--brand-600); font-weight: 600; text-decoration: none; }
        .wk-contact-card a:hover { text-decoration: underline; }

        /* ─── Footer ─── */
        .wk-footer {
            background: var(--ink-900); color: rgba(255,255,255,.72);
            padding: 30px clamp(18px, 4vw, 40px);
        }
        .wk-footer-inner {
            max-width: 1180px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap;
        }
        .wk-footer-brand { display: flex; align-items: center; gap: 12px; }
        .wk-footer-brand img { width: clamp(110px, 13vw, 150px); height: auto; opacity: .95; }
        .wk-footer-brand div { font-size: 12px; line-height: 1.5; }
        .wk-footer-brand b { display: block; color: #fff; font-size: 13px; }
        .wk-footer-links { display: flex; gap: 20px; flex-wrap: wrap; }
        .wk-footer-links a { color: rgba(255,255,255,.72); font-size: 12.5px; font-weight: 500; text-decoration: none; }
        .wk-footer-links a:hover { color: var(--gold-400); }
        .wk-footer-copy { font-size: 11.5px; color: rgba(255,255,255,.45); }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .wk-grid-4 { grid-template-columns: repeat(2, 1fr); }
            .wk-contact-grid { grid-template-columns: repeat(2, 1fr); }
            .wk-login-wrap { grid-template-columns: 1fr; }
        }
        @media (max-width: 820px) {
            .wk-burger { display: block; }
            .wk-nav-links {
                display: none;
                position: absolute; top: 100%; left: 0; right: 0;
                background: #fff; border-bottom: 1px solid var(--ink-100);
                flex-direction: column; align-items: stretch; gap: 0;
                padding: 8px 18px 16px; box-shadow: var(--fluent-depth-8);
            }
            .wk-nav-links.wk-open { display: flex; }
            .wk-nav-links a { padding: 12px 4px; border-bottom: 1px solid var(--ink-50); }
            .wk-nav-links a:last-child { border-bottom: none; }
            .wk-nav-cta { margin-top: 8px; justify-content: center; }
            .wk-hero-inner { flex-direction: column; text-align: center; }
            .wk-kicker { justify-content: center; }
            .wk-kicker::before { display: none; }
            .wk-slogans, .wk-hero-ctas { justify-content: center; }
            .wk-hero p.lead { margin-inline: auto; }
            .wk-hero-art { order: -1; }
            .wk-hero-art img { max-width: 320px; }
            .wk-grid-3 { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .wk-grid-4, .wk-contact-grid { grid-template-columns: 1fr; }
            .wk-brand-text strong { font-size: 13px; }
            .wk-brand-text span { display: none; }
        }
    </style>
</head>
<body>

<a class="wk-skip" href="#main">Skip to content</a>

{{-- ═══════ Sticky header — QU identity on every scroll ═══════ --}}
<header class="wk-nav">
    <div class="wk-nav-inner">
        <a class="wk-brand" href="/">
            <img src="/images/logo-maroon.png" alt="Qatar University">
            <span class="wk-brand-text">
                <strong>Research Tracking System</strong>
                <span>Office of Research &amp; Graduate Studies</span>
            </span>
        </a>
        <button class="wk-burger" id="wk-burger" aria-label="Toggle menu" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
        <nav class="wk-nav-links" id="wk-nav-links" aria-label="Main navigation">
            <a href="#platform">System</a>
            <a href="#team">Our Team</a>
            <a href="#contact">Contact</a>
            <a href="#login" class="wk-nav-cta"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        </nav>
    </div>
</header>

<main id="main">

    {{-- ═══════ Hero — title & slogans ═══════ --}}
    <section class="wk-hero">
        <div class="wk-hero-inner">
            <div class="wk-hero-text">
                <span class="wk-kicker">Office of Research &amp; Graduate Studies</span>
                <h1>Research Tracking System<br><em>Qatar University</em></h1>

                <ul class="wk-slogans">
                    <li><i class="fa-solid fa-circle-check"></i> Research Project Registration</li>
                    <li><i class="fa-solid fa-circle-check"></i> Project Progress Update</li>
                    <li><i class="fa-solid fa-circle-check"></i> Progress Grading</li>
                </ul>

                <p class="lead">
                    RTS records the internal grant lifecycle: proposal registration, reviewer
                    assignment, progress reports, final grading and budget utilization.
                </p>

                <div class="wk-hero-ctas">
                    <a href="#login" class="wk-btn wk-btn-gold"><i class="fa-solid fa-right-to-bracket"></i> Sign in to RTS</a>
                </div>
            </div>

            <div class="wk-hero-art" aria-hidden="true">
                <img src="/images/qu-building.svg" alt="">
            </div>
        </div>
    </section>

    {{-- ═══════ Platform — what RTS covers ═══════ --}}
    <section class="wk-sec" id="platform">
        <div class="wk-sec-inner">
            <div class="wk-sec-head">
                <span class="wk-sec-kicker">The System</span>
                <h2>What the system covers</h2>
                <p>RTS maintains one record of every research project, shared by administrators, investigators and reviewers.</p>
            </div>

            <div class="wk-grid wk-grid-4">
                <div class="wk-card">
                    <div class="wk-card-ico wk-ico-brand"><i class="fa-solid fa-diagram-project"></i></div>
                    <h3>Project Lifecycle</h3>
                    <p>Track projects from registration, progress reporting and assignment through grading to completion.</p>
                </div>
                <div class="wk-card">
                    <div class="wk-card-ico wk-ico-gold"><i class="fa-solid fa-check-double"></i></div>
                    <h3>Reviewer Workflow</h3>
                    <p>Proposal acceptance and rejection, grading submission and report card generation in one flow.</p>
                </div>
                <div class="wk-card">
                    <div class="wk-card-ico wk-ico-info"><i class="fa-solid fa-user-friends"></i></div>
                    <h3>Reviewer Assignment</h3>
                    <p>Two reviewers per project with mutual-exclusion validation.</p>
                </div>
                <div class="wk-card">
                    <div class="wk-card-ico wk-ico-ok"><i class="fa-solid fa-tasks"></i></div>
                    <h3>Outcomes Management</h3>
                    <p>Record and track project outcomes &mdash; publications, intellectual property and student impact.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════ Sign in — login section + login information ═══════ --}}
    <section class="wk-sec wk-sec-alt" id="login">
        <div class="wk-sec-inner">
            <div class="wk-sec-head">
                <span class="wk-sec-kicker">Sign In</span>
                <h2>Sign in to RTS</h2>
                <p>{{ in_array(strtolower(config('app.login_mode')), ['saml', 'mock']) ? 'Sign in with your Qatar University account.' : 'Sign in with your Qatar University account, or with your RTS email and password.' }}</p>
            </div>

            <div class="wk-login-wrap">

                {{-- Login form --}}
                <div class="wk-login-card">
                    <div class="wk-login-card-top">
                        <h3>{{ in_array(strtolower(config('app.login_mode')), ['saml', 'mock']) ? 'Sign in with Qatar University' : 'Login to your account' }}</h3>
                        <p>{{ in_array(strtolower(config('app.login_mode')), ['saml', 'mock']) ? 'Single Sign-On via the university identity manager' : 'Enter your credentials to continue' }}</p>
                    </div>
                    <div class="wk-login-card-body">
                        @if(strtolower(config('app.login_mode')) === 'saml' || strtolower(config('app.login_mode')) === 'mock')
                            @if(strtolower(config('app.login_mode')) === 'mock')
                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:12px;">
                                <span style="font-size:9px;font-weight:700;letter-spacing:.08em;padding:2px 8px;border-radius:999px;background:#fff4e5;color:#b45309;border:1px solid #fcd9a8;">SAML TEST MODE</span>
                            </div>
                            @endif
                            <a href="{{ route('saml.login') }}" class="btn-primary"
                               style="width:100%;padding:11px 20px;font-size:14px;justify-content:center;text-decoration:none;display:flex;align-items:center;gap:10px;">
                                <i class="fa-solid fa-university"></i> Sign in with Qatar University
                            </a>
                        @else
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div style="margin-bottom:15px;">
                                <label for="email">Email Address</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}"
                                       required autocomplete="email"
                                       placeholder="name@qu.edu.qa">
                                @error('email')
                                    <span style="font-size:12px;color:var(--danger);margin-top:4px;display:block;"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div style="margin-bottom:15px;">
                                <label for="password">Password</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="current-password"
                                       placeholder="Enter your password">
                                @error('password')
                                    <span style="font-size:12px;color:var(--danger);margin-top:4px;display:block;"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="wk-login-row">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    Remember Me
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">Forgot Password?</a>
                                @endif
                            </div>

                            <button type="submit" class="btn-primary" style="width:100%;padding:11px 20px;font-size:14px;justify-content:center;">
                                <i class="fa-solid fa-right-to-bracket"></i> Login
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Login information --}}
                <div>
                    <div class="wk-info-block">
                        <h3><i class="fa-solid fa-key"></i> Ways to sign in</h3>
                        <div class="wk-way">
                            <span class="wk-way-ico"><i class="fa-solid fa-shield-halved"></i></span>
                            <div>
                                <b>Qatar University SSO (recommended)</b>
                                <p>Click &ldquo;Sign in with Qatar University&rdquo; and use your QU ADFS credentials &mdash; no separate RTS password needed.</p>
                            </div>
                        </div>
                        <div class="wk-way">
                            <span class="wk-way-ico"><i class="fa-solid fa-envelope"></i></span>
                            <div>
                                <b>Email &amp; password</b>
                                <p>Sign in with the email address issued by the Office of Research &amp; Graduate Studies. Forgotten passwords reset via the &ldquo;Forgot Password?&rdquo; link, or contact support below.</p>
                            </div>
                        </div>
                    </div>

                    <div class="wk-info-block">
                        <h3><i class="fa-solid fa-users-gear"></i> Who uses RTS</h3>
                        <div class="wk-role-row">
                            <span class="wk-role-tag wk-role-admin">Admin</span>
                            <p><b>Post-award administrators</b> &mdash; register projects, configure cycles, assign reviewers and oversee the whole portfolio.</p>
                        </div>
                        <div class="wk-role-row">
                            <span class="wk-role-tag wk-role-lpi">LPI</span>
                            <p><b>Lead Project Investigators</b> &mdash; submit proposals, upload progress &amp; final reports, and track approval status.</p>
                        </div>
                        <div class="wk-role-row">
                            <span class="wk-role-tag wk-role-rev">Reviewer</span>
                            <p><b>Assigned reviewers</b> &mdash; accept or reject proposals, grade submissions and generate report cards.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════ Team ═══════ --}}
    <section class="wk-sec" id="team">
        <div class="wk-sec-inner">
            <div class="wk-sec-head">
                <span class="wk-sec-kicker">Post-Award Team</span>
                <h2>Our Team</h2>
                <p>Office of Research &amp; Graduate Studies, Qatar University.</p>
            </div>

            @php
                $palettes = [
                    ['bg' => '#fdf2f4', 'fg' => 'var(--brand-600)',  'tile' => 'var(--brand-100)'],
                    ['bg' => '#eff6ff', 'fg' => '#1d4ed8',           'tile' => '#dbeafe'],
                    ['bg' => '#fffbeb', 'fg' => '#b45309',           'tile' => '#fef3c7'],
                    ['bg' => '#ecfdf5', 'fg' => '#047857',           'tile' => '#d1fae5'],
                ];
            @endphp

            @if($teamMembers->isNotEmpty())
                <div class="wk-grid" style="grid-template-columns:repeat(auto-fit,minmax(210px,1fr));">
                    @foreach($teamMembers as $m)
                        @php
                            $initials = collect(explode(' ', $m->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                            $c = $palettes[($m->id - 1) % count($palettes)];
                        @endphp
                        <div class="wk-team-card">
                            <div class="wk-avatar" style="background:{{ $c['tile'] }}; color:{{ $c['fg'] }};">{{ $initials }}</div>
                            <h3>{{ $m->name }}</h3>
                            <p class="wk-team-title">{{ $m->introduction }}</p>
                            <ul class="wk-team-meta">
                                @if($m->email)
                                    <li><i class="fa-solid fa-envelope"></i><a href="mailto:{{ $m->email }}">{{ $m->email }}</a></li>
                                @endif
                                @if($m->phone)
                                    <li><i class="fa-solid fa-phone"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $m->phone) }}">{{ $m->phone }}</a></li>
                                @endif
                                @if($m->address)
                                    <li><i class="fa-solid fa-location-dot"></i>{{ $m->address }}</li>
                                @endif
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="text-align:center;color:var(--ink-400);font-size:13.5px;">Team directory is being updated &mdash; please check <a href="{{ route('about.team') }}">about/team</a>.</p>
            @endif
        </div>
    </section>

    {{-- ═══════ Contact ═══════ --}}
    <section class="wk-sec wk-sec-alt" id="contact">
        <div class="wk-sec-inner">
            <div class="wk-sec-head">
                <span class="wk-sec-kicker">Contact</span>
                <h2>Contact information</h2>
                <p>Reach the Post-Award office for system support and research administration questions.</p>
            </div>

            <div class="wk-contact-grid">
                <div class="wk-contact-card">
                    <div class="wk-card-ico wk-ico-brand"><i class="fa-solid fa-envelope"></i></div>
                    <h3>Email Support</h3>
                    <p><a href="mailto:rts@rts.edu.qa">rts@rts.edu.qa</a><br>System and account queries</p>
                </div>
                <div class="wk-contact-card">
                    <div class="wk-card-ico wk-ico-gold"><i class="fa-regular fa-clock"></i></div>
                    <h3>Office Hours</h3>
                    <p>Sunday &ndash; Thursday<br>7:30 AM &ndash; 3:00 PM</p>
                </div>
                <div class="wk-contact-card">
                    <div class="wk-card-ico wk-ico-ok"><i class="fa-solid fa-location-dot"></i></div>
                    <h3>Visit Us</h3>
                    <p>Building H10, Zone 1<br>Qatar University Campus, Doha</p>
                </div>
            </div>
        </div>
    </section>

</main>

{{-- ═══════ Footer ═══════ --}}
<footer class="wk-footer">
    <div class="wk-footer-inner">
        <div class="wk-footer-brand">
            <img src="/images/logo.png" alt="Qatar University">
            <div>
                <b>Research Tracking System</b>
                Office of Research &amp; Graduate Studies
            </div>
        </div>
        <nav class="wk-footer-links" aria-label="Footer">
            <a href="{{ route('about.team') }}">Our Team</a>
            <a href="#login">Sign In</a>
            <a href="mailto:rts@rts.edu.qa">rts@rts.edu.qa</a>
        </nav>
        <span class="wk-footer-copy">&copy; {{ date('Y') }} Qatar University &bull; RTS &bull; All rights reserved.</span>
    </div>
</footer>

<script>
    (function () {
        var burger = document.getElementById('wk-burger');
        var links = document.getElementById('wk-nav-links');
        if (burger && links) {
            burger.addEventListener('click', function () {
                var open = links.classList.toggle('wk-open');
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
                burger.innerHTML = open
                    ? '<i class="fa-solid fa-xmark"></i>'
                    : '<i class="fa-solid fa-bars"></i>';
            });
            links.addEventListener('click', function (e) {
                if (e.target.tagName === 'A') {
                    links.classList.remove('wk-open');
                    burger.setAttribute('aria-expanded', 'false');
                    burger.innerHTML = '<i class="fa-solid fa-bars"></i>';
                }
            });
        }
    })();
</script>

</body>
</html>
