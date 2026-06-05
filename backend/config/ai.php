<?php

return [
    'enabled' => env('AI_ENABLED', false),
    'provider' => env('AI_PROVIDER', 'gemini'),
    'timeout' => (int) env('AI_TIMEOUT_SECONDS', 20),
    'daily_limit_per_user' => (int) env('AI_DAILY_LIMIT_PER_USER', 10),
    'monthly_limit_per_user' => (int) env('AI_MONTHLY_LIMIT_PER_USER', 100),
    'beta_user_ids' => array_filter(array_map('trim', explode(',', (string) env('AI_BETA_USER_IDS', '')))),
    'beta_user_emails' => array_filter(array_map(
        fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('AI_BETA_USER_EMAILS', ''))
    )),

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    'huggingface' => [
        'key' => env('HUGGINGFACE_API_KEY'),
        'model' => env('HUGGINGFACE_MODEL', 'meta-llama/Llama-3.1-8B-Instruct'),
        'base_url' => env('HUGGINGFACE_BASE_URL', 'https://router.huggingface.co/v1'),
    ],

    'ollama' => [
        'key' => env('OLLAMA_API_KEY'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
    ],
];
