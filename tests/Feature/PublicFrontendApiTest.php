<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Homepage;
use App\Models\KnowledgeBaseEntry;
use App\Models\Page;
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

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.worldwideweb.test');
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
            'reading_time_minutes' => 8,
            'word_count' => 900,
            'template_id' => $activeTemplate->id,
        ], [$activeTag]);
        $published->seo()->create([
            'meta_title' => 'How AI Agent Memory Works',
            'meta_description' => 'SEO description for the published article.',
            'canonical_url' => 'https://service.widewebblog.test/how-ai-agent-memory-works/',
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
            'reading_time_minutes' => 5,
            'template_id' => $archivedTemplate->id,
        ], [$activeTag]);

        $relatedPublished = $this->createPost($admin, $activeCategory, [
            'title' => 'Agent Context Windows Explained',
            'slug' => 'agent-context-windows-explained',
            'excerpt' => 'Context handling for modern agent systems.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 08:00:00',
            'reading_time_minutes' => 6,
        ]);

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

        Homepage::query()->create([
            'singleton_key' => Homepage::SINGLETON_KEY,
            'hero' => [
                'eyebrow' => 'Start here',
                'title' => 'Build better internet systems',
                'description' => 'Editorially curated homepage content.',
                'primary_cta_label' => 'Read featured stories',
                'primary_cta_url' => 'https://widewebblog.test/featured',
                'secondary_cta_label' => 'Browse resources',
                'secondary_cta_url' => 'https://widewebblog.test/resources',
                'media_url' => 'https://cdn.widewebblog.test/home/hero.png',
                'media_alt' => 'Homepage hero artwork',
            ],
            'featured_editorial' => [
                'title' => 'Featured editorial',
                'description' => 'Hand-picked editorial cards.',
                'mode' => Homepage::SECTION_MODE_MANUAL,
                'post_ids' => [],
                'category_ids' => null,
                'limit' => 2,
            ],
            'guide_section' => [
                'title' => 'Guides and resources',
                'description' => 'Automatically selected guides.',
                'mode' => Homepage::SECTION_MODE_MANUAL,
                'post_ids' => [],
                'category_ids' => null,
                'limit' => 3,
            ],
            'topic_section' => [
                'title' => 'Browse topics',
                'description' => 'Explore the editorial taxonomy.',
                'category_ids' => [],
            ],
            'promo_section' => [
                'enabled' => true,
                'eyebrow' => 'Resource pack',
                'title' => 'Download the operator kit',
                'description' => 'Promotional support section for a featured resource.',
                'bullet_points' => ['Checklists', 'Benchmarks', 'Field notes'],
                'primary_cta_label' => 'Get the kit',
                'primary_cta_url' => 'https://widewebblog.test/kit',
                'stats' => [
                    ['label' => 'Templates', 'value' => '12'],
                    ['label' => 'Playbooks', 'value' => '8'],
                ],
            ],
            'newsletter_section' => [
                'enabled' => true,
                'title' => 'Get weekly dispatches',
                'description' => 'Editorial updates and new resources.',
            ],
            'seo' => [
                'meta_title' => 'Wide Web Blog | Homepage',
                'meta_description' => 'Homepage metadata for discovery and click-through.',
            ],
            'updated_by_user_id' => $admin->id,
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
            ->assertJsonPath('data.0.post_count', 3);

        $this->getJson('/api/v1/public/categories/ai-agents')
            ->assertOk()
            ->assertJsonPath('data.slug', 'ai-agents')
            ->assertJsonCount(3, 'data.posts')
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
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('data.0.author.name', $admin->name)
            ->assertJsonPath('data.0.read_time', '8 min read')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.visibility');

        $this->getJson('/api/v1/public/posts?category=ai-agents&tag=memory&search=Memory')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/public/posts/how-ai-agent-memory-works')
            ->assertOk()
            ->assertJsonPath('data.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('data.author.name', $admin->name)
            ->assertJsonPath('data.read_time', '8 min read')
            ->assertJsonPath('data.word_count', 900)
            ->assertJsonPath('data.content', 'Published content block.')
            ->assertJsonPath('data.content_markdown', 'Published content block.')
            ->assertJsonPath('data.seo.meta_title', 'How AI Agent Memory Works')
            ->assertJsonCount(2, 'data.related_posts')
            ->assertJsonPath('data.related_posts.0.slug', 'archived-template-post')
            ->assertJsonPath('data.related_posts.1.slug', 'agent-context-windows-explained')
            ->assertJsonPath('data.canonical_url', 'https://www.worldwideweb.test/how-ai-agent-memory-works/')
            ->assertJsonPath('data.seo.canonical_url', 'https://www.worldwideweb.test/how-ai-agent-memory-works/')
            ->assertJsonPath('data.schema.@context', 'https://schema.org')
            ->assertJsonPath('data.schema.@graph.0.url', 'https://www.worldwideweb.test/')
            ->assertJsonPath('data.schema.@graph.1.url', 'https://www.worldwideweb.test/')
            ->assertJsonPath('data.schema.@graph.2.itemListElement.0.item', 'https://www.worldwideweb.test/')
            ->assertJsonPath('data.schema.@graph.3.url', 'https://www.worldwideweb.test/how-ai-agent-memory-works/')
            ->assertJsonPath('data.template.slug', 'tutorial')
            ->assertJsonPath('data.blocks.0.content_markdown', 'Published content block.');

        $this->getJson('/api/v1/public/posts/archived-template-post')
            ->assertOk()
            ->assertJsonPath('data.template', null);

        $this->getJson('/api/v1/public/posts/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');

        $privacyPage = $this->createPage($admin, [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'type' => Page::TYPE_LEGAL,
            'status' => Page::STATUS_PUBLISHED,
            'visibility' => Page::VISIBILITY_PUBLIC,
            'summary' => 'How Wide Web Blog handles personal information.',
            'content_markdown' => '# Privacy Policy',
            'published_at' => '2026-06-15 12:00:00',
        ]);
        $privacyPage->seo()->create([
            'meta_title' => 'Privacy Policy',
            'meta_description' => 'Privacy policy for Wide Web Blog.',
            'canonical_url' => 'https://service.widewebblog.test/pages/privacy-policy/',
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'Privacy Policy',
            'og_description' => 'Privacy policy details.',
            'schema_type' => 'WebPage',
        ]);

        $draftPage = $this->createPage($admin, [
            'title' => 'Draft Terms',
            'slug' => 'draft-terms',
            'type' => Page::TYPE_LEGAL,
            'status' => Page::STATUS_DRAFT,
            'visibility' => Page::VISIBILITY_PUBLIC,
            'content_markdown' => '# Draft Terms',
        ]);

        $internalPage = $this->createPage($admin, [
            'title' => 'Internal Notes',
            'slug' => 'internal-notes',
            'type' => Page::TYPE_SUPPORT,
            'status' => Page::STATUS_PUBLISHED,
            'visibility' => Page::VISIBILITY_INTERNAL,
            'content_markdown' => '# Internal Notes',
            'published_at' => '2026-06-15 13:00:00',
        ]);

        $this->getJson('/api/v1/public/posts/draft-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/review-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/archived-post')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/private-published')->assertStatus(404);
        $this->getJson('/api/v1/public/posts/inactive-category-post')->assertStatus(404);

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Build better internet systems')
            ->assertJsonPath('data.hero.primary_cta_url', 'https://widewebblog.test/featured')
            ->assertJsonPath('data.featured_editorial.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.featured_editorial.post_ids.0', $published->id)
            ->assertJsonPath('data.featured_editorial.posts.0.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('data.guide_section.title', 'Recent Articles')
            ->assertJsonPath('data.guide_section.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.guide_section.post_ids.0', $published->id)
            ->assertJsonPath('data.guide_section.post_ids.1', $publishedWithArchivedTemplate->id)
            ->assertJsonPath('data.guide_section.posts.1.slug', 'archived-template-post')
            ->assertJsonPath('data.topic_section.category_ids.0', $activeCategory->id)
            ->assertJsonPath('data.topic_section.categories.0.slug', 'ai-agents')
            ->assertJsonPath('data.promo_section.stats.1.label', 'Playbooks')
            ->assertJsonPath('data.newsletter_section.enabled', true)
            ->assertJsonPath('data.seo.meta_title', 'Wide Web Blog | Homepage')
            ->assertJsonMissingPath('data.featured_posts')
            ->assertJsonMissingPath('data.latest_posts')
            ->assertJsonMissingPath('data.categories');

        $this->getJson('/api/v1/public/pages/privacy-policy')
            ->assertOk()
            ->assertJsonPath('data.slug', 'privacy-policy')
            ->assertJsonPath('data.type', Page::TYPE_LEGAL)
            ->assertJsonPath('data.content_markdown', '# Privacy Policy')
            ->assertJsonPath('data.canonical_url', 'https://www.worldwideweb.test/pages/privacy-policy/')
            ->assertJsonPath('data.seo.meta_title', 'Privacy Policy')
            ->assertJsonPath('data.seo.canonical_url', 'https://www.worldwideweb.test/pages/privacy-policy/');

        $this->getJson('/api/v1/public/pages/draft-terms')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');

        $this->getJson('/api/v1/public/pages/internal-notes')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');

        $this->getJson('/api/v1/public/search?q=memory')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/search?q=context')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'agent-context-windows-explained');

        $this->getJson('/api/v1/public/search?q=Published%20content%20block')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/search?q=scheduled')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/public/search?q=')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);

        $this->getJson('/api/v1/public/sitemap')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->getJson('/api/v1/public/rss')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'how-ai-agent-memory-works');

        $this->assertNotEquals($draft->id, $published->id);
        $this->assertNotEquals($scheduled->id, $published->id);
        $this->assertNotEquals($archived->id, $publishedWithArchivedTemplate->id);
        $this->assertNotEquals($privatePublished->id, $inactiveCategoryPublished->id);
        $this->assertNotEquals($draftPage->id, $internalPage->id);
        $this->assertNotEquals($relatedPublished->id, $published->id);
    }

    public function test_public_home_bootstraps_default_homepage_shape(): void
    {
        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonPath('data.hero.title', null)
            ->assertJsonPath('data.featured_editorial.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.featured_editorial.post_ids', [])
            ->assertJsonPath('data.featured_editorial.posts', [])
            ->assertJsonPath('data.guide_section.title', 'Recent Articles')
            ->assertJsonPath('data.guide_section.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.guide_section.posts', [])
            ->assertJsonPath('data.topic_section.category_ids', [])
            ->assertJsonPath('data.topic_section.categories', [])
            ->assertJsonPath('data.promo_section.enabled', false)
            ->assertJsonPath('data.newsletter_section.enabled', false)
            ->assertJsonPath('data.seo.meta_title', null);

        $this->assertDatabaseHas('homepages', [
            'singleton_key' => Homepage::SINGLETON_KEY,
        ]);
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
     */
    private function createPage(User $author, array $overrides = []): Page
    {
        return Page::query()->create(array_merge([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => null,
            'title' => 'Sample Page',
            'slug' => 'sample-page',
            'type' => Page::TYPE_STANDARD,
            'status' => Page::STATUS_DRAFT,
            'summary' => null,
            'content_markdown' => 'Sample page content.',
            'visibility' => Page::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'meta' => null,
        ], $overrides));
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
