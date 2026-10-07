<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Graceful SSO failure handling: SAML exceptions and "no matching account"
 * situations must surface a friendly message on the login page, never a 500.
 */
class SsoErrorHandlingTest extends TestCase
{
    use DatabaseTransactions;

    /** The login page renders the SSO error banner when a message is flashed. */
    public function test_login_page_shows_sso_error_banner()
    {
        $res = $this->withSession([
            'sso_error' => 'Your QU account is not registered in RTS, or it is inactive.',
        ])->get('/login');

        $res->assertStatus(200);
        $res->assertSee('not registered in RTS', false);
    }

    /** A thrown SAML validation error is converted into a login redirect. */
    public function test_saml_exception_redirects_to_login_with_message()
    {
        Route::get('/__test_saml_failure', function () {
            throw new \OneLogin\Saml2\ValidationError('NameID not found in the assertion of the Response');
        });

        $res = $this->get('/__test_saml_failure');

        $res->assertRedirect(route('login'));
        $res->assertSessionHas('sso_error');
    }

    /** A generic SAML-namespaced exception is also handled. */
    public function test_saml_error_class_is_handled()
    {
        Route::get('/__test_saml_error', function () {
            throw new \OneLogin\Saml2\Error('Invalid signature', 1);
        });

        $res = $this->get('/__test_saml_error');

        $res->assertRedirect(route('login'));
        $res->assertSessionHas('sso_error');
    }
}
