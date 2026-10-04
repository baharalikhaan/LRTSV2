<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SetupController extends Controller
{
    public function setup(Request $request)
    {
        // Security gate — in production the route requires a secret key so it
        // can be used on shared hosting (no shell access) without being public.
        $deployKey = env('APP_DEPLOY_KEY', '');
        if (!app()->isLocal() && (!$request->filled('key') || !hash_equals($deployKey, $request->input('key')))) {
            abort(403, 'Forbidden. Provide the deploy key (?key=...) to run setup.');
        }

        $results = [];

        // 1. Ensure storage directories exist
        $dirs = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/uploads'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path('storage'),
        ];

        foreach ($dirs as $dir) {
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
                $results[] = "Created: {$dir}";
            } else {
                $results[] = "Exists: {$dir}";
            }
        }

        // 2. Set permissions (skip on Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            foreach ($dirs as $dir) {
                if (File::isDirectory($dir)) {
                    chmod($dir, 0755);
                }
            }
            $results[] = "Permissions set to 755";
        } else {
            $results[] = "Windows detected — skipped chmod";
        }

        // 3. Clear all caches
        try {
            Artisan::call('cache:clear');
            $results[] = "Cache cleared: " . Artisan::output();
        } catch (\Exception $e) {
            $results[] = "Cache clear failed: " . $e->getMessage();
        }

        try {
            Artisan::call('config:clear');
            $results[] = "Config cleared: " . Artisan::output();
        } catch (\Exception $e) {
            $results[] = "Config clear failed: " . $e->getMessage();
        }

        try {
            Artisan::call('route:clear');
            $results[] = "Routes cleared: " . Artisan::output();
        } catch (\Exception $e) {
            $results[] = "Route clear failed: " . $e->getMessage();
        }

        try {
            Artisan::call('view:clear');
            $results[] = "Views cleared: " . Artisan::output();
        } catch (\Exception $e) {
            $results[] = "View clear failed: " . $e->getMessage();
        }

        // 4. Storage link
        try {
            if (!File::isDirectory(public_path('storage'))) {
                Artisan::call('storage:link');
                $results[] = "Storage linked: " . Artisan::output();
            } else {
                $results[] = "Storage link already exists";
            }
        } catch (\Exception $e) {
            $results[] = "Storage link failed: " . $e->getMessage();
        }

        // 5. Run migrations (optional, only if --migrate flag passed)
        if ($request->has('migrate')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $results[] = "Migrations run: " . Artisan::output();
            } catch (\Exception $e) {
                $results[] = "Migration failed: " . $e->getMessage();
            }
        }

        // 6. Import legacy data (optional, only if --import flag passed)
        if ($request->has('import')) {
            try {
                Artisan::call('import:legacy', ['--clean' => true]);
                $results[] = "Legacy import: " . Artisan::output();
            } catch (\Exception $e) {
                $results[] = "Legacy import failed: " . $e->getMessage();
            }
        }

        // 7. Mail/SMTP test (optional, only if --mailtest flag passed).
        //    Usage: /setup?key=KEY&mailtest=recipient@example.com
        //    Reports SMTP config WITHOUT ever exposing the password value.
        if ($request->has('mailtest')) {
            $smtpCfg = [
                'mailer'       => config('mail.default'),
                'host'         => config('mail.mailers.smtp.host'),
                'port'         => config('mail.mailers.smtp.port'),
                'encryption'   => config('mail.mailers.smtp.encryption'),
                'username'     => config('mail.mailers.smtp.username'),
                'password_set' => filled(config('mail.mailers.smtp.password')) ? 'yes (hidden)' : 'NO',
                'from_address' => config('mail.from.address'),
                'queue'        => config('queue.default'),
            ];
            $results[] = "Mail config: " . json_encode($smtpCfg);

            $to = $request->input('mailtest');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $to = config('mail.from.address');
            }

            if (!config('mail.enabled')) {
                $results[] = "Mail test: SKIPPED - MAIL_ENABLED=false (sending disabled)";
            } else {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "RTS setup mail test\n\nSent at: " . now() . "\nIf you received this, SMTP is working.",
                        function ($message) use ($to) {
                            $message->to($to)->subject('RTS Setup Mail Test');
                        }
                    );
                    $results[] = "Mail test: SENT OK to {$to}";
                } catch (\Exception $e) {
                    $results[] = "Mail test FAILED: " . $e->getMessage();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Setup complete',
            'steps' => $results,
        ]);
    }
}
