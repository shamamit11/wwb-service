<?php

namespace Tests\Feature;

use App\Models\ArticleVideo;
use App\Models\ArticleVideoMemoryEntry;
use App\Models\ArticleVideoRecommendation;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationFormat;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationPriority;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationStatus;
use App\Modules\ArticleVideos\Enums\ArticleVideoRenderMode;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleVideoFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_video_models_expose_expected_relationships_and_casts(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->create([
            'created_by_user_id' => $author->id,
            'name' => 'AI Tools',
            'slug' => 'ai-tools-foundation',
        ]);

        $post = Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'OpenAI SDK Patterns',
            'slug' => 'openai-sdk-patterns',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => now(),
        ]);

        $recommendation = ArticleVideoRecommendation::query()->create([
            'post_id' => $post->id,
            'score' => 92,
            'priority' => ArticleVideoRecommendationPriority::High,
            'recommended_format' => ArticleVideoRecommendationFormat::Explainer,
            'reason' => 'The article is concise and suited to short-form explanation.',
            'suggested_hook' => 'The fastest way to understand OpenAI SDK patterns.',
            'risk_note' => 'Avoid overclaiming benchmark gains.',
            'status' => ArticleVideoRecommendationStatus::Pending,
            'evaluated_at' => now(),
        ]);

        $video = ArticleVideo::query()->create([
            'post_id' => $post->id,
            'article_video_recommendation_id' => $recommendation->id,
            'status' => ArticleVideoStatus::Approved,
            'render_mode' => ArticleVideoRenderMode::TextOnly,
            'hook' => 'Build better SDK integrations in under a minute.',
            'voiceover_text' => 'A short explainer about reliable OpenAI SDK usage patterns.',
            'script_json' => [
                'scenes' => [
                    ['text' => 'Start with one narrow use case.'],
                ],
            ],
            'captions_json' => [
                ['start' => 0, 'end' => 2, 'text' => 'Start with one narrow use case.'],
            ],
            'approved_at' => now(),
        ]);

        $memoryEntry = ArticleVideoMemoryEntry::query()->create([
            'post_id' => $post->id,
            'article_video_id' => $video->id,
            'hook' => 'Build better SDK integrations in under a minute.',
            'core_angle' => 'practical integration advice',
            'voiceover_text' => 'A short explainer about reliable OpenAI SDK usage patterns.',
        ]);

        $recommendation->refresh();
        $video->refresh();

        $this->assertSame($post->id, $recommendation->post()->firstOrFail()->id);
        $this->assertSame($recommendation->id, $post->articleVideoRecommendations()->firstOrFail()->id);
        $this->assertSame($video->id, $post->articleVideos()->firstOrFail()->id);
        $this->assertSame($video->id, $recommendation->videos()->firstOrFail()->id);
        $this->assertSame($recommendation->id, $video->recommendation()->firstOrFail()->id);
        $this->assertSame($post->id, $memoryEntry->post()->firstOrFail()->id);
        $this->assertSame($memoryEntry->id, $post->articleVideoMemoryEntries()->firstOrFail()->id);
        $this->assertSame($memoryEntry->id, $video->memoryEntries()->firstOrFail()->id);
        $this->assertSame($video->id, $memoryEntry->articleVideo()->firstOrFail()->id);

        $this->assertSame(ArticleVideoRecommendationPriority::High, $recommendation->priority);
        $this->assertSame(ArticleVideoRecommendationFormat::Explainer, $recommendation->recommended_format);
        $this->assertSame(ArticleVideoRecommendationStatus::Pending, $recommendation->status);
        $this->assertSame(ArticleVideoStatus::Approved, $video->status);
        $this->assertSame(ArticleVideoRenderMode::TextOnly, $video->render_mode);

        $this->assertTrue($recommendation->isPending());
        $this->assertFalse($recommendation->isSelected());
        $this->assertTrue($video->isApproved());
        $this->assertTrue($video->canRender());
        $this->assertFalse($video->canRegenerateDraft());
        $this->assertFalse($video->isRendered());
    }

    public function test_article_video_enum_values_match_the_mvp_contract(): void
    {
        $this->assertSame(
            ['high', 'medium', 'low'],
            array_map(static fn (ArticleVideoRecommendationPriority $priority): string => $priority->value, ArticleVideoRecommendationPriority::cases()),
        );

        $this->assertSame(
            ['explainer', 'checklist', 'opinion', 'news_pulse'],
            array_map(static fn (ArticleVideoRecommendationFormat $format): string => $format->value, ArticleVideoRecommendationFormat::cases()),
        );

        $this->assertSame(
            ['pending', 'selected', 'skipped', 'rejected'],
            array_map(static fn (ArticleVideoRecommendationStatus $status): string => $status->value, ArticleVideoRecommendationStatus::cases()),
        );

        $this->assertSame(
            ['draft', 'approved', 'rendering', 'rendered', 'failed', 'rejected'],
            array_map(static fn (ArticleVideoStatus $status): string => $status->value, ArticleVideoStatus::cases()),
        );

        $this->assertSame(
            ['text_only'],
            array_map(static fn (ArticleVideoRenderMode $mode): string => $mode->value, ArticleVideoRenderMode::cases()),
        );
    }
}
