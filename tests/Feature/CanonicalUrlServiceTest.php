<?php

namespace Tests\Feature;

use App\Http\Resources\Api\V1\PostResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\Seo\Services\CanonicalUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalUrlServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.worldwideweb.test');
    }

    public function test_service_derives_default_canonical_urls_by_content_type(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-16 12:00:00',
        ]);

        $service = app(CanonicalUrlService::class);

        $this->assertSame('https://www.worldwideweb.test/categories/ai-agents/', $service->for($category));
        $this->assertSame('https://www.worldwideweb.test/how-ai-agent-memory-works/', $service->for($post));
    }

    public function test_service_respects_canonical_override_when_present(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');

        $category->seo()->create([
            'meta_title' => 'AI Agents Category',
            'canonical_url' => 'https://override.example/categories/ai-agents',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $service = app(CanonicalUrlService::class);

        $this->assertSame('https://override.example/categories/ai-agents', $service->for($category->fresh()->load('seo')));
    }

    public function test_service_normalizes_service_host_canonical_override_to_frontend_host(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');

        $category->seo()->create([
            'meta_title' => 'AI Agents Category',
            'canonical_url' => 'https://service.widewebblog.test/categories/ai-agents',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $service = app(CanonicalUrlService::class);

        $this->assertSame(
            'https://www.worldwideweb.test/categories/ai-agents',
            $service->for($category->fresh()->load('seo')),
        );
    }

    public function test_published_post_resource_exposes_canonical_value(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-16 12:00:00',
        ])->load('seo');

        $payload = (new PostResource($post))->resolve();

        $this->assertSame('https://www.worldwideweb.test/how-ai-agent-memory-works/', $payload['canonical_url']);
    }

    private function createCategory(User $author, string $name, string $slug): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPost(User $author, Category $category, array $overrides = []): Post
    {
        return Post::query()->create(array_merge([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post',
            'excerpt' => null,
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'content_version' => 1,
            'reading_time_minutes' => null,
            'word_count' => null,
            'is_featured' => false,
            'meta' => null,
        ], $overrides));
    }
}
