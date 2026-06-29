<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleVideoConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_video_config_exposes_expected_defaults(): void
    {
        config()->set('ai.service.providers.openai.text_model', 'gpt-5-mini');
        config()->set('ai.service.providers.openai.audio_model', 'gpt-4o-mini-tts');
        config()->set('ai.service.providers.openai.audio_voice', 'alloy');

        $this->assertSame(2, config('article_videos.weekly_limit'));
        $this->assertSame(30, config('article_videos.lookback_days'));
        $this->assertSame('text_only', config('article_videos.render.mode'));
        $this->assertSame('ai', config('article_videos.queues.ai'));
        $this->assertSame('video-render', config('article_videos.queues.render'));
        $this->assertSame('r2', config('article_videos.storage.disk'));
        $this->assertSame('article-videos', config('article_videos.storage.base_path'));
        $this->assertSame('openai', config('article_videos.ai.provider'));
        $this->assertSame('openai', config('article_videos.ai.tts_provider'));
    }

    public function test_article_video_config_supports_runtime_overrides_for_models_voice_and_paths(): void
    {
        config()->set('article_videos.ai.script_model', 'gpt-4o-mini');
        config()->set('article_videos.ai.tts_model', 'gpt-4o-mini-tts');
        config()->set('article_videos.ai.tts_voice', 'sage');
        config()->set('article_videos.render.ffmpeg_binary', '/opt/bin/ffmpeg');
        config()->set('article_videos.render.ffprobe_binary', '/opt/bin/ffprobe');
        config()->set('article_videos.render.working_directory', storage_path('app/custom-article-video-tmp'));
        config()->set('article_videos.render.timeout_seconds', 240);

        $this->assertSame('gpt-4o-mini', config('article_videos.ai.script_model'));
        $this->assertSame('gpt-4o-mini-tts', config('article_videos.ai.tts_model'));
        $this->assertSame('sage', config('article_videos.ai.tts_voice'));
        $this->assertSame('/opt/bin/ffmpeg', config('article_videos.render.ffmpeg_binary'));
        $this->assertSame('/opt/bin/ffprobe', config('article_videos.render.ffprobe_binary'));
        $this->assertSame(storage_path('app/custom-article-video-tmp'), config('article_videos.render.working_directory'));
        $this->assertSame(240, config('article_videos.render.timeout_seconds'));
    }

    public function test_ai_config_exposes_openai_audio_defaults_for_article_video_usage(): void
    {
        config()->set('ai.service.providers.openai.audio_model', 'gpt-4o-mini-tts');
        config()->set('ai.service.providers.openai.audio_voice', 'alloy');

        $this->assertSame('gpt-4o-mini-tts', config('ai.service.providers.openai.audio_model'));
        $this->assertSame('alloy', config('ai.service.providers.openai.audio_voice'));
    }
}
