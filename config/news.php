<?php

return [
    'discovery' => [
        'default_limit' => (int) env('NEWS_DISCOVERY_DEFAULT_LIMIT', 10),
        'language' => env('NEWS_DISCOVERY_LANGUAGE', 'en'),
        'country' => env('NEWS_DISCOVERY_COUNTRY', 'us'),
        'category_queries' => [
            'ai-tools' => [
                'AI tool launch',
                'new AI feature',
                'AI productivity tool',
            ],
            'ai-agents' => [
                'AI agent framework',
                'agent workflow',
                'model context protocol',
            ],
            'seo' => [
                'Google Search update',
                'SEO update',
                'ranking update',
            ],
            'content-marketing' => [
                'editorial workflow',
                'publishing workflow',
                'content operations',
            ],
            'productivity-automation' => [
                'workflow automation',
                'developer productivity tool',
                'automation platform update',
            ],
            'developer-ai' => [
                'Laravel AI',
                'MCP',
                'coding agent',
                'developer AI framework',
            ],
        ],
    ],
    'scoring' => [
        'ignore_below' => (int) env('NEWS_SCORE_IGNORE_BELOW', 45),
        'topic_threshold' => (int) env('NEWS_SCORE_TOPIC_THRESHOLD', 65),
        'both_threshold' => (int) env('NEWS_SCORE_BOTH_THRESHOLD', 80),
        'draft_threshold' => (int) env('NEWS_SCORE_DRAFT_THRESHOLD', 90),
        'trusted_publishers' => [
            'TechCrunch' => 18,
            'The Verge' => 16,
            'Google' => 18,
            'Google Search Central' => 20,
            'OpenAI' => 20,
            'Anthropic' => 18,
            'Laravel News' => 18,
            'GitHub' => 18,
        ],
        'implementation_signals' => [
            'api',
            'sdk',
            'release',
            'launch',
            'guide',
            'tutorial',
            'framework',
            'developer',
            'workflow',
            'integration',
            'laravel',
            'mcp',
            'agent',
            'seo',
            'automation',
        ],
        'low_value_signals' => [
            'rumor',
            'gossip',
            'celebrity',
            'funding round',
        ],
    ],
];
