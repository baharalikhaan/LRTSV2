@extends('layouts.app')

@section('title', 'Login - RTS')

@section('content')
<div class="login-card">
    <div class="login-brand">
        <div class="login-logo">QU</div>
        <h2>Welcome to RTS</h2>
        <p>Research Tracking System · Qatar University</p>
    </div>

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

        <div style="text-align:center;margin-top:24px;">
            <span style="font-size:11px;color:var(--ink-400);">&copy; {{ date('Y') }} Qatar University. All rights reserved.</span>
        </div>
    </form>
</div>
@endsection
