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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'crossref' => [
        'base_url' => env('CROSSREF_BASE_URL', 'https://api.crossref.org'),
        'mailto' => env('CROSSREF_MAILTO'),
        'user_agent' => env('CROSSREF_USER_AGENT', 'Sello-ITSa/1.0'),
    ],

    'openalex' => [
        'base_url' => env('OPENALEX_BASE_URL', 'https://api.openalex.org'),
        'api_key' => env('OPENALEX_API_KEY'),
        'user_agent' => env('OPENALEX_USER_AGENT', 'Sello-ITSa/1.0'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
