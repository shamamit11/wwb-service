<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeBaseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');
    }

    public function test_admin_knowledge_base_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/knowledge-base')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_archive_search_filter_and_link_knowledge_base_entries(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAuthor = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $media = $this->createMedia($admin, 'knowledge-entry.webp');
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category, [
            'title' => 'Grounding AI Agents',
            'slug' => 'grounding-ai-agents',
        ]);

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/knowledge-base', [
            'title' => 'Laravel Queue Retry Patterns',
            'summary' => 'Notes on retries and timeouts.',
            'entry_type' => KnowledgeBaseEntry::TYPE_ARCHITECTURE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'content_markdown' => 'When using queues for AI jobs, idempotency matters.',
            'source_url' => 'https://laravel.com/docs/queues',
            'featured_media_id' => $media->id,
            'metadata' => [
                'tags' => ['laravel', 'queues'],
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Laravel Queue Retry Patterns')
            ->assertJsonPath('data.slug', 'laravel-queue-retry-patterns')
            ->assertJsonPath('data.entry_type', KnowledgeBaseEntry::TYPE_ARCHITECTURE)
            ->assertJsonPath('data.status', KnowledgeBaseEntry::STATUS_ACTIVE)
            ->assertJsonPath('data.featured_media.id', $media->id)
            ->assertJsonPath('data.linked_posts', [])
            ->assertJsonPath('data.linked_topics', []);

        $entryId = (int) $createResponse->json('data.id');

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $otherAuthor->id,
            'updated_by_user_id' => null,
            'title' => 'Topic Ideas',
            'slug' => 'topic-ideas',
            'entry_type' => KnowledgeBaseEntry::TYPE_IDEA,
            'status' => KnowledgeBaseEntry::STATUS_DRAFT,
            'summary' => 'Internal topic list.',
            'content_markdown' => 'Ideas for future editorial angles.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/knowledge-base?status=active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $entryId);

        $this->withToken($token)->getJson('/api/v1/admin/knowledge-base?entry_type=idea')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'topic-ideas');

        $this->withToken($token)->getJson('/api/v1/admin/knowledge-base?search=idempotency')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $entryId);

        $this->withToken($token)->getJson('/api/v1/admin/knowledge-base?created_by_user_id='.$otherAuthor->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'topic-ideas');

        $this->withToken($token)->getJson("/api/v1/admin/knowledge-base/{$entryId}")
            ->assertOk()
            ->assertJsonPath('data.created_by.id', $admin->id);

        $this->withToken($token)->putJson("/api/v1/admin/knowledge-base/{$entryId}", [
            'title' => 'Laravel Queue Timeout Patterns',
            'slug' => 'laravel-queue-timeout-patterns',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ARCHIVED,
            'summary' => 'Archived notes after consolidation.',
            'content_markdown' => 'Updated content.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => [
                'editorial_state' => 'archived',
            ],
        ])->assertOk()
            ->assertJsonPath('data.slug', 'laravel-queue-timeout-patterns')
            ->assertJsonPath('data.entry_type', KnowledgeBaseEntry::TYPE_REFERENCE)
            ->assertJsonPath('data.status', KnowledgeBaseEntry::STATUS_ARCHIVED);

        $this->withToken($token)->postJson("/api/v1/admin/knowledge-base/{$entryId}/link-post", [
            'post_id' => $post->id,
        ])->assertOk()
            ->assertJsonPath('data.linked_posts.0.id', $post->id)
            ->assertJsonPath('data.linked_posts.0.slug', $post->slug);

        $this->withToken($token)->postJson("/api/v1/admin/knowledge-base/{$entryId}/link-topic", [
            'topic_id' => 42,
        ])->assertOk()
            ->assertJsonPath('data.linked_topics.0.id', 42)
            ->assertJsonPath('data.linked_posts.0.id', $post->id);

        $this->withToken($token)->deleteJson("/api/v1/admin/knowledge-base/{$entryId}")
            ->assertNoContent();

        $this->assertSoftDeleted('knowledge_base_entries', [
            'id' => $entryId,
        ]);
    }

    public function test_admin_knowledge_base_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/knowledge-base', [
            'title' => '',
            'entry_type' => 'invalid-type',
            'status' => 'invalid-status',
            'content_markdown' => '',
            'source_url' => 'not-a-url',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['title', 'entry_type', 'status', 'content_markdown', 'source_url'],
                'meta' => ['request_id'],
            ]);
    }

    private function createMedia(User $author, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'generated_by_ai_job_id' => null,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'knowledge-base/'.$filename,
            'original_filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 12345,
            'checksum_sha256' => str_repeat('c', 64),
            'width' => 1200,
            'height' => 630,
            'alt_text' => null,
            'caption' => null,
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'status' => 'ready',
            'metadata' => null,
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
