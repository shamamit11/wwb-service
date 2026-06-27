<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFeedAndSitemapApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_public_rss_returns_article_links_with_articles_prefix(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'Newer Published',
            'slug' => 'newer-published',
            'short_description' => 'Newer feed summary',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
        ]);

        $this->getJson('/api/v1/public/rss')
            ->assertOk()
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonPath('data.0.link', 'https://www.widewebblog.com/articles/newer-published/');
    }

    public function test_public_sitemap_returns_article_canonical_urls_with_articles_prefix(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'Newer Published',
            'slug' => 'newer-published',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
        ]);

        $this->getJson('/api/v1/public/sitemap')
            ->assertOk()
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonPath('data.0.canonical_url', 'https://www.widewebblog.com/articles/newer-published/');
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
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post',
            'short_description' => null,
            'description' => null,
            'full_article_html' => '<h1>Sample Post</h1><p>Sample body.</p>',
            'full_article_delta' => null,
            'faq' => [],
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'meta' => null,
        ], $overrides));
    }
}
