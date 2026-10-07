@extends('layouts.app')

@section('title', 'Login - RTS')

@php
    // Auth system switch: 'local' | 'saml' | 'mock' (config/app.php -> AUTH_LOGIN_MODE)
    $authMode = strtolower(config('app.login_mode', 'local'));
    $ssoMode = $authMode === 'saml' || $authMode === 'mock';
@endphp

@section('content')
<div class="login-card">
    <div class="login-brand">
        {{-- The official logo artwork is white-on-transparent (meant for the
             dark sidebar), so it sits on a maroon QU banner chip here or it
             would be invisible on the white card. --}}
        <div style="background:linear-gradient(135deg, var(--brand-700,#6d1230), var(--brand-500,#8d1b3d)); border-radius:12px; padding:12px 22px; display:inline-block; margin:0 auto 12px; box-shadow:0 6px 18px rgba(53,8,24,.25);">
            <img src="{{ asset('images/logo.png') }}" alt="Qatar University" style="height:56px;width:auto;display:block;margin:0 auto;">
        </div>
        <h2>Welcome to RTS</h2>
        <p>Research Tracking System · Qatar University</p>
    </div>

@php
    // Friendly SSO failure message (set by the SAML error handler, the SSO
    // listener when no RTS account matches, or the package's error flash).
    $ssoError = session('sso_error')
        ?? (session('saml2_error') ? 'Single sign-on could not be completed. Please try again or contact the research office.' : null);
    if ($ssoError) {
        session()->forget(['sso_error', 'saml2_error']);
    }
@endphp
@if($ssoError)
<div style="display:flex;align-items:flex-start;gap:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:11px 14px;font-size:12.5px;line-height:1.5;margin-bottom:16px;">
    <i class="fas fa-exclamation-circle" style="margin-top:1px;"></i>
    <span>{{ $ssoError }}</span>
</div>
@endif

@if($ssoMode)
    {{-- ─── SSO MODE: single QU IdP button (made prominent) ─── --}}
    <form method="POST" action="{{ route('login') }}">
        @if($authMode === 'mock')
        <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:14px;">
            <span style="font-size:9px;font-weight:700;letter-spacing:.08em;padding:2px 8px;border-radius:999px;background:#fff4e5;color:#b45309;border:1px solid #fcd9a8;">SAML TEST MODE</span>
            <span style="font-size:10px;color:var(--ink-400);">using MockSAML — not the live QU IdP</span>
        </div>
        @endif

        <a href="{{ route('saml.login') }}" class="btn-primary"
           style="width:100%;padding:12px 20px;font-size:14px;justify-content:center;text-decoration:none;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-university"></i> Sign in with Qatar University
        </a>
        <p style="text-align:center;font-size:11.5px;color:var(--ink-400);margin:14px 0 0;">
            Login is handled by the university identity manager.<br>
            You will be returned here once authenticated.
        </p>
    </form>
@else
    {{-- ─── LOCAL MODE: email + password form ─── --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div style="margin-bottom:16px;">
            <label for="email">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}"
                   required autocomplete="email" autofocus
                   placeholder="Enter your email">
            @error('email')
                <span style="font-size:12px;color:var(--danger);margin-top:4px;display:block;">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div style="margin-bottom:16px;">
            <label for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                   name="password" required autocomplete="current-password"
                   placeholder="Enter your password">
            @error('password')
                <span style="font-size:12px;color:var(--danger);margin-top:4px;display:block;">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400;font-size:13px;cursor:pointer;">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}
                       style="width:15px;height:15px;accent-color:var(--brand-500);">
                Remember Me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color:var(--brand-500);font-weight:500;font-size:13px;">
                    Forgot Password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary" style="width:100%;padding:12px 20px;font-size:14px;justify-content:center;">
            <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>
    </form>
@endif

    <div style="text-align:center;margin-top:24px;">
        <span style="font-size:11px;color:var(--ink-400);">&copy; {{ date('Y') }} Qatar University. All rights reserved.</span>
    </div>
</div>
@endsection
