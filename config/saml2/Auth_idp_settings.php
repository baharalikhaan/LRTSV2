<?php

/*
|--------------------------------------------------------------------------
| SAML IdP Settings — Auth
|--------------------------------------------------------------------------
|
| SAML_MODE in .env selects which Identity Provider is used:
|   'test' → MockSAML public test IdP (https://mocksaml.com)
|   'live' → Qatar University ADFS production IdP (sts.qu.edu.qa)
|
| NOTE: AppServiceProvider::register() also builds these settings
| programmatically and overrides the Saml2Auth container binding, so the
| app works even if this file cannot be loaded. Keep both in sync.
*/

$samlMode = strtolower(env('SAML_MODE', 'live'));

// Test IdP: MockSAML signing certificate (https://mocksaml.com/api/saml/metadata)
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

// Live IdP: QU ADFS signing certificate
$quCert = '-----BEGIN CERTIFICATE-----' . "\n"
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

if ($samlMode === 'test') {
    $idpEntity = env('SAML_TEST_IDP_ENTITYID', 'https://saml.example.com/entityid');
    $idpSso    = env('SAML_TEST_IDP_SSO_URL', 'https://mocksaml.com/api/saml/sso');
    $idpSlo    = env('SAML_TEST_IDP_SL_URL', 'https://mocksaml.com/api/saml/sso');
    $idpCert   = env('SAML_TEST_IDP_x509', $mockCert);
    $spCert    = env('SAML_TEST_SP_x509', '');
    $spKey     = env('SAML_TEST_SP_PRIVATEKEY', '');
} else {
    $idpEntity = env('SAML_Auth_IDP_ENTITYID', 'https://sts.qu.edu.qa/adfs/services/trust');
    $idpSso    = env('SAML_Auth_IDP_SSO_URL', 'https://sts.qu.edu.qa/adfs/ls/');
    $idpSlo    = env('SAML_Auth_IDP_SL_URL', 'https://sts.qu.edu.qa/adfs/ls/');
    $idpCert   = env('SAML_Auth_IDP_x509', $quCert);
    $spCert    = env('SAML_Auth_SP_x509', '');
    $spKey     = env('SAML_Auth_SP_PRIVATEKEY', '');
}

return array(
    'strict'  => false,
    'debug'   => env('APP_DEBUG', false),

    'sp'      => array(
        'x509cert'  => $spCert,
        'privateKey' => $spKey,
        'entityId'  => '',
        'assertionConsumerService' => array(
            'url' => '',
        ),
        'singleLogoutService' => array(
            'url' => '',
        ),
    ),

    'idp'      => array(
        'entityId'            => $idpEntity,
        'singleSignOnService' => array(
            'url' => $idpSso,
        ),
        'singleLogoutService' => array(
            'url' => $idpSlo,
        ),
        'x509cert' => $idpCert,
    ),

    'security' => array(
        'nameIdEncrypted'       => false,
        'authnRequestsSigned'   => false,
        'logoutRequestSigned'   => false,
        'logoutResponseSigned'  => false,
        'signMetadata'          => false,
        'wantMessagesSigned'    => false,
        'wantAssertionsSigned'  => false,
        'wantNameIdEncrypted'   => false,
        // QU ADFS may omit the NameID; authentication is by the 'email id'
        // attribute, so a NameID is not required.
        'wantNameId'            => false,
        'requestedAuthnContext' => true,
    ),

    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'LRTS',
            'emailAddress' => 'no-reply@qu.edu.qa',
        ),
        'support' => array(
            'givenName' => 'LRTS',
            'emailAddress' => 'no-reply@qu.edu.qa',
        ),
    ),

    'organization' => array(
        'en-US' => array(
            'name'        => 'Qatar University',
            'displayname' => 'Qatar University',
            'url'         => 'https://www.qu.edu.qa',
        ),
    ),
);
