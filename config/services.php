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

    'lm_studio' => [
        'base_url' => env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1'),
        'model' => env('LM_STUDIO_MODEL', 'lmstudio-community/qwen2.5-7b-instruct'),
        'api_key' => env('LM_STUDIO_API_KEY'),
        'timeout' => env('LM_STUDIO_TIMEOUT', 120),
        'connect_timeout' => env('LM_STUDIO_CONNECT_TIMEOUT', 10),
        'retries' => env('LM_STUDIO_RETRIES', 2),
        'max_tokens' => env('LM_STUDIO_MAX_TOKENS', 8192),
    ],

    'judge0' => [
        'base_url' => env('JUDGE0_BASE_URL', 'https://ce.judge0.com'),
        'timeout' => env('JUDGE0_TIMEOUT', 30),
        'connect_timeout' => env('JUDGE0_CONNECT_TIMEOUT', 10),
        'retries' => env('JUDGE0_RETRIES', 1),
    ],

    'workspace' => [
        'execution_timeout' => (int) env('WORKSPACE_EXECUTION_TIMEOUT', 60),
    ],

    'dialogflow' => [
        'project_id' => env('DIALOGFLOW_PROJECT_ID'),
        'language_code' => env('DIALOGFLOW_LANGUAGE_CODE', 'es'),
        'confidence_threshold' => (float) env('DIALOGFLOW_CONFIDENCE_THRESHOLD', 0.55),
    ],

];
