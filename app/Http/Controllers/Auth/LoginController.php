<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Only active accounts may sign in with email + password. Mirrors the
     * is_active check already enforced on the SAML SSO listener.
     */
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');

        // 'type' column may store composite roles — treat any non-zero
        // is_active value as enabled.
        $credentials['is_active'] = 1;

        return $credentials;
    }

    /**
     * Mode-aware logout:
     *  - all modes: destroy the local session
     *  - 'saml' / 'mock': additionally hand off to the IdP single logout
     *    (saml2_logout) so the ADFS / MockSAML session ends as well —
     *    otherwise the user can be silently re-authenticated on the next
     *    SSO click.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mode = strtolower((string) config('app.login_mode', 'local'));
        if ($mode === 'saml' || $mode === 'mock') {
            return redirect()->route('saml2_logout', ['idpName' => 'Auth']);
        }

        return redirect('/');
    }
}
