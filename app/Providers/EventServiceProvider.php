<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // ─── User activity: sign-in / sign-out ────────────────────────────
        // Records when and from which IP a user signs in, and (on sign-out)
        // how long the session lasted. Actions are logged by LogUserActivity.
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            session()->put('activity_login_at', now()->timestamp);
            try {
                \App\Models\ActivityLog::create([
                    'user_id'     => $event->user->id,
                    'event'       => 'login',
                    'description' => 'Signed in',
                    'ip'          => request()->ip(),
                    'user_agent'  => substr((string) request()->userAgent(), 0, 255),
                    'url'         => substr(request()->fullUrl(), 0, 2000),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Activity log (login) failed: ' . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            $loginAt = session()->get('activity_login_at');
            $duration = $loginAt ? max(0, now()->timestamp - (int) $loginAt) : null;
            try {
                \App\Models\ActivityLog::create([
                    'user_id'          => optional($event->user)->id,
                    'event'            => 'logout',
                    'description'      => 'Signed out',
                    'ip'               => request()->ip(),
                    'user_agent'       => substr((string) request()->userAgent(), 0, 255),
                    'duration_seconds' => $duration,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Activity log (logout) failed: ' . $e->getMessage());
            }
            session()->forget('activity_login_at');
        });

        // ─── SAML SSO login ───────────────────────────────────────────────
        // Fired by the aacotroneo/laravel-saml2 package after a successful
        // Assertion Consumer Service (ACS) response from the Qatar University
        // ADFS Identity Provider (sts.qu.edu.qa).
        Event::listen(\Aacotroneo\Saml2\Events\Saml2LoginEvent::class, function ($event) {
            $samlUser = $event->getSaml2User();
            $attributes = $samlUser->getAttributes();

            // The QU ADFS IdP exposes the user's QU email under the 'email id'
            // attribute. Fall back to common aliases if it is not present.
            $quEmail = $attributes['email id'][0]
                ?? $attributes['emailaddress'][0]
                ?? $attributes['mail'][0]
                ?? $attributes['email'][0]
                ?? null;

            if (!$quEmail) {
                session()->put('sso_error', 'Your QU account did not provide an email address. Please contact the research office.');
                return;
            }

            $quEmail = strtolower(trim($quEmail));

            // Match the authenticated user by QU ID (the QU university ID-based
            // email, stored in users.qu_id), case-insensitive. Fall back to the
            // plain email column for the few accounts without a qu_id.
            $user = \App\Models\User::whereRaw('LOWER(TRIM(qu_id)) = ?', [$quEmail])->first()
                ?? \App\Models\User::whereRaw('LOWER(email) = ?', [$quEmail])->first();

            if ($user && $user->is_active) {
                // Avoid a full logout; log in the matched user.
                if (auth()->user() && auth()->user()->id === $user->id) {
                    return;
                }
                auth()->login($user, true);
            } else {
                // SSO itself succeeded, but there is no matching/active RTS
                // account. Surface a clear message on the login page instead of
                // silently bouncing back.
                session()->put(
                    'sso_error',
                    'Your QU account is not registered in RTS, or it is inactive. Please contact the research office.'
                );
            }
        });
    }
}
