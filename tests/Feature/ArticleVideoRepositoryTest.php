<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\ArticleVideos\Data\CreateArticleVideoData;
use App\Modules\ArticleVideos\Data\CreateArticleVideoMemoryEntryData;
use App\Modules\ArticleVideos\Data\CreateArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\RefreshPendingArticleVideoRecommendationData;
use App\Modules\ArticleVideos\Data\UpdateArticleVideoData;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationFormat;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationPriority;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationStatus;
use App\Modules\ArticleVideos\Enums\ArticleVideoRenderMode;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use App\Modules\ArticleVideos\Repositories\ArticleVideoMemoryEntryRepository;
use App\Modules\ArticleVideos\Repositories\ArticleVideoRecommendationRepository;
use App\Modules\ArticleVideos\Repositories\ArticleVideoRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleVideoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendation_repository_upserts_the_latest_pending_recommendation_for_a_post(): void
    {
        $recommendations = app(ArticleVideoRecommendationRepository::class);
        $post = $this->createPost('openai-sdk-upsert');

        $first = $recommendations->upsertPendingForPost(new RefreshPendingArticleVideoRecommendationData(
            postId: $post->id,
            score: 88,
            priority: ArticleVideoRecommendationPriority::High,
            recommendedFormat: ArticleVideoRecommendationFormat::Explainer,
            reason: 'First recommendation',
            suggestedHook: 'First hook',
            riskNote: 'First risk',
            evaluatedAt: now()->toDateTimeString(),
        ));

        $second = $recommendations->upsertPendingForPost(new RefreshPendingArticleVideoRecommendationData(
            postId: $post->id,
            score: 91,
            priority: ArticleVideoRecommendationPriority::Medium,
            recommendedFormat: ArticleVideoRecommendationFormat::Checklist,
            reason: 'Updated recommendation',
            suggestedHook: 'Updated hook',
            riskNote: 'Updated risk',
            evaluatedAt: now()->addMinute()->toDateTimeString(),
        ));

        $this->assertSame($first->id, $second->id);
        $this->assertSame($second->id, $recommendations->findLatestPendingByPostId($post->id)?->id);
        $this->assertCount(1, $recommendations->findByPostId($post->id));
        $this->assertDatabaseHas('article_video_recommendations', [
            'id' => $second->id,
            'post_id' => $post->id,
            'score' => 91,
            'status' => ArticleVideoRecommendationStatus::Pending->value,
            'suggested_hook' => 'Updated hook',
        ]);
    }

    public function test_video_repository_supports_active_draft_and_rendered_video_queries(): void
    {
        $recommendations = app(ArticleVideoRecommendationRepository::class);
        $videos = app(ArticleVideoRepository::class);
        $post = $this->createPost('openai-sdk-video');

        $recommendation = $recommendations->create(new CreateArticleVideoRecommendationData(
            postId: $post->id,
            score: 95,
            priority: ArticleVideoRecommendationPriority::High,
            recommendedFormat: ArticleVideoRecommendationFormat::Explainer,
            reason: 'High confidence fit for short video.',
            suggestedHook: 'Why this SDK pattern matters.',
            riskNote: null,
            status: ArticleVideoRecommendationStatus::Selected,
            evaluatedAt: now()->toDateTimeString(),
        ));

        $draft = $videos->create(new CreateArticleVideoData(
            postId: $post->id,
            articleVideoRecommendationId: $recommendation->id,
            status: ArticleVideoStatus::Draft,
            renderMode: ArticleVideoRenderMode::TextOnly,
            hook: 'Draft hook',
            voiceoverText: 'Draft voiceover',
            scriptJson: ['scenes' => [['text' => 'Draft scene']]],
            captionsJson: [['text' => 'Draft caption']],
        ));

        $this->assertSame($draft->id, $videos->findActiveDraftForPostId($post->id)?->id);
        $this->assertFalse($videos->hasRenderedVideoForPostId($post->id));

        $videos->update($draft, new UpdateArticleVideoData(
            articleVideoRecommendationId: $recommendation->id,
            status: ArticleVideoStatus::Rendered,
            renderMode: ArticleVideoRenderMode::TextOnly,
            hook: 'Rendered hook',
            voiceoverText: 'Rendered voiceover',
            scriptJson: ['scenes' => [['text' => 'Rendered scene']]],
            captionsJson: [['text' => 'Rendered caption']],
            voiceoverPath: 'article-videos/1/voiceover.mp3',
            captionsPath: 'article-videos/1/captions.srt',
            thumbnailPath: 'article-videos/1/thumbnail.jpg',
            videoPath: 'article-videos/1/final.mp4',
            durationSeconds: 42,
            approvedAt: now()->subMinute()->toDateTimeString(),
            renderedAt: now()->toDateTimeString(),
            errorMessage: null,
        ));

        $this->assertNull($videos->findActiveDraftForPostId($post->id));
        $this->assertTrue($videos->hasRenderedVideoForPostId($post->id));
    }

    public function test_memory_entry_repository_returns_recent_entries_first(): void
    {
        $videos = app(ArticleVideoRepository::class);
        $memory = app(ArticleVideoMemoryEntryRepository::class);
        $post = $this->createPost('openai-sdk-memory');

        $video = $videos->create(new CreateArticleVideoData(
            postId: $post->id,
            articleVideoRecommendationId: null,
            status: ArticleVideoStatus::Draft,
            renderMode: ArticleVideoRenderMode::TextOnly,
        ));

        $older = $memory->create(new CreateArticleVideoMemoryEntryData(
            postId: $post->id,
            articleVideoId: $video->id,
            hook: 'Older hook',
            coreAngle: 'older angle',
            voiceoverText: 'Older voiceover',
        ));
        $older->forceFill([
            'created_at' => now()->subMinute(),
        ])->save();

        $newer = $memory->create(new CreateArticleVideoMemoryEntryData(
            postId: $post->id,
            articleVideoId: $video->id,
            hook: 'Newer hook',
            coreAngle: 'newer angle',
            voiceoverText: 'Newer voiceover',
        ));

        $recent = $memory->findRecentForPostId($post->id, 1);

        $this->assertCount(1, $recent);
        $this->assertSame($newer->id, $recent->first()?->id);
        $this->assertSame($video->id, $memory->findById($older->id)?->articleVideo?->id);
    }

    private function createPost(string $slug): Post
    {
        $author = User::factory()->create();
        $category = Category::query()->create([
            'created_by_user_id' => $author->id,
            'name' => 'AI Tools '.strtoupper($slug),
            'slug' => 'category-'.$slug,
        ]);

        return Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post '.$slug,
            'slug' => $slug,
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => now(),
        ]);
    }
}
