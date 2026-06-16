<?php

namespace Tests\Feature;

use App\Enums\ContentBlockType;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Template;
use App\Models\TemplateBlock;
use App\Models\User;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Exceptions\InvalidPostBlockPayloadException;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\DeletePostService;
use App\Modules\Posts\Services\UpdatePostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_service_persists_post_tags_and_normalized_blocks(): void
    {
        $service = app(CreatePostService::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $template = $this->createTemplate($author, 'Tutorial', 'tutorial');
        $templateBlock = $this->createTemplateBlock($template);
        $featuredMedia = $this->createMedia($author, 'featured.jpg');
        $tagOne = $this->createTag('Memory', 'memory');
        $tagTwo = $this->createTag('Architecture', 'architecture');

        $post = $service->handle(new CreatePostCommandData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: $template->id,
            featuredMediaId: $featuredMedia->id,
            title: 'How AI Agent Memory Works',
            slug: '',
            excerpt: 'A practical look at memory.',
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: 8,
            wordCount: 1200,
            isFeatured: true,
            meta: ['hero_style' => 'tutorial'],
            tagIds: [$tagOne->id, $tagTwo->id, $tagTwo->id],
            blocks: [
                new PostBlockPayloadData(
                    blockType: ContentBlockType::LIST->value,
                    sortOrder: 2,
                    content: [
                        'items' => ['First point', 'Second point'],
                    ],
                ),
                new PostBlockPayloadData(
                    blockType: ContentBlockType::HEADING->value,
                    sortOrder: 1,
                    content: [
                        'text' => 'How AI Agent Memory Works',
                        'level' => 1,
                    ],
                    sourceTemplateBlockId: $templateBlock->id,
                ),
            ],
        ));

        $this->assertSame('how-ai-agent-memory-works', $post->slug);
        $this->assertSame([$tagOne->id, $tagTwo->id], $post->tags->sortBy('id')->modelKeys());
        $this->assertCount(2, $post->blocks);
        $this->assertSame(
            [ContentBlockType::HEADING->value, ContentBlockType::LIST->value],
            $post->blocks->pluck('block_type')->all(),
        );
        $this->assertSame('# How AI Agent Memory Works', $post->blocks[0]->content_markdown);
        $this->assertSame(['level' => 1], $post->blocks[0]->settings);
        $this->assertSame($templateBlock->id, $post->blocks[0]->sourceTemplateBlock?->id);
        $this->assertSame("- First point\n- Second point", $post->blocks[1]->content_markdown);
        $this->assertSame(['items' => ['First point', 'Second point']], $post->blocks[1]->settings);
    }

    public function test_update_service_replaces_blocks_and_generates_unique_slug(): void
    {
        $createService = app(CreatePostService::class);
        $updateService = app(UpdatePostService::class);
        $author = User::factory()->create(['is_admin' => true]);
        $replacementAuthor = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $updatedCategory = $this->createCategory($author, 'SEO', 'seo');
        $tag = $this->createTag('Memory', 'memory');
        $replacementTag = $this->createTag('SEO', 'seo');

        Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
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
        ]);

        $post = $createService->handle(new CreatePostCommandData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'How AI Agent Memory Works',
            slug: '',
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
            blocks: [
                new PostBlockPayloadData(
                    blockType: ContentBlockType::PARAGRAPH->value,
                    sortOrder: 1,
                    content: [
                        'markdown' => 'Initial content',
                    ],
                ),
            ],
        ));

        $updated = $updateService->handle($post, new UpdatePostCommandData(
            authorUserId: $replacementAuthor->id,
            categoryId: $updatedCategory->id,
            templateId: null,
            featuredMediaId: null,
            title: 'How AI Agent Memory Works',
            slug: '',
            excerpt: 'Updated excerpt',
            status: Post::STATUS_SCHEDULED,
            visibility: Post::VISIBILITY_INTERNAL,
            publishedAt: null,
            scheduledFor: '2026-06-18 09:00:00',
            contentVersion: 2,
            readingTimeMinutes: 10,
            wordCount: 1400,
            isFeatured: true,
            meta: ['workflow' => 'scheduled'],
            tagIds: [$replacementTag->id],
            blocks: [
                new PostBlockPayloadData(
                    blockType: ContentBlockType::FAQ->value,
                    sortOrder: 1,
                    content: [
                        'items' => [[
                            'question' => 'What is agent memory?',
                            'answer_markdown' => 'It is stored context for the system.',
                        ]],
                    ],
                ),
                new PostBlockPayloadData(
                    blockType: ContentBlockType::CODE->value,
                    sortOrder: 2,
                    content: [
                        'language' => 'php',
                        'code' => "<?php\nreturn 'ok';\n",
                    ],
                ),
            ],
        ));

        $this->assertSame('how-ai-agent-memory-works-2', $updated->slug);
        $this->assertSame($replacementAuthor->id, $updated->author?->id);
        $this->assertSame($updatedCategory->id, $updated->category?->id);
        $this->assertSame([$replacementTag->id], $updated->tags->modelKeys());
        $this->assertSame(
            [ContentBlockType::FAQ->value, ContentBlockType::CODE->value],
            $updated->blocks->pluck('block_type')->all(),
        );
        $this->assertStringContainsString('## What is agent memory?', (string) $updated->blocks[0]->content_markdown);
        $this->assertSame(['language' => 'php'], $updated->blocks[1]->settings);
        $this->assertDatabaseMissing('post_blocks', [
            'post_id' => $updated->id,
            'content_markdown' => 'Initial content',
        ]);
    }

    public function test_delete_service_soft_deletes_post_and_removes_blocks_and_tags(): void
    {
        $createService = app(CreatePostService::class);
        $deleteService = app(DeletePostService::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $tag = $this->createTag('Memory', 'memory');

        $post = $createService->handle(new CreatePostCommandData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Draft Post',
            slug: '',
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
            blocks: [
                new PostBlockPayloadData(
                    blockType: ContentBlockType::PARAGRAPH->value,
                    sortOrder: 1,
                    content: [
                        'markdown' => 'Draft content',
                    ],
                ),
            ],
        ));

        $blockIds = $post->blocks->modelKeys();

        $deleteService->handle($post);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_tags', ['post_id' => $post->id, 'tag_id' => $tag->id]);
        $this->assertDatabaseMissing('post_blocks', ['id' => $blockIds[0]]);
    }

    public function test_services_reject_invalid_block_payloads(): void
    {
        $service = app(CreatePostService::class);
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');

        $this->expectException(InvalidPostBlockPayloadException::class);
        $this->expectExceptionMessage('Duplicate block sort_order');

        $service->handle(new CreatePostCommandData(
            authorUserId: $author->id,
            categoryId: $category->id,
            templateId: null,
            featuredMediaId: null,
            title: 'Invalid Post',
            slug: '',
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
            blocks: [
                new PostBlockPayloadData(
                    blockType: ContentBlockType::HEADING->value,
                    sortOrder: 1,
                    content: ['text' => 'One', 'level' => 1],
                ),
                new PostBlockPayloadData(
                    blockType: ContentBlockType::PARAGRAPH->value,
                    sortOrder: 1,
                    content: ['markdown' => 'Duplicate sort order'],
                ),
            ],
        ));
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

    private function createTemplateBlock(Template $template): TemplateBlock
    {
        return TemplateBlock::query()->create([
            'template_id' => $template->id,
            'block_type' => ContentBlockType::HEADING->value,
            'sort_order' => 1,
            'label' => 'Title',
            'default_markdown' => '# {{title}}',
            'settings' => ['level' => 1],
            'is_required' => true,
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
