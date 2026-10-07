<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * SAML / SSO failures (IdP unreachable, invalid assertion, missing NameID,
     * certificate problems, etc.) are turned into a friendly redirect back to
     * the login page instead of a raw 500 error page.
     */
    public function render($request, Throwable $e)
    {
        if ($this->isSamlFailure($e)) {
            \Log::error('SSO sign-in failed: ' . $e->getMessage());

            return redirect()->route('login')->with(
                'sso_error',
                'Single sign-on could not be completed. Your QU account may not be registered in RTS, or it may be inactive. Please contact the research office.'
            );
        }

        return parent::render($request, $e);
    }

    /**
     * True when the exception originated from the SAML/SSO stack.
     */
    protected function isSamlFailure(Throwable $e): bool
    {
        return $e instanceof \OneLogin\Saml2\Error
            || $e instanceof \OneLogin\Saml2\ValidationError
            || stripos(get_class($e), 'saml') !== false;
    }
}
