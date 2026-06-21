<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaUsageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('featured_media_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seo_metadata')) {
            Schema::create('seo_metadata', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('og_image_media_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('knowledge_base_entries')) {
            Schema::create('knowledge_base_entries', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('featured_media_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_media_index_supports_search_and_filters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $image = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/architecture.webp',
            'original_filename' => 'architecture.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'width' => 1600,
            'height' => 900,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/document.pdf',
            'original_filename' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size_bytes' => 100,
            'source_type' => 'stock',
            'status' => 'archived',
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/media?search=architecture&source_type=uploaded&is_image=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $image->id);
    }

    public function test_media_index_can_filter_by_used_flag(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $usedMedia = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/used.webp',
            'original_filename' => 'used.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        $unusedMedia = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/unused.webp',
            'original_filename' => 'unused.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        $this->createFeaturedPost($admin, $usedMedia->id, 'used-post');

        $this->withToken($token)->getJson('/api/v1/admin/media?used=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $usedMedia->id);

        $this->withToken($token)->getJson('/api/v1/admin/media?used=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $unusedMedia->id);
    }

    public function test_media_show_includes_usage_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $media = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/og.webp',
            'original_filename' => 'og.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        DB::table('seo_metadata')->insert([
            'seoable_type' => Post::class,
            'seoable_id' => 999,
            'og_image_media_id' => $media->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)->getJson("/api/v1/admin/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('data.usage_count', 1)
            ->assertJsonPath('data.usage.0.type', 'seo_image');
    }

    public function test_media_show_includes_inline_post_usage_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $media = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/inline.webp',
            'original_filename' => 'inline.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        $postId = $this->createFeaturedPost($admin, null, 'inline-post');
        DB::table('post_media')->insert([
            'post_id' => $postId,
            'media_id' => $media->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($token)->getJson("/api/v1/admin/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('data.usage_count', 1)
            ->assertJsonPath('data.usage.0.type', 'inline_post');
    }

    public function test_delete_is_blocked_when_media_is_in_use(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $media = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/featured.webp',
            'original_filename' => 'featured.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);

        $this->createFeaturedPost($admin, $media->id, 'featured-post');

        $this->withToken($token)->deleteJson("/api/v1/admin/media/{$media->id}")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('meta.usage_count', 1)
            ->assertJsonPath('errors.usage.0.type', 'featured_post');
    }

    private function createFeaturedPost(User $admin, ?int $mediaId, string $slug): int
    {
        $category = Category::query()->first() ?? Category::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'name' => 'Media Fixtures',
            'slug' => 'media-fixtures',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $post = Post::query()->create([
            'author_user_id' => $admin->id,
            'category_id' => $category->id,
            'featured_media_id' => $mediaId,
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'short_description' => null,
            'description' => null,
            'full_article_html' => '<p>Media fixture</p>',
            'full_article_delta' => null,
            'faq' => [],
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'meta' => null,
        ]);

        return (int) $post->id;
    }
}
