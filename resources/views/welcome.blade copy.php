<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome - Research Tracking System (RTS)</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/qu-theme.css">

    <style>
        /* ─── Split layout: fixed viewport, no page scrolling ─── */
        html, body { height: 100%; overflow: hidden; }
        .wk-layout { display: flex; height: 100vh; }

        /* LEFT — 75% */
        .wk-main {
            flex: 1 1 75%; min-width: 0;
            display: flex; flex-direction: column;
            position: relative;
            background:
                linear-gradient(115deg, rgba(76,12,33,.30), rgba(122,22,54,.18) 55%, rgba(141,27,61,.05)),
                url('/images/welcome-bg.svg') center / cover no-repeat var(--brand-800);
            color: #fff;
        }

        /* floating QU logo — top-left corner */
        .wk-logo {
            position: absolute; top: clamp(16px, 3vh, 30px); left: clamp(22px, 3vw, 46px);
            z-index: 10;
        }
        .wk-logo img {
            width: clamp(156px, 27vh, 228px); height: auto;
        }

        /* middle — hero text + big building */
        .wk-middle {
            flex: 1; min-height: 0;
            display: flex; align-items: stretch;
            padding: clamp(64px, 11vh, 96px) clamp(20px, 3vw, 44px) 0;
            gap: clamp(16px, 3vw, 48px);
        }
        .wk-hero-text {
            flex: 0 1 46%;
            display: flex; flex-direction: column; justify-content: center;
            min-width: 0;
        }
        .wk-kicker {
            display: inline-flex; align-items: center; gap: 9px;
            font-size: clamp(10px, 1.1vh, 12px); font-weight: 700; letter-spacing: 2.4px; text-transform: uppercase;
            color: var(--gold-400); margin-bottom: clamp(10px, 2vh, 22px);
        }
        .wk-kicker::before { content: ''; width: 34px; height: 2px; background: currentColor; opacity: .7; }
        .wk-hero-text h1 {
            font-family: "Fraunces", serif; font-weight: 600;
            font-size: clamp(26px, 5.4vh, 52px); line-height: 1.13; letter-spacing: -.5px;
            margin: 0;
        }
        .wk-hero-text h1 em { font-style: normal; color: var(--gold-400); }
        .wk-hero-text p.lead {
            font-size: clamp(12.5px, 1.9vh, 15.5px); line-height: 1.7;
            color: rgba(250,247,240,.85);
            max-width: 480px;
            margin: clamp(10px, 2.2vh, 24px) 0 0;
        }

        /* big building illustration — anchored to the ground line */
        .wk-art {
            flex: 1 1 54%;
            min-width: 0;
            display: flex; align-items: flex-end; justify-content: center;
            padding-top: clamp(8px, 2vh, 24px);
        }
        .wk-art img { width: 100%; height: auto; max-height: 100%; display: block; }

        /* bottom strip — condensed info, fits without scrolling */
        .wk-strip {
            border-top: 1px solid rgba(250,247,240,.14);
            background: rgba(53,8,24,.42);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            padding: clamp(10px, 1.8vh, 16px) clamp(20px, 3vw, 44px);
            display: flex; align-items: center; justify-content: space-between; gap: 18px;
            flex-wrap: wrap;
        }
        .wk-strip-group { display: flex; align-items: center; gap: clamp(14px, 2.2vw, 30px); flex-wrap: wrap; }
        .wk-chip { display: flex; align-items: center; gap: 8px; font-size: clamp(11px, 1.5vh, 12.5px); color: rgba(250,247,240,.88); white-space: nowrap; }
        .wk-chip i { color: var(--gold-400); font-size: 12px; }
        .wk-chip a { color: #fff; font-weight: 600; text-decoration: none; border-bottom: 1px dotted rgba(255,255,255,.4); }
        .wk-copy { font-size: 11px; color: rgba(250,247,240,.55); }

        /* RIGHT — login panel 25% */
        .wk-side {
            flex: 0 0 25%; min-width: 340px; max-width: 440px;
            height: 100vh;
            background: #fff;
            border-left: 1px solid var(--ink-100);
            display: flex; flex-direction: column;
            overflow-y: auto;
        }
        .wk-side-inner { margin: auto; width: 100%; padding: 32px 30px; }
        .wk-side .side-title { text-align: center; margin-bottom: 24px; }
        .wk-side .side-title h2 { font-family: "Fraunces", serif; font-weight: 600; font-size: 21px; color: var(--ink-800); margin: 0; }
        .wk-side .side-title p { font-size: 12.5px; color: var(--ink-500); margin: 5px 0 0; }
        .wk-side label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-700); margin-bottom: 6px; }
        .wk-side .form-control { width: 100%; }

        @media (max-width: 980px) {
            html, body { overflow: auto; height: auto; }
            .wk-layout { flex-direction: column; height: auto; }
            .wk-main { overflow: visible; }
            .wk-middle { flex-direction: column; padding-top: 28px; }
            .wk-art { order: -1; }
            .wk-art img { max-height: 260px; margin: 0 auto; }
            .wk-hero-text { flex: none; padding-bottom: 26px; }
            .wk-side { width: 100%; max-width: none; min-width: 0; height: auto; border-left: none; border-top: 1px solid var(--ink-100); padding: 36px 20px; }
        }
    </style>
</head>
<body>

<div class="wk-layout">

    {{-- ═══════════ LEFT — Welcome (75%) ═══════════ --}}
    <div class="wk-main">

        {{-- Floating QU logo — top-left corner --}}
        <div class="wk-logo">
            <img src="/images/logo.png" alt="Qatar University">
        </div>

        {{-- Middle: hero text + big building --}}
        <div class="wk-middle">
            <div class="wk-hero-text">
                <span class="wk-kicker">Research Tracking System</span>
                <h1>Research,<br><em>tracked end&#8209;to&#8209;end.</em></h1>
                <p class="lead">
                    From proposal registration and reviewer assignment to progress reports,
                    final grading, and budget utilization &mdash; Qatar University's complete
                    internal grant lifecycle in one platform.
                </p>
            </div>

            <div class="wk-art" aria-hidden="true">
                {{-- Big stylized QU building (Kamal El Kafrawi-inspired campus) --}}
                <img src="/images/qu-building.svg" alt="">
            </div>
        </div>

        {{-- Bottom strip: condensed info (no scrolling needed) --}}
        <div class="wk-strip">
            <div class="wk-strip-group">
                <span class="wk-chip"><i class="fa-solid fa-envelope"></i> Support: <a href="mailto:lrts@qu.edu.qa">lrts@qu.edu.qa</a></span>
                <span class="wk-chip"><i class="fa-solid fa-headset"></i> Login issues: QU ITS Service Desk</span>
                <span class="wk-chip"><i class="fa-regular fa-clock"></i> Sun&ndash;Thu, 7:30 AM&ndash;3:00 PM</span>
            </div>
            <span class="wk-copy">&copy; {{ date('Y') }} Qatar University &bull; RTS &bull; All rights reserved.</span>
        </div>
    </div>

    {{-- ═══════════ RIGHT — Login panel (25%) ═══════════ --}}
    <aside class="wk-side" id="login-panel">
        <div class="wk-side-inner">
            <div style="text-align:center;margin-bottom:24px;">
                <div class="side-title">
                    <h2>Welcome to RTS</h2>
                    <p>Sign in to continue</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div style="margin-bottom:15px;">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}"
                           required autocomplete="email"
                           placeholder="Enter your email">
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

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                    <label style="display:flex;align-items:center;gap:8px;font-weight:400;font-size:13px;cursor:pointer;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                               style="width:15px;height:15px;accent-color:var(--brand-500);">
                        Remember Me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="color:var(--brand-500);font-weight:500;font-size:12.5px;">
                            Forgot Password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-primary" style="width:100%;padding:11px 20px;font-size:14px;justify-content:center;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>

                <div style="display:flex;align-items:center;gap:12px;margin:17px 0;">
                    <div style="flex:1;height:1px;background:var(--ink-100);"></div>
                    <span style="font-size:10.5px;color:var(--ink-400);text-transform:uppercase;letter-spacing:1px;">or</span>
                    <div style="flex:1;height:1px;background:var(--ink-100);"></div>
                </div>

                <a href="{{ route('saml.login') }}" class="btn-secondary"
                   style="width:100%;padding:11px 20px;font-size:14px;justify-content:center;text-decoration:none;display:flex;align-items:center;gap:10px;">
                    <i class="fa-solid fa-university"></i> Sign in with Qatar University
                </a>

                @if(config('saml2.mode') === 'test')
                    <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:10px;">
                        <span style="font-size:9px;font-weight:700;letter-spacing:.08em;padding:2px 8px;border-radius:999px;background:#fff4e5;color:#b45309;border:1px solid #fcd9a8;">SAML TEST MODE</span>
                        <span style="font-size:10px;color:var(--ink-400);">using MockSAML</span>
                    </div>
                @endif
            </form>

            <div style="text-align:center;margin-top:24px;padding-top:16px;border-top:1px solid var(--ink-100);">
                <span style="font-size:11px;color:var(--ink-400);line-height:1.6;display:block;">
                    Trouble signing in?<br>
                    <a href="mailto:lrts@qu.edu.qa" style="color:var(--brand-500);font-weight:600;text-decoration:none;">lrts@qu.edu.qa</a>
                </span>
            </div>
        </div>
    </aside>

</div>

</body>
</html>
