<?php

namespace Tests\Feature;

use App\Enums\ContentBlockType;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\Seo\Services\GenerateSchemaPayloadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://widewebblog.test');
        config()->set('app.name', 'Wide Web Blog');
    }

    public function test_admin_schema_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/seo/schema/post/1')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_post_schema_service_and_endpoint_generate_article_breadcrumb_org_website_and_faq_payloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Editor One']);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'excerpt' => 'A practical explanation of agent memory systems.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
            'updated_at' => '2026-06-13 12:15:00',
            'word_count' => 1200,
        ]);

        $post->blocks()->create([
            'block_type' => ContentBlockType::FAQ->value,
            'sort_order' => 1,
            'content_markdown' => "## What is agent memory?\nStored context for the system.",
            'content_html_cache' => null,
            'plain_text_cache' => "What is agent memory?\nStored context for the system.",
            'settings' => [
                'items' => [[
                    'question' => 'What is agent memory?',
                    'answer_markdown' => 'Stored context for the system.',
                ]],
            ],
            'source_template_block_id' => null,
        ]);

        $post->seo()->create([
            'meta_title' => 'How AI Agent Memory Works',
            'meta_description' => 'SEO description for AI agent memory.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_type' => 'TechArticle',
            'schema_payload' => [
                'about' => ['AI agents', 'Memory'],
            ],
            'focus_keyword' => 'ai agent memory',
        ]);

        $payload = app(GenerateSchemaPayloadService::class)->handle('post', $post->id);

        $this->assertSame('https://schema.org', $payload['@context']);
        $this->assertCount(5, $payload['@graph']);
        $this->assertSame('Organization', $payload['@graph'][0]['@type']);
        $this->assertSame('WebSite', $payload['@graph'][1]['@type']);
        $this->assertSame('BreadcrumbList', $payload['@graph'][2]['@type']);
        $this->assertSame('TechArticle', $payload['@graph'][3]['@type']);
        $this->assertSame(['AI agents', 'Memory'], $payload['@graph'][3]['about']);
        $this->assertSame('FAQPage', $payload['@graph'][4]['@type']);
        $this->assertSame('What is agent memory?', $payload['@graph'][4]['mainEntity'][0]['name']);

        $this->withToken($token)->getJson("/api/v1/admin/seo/schema/post/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.@context', 'https://schema.org')
            ->assertJsonPath('data.@graph.0.@type', 'Organization')
            ->assertJsonPath('data.@graph.1.@type', 'WebSite')
            ->assertJsonPath('data.@graph.2.itemListElement.1.name', 'AI Agents')
            ->assertJsonPath('data.@graph.3.@type', 'TechArticle')
            ->assertJsonPath('data.@graph.3.headline', 'How AI Agent Memory Works')
            ->assertJsonPath('data.@graph.3.description', 'SEO description for AI agent memory.')
            ->assertJsonPath('data.@graph.3.url', 'https://widewebblog.test/how-ai-agent-memory-works/')
            ->assertJsonPath('data.@graph.3.author.name', 'Editor One')
            ->assertJsonPath('data.@graph.3.articleSection', 'AI Agents')
            ->assertJsonPath('data.@graph.3.keywords', 'ai agent memory')
            ->assertJsonPath('data.@graph.4.@type', 'FAQPage')
            ->assertJsonPath('data.@graph.4.mainEntity.0.acceptedAnswer.text', 'Stored context for the system.');
    }

    public function test_category_schema_service_and_endpoint_generate_collection_page_and_breadcrumb_payloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');

        $category->seo()->create([
            'meta_title' => 'AI Agents Category',
            'meta_description' => 'Technical content about AI agents.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_type' => 'CollectionPage',
            'schema_payload' => [
                'inLanguage' => 'en',
            ],
        ]);

        $payload = app(GenerateSchemaPayloadService::class)->handle('category', $category->id);

        $this->assertSame('CollectionPage', $payload['@graph'][3]['@type']);
        $this->assertSame('en', $payload['@graph'][3]['inLanguage']);

        $this->withToken($token)->getJson("/api/v1/admin/seo/schema/category/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.@context', 'https://schema.org')
            ->assertJsonPath('data.@graph.2.@type', 'BreadcrumbList')
            ->assertJsonPath('data.@graph.2.itemListElement.1.name', 'AI Agents')
            ->assertJsonPath('data.@graph.3.@type', 'CollectionPage')
            ->assertJsonPath('data.@graph.3.name', 'AI Agents Category')
            ->assertJsonPath('data.@graph.3.description', 'Technical content about AI agents.')
            ->assertJsonPath('data.@graph.3.url', 'https://widewebblog.test/categories/ai-agents/')
            ->assertJsonPath('data.@graph.3.inLanguage', 'en');
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
        return tap(Post::query()->create(array_merge([
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
        ], $overrides)), function (Post $post) use ($overrides): void {
            if (array_key_exists('updated_at', $overrides)) {
                $post->forceFill(['updated_at' => $overrides['updated_at']])->saveQuietly();
            }
        });
    }
}
