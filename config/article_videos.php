<?php

return [
    'weekly_limit' => (int) env('ARTICLE_VIDEO_WEEKLY_LIMIT', 2),
    'lookback_days' => (int) env('ARTICLE_VIDEO_LOOKBACK_DAYS', 30),

    'queues' => [
        'ai' => env('ARTICLE_VIDEO_AI_QUEUE', 'ai'),
        'render' => env('ARTICLE_VIDEO_RENDER_QUEUE', 'video-render'),
    ],

    'render' => [
        'mode' => env('ARTICLE_VIDEO_RENDER_MODE', 'text_only'),
        'ffmpeg_binary' => env('ARTICLE_VIDEO_FFMPEG_BINARY', 'ffmpeg'),
        'ffprobe_binary' => env('ARTICLE_VIDEO_FFPROBE_BINARY', 'ffprobe'),
        'working_directory' => env('ARTICLE_VIDEO_WORKING_DIRECTORY', storage_path('app/article-videos/tmp')),
        'timeout_seconds' => (int) env('ARTICLE_VIDEO_RENDER_TIMEOUT_SECONDS', 180),
    ],

    'storage' => [
        'disk' => env('ARTICLE_VIDEO_STORAGE_DISK', env('MEDIA_DISK', 'r2')),
        'base_path' => env('ARTICLE_VIDEO_STORAGE_BASE_PATH', 'article-videos'),
    ],

    'ai' => [
        'provider' => env('ARTICLE_VIDEO_AI_PROVIDER', env('AI_DEFAULT_PROVIDER', 'openai')),
        'script_model' => env('OPENAI_VIDEO_SCRIPT_MODEL', env('OPENAI_TEXT_MODEL')),
        'tts_provider' => env('ARTICLE_VIDEO_TTS_PROVIDER', env('AI_DEFAULT_AUDIO_PROVIDER', 'openai')),
        'tts_model' => env('OPENAI_TTS_MODEL', env('OPENAI_AUDIO_MODEL')),
        'tts_voice' => env('OPENAI_TTS_VOICE', env('OPENAI_AUDIO_VOICE', 'alloy')),
    ],
];
