<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─── External APIs ─────────────────────────────────────────────────────
    'student_api' => [
        'url' => env('STUDENT_API_URL', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std'),
        'sec_key' => env('STUDENT_API_SEC_KEY', 'STD@R'),
        'use_test_response' => env('STUDENT_API_USE_TEST_RESPONSE', false),
    ],

    'crossref_api' => [
        'url' => env('CROSSREF_API_URL', 'https://api.crossref.org/works/'),
        'verify_ssl' => env('CROSSREF_API_VERIFY_SSL', false),
    ],

    'elsevier_api' => [
        'url' => env('ELSEVIER_API_URL', 'https://api.elsevier.com/content/search/scopus'),
        'key' => env('ELSEVIER_API_KEY', ''),
        'inst_token' => env('ELSEVIER_API_INST_TOKEN', ''),
    ],

    'budget_api' => [
        'url' => env('BUDGET_API_URL', 'https://residence.qu.edu.qa/ords/qucust/quapi/getProjectBudget'),
        'key' => env('BUDGET_API_KEY', '123$$321'),
    ],

];
