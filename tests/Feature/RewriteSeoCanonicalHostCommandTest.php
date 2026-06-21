<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewriteSeoCanonicalHostCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_command_can_preview_matching_canonical_urls_without_updating(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');

        $category->seo()->create([
            'canonical_url' => 'https://service.widewebblog.com/categories/ai-agents/',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $this->artisan('seo:rewrite-canonical-host', [
            'from' => 'https://service.widewebblog.com',
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('seo_metadata', [
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
            'canonical_url' => 'https://service.widewebblog.com/categories/ai-agents/',
        ]);
    }

    public function test_command_rewrites_only_matching_stored_canonical_urls(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
        ]);
        $otherPost = $this->createPost($author, $category, [
            'title' => 'External Canonical',
            'slug' => 'external-canonical',
        ]);

        $category->seo()->create([
            'canonical_url' => 'https://service.widewebblog.com/categories/ai-agents/',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $post->seo()->create([
            'canonical_url' => 'https://service.widewebblog.com/how-ai-agent-memory-works/',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $otherPost->seo()->create([
            'canonical_url' => 'https://override.example/external-canonical/',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $this->artisan('seo:rewrite-canonical-host', [
            'from' => 'https://service.widewebblog.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('seo_metadata', [
            'seoable_type' => Category::class,
            'seoable_id' => $category->id,
            'canonical_url' => 'https://www.widewebblog.com/categories/ai-agents/',
        ]);

        $this->assertDatabaseHas('seo_metadata', [
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'canonical_url' => 'https://www.widewebblog.com/how-ai-agent-memory-works/',
        ]);

        $this->assertDatabaseHas('seo_metadata', [
            'seoable_type' => Post::class,
            'seoable_id' => $otherPost->id,
            'canonical_url' => 'https://override.example/external-canonical/',
        ]);
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
