<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI API Key
    |--------------------------------------------------------------------------
    |
    | Your AI provider API key. If using an OpenAI-compatible provider,
    | set this alongside the base_uri below.
    */

    'api_key' => env('AI_API_KEY'),
    'organization' => env('AI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | AI API Project
    |--------------------------------------------------------------------------
    |
    | Optionally specify your AI provider project for legacy API keys.
    */

    'project' => env('AI_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | AI API Base URL
    |--------------------------------------------------------------------------
    |
    | Set this when using a non-OpenAI compatible endpoint.
    | Defaults to: api.openai.com/v1
    */

    'base_uri' => env('AI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait for a response. Default: 30
    */

    'request_timeout' => env('AI_REQUEST_TIMEOUT', 30),
];
