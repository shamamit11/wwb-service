<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostDetailApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_public_post_detail_uses_articles_canonical_url_in_payload_and_schema(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'short_description' => 'Practical memory patterns for agent systems.',
            'full_article_html' => '<p>Article body with enough words for schema coverage.</p>',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-16 12:00:00',
        ]);

        $response = $this->getJson("/api/v1/public/posts/{$post->slug}")
            ->assertOk();

        $payload = $response->json('data');
        $graph = $payload['schema']['@graph'];
        $breadcrumb = $graph[2];
        $article = $graph[3];

        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/',
            $payload['canonical_url'],
        );
        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/#breadcrumb',
            $breadcrumb['@id'],
        );
        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/',
            $breadcrumb['itemListElement'][2]['item'],
        );
        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/#article',
            $article['@id'],
        );
        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/',
            $article['url'],
        );
        $this->assertSame(
            'https://www.widewebblog.com/articles/how-ai-agent-memory-works/',
            $article['mainEntityOfPage'],
        );
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
