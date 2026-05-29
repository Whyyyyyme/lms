<?php

return [
    'enabled' => env('LMS_AI_ENABLED', true),
    'provider' => env('LMS_AI_PROVIDER', 'gemini'),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
        'timeout' => env('GEMINI_TIMEOUT', 30),
    ],

    'max_context_materials' => env('LMS_AI_MAX_CONTEXT_MATERIALS', 8),
    'max_context_assignments' => env('LMS_AI_MAX_CONTEXT_ASSIGNMENTS', 10),
    'max_history_messages' => env('LMS_AI_MAX_HISTORY_MESSAGES', 8),
    'temperature' => env('LMS_AI_TEMPERATURE', 0.3),
    'max_output_tokens' => env('LMS_AI_MAX_OUTPUT_TOKENS', 900),
    'fallback_enabled' => env('LMS_AI_FALLBACK_ENABLED', true),
];
