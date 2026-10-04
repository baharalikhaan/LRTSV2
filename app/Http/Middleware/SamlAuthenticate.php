<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SamlAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * Ensures the session is started for SAML routes and that the IdP
     * config is available regardless of filesystem case-sensitivity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty(config('saml2.Auth_idp_settings.idp.entityId'))) {
            $this->loadIdpConfig();
        }

        return $next($request);
    }

    /**
     * Load the IdP config directly into the Laravel config repository.
     * Used as a safety net when the config file cannot be found on disk
     * (e.g. NTFS case-sensitivity on Windows).
     */
    protected function loadIdpConfig(): void
    {
        $cert = '-----BEGIN CERTIFICATE-----' . "\n"
            . 'MIIC1jCCAb6gAwIBAgIQHjiKrH37Vb5Nl4f1fhti/zANBgkqhkiG9w0BAQsFADAn' . "\n"
            . 'MSUwIwYDVQQDExxBREZTIFNpZ25pbmcgLSBzdHMucXUuZWR1LnFhMB4XDTIxMDQy' . "\n"
            . 'NzA4MzczN1oXDTQ2MDQyMTA4MzczN1owJzElMCMGA1UEAxMcQURGUyBTaWduaW5n' . "\n"
            . 'IC0gc3RzLnF1LmVkdS5xYTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEB' . "\n"
            . 'ANqskegilZcyPom5sZ0orRUEnsclQLV5LyW3n39/lTDbQPlP/ETfXik3AX419i0E' . "\n"
            . 'd4tA7Zro83rWQh3cM/vV7jLNQwquKCnoSRtV9r1Sb1uIUptWmttpgk0Lh3+rQhDl' . "\n"
            . 'TiE5k/orbRUtnR5jB+NXXVvNJm23s6Mb8MEUf4fwRbkoADH7ux0nIKdhTErkyt4I' . "\n"
            . 'tb+fZOr/WjlU0Igq11n/bkp5SYRKyRuNCCAjcu7J4EA72LfkBxlMJwYJz3G1v17v' . "\n"
            . 'MhzoLN3hCA+3uLXdvmSzEtkc9XO0UaiqJx0DqbkTubZJphG83QpqOGCOPJ6GIjic' . "\n"
            . 'lCjRRZvw8ZrAalnY+J8fB/MCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAuV2ZB+bc' . "\n"
            . 'tfqPK2OPT6QCXyxkpEsbg+DFxNC8G3S0ARN07Yl89LgtlzwUF3gUwDcWObg7P5pe' . "\n"
            . 'tZb03Bv/+IfhXg3PQMwksOZVHiUUcgj8ylPSv2LhiflDw7UyF1P9i7dndUnONBjd' . "\n"
            . 'w9Svx1V9Qfs9rC4Ba5vvmF/+KLb7cFTMDLi4Ke1Ru5D7XCg6r3ixORmipN7hL1QJ' . "\n"
            . 'whbXJd+JLZpI+K2qJzAWXCboYHvkRymgzkxo43fK0gBlAVvbgO4WpB8cAk8VeBE5' . "\n"
            . 'M3rtZfNCr196Ppzf2+yw6Rcm5mfRNddL9trxYey85Z1r5deUTsRykhLjFdGa+E6G' . "\n"
            . 'MuauyN9S5+/cgQ==' . "\n"
            . '-----END CERTIFICATE-----';

        config(['saml2.Auth_idp_settings' => [
            'strict'  => false,
            'debug'   => false,
            'sp'      => [
                'x509cert'  => '',
                'privateKey' => '',
                'entityId'  => '',
                'assertionConsumerService' => ['url' => ''],
                'singleLogoutService'      => ['url' => ''],
            ],
            'idp'      => [
                'entityId'            => 'https://sts.qu.edu.qa/adfs/services/trust',
                'singleSignOnService' => ['url' => 'https://sts.qu.edu.qa/adfs/ls/'],
                'singleLogoutService' => ['url' => 'https://sts.qu.edu.qa/adfs/ls/'],
                'x509cert'            => $cert,
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
        ]]);
    }
}
