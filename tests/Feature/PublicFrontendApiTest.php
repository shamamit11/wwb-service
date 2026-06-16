<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFrontendApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://widewebblog.test');
        config()->set('app.name', 'Wide Web Blog');
    }

    public function test_public_routes_only_expose_active_categories_and_published_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $activeCategory = $this->createCategory($admin, 'AI Agents', 'ai-agents', true);
        $inactiveCategory = $this->createCategory($admin, 'Hidden', 'hidden', false);
        $activeTag = $this->createTag('Memory', 'memory', true);
        $inactiveTag = $this->createTag('Hidden Tag', 'hidden-tag', false);
        $activeTemplate = $this->createTemplate($admin, 'Tutorial', 'tutorial', Template::STATUS_ACTIVE);
        $archivedTemplate = $this->createTemplate($admin, 'Archived', 'archived-template', Template::STATUS_ARCHIVED);

        $published = $this->createPost($admin, $activeCategory, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'excerpt' => 'Published article excerpt.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-14 10:00:00',
            'word_count' => 900,
            'template_id' => $activeTemplate->id,
        ], [$activeTag]);
        $published->seo()->create([
            'meta_title' => 'How AI Agent Memory Works',
            'meta_description' => 'SEO description for the published article.',
            'canonical_url' => 'https://widewebblog.test/how-ai-agent-memory-works/',
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'AI Agent Memory',
            'og_description' => 'Open Graph description.',
            'schema_type' => 'TechArticle',
        ]);

        $published->blocks()->create([
            'block_type' => 'paragraph',
            'sort_order' => 1,
            'content_markdown' => 'Published content block.',
            'content_html_cache' => null,
            'plain_text_cache' => 'Published content block.',
            'settings' => [],
            'source_template_block_id' => null,
        ]);

        $publishedWithArchivedTemplate = $this->createPost($admin, $activeCategory, [
            'title' => 'Archived Template Post',
            'slug' => 'archived-template-post',
            'excerpt' => 'Another public post.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-13 09:00:00',
            'template_id' => $archivedTemplate->id,
        ], [$activeTag]);

        $draft = $this->createPost($admin, $activeCategory, [
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
        ], [$activeTag]);

        $scheduled = $this->createPost($admin, $activeCategory, [
            'title' => 'Scheduled Post',
            'slug' => 'scheduled-post',
            'status' => Post::STATUS_SCHEDULED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => '2026-06-20 10:00:00',
        ], [$activeTag]);

        $archived = $this->createPost($admin, $activeCategory, [
            'title' => 'Archived Post',
            'slug' => 'archived-post',
            'status' => Post::STATUS_ARCHIVED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
        ], [$activeTag]);

        $privatePublished = $this->createPost($admin, $activeCategory, [
            'title' => 'Private Published',
            'slug' => 'private-published',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PRIVATE,
            'published_at' => '2026-06-15 10:00:00',
        ], [$inactiveTag]);

        $inactiveCategoryPublished = $this->createPost($admin, $inactiveCategory, [
            'title' => 'Inactive Category Post',
            'slug' => 'inactive-category-post',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 10:00:00',
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Internal Note',
            'slug' => 'internal-note',
            'entry_type' => KnowledgeBaseEntry::TYPE_NOTE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Should never appear in public routes.',
            'content_markdown' => 'Internal note.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $this->getJson('/api/v1/public/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'ai-agents')
            ->assertJsonPath('data.0.post_count', 2);

        $this->getJson('/api/v1/public/categories/ai-agents')
            ->assertOk()
            ->assertJsonPath('data.slug', 'ai-agents')
            ->assertJsonCount(2, 'data.posts')
            ->assertJsonPath('data.posts.0.slug', 'how-ai-agent-memory-works')
            ->assertJsonMissingPath('data.posts.0.status');

        $this->getJson('/api/v1/public/categories/hidden')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');

        $this->getJson('/api/v1/public/tags')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'memory')
            ->assertJsonPath('data.0.post_count', 2);

        $this->getJson('/api/v1/public/tags/memory')
            ->assertOk()
            ->assertJsonCount(2, 'data.posts')
            ->assertJsonPath('data.posts.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/tags/hidden-tag')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');

        $this->getJson('/api/v1/public/posts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.visibility');

        $this->getJson('/api/v1/public/posts?category=ai-agents&tag=memory&search=Memory')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/public/posts/how-ai-agent-memory-works')
            ->assertOk()
            ->assertJsonPath('data.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('data.seo.meta_title', 'How AI Agent Memory Works')
            ->assertJsonPath('data.schema.@context', 'https://schema.org')
            ->assertJsonPath('data.template.slug', 'tutorial')
            ->assertJsonPath('data.blocks.0.content_markdown', 'Published content block.');

        $this->getJson('/api/v1/public/posts/archived-template-post')
            ->assertOk()
            ->assertJsonPath('data.template', null);

        $this->getJson('/api/v1/public/posts/draft-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/review-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/archived-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/private-published')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/inactive-category-post')->assertStatus(404);

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonPath('data.featured_posts.0.slug', 'how-ai-agent-memory-works')
            ->assertJsonCount(2, 'data.latest_posts')
            ->assertJsonCount(1, 'data.categories')
            ->assertJsonPath('data.seo.canonical_url', 'https://widewebblog.test/');

        $this->getJson('/api/v1/public/search?q=memory')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/search?q=scheduled')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/public/sitemap')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/rss')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->assertNotEquals($draft->id, $published->id);
        $this->assertNotEquals($scheduled->id, $published->id);
        $this->assertNotEquals($archived->id, $publishedWithArchivedTemplate->id);
        $this->assertNotEquals($privatePublished->id, $inactiveCategoryPublished->id);
    }

    private function createCategory(User $author, string $name, string $slug, bool $isActive = true): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'description' => "{$name} description.",
            'is_active' => $isActive,
            'sort_order' => 0,
        ]);
    }

    private function createTag(string $name, string $slug, bool $isActive = true): Tag
    {
        return Tag::query()->create([
            'name' => $name,
            'slug' => $slug,
            'description' => "{$name} description.",
            'is_active' => $isActive,
        ]);
    }

    private function createTemplate(User $author, string $name, string $slug, string $status): Template
    {
        return Template::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'template_type' => Template::TYPE_TUTORIAL,
            'description' => "{$name} description.",
            'status' => $status,
            'default_excerpt_prompt' => null,
            'default_meta' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  list<Tag>  $tags
     */
    private function createPost(User $author, Category $category, array $overrides = [], array $tags = []): Post
    {
        $post = Post::query()->create(array_merge([
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

        $post->tags()->sync(array_map(static fn (Tag $tag): int => $tag->id, $tags));

        return $post;
    }
}
