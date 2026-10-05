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
            }
        });
    }
}
