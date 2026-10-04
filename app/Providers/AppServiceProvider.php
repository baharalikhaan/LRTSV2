<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The personal_access_tokens table already exists in the connected
        // database (created via the consolidated create_all_tables migration),
        // so skip Sanctum's own migration to avoid "already exists" errors.
        // Must run in register() — package providers boot before this one,
        // so calling it in boot() would be too late.
        \Laravel\Sanctum\Sanctum::ignoreMigrations();

        // ─── SAML IdP config ─────────────────────────────────────────────
        // AUTH_LOGIN_MODE in .env selects the single login system:
        //   'local' → email + password login form (no SSO buttons)
        //   'saml'  → Qatar University ADFS (sts.qu.edu.qa) production IdP
        //   'mock'  → MockSAML (https://mocksaml.com) public test IdP
        // The legacy SAML_MODE=live|test switch is still honored when
        // AUTH_LOGIN_MODE is not set, for backward compatibility.
        //
        // IMPORTANT — the login-mode keys are read DIRECTLY from the .env
        // file (envFileValue), NOT through env()/config(): when a config
        // cache (bootstrap/cache/config.php) is present, env() returns NULL
        // for every variable and the cached snapshot may be stale, which
        // silently forces the switch back to its default and, for the live
        // IdP, triggers 'certificate missing' failures like the one that
        // used to occur. Reading the file is authoritative in both cached
        // and uncached states.
        $loginMode = strtolower(trim((string) $this->envFileValue('AUTH_LOGIN_MODE', env('AUTH_LOGIN_MODE', ''))));
        $samlMode  = strtolower(trim((string) $this->envFileValue('SAML_MODE', env('SAML_MODE', 'live'))));
        if ($loginMode === 'mock') {
            $samlMode = 'test';
        } elseif ($loginMode === 'saml') {
            $samlMode = 'live';
        }

        // Test IdP: MockSAML signing certificate (from https://mocksaml.com/api/saml/metadata)
        $mockCert = '-----BEGIN CERTIFICATE-----' . "\n"
            . 'MIIC4jCCAcoCCQC33wnybT5QZDANBgkqhkiG9w0BAQsFADAyMQswCQYDVQQGEwJV' . "\n"
            . 'SzEPMA0GA1UECgwGQm94eUhRMRIwEAYDVQQDDAlNb2NrIFNBTUwwIBcNMjIwMjI4' . "\n"
            . 'MjE0NjM4WhgPMzAyMTA3MDEyMTQ2MzhaMDIxCzAJBgNVBAYTAlVLMQ8wDQYDVQQK' . "\n"
            . 'DAZCb3h5SFExEjAQBgNVBAMMCU1vY2sgU0FNTDCCASIwDQYJKoZIhvcNAQEBBQAD' . "\n"
            . 'ggEPADCCAQoCggEBALGfYettMsct1T6tVUwTudNJH5Pnb9GGnkXi9Zw/e6x45DD0' . "\n"
            . 'RuRONbFlJ2T4RjAE/uG+AjXxXQ8o2SZfb9+GgmCHuTJFNgHoZ1nFVXCmb/Hg8Hpd' . "\n"
            . '4vOAGXndixaReOiq3EH5XvpMjMkJ3+8+9VYMzMZOjkgQtAqO36eAFFfNKX7dTj3V' . "\n"
            . 'pwLkvz6/KFCq8OAwY+AUi4eZm5J57D31GzjHwfjH9WTeX0MyndmnNB1qV75qQR3b' . "\n"
            . '2/W5sGHRv+9AarggJkF+ptUkXoLtVA51wcfYm6hILptpde5FQC8RWY1YrswBWAEZ' . "\n"
            . 'NfyrR4JeSweElNHg4NVOs4TwGjOPwWGqzTfgTlECAwEAATANBgkqhkiG9w0BAQsF' . "\n"
            . 'AAOCAQEAAYRlYflSXAWoZpFfwNiCQVE5d9zZ0DPzNdWhAybXcTyMf0z5mDf6FWBW' . "\n"
            . '5Gyoi9u3EMEDnzLcJNkwJAAc39Apa4I2/tml+Jy29dk8bTyX6m93ngmCgdLh5Za4' . "\n"
            . 'khuU3AM3L63g7VexCuO7kwkjh/+LqdcIXsVGO6XDfu2QOs1Xpe9zIzLpwm/RNYeX' . "\n"
            . 'UjbSj5ce/jekpAw7qyVVL4xOyh8AtUW1ek3wIw1MJvEgEPt0d16oshWJpoS1OT8L' . "\n"
            . 'r/22SvYEo3EmSGdTVGgk3x3s+A0qWAqTcyjr7Q4s/GKYRFfomGwz0TZ4Iw1ZN99M' . "\n"
            . 'm0eo2USlSRTVl7QHRTuiuSThHpLKQQ==' . "\n"
            . '-----END CERTIFICATE-----';

        if ($samlMode === 'test') {
            $idpEntity = env('SAML_TEST_IDP_ENTITYID', 'https://saml.example.com/entityid');
            $idpSso    = env('SAML_TEST_IDP_SSO_URL', 'https://mocksaml.com/api/saml/sso');
            $idpSlo    = env('SAML_TEST_IDP_SL_URL', 'https://mocksaml.com/api/saml/sso');
            $idpCert   = env('SAML_TEST_IDP_x509', $mockCert);
            $spCert    = env('SAML_TEST_SP_x509', '');
            $spKey     = env('SAML_TEST_SP_PRIVATEKEY', '');
        } else {
            // Live IdP: all QU ADFS data must come from .env — no code fallback.
            // Cert read via envFileValue (cache-proof) — see comment above.
            $idpEntity = $this->envFileValue('SAML_Auth_IDP_ENTITYID', env('SAML_Auth_IDP_ENTITYID', 'https://sts.qu.edu.qa/adfs/services/trust'));
            $idpSso    = $this->envFileValue('SAML_Auth_IDP_SSO_URL', env('SAML_Auth_IDP_SSO_URL', 'https://sts.qu.edu.qa/adfs/ls/'));
            $idpSlo    = $this->envFileValue('SAML_Auth_IDP_SL_URL', env('SAML_Auth_IDP_SL_URL', 'https://sts.qu.edu.qa/adfs/ls/'));
            $rawCert   = trim((string) $this->envFileValue('SAML_Auth_IDP_x509', env('SAML_Auth_IDP_x509', '')));
            if ($rawCert === '') {
                throw new \RuntimeException(
                    'QU SSO (live) is not configured: set SAML_Auth_IDP_x509 in .env with the QU ADFS signing '
                    . 'certificate — either the full PEM or its one-line base64 body.'
                );
            }
            // Accept both the full PEM and the bare one-line base64 body
            $idpCert   = str_starts_with($rawCert, '-----BEGIN ')
                ? $rawCert
                : '-----BEGIN CERTIFICATE-----' . "\n" . chunk_split(rtrim($rawCert), 64, "\n") . '-----END CERTIFICATE-----';
            $spCert    = $this->envFileValue('SAML_Auth_SP_x509', env('SAML_Auth_SP_x509', ''));
            $spKey     = $this->envFileValue('SAML_Auth_SP_PRIVATEKEY', env('SAML_Auth_SP_PRIVATEKEY', ''));
        }

        $samlIdpSettings = [
            'strict'  => false,
            'debug'   => env('APP_DEBUG', false),
            'sp'      => [
                'x509cert'  => $spCert,
                'privateKey' => $spKey,
                'entityId'  => '',
                'assertionConsumerService' => ['url' => ''],
                'singleLogoutService'      => ['url' => ''],
            ],
            'idp'      => [
                'entityId'            => $idpEntity,
                'singleSignOnService' => ['url' => $idpSso],
                'singleLogoutService' => ['url' => $idpSlo],
                'x509cert'            => $idpCert,
            ],
            'security' => [
                'nameIdEncrypted'       => false,
                'authnRequestsSigned'   => false,
                'logoutRequestSigned'   => false,
                'logoutResponseSigned'  => false,
                'signMetadata'          => false,
                'wantMessagesSigned'    => false,
                'wantAssertionsSigned'  => false,
                'wantNameIdEncrypted'   => false,
                'requestedAuthnContext' => true,
            ],
            'contactPerson' => [
                'technical' => ['givenName' => 'LRTS', 'emailAddress' => 'no-reply@qu.edu.qa'],
                'support'   => ['givenName' => 'LRTS', 'emailAddress' => 'no-reply@qu.edu.qa'],
            ],
            'organization' => [
                'en-US' => [
                    'name'        => 'Qatar University',
                    'displayname' => 'Qatar University',
                    'url'         => 'https://www.qu.edu.qa',
                ],
            ],
        ];

        // Make the settings visible to anything reading via config() as well.
        config([
            'saml2.Auth_idp_settings' => $samlIdpSettings,
            'saml2.mode'              => $samlMode,
        ]);

        // ─── Override the package's Saml2Auth singleton ──────────────────
        // Registered AFTER Saml2ServiceProvider::register(), so this binding
        // replaces the package's. Builds the OneLogin Auth object directly
        // from the hardcoded settings array above, bypassing Laravel's
        // config-file lookup entirely. This is immune to config caches,
        // filename case issues, and missing config files.
        $this->app->singleton(\Aacotroneo\Saml2\Saml2Auth::class, function () use ($samlIdpSettings) {
            // Fill SP URLs from routes exactly like the package does.
            if (empty($samlIdpSettings['sp']['entityId'])) {
                $samlIdpSettings['sp']['entityId'] = \URL::route('saml2_metadata', 'Auth');
            }
            if (empty($samlIdpSettings['sp']['assertionConsumerService']['url'])) {
                $samlIdpSettings['sp']['assertionConsumerService']['url'] = \URL::route('saml2_acs', 'Auth');
            }
            if (!empty($samlIdpSettings['sp']['singleLogoutService']) &&
                empty($samlIdpSettings['sp']['singleLogoutService']['url'])) {
                $samlIdpSettings['sp']['singleLogoutService']['url'] = \URL::route('saml2_sls', 'Auth');
            }

            return new \Aacotroneo\Saml2\Saml2Auth(
                new \OneLogin\Saml2\Auth($samlIdpSettings)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Global email kill switch — MAIL_ENABLED=false forces the mailer to
        // the array transport so nothing ever leaves the server. This catches
        // every Mail:: call site (including framework mail such as password
        // reset and any future senders); the senders themselves also check
        // config('mail.enabled') so no send-log rows are written when off.
        if (!config('mail.enabled')) {
            config(['mail.default' => 'array']);
        }
    }

    /**
     * Read a key straight from the .env file, bypassing any config cache.
     * Returns the in-memory value passed as $fallback when the key is not
     * present in the file (or when the value itself looks like a stale
     * unresolvable ${VAR} reference).
     */
    protected function envFileValue(string $key, $fallback = null)
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            $path = $this->app->environmentFilePath();
            if (file_exists($path)) {
                foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    $pos = strpos($line, '=');
                    if ($pos !== false) {
                        $k = trim(substr($line, 0, $pos));
                        $v = trim(substr($line, $pos + 1));
                        // resolve simple ${OTHER} references from the same file
                        if (preg_match_all('/\$\{([A-Z0-9_]+)\}/', $v, $m)) {
                            foreach ($m[1] as $ref) {
                                if (isset($cache[$ref])) {
                                    $v = str_replace('${' . $ref . '}', $cache[$ref], $v);
                                }
                            }
                        }
                        $cache[$k] = trim($v, "\"'");
                    }
                }
            }
        }
        $value = $cache[$key] ?? null;
        if ($value === null || ($value !== '' && str_starts_with($value, '${'))) {
            return $fallback;
        }
        return $value;
    }
}
