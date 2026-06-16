<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoMetadataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');
    }

    public function test_admin_seo_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/seo/post/1')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_read_and_write_seo_metadata_for_posts_and_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
        ]);
        $media = $this->createMedia($admin, 'seo-og.webp');

        $this->withToken($token)->getJson("/api/v1/admin/seo/post/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.id', null)
            ->assertJsonPath('data.seoable_type', 'post')
            ->assertJsonPath('data.seoable_id', $post->id)
            ->assertJsonPath('data.canonical_url', null)
            ->assertJsonPath('data.robots_index', true)
            ->assertJsonPath('data.robots_follow', true)
            ->assertJsonPath('data.schema_payload', []);

        $this->withToken($token)->putJson("/api/v1/admin/seo/post/{$post->id}", [
            'meta_title' => 'How AI Agent Memory Works',
            'meta_description' => 'A practical explanation of memory design for AI agents.',
            'canonical_url' => 'https://widewebblog.test/how-ai-agent-memory-works',
            'robots_index' => true,
            'robots_follow' => false,
            'og_title' => 'AI Agent Memory',
            'og_description' => 'Practical AI agent memory guide.',
            'og_image_media_id' => $media->id,
            'schema_type' => 'Article',
            'schema_payload' => ['@type' => 'Article'],
            'focus_keyword' => 'ai agent memory',
        ])->assertOk()
            ->assertJsonPath('data.seoable_type', 'post')
            ->assertJsonPath('data.seoable_id', $post->id)
            ->assertJsonPath('data.canonical_url', 'https://widewebblog.test/how-ai-agent-memory-works')
            ->assertJsonPath('data.robots_follow', false)
            ->assertJsonPath('data.og_image_media.id', $media->id)
            ->assertJsonPath('data.schema_type', 'Article')
            ->assertJsonPath('data.schema_payload.@type', 'Article');

        $this->withToken($token)->getJson("/api/v1/admin/seo/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.meta_title', 'How AI Agent Memory Works')
            ->assertJsonPath('data.focus_keyword', 'ai agent memory');

        $this->withToken($token)->putJson("/api/v1/admin/seo/category/{$category->id}", [
            'meta_title' => 'AI Agents Category',
            'meta_description' => 'Technical content about AI agents.',
            'canonical_url' => 'https://widewebblog.test/categories/ai-agents',
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'AI Agents',
            'og_description' => 'Category hub for AI agent content.',
            'schema_type' => 'CollectionPage',
        ])->assertOk()
            ->assertJsonPath('data.seoable_type', 'category')
            ->assertJsonPath('data.seoable_id', $category->id)
            ->assertJsonPath('data.schema_type', 'CollectionPage');

        $this->withToken($token)->getJson("/api/v1/admin/seo/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.meta_title', 'AI Agents Category')
            ->assertJsonPath('data.robots_index', true);
    }

    public function test_admin_seo_read_derives_default_canonical_when_override_is_missing(): void
    {
        config()->set('app.url', 'https://widewebblog.test');

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');

        $this->withToken($token)->getJson("/api/v1/admin/seo/category/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.canonical_url', 'https://widewebblog.test/categories/ai-agents/');
    }

    public function test_admin_seo_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');

        $this->withToken($token)->putJson("/api/v1/admin/seo/category/{$category->id}", [
            'meta_title' => str_repeat('a', 256),
            'meta_description' => str_repeat('b', 321),
            'canonical_url' => 'not-a-url',
            'og_title' => str_repeat('c', 256),
            'og_description' => str_repeat('d', 321),
            'og_image_media_id' => 999999,
            'schema_type' => str_repeat('e', 121),
            'focus_keyword' => str_repeat('f', 191),
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'meta_title',
                    'meta_description',
                    'canonical_url',
                    'og_title',
                    'og_description',
                    'og_image_media_id',
                    'schema_type',
                    'focus_keyword',
                ],
                'meta' => ['request_id'],
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

    private function createMedia(User $author, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'generated_by_ai_job_id' => null,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'seo/'.$filename,
            'original_filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 1024,
            'checksum_sha256' => str_repeat('e', 64),
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
}
