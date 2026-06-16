<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Modules\Seo\Data\CreateSeoMetadataData;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Repositories\SeoMetadataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeoMetadataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_metadata_table_matches_the_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('seo_metadata'));
        $this->assertTrue(Schema::hasColumns('seo_metadata', [
            'id',
            'seoable_type',
            'seoable_id',
            'meta_title',
            'meta_description',
            'canonical_url',
            'robots_index',
            'robots_follow',
            'og_title',
            'og_description',
            'og_image_media_id',
            'schema_type',
            'schema_payload',
            'focus_keyword',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_repository_stores_seo_metadata_for_posts_and_categories_via_polymorphic_one_to_one(): void
    {
        $repository = app(SeoMetadataRepository::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $post = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
        ]);
        $ogImage = $this->createMedia($author, 'og.webp');

        $postSeo = $repository->createFor($post, new CreateSeoMetadataData(
            metaTitle: 'How AI Agent Memory Works',
            metaDescription: 'A practical explanation of memory design for AI agents.',
            canonicalUrl: 'https://widewebblog.test/how-ai-agent-memory-works',
            robotsIndex: true,
            robotsFollow: true,
            ogTitle: 'AI Agent Memory',
            ogDescription: 'Practical agent memory design.',
            ogImageMediaId: $ogImage->id,
            schemaType: 'Article',
            schemaPayload: ['@type' => 'Article'],
            focusKeyword: 'ai agent memory',
        ));

        $categorySeo = $repository->createFor($category, new CreateSeoMetadataData(
            metaTitle: 'AI Agents Category',
            metaDescription: 'Technical content about AI agents.',
            canonicalUrl: 'https://widewebblog.test/categories/ai-agents',
            robotsIndex: true,
            robotsFollow: true,
            ogTitle: 'AI Agents',
            ogDescription: 'Category hub for AI agents.',
            ogImageMediaId: null,
            schemaType: 'CollectionPage',
            schemaPayload: ['@type' => 'CollectionPage'],
            focusKeyword: 'ai agents',
        ));

        $this->assertSame($postSeo->id, $repository->findFor($post)?->id);
        $this->assertSame($categorySeo->id, $repository->findFor($category)?->id);
        $this->assertSame($postSeo->id, $post->fresh()->seo?->id);
        $this->assertSame($categorySeo->id, $category->fresh()->seo?->id);
        $this->assertSame($ogImage->id, $postSeo->ogImageMedia?->id);
        $this->assertSame(Post::class, $postSeo->seoable_type);
        $this->assertSame(Category::class, $categorySeo->seoable_type);

        $updatedPostSeo = $repository->updateFor($post, new UpdateSeoMetadataData(
            metaTitle: 'Updated Memory Guide',
            metaDescription: 'Updated description.',
            canonicalUrl: 'https://widewebblog.test/how-ai-agent-memory-works-updated',
            robotsIndex: false,
            robotsFollow: true,
            ogTitle: 'Updated AI Agent Memory',
            ogDescription: 'Updated OG copy.',
            ogImageMediaId: null,
            schemaType: 'TechArticle',
            schemaPayload: ['@type' => 'TechArticle'],
            focusKeyword: 'agent memory guide',
        ));

        $this->assertSame($postSeo->id, $updatedPostSeo->id);
        $this->assertFalse($updatedPostSeo->robots_index);
        $this->assertNull($updatedPostSeo->ogImageMedia);
        $this->assertDatabaseHas('seo_metadata', [
            'id' => $updatedPostSeo->id,
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'meta_title' => 'Updated Memory Guide',
            'schema_type' => 'TechArticle',
            'focus_keyword' => 'agent memory guide',
        ]);

        $repository->deleteFor($category);

        $this->assertNull($repository->findFor($category));
        $this->assertDatabaseMissing('seo_metadata', [
            'id' => $categorySeo->id,
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
            'checksum_sha256' => str_repeat('d', 64),
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
