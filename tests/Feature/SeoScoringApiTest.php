<?php

namespace Tests\Feature;

use App\Enums\ContentBlockType;
use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Seo\Services\ScorePostSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoScoringApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://widewebblog.test');
        config()->set('app.name', 'Wide Web Blog');
    }

    public function test_admin_seo_score_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/seo/score/post/1')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_post_can_receive_structured_seo_score_with_subscores(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Editor One']);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $memoryTag = $this->createTag('Memory', 'memory');

        $post = $this->createPost($admin, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'excerpt' => 'A practical explanation of agent memory systems and retrieval.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
            'updated_at' => '2026-06-13 12:15:00',
            'word_count' => 1200,
        ], [$memoryTag]);

        $post->blocks()->create([
            'block_type' => ContentBlockType::HEADING->value,
            'sort_order' => 1,
            'content_markdown' => '# How AI Agent Memory Works',
            'content_html_cache' => null,
            'plain_text_cache' => 'How AI Agent Memory Works',
            'settings' => ['level' => 1],
            'source_template_block_id' => null,
        ]);

        $post->blocks()->create([
            'block_type' => ContentBlockType::FAQ->value,
            'sort_order' => 2,
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
            'canonical_url' => 'https://widewebblog.test/how-ai-agent-memory-works/',
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'AI Agent Memory',
            'og_description' => 'Open Graph summary for AI agent memory.',
            'schema_type' => 'TechArticle',
            'schema_payload' => ['about' => ['AI agents']],
            'focus_keyword' => 'ai agent memory',
        ]);

        $relatedPost = $this->createPost($admin, $category, [
            'title' => 'AI Agent Memory Patterns',
            'slug' => 'ai-agent-memory-patterns',
            'excerpt' => 'Related published article.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
        ], [$memoryTag]);
        $relatedPost->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'agent memory patterns',
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'title' => 'Agent Memory Research Notes',
            'slug' => 'agent-memory-research-notes',
            'entry_type' => KnowledgeBaseEntry::TYPE_RESEARCH,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Research notes about memory.',
            'content_markdown' => 'AI agents need memory retrieval strategies.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => [
                'tags' => ['memory'],
                KnowledgeBaseEntry::LINK_HOOKS_KEY => [
                    'posts' => [[
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                    ]],
                ],
            ],
        ]);

        $payload = app(ScorePostSeoService::class)->handle('post', $post->id);

        $this->assertSame('post', $payload['seoable_type']);
        $this->assertSame($post->id, $payload['seoable_id']);
        $this->assertTrue($payload['advisory']);
        $this->assertSame(100, $payload['max_score']);
        $this->assertArrayHasKey('metadata', $payload['subscores']);
        $this->assertArrayHasKey('content', $payload['subscores']);
        $this->assertArrayHasKey('schema', $payload['subscores']);
        $this->assertArrayHasKey('internal_linking', $payload['subscores']);
        $this->assertGreaterThan(0, $payload['subscores']['metadata']['score']);
        $this->assertGreaterThan(0, $payload['subscores']['content']['score']);
        $this->assertGreaterThan(0, $payload['subscores']['schema']['score']);
        $this->assertGreaterThan(0, $payload['subscores']['internal_linking']['score']);
        $this->assertContains('good', ['excellent', 'good', 'needs_work', 'poor']); // sanity on expected set

        $this->withToken($token)->getJson("/api/v1/admin/seo/score/post/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.seoable_type', 'post')
            ->assertJsonPath('data.seoable_id', $post->id)
            ->assertJsonPath('data.advisory', true)
            ->assertJsonPath('data.max_score', 100)
            ->assertJsonPath('data.grade', $payload['grade'])
            ->assertJsonPath('data.total_score', $payload['total_score'])
            ->assertJsonPath('data.subscores.metadata.max_score', 35)
            ->assertJsonPath('data.subscores.content.max_score', 30)
            ->assertJsonPath('data.subscores.schema.max_score', 20)
            ->assertJsonPath('data.subscores.internal_linking.max_score', 15)
            ->assertJsonPath('data.subscores.internal_linking.suggestion_count', 2);
    }

    public function test_sparse_post_receives_low_score_and_recommendations(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category, [
            'title' => 'Short Draft',
            'slug' => 'short-draft',
            'excerpt' => null,
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'word_count' => 120,
        ]);

        $payload = app(ScorePostSeoService::class)->handle('post', $post->id);

        $this->assertSame('poor', $payload['grade']);
        $this->assertNotEmpty($payload['recommendations']);
        $this->assertSame(0, $payload['subscores']['internal_linking']['suggestion_count']);
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
     * @param  list<Tag>  $tags
     */
    private function createPost(User $author, Category $category, array $overrides = [], array $tags = []): Post
    {
        $post = tap(Post::query()->create(array_merge([
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

        $post->tags()->sync(array_map(static fn (Tag $tag): int => $tag->id, $tags));

        return $post->load(['tags', 'category', 'seo']);
    }

    private function createTag(string $name, string $slug): Tag
    {
        return Tag::query()->create([
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'is_active' => true,
        ]);
    }
}
