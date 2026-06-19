<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\AiJob;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Data\PostFiltersData;
use App\Modules\Posts\Data\UpdatePostData;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_table_matches_the_documented_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasColumns('posts', [
            'id',
            'ulid',
            'author_user_id',
            'category_id',
            'template_id',
            'featured_media_id',
            'title',
            'slug',
            'excerpt',
            'status',
            'visibility',
            'published_at',
            'scheduled_for',
            'content_version',
            'reading_time_minutes',
            'word_count',
            'is_featured',
            'meta',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_repository_supports_admin_reads_writes_and_relationships(): void
    {
        $repository = app(PostRepository::class);
        $author = User::factory()->create(['is_admin' => true]);
        $replacementAuthor = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $updatedCategory = $this->createCategory($author, 'Architecture', 'architecture');
        $template = $this->createTemplate($author, 'Tutorial', 'tutorial');
        $updatedTemplate = $this->createTemplate($author, 'Comparison', 'comparison');
        $featuredMedia = $this->createMedia($author, 'featured.jpg');
        $updatedMedia = $this->createMedia($author, 'updated.jpg');
        $firstTag = $this->createTag('Memory', 'memory');
        $secondTag = $this->createTag('Architecture', 'architecture');
        $thirdTag = $this->createTag('Performance', 'performance');

        $post = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: $template->id,
            featuredMediaId: $featuredMedia->id,
            title: 'How AI Agent Memory Works',
            slug: 'how-ai-agent-memory-works',
            excerpt: 'A practical look at agent memory patterns.',
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: 8,
            wordCount: 1240,
            isFeatured: true,
            meta: ['hero_style' => 'standard'],
            tagIds: [$firstTag->id, $secondTag->id],
        ));

        $updated = $repository->update($post, new UpdatePostData(
            authorUserId: $replacementAuthor->id,
            categoryId: $updatedCategory->id,
            templateId: $updatedTemplate->id,
            featuredMediaId: $updatedMedia->id,
            title: 'How Distributed Agent Memory Works',
            slug: 'how-distributed-agent-memory-works',
            excerpt: 'Updated excerpt.',
            status: Post::STATUS_SCHEDULED,
            visibility: Post::VISIBILITY_INTERNAL,
            publishedAt: null,
            scheduledFor: '2026-06-20 10:00:00',
            contentVersion: 2,
            readingTimeMinutes: 10,
            wordCount: 1600,
            isFeatured: false,
            meta: ['hero_style' => 'comparison'],
            tagIds: [$thirdTag->id],
        ));

        $this->assertSame($updated->id, $repository->findById($updated->id)?->id);
        $this->assertSame($updated->id, $repository->findBySlug('how-distributed-agent-memory-works')?->id);
        $this->assertTrue($repository->existsBySlug('how-distributed-agent-memory-works'));
        $this->assertFalse($repository->existsBySlug('missing-post'));
        $this->assertSame([$updated->id], $repository->getAdminOrdered()->modelKeys());

        $this->assertSame($replacementAuthor->id, $updated->author?->id);
        $this->assertSame($updatedCategory->id, $updated->category?->id);
        $this->assertSame($updatedTemplate->id, $updated->template?->id);
        $this->assertSame($updatedMedia->id, $updated->featuredMedia?->id);
        $this->assertSame([$thirdTag->id], $updated->tags->modelKeys());
        $this->assertSame(26, strlen($updated->ulid));
        $this->assertDatabaseHas('posts', [
            'id' => $updated->id,
            'author_user_id' => $replacementAuthor->id,
            'category_id' => $updatedCategory->id,
            'template_id' => $updatedTemplate->id,
            'featured_media_id' => $updatedMedia->id,
            'slug' => 'how-distributed-agent-memory-works',
            'status' => Post::STATUS_SCHEDULED,
            'visibility' => Post::VISIBILITY_INTERNAL,
            'content_version' => 2,
            'is_featured' => false,
        ]);
    }

    public function test_repository_supports_public_safe_published_queries(): void
    {
        $repository = app(PostRepository::class);
        $author = User::factory()->create(['is_admin' => true]);
        $aiCategory = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $seoCategory = $this->createCategory($author, 'SEO', 'seo');
        $tag = $this->createTag('Agents', 'agents');

        $olderPublished = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $aiCategory->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Older Published Post',
            slug: 'older-published-post',
            excerpt: null,
            status: Post::STATUS_PUBLISHED,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: '2026-06-01 08:00:00',
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            meta: null,
            tagIds: [$tag->id],
        ));

        $featuredPublished = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $aiCategory->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Featured Published Post',
            slug: 'featured-published-post',
            excerpt: null,
            status: Post::STATUS_PUBLISHED,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: '2026-06-10 09:30:00',
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: true,
            meta: null,
            tagIds: [$tag->id],
        ));

        $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $seoCategory->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Draft Post',
            slug: 'draft-post',
            excerpt: null,
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            meta: null,
            tagIds: [],
        ));

        $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $aiCategory->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Private Published Post',
            slug: 'private-published-post',
            excerpt: null,
            status: Post::STATUS_PUBLISHED,
            visibility: Post::VISIBILITY_PRIVATE,
            publishedAt: '2026-06-11 11:00:00',
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: true,
            meta: null,
            tagIds: [],
        ));

        $this->assertSame(
            [$featuredPublished->id, $olderPublished->id],
            $repository->getPublishedOrdered()->modelKeys(),
        );
        $this->assertSame(
            [$featuredPublished->id, $olderPublished->id],
            $repository->getPublishedOrdered('ai-agents')->modelKeys(),
        );
        $this->assertSame(
            [$featuredPublished->id],
            $repository->getPublishedOrdered('ai-agents', true)->modelKeys(),
        );
        $this->assertNull($repository->findPublishedBySlug('draft-post'));
        $this->assertNull($repository->findPublishedBySlug('private-published-post'));
        $this->assertSame(
            $featuredPublished->id,
            $repository->findPublishedBySlug('featured-published-post')?->id,
        );
    }

    public function test_repository_supports_ai_provenance_filters_for_admin_review(): void
    {
        $repository = app(PostRepository::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $aiJob = AiJob::query()->create([
            'type' => 'blog_writer',
            'status' => 'completed',
            'entity_type' => 'content_brief',
            'entity_id' => 25,
            'input_payload' => [],
            'output_payload' => [],
            'usage_payload' => [],
            'attempts' => 1,
        ]);

        $manual = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Manual Draft',
            slug: 'manual-draft',
            excerpt: null,
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_INTERNAL,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            meta: null,
            tagIds: [],
        ));

        $ai = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'AI Draft',
            slug: 'ai-draft',
            excerpt: null,
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_INTERNAL,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            meta: [
                'source_content_brief_id' => 11,
                'source_content_topic_id' => 22,
                'ai_job_id' => $aiJob->id,
                'generated_by' => 'BlogWriterAgent',
            ],
            tagIds: [],
        ));

        $this->assertSame([$ai->id], $repository->searchAdmin(new PostFiltersData(
            isAiGenerated: true,
        ))->modelKeys());

        $this->assertSame([$manual->id], $repository->searchAdmin(new PostFiltersData(
            isAiGenerated: false,
        ))->modelKeys());

        $this->assertSame([$ai->id], $repository->searchAdmin(new PostFiltersData(
            sourceContentBriefId: 11,
        ))->modelKeys());

        $this->assertSame([$ai->id], $repository->searchAdmin(new PostFiltersData(
            sourceContentTopicId: 22,
        ))->modelKeys());

        $this->assertSame([$ai->id], $repository->searchAdmin(new PostFiltersData(
            generatedByAiJobId: (int) $aiJob->id,
        ))->modelKeys());
    }

    public function test_repository_delete_soft_deletes_post_and_detaches_tags(): void
    {
        $repository = app(PostRepository::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $tag = $this->createTag('Agents', 'agents');

        $post = $repository->create(new CreatePostData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Deletable Post',
            slug: 'deletable-post',
            excerpt: null,
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            meta: null,
            tagIds: [$tag->id],
        ));

        $repository->delete($post);

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);
        $this->assertDatabaseMissing('post_tags', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
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

    private function createTemplate(User $author, string $name, string $slug): Template
    {
        return Template::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'template_type' => $slug,
            'description' => null,
            'status' => Template::STATUS_ACTIVE,
            'default_excerpt_prompt' => null,
            'default_meta' => null,
        ]);
    }

    private function createMedia(User $author, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'generated_by_ai_job_id' => null,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'posts/'.$filename,
            'original_filename' => $filename,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size_bytes' => 12345,
            'checksum_sha256' => str_repeat('a', 64),
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
