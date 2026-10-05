<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\User; // Assuming this is your User model
use Illuminate\Support\Facades\Auth;
use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

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


        // session(['alerts' => $recordsArray]);


        View::composer('components.navbar', function ($view) {
            $userid = null;
            if (Auth::user())
                $userid = Auth::user()->id;
            $alerts = DB::table('alerts')
                ->where("user_id", "=",  $userid)
                ->get();
            // $recordsArray = $alerts->toArray();

            $view->with('alerts', $alerts);
        });



        // Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
        //     $messageId = $event->getSaml2Auth()->getLastMessageId();
        //     $user = $event->getSaml2User();
        //     $userData = [
        //         'id' => $user->getUserId(),
        //         'attributes' => $user->getAttributes(),
        //         'assertion' => $user->getRawSamlAssertion()
        //     ];
        //     $att = $user->getAttributes();
        //     $email = $att['email'][0] ?? null;
        //     $user2 = User::where('email', $email)->first();

        //     Auth::login($user2);

        // });

        // Retrieve the record(s) from the database





        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {

            $messageId = $event->getSaml2Auth()->getLastMessageId();
            $user = $event->getSaml2User();

            $userData = [
                'id' => $user->getUserId(),
                'attributes' => $user->getAttributes(),
                'assertion' => $user->getRawSamlAssertion()
            ];
            $att = $user->getAttributes();
            $email = $att['email id'][0] ?? null;
            // $email = $att['email'][0] ?? null;
            $user2 = User::where(DB::raw('UPPER(email)'), strtoupper($email))->orWhere('userid', $email)->first();
            // $user2 = User::where('email', $email)->first();

            if ($user2) {
                Auth::logout();
                Auth::login($user2);
            } else {
            }
        });
    }
}
