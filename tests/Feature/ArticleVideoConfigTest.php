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
        $this->assertSame('ai', config('article_videos.jobs.recommendation.queue'));
        $this->assertSame('ai', config('article_videos.jobs.draft.queue'));
        $this->assertSame('video-render', config('article_videos.jobs.render.queue'));
        $this->assertSame(3, config('article_videos.jobs.recommendation.tries'));
        $this->assertSame([60, 300, 900], config('article_videos.jobs.recommendation.backoff_seconds'));
        $this->assertSame(120, config('article_videos.jobs.recommendation.timeout_seconds'));
        $this->assertSame(2, config('article_videos.jobs.render.tries'));
        $this->assertSame([120, 600], config('article_videos.jobs.render.backoff_seconds'));
        $this->assertSame(180, config('article_videos.jobs.render.timeout_seconds'));
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

    public function test_article_video_config_supports_runtime_overrides_for_queue_retry_policy(): void
    {
        config()->set('article_videos.jobs.recommendation.tries', 4);
        config()->set('article_videos.jobs.recommendation.backoff_seconds', [30, 120, 600, 1800]);
        config()->set('article_videos.jobs.recommendation.timeout_seconds', 150);
        config()->set('article_videos.jobs.render.tries', 3);
        config()->set('article_videos.jobs.render.backoff_seconds', [180, 900]);
        config()->set('article_videos.jobs.render.timeout_seconds', 300);

        $this->assertSame(4, config('article_videos.jobs.recommendation.tries'));
        $this->assertSame([30, 120, 600, 1800], config('article_videos.jobs.recommendation.backoff_seconds'));
        $this->assertSame(150, config('article_videos.jobs.recommendation.timeout_seconds'));
        $this->assertSame(3, config('article_videos.jobs.render.tries'));
        $this->assertSame([180, 900], config('article_videos.jobs.render.backoff_seconds'));
        $this->assertSame(300, config('article_videos.jobs.render.timeout_seconds'));
    }

    public function test_ai_config_exposes_openai_audio_defaults_for_article_video_usage(): void
    {
        config()->set('ai.service.providers.openai.audio_model', 'gpt-4o-mini-tts');
        config()->set('ai.service.providers.openai.audio_voice', 'alloy');

        $this->assertSame('gpt-4o-mini-tts', config('ai.service.providers.openai.audio_model'));
        $this->assertSame('alloy', config('ai.service.providers.openai.audio_voice'));
    }

    public function test_commands_doc_mentions_article_video_worker_queues(): void
    {
        $commands = file_get_contents(base_path('.agent/COMMANDS.md'));

        $this->assertIsString($commands);
        $this->assertStringContainsString('video-render', $commands);
        $this->assertStringContainsString('php artisan queue:work --queue=video-render,ai,default', $commands);
    }
}
