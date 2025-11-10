<?php

return [
    'llm' => [
        'default' => env('REQQY_LLM'),
        'small' => env('REQQY_LLM_SMALL'),
    ],
    'max_tokens' => [
        'default' => env('REQQY_MAX_TOKENS_DEFAULT', 100000),
        'prd' => env('REQQY_MAX_TOKENS_PRD', 100000),
        'chat' => env('REQQY_MAX_TOKENS_CHAT', 4096),
    ],
    'api_keys' => [
        'openai' => env('OPENAI_API_KEY'),
        'anthropic' => env('ANTHROPIC_API_KEY'),
        'github' => env('GITHUB_API_KEY'),
    ],
];
