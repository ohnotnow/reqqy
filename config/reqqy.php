<?php

return [
    'llm' => [
        'default' => env('REQQY_LLM'),
        'small' => env('REQQY_LLM_SMALL'),
    ],
    'api_keys' => [
        'openai' => env('OPENAI_API_KEY'),
        'anthropic' => env('ANTHROPIC_API_KEY'),
        'github' => env('GITHUB_API_KEY'),
    ],
];
