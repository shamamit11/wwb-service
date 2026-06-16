<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Data\UpdateCategoryData;
use App\Modules\Categories\Services\CreateCategoryService;
use App\Modules\Categories\Services\DeleteCategoryService;
use App\Modules\Categories\Services\UpdateCategoryService;
use App\Modules\Media\Data\UpdateMediaMetadataData;
use App\Modules\Media\Data\UploadMediaData;
use App\Modules\Media\Services\DeleteMediaService;
use App\Modules\Media\Services\UpdateMediaMetadataService;
use App\Modules\Media\Services\UploadMediaService;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Data\SchedulePostData;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\PublishPostService;
use App\Modules\Posts\Services\SchedulePostService;
use App\Modules\Posts\Services\UnpublishPostService;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Services\UpsertSeoMetadataService;
use App\Modules\Templates\Data\CreateTemplateBlockData;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Services\CreateTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('filesystems.disks.r2.bucket', 'wwb-media');
    }

    public function test_user_model_changes_are_recorded_in_activity_log(): void
    {
        $user = User::factory()->create([
            'name' => 'Initial Author',
            'email' => 'author@example.com',
        ]);

        $user->update([
            'name' => 'Updated Author',
        ]);

        $activities = Activity::query()->orderBy('id')->get();

        $this->assertCount(2, $activities);

        $this->assertSame('auth', $activities[0]->log_name);
        $this->assertSame('user.created', $activities[0]->description);
        $this->assertSame('created', $activities[0]->event);
        $this->assertSame('Initial Author', $activities[0]->attribute_changes['attributes']['name']);

        $this->assertSame('auth', $activities[1]->log_name);
        $this->assertSame('user.updated', $activities[1]->description);
        $this->assertSame('updated', $activities[1]->event);
        $this->assertSame('Updated Author', $activities[1]->attribute_changes['attributes']['name']);
        $this->assertSame('Initial Author', $activities[1]->attribute_changes['old']['name']);
    }

    public function test_editorial_mutations_are_recorded_with_curated_audit_payloads(): void
    {
        $admin = User::factory()->create([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'is_admin' => true,
        ]);
        $this->actingAs($admin);
        app('auth')->shouldUse('sanctum');
        request()->setUserResolver(fn () => $admin);
        $this->app->instance('request', Request::create('/'));
        $this->app['request']->setUserResolver(fn () => $admin);

        $createCategory = app(CreateCategoryService::class);
        $updateCategory = app(UpdateCategoryService::class);
        $deleteCategory = app(DeleteCategoryService::class);
        $createPost = app(CreatePostService::class);
        $publishPost = app(PublishPostService::class);
        $schedulePost = app(SchedulePostService::class);
        $unpublishPost = app(UnpublishPostService::class);
        $uploadMedia = app(UploadMediaService::class);
        $updateMedia = app(UpdateMediaMetadataService::class);
        $deleteMedia = app(DeleteMediaService::class);
        $createTemplate = app(CreateTemplateService::class);
        $upsertSeo = app(UpsertSeoMetadataService::class);

        $category = $createCategory->handle(new CreateCategoryData(
            parentId: null,
            createdByUserId: $admin->id,
            updatedByUserId: null,
            name: 'AI Agents',
            slug: '',
            description: 'Technical content about agents.',
            isActive: true,
            sortOrder: 1,
        ));

        $updateCategory->handle($category, new UpdateCategoryData(
            parentId: null,
            updatedByUserId: $admin->id,
            name: 'AI Systems',
            slug: 'ai-systems',
            description: 'Updated description.',
            isActive: false,
            sortOrder: 2,
        ));

        $template = $createTemplate->handle(new CreateTemplateData(
            createdByUserId: $admin->id,
            updatedByUserId: null,
            name: 'Tutorial',
            slug: '',
            templateType: 'tutorial',
            description: 'Step-by-step layout',
            status: 'active',
            defaultExcerptPrompt: null,
            defaultMeta: null,
            blocks: [
                new CreateTemplateBlockData(
                    blockType: 'heading',
                    sortOrder: 1,
                    label: 'Title',
                    defaultMarkdown: '# {{title}}',
                    settings: ['level' => 1],
                    isRequired: true,
                ),
            ],
        ));

        $media = $uploadMedia->upload(new UploadMediaData(
            uploadedByUserId: $admin->id,
            originalFilename: 'diagram.webp',
            mimeType: 'image/webp',
            contents: 'image-bytes',
            extension: 'webp',
            fileSizeBytes: 11,
            width: 1200,
            height: 630,
            altText: null,
            caption: null,
            sourceType: 'uploaded',
            sourceUrl: null,
            attributionText: null,
            metadata: null,
        ));

        $updateMedia->handle($media, new UpdateMediaMetadataData(
            altText: 'Architecture diagram',
            caption: 'Queue flow',
            sourceType: 'uploaded',
            sourceUrl: 'https://example.com/diagram',
            attributionText: 'Internal',
            metadata: ['reviewed' => true],
        ));

        $post = $createPost->handle(new CreatePostCommandData(
            authorUserId: $admin->id,
            categoryId: $category->id,
            templateId: $template->id,
            featuredMediaId: $media->id,
            title: 'How AI Agent Memory Works',
            slug: '',
            excerpt: 'Practical memory design.',
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: 1,
            readingTimeMinutes: 8,
            wordCount: 1200,
            isFeatured: true,
            meta: ['seo' => ['title' => 'AI Agent Memory']],
            tagIds: [],
            blocks: [
                new PostBlockPayloadData(
                    blockType: 'paragraph',
                    sortOrder: 1,
                    content: ['markdown' => 'Body copy'],
                ),
            ],
        ));

        $schedulePost->handle($post, new SchedulePostData('2026-06-20 09:00:00'));
        $published = $publishPost->handle($post->fresh());
        $unpublishPost->handle($published);

        $upsertSeo->handle('post', $post->id, new UpdateSeoMetadataData(
            metaTitle: 'How AI Agent Memory Works',
            metaDescription: 'A practical explanation of memory design.',
            canonicalUrl: 'https://widewebblog.test/how-ai-agent-memory-works',
            robotsIndex: true,
            robotsFollow: false,
            ogTitle: 'AI Agent Memory',
            ogDescription: 'Practical guide.',
            ogImageMediaId: $media->id,
            schemaType: 'Article',
            schemaPayload: ['@type' => 'Article'],
            focusKeyword: 'ai agent memory',
        ));

        $deleteMedia->delete($media->fresh());
        $deleteCategory->handle($category->fresh());

        $activities = Activity::query()
            ->where('log_name', 'content')
            ->orderBy('id')
            ->get();

        $descriptions = $activities->pluck('description')->all();

        $this->assertContains('category.created', $descriptions);
        $this->assertContains('category.updated', $descriptions);
        $this->assertContains('template.created', $descriptions);
        $this->assertContains('media.created', $descriptions);
        $this->assertContains('media.updated', $descriptions);
        $this->assertContains('post.created', $descriptions);
        $this->assertContains('post.scheduled', $descriptions);
        $this->assertContains('post.published', $descriptions);
        $this->assertContains('post.unpublished', $descriptions);
        $this->assertContains('seo_metadata.created', $descriptions);
        $this->assertContains('media.deleted', $descriptions);
        $this->assertContains('category.deleted', $descriptions);

        $postCreated = $activities->firstWhere('description', 'post.created');
        $this->assertSame($admin->id, $postCreated?->causer_id);
        $this->assertSame('How AI Agent Memory Works', $postCreated?->properties['attributes']['title']);
        $this->assertSame(1, $postCreated?->properties['context']['block_count']);

        $postScheduled = $activities->firstWhere('description', 'post.scheduled');
        $this->assertSame(Post::STATUS_DRAFT, $postScheduled?->properties['old']['status']);
        $this->assertSame(Post::STATUS_SCHEDULED, $postScheduled?->properties['attributes']['status']);

        $seoCreated = $activities->firstWhere('description', 'seo_metadata.created');
        $this->assertSame('https://widewebblog.test/how-ai-agent-memory-works', $seoCreated?->properties['attributes']['canonical_url']);
        $this->assertSame(Post::class, $seoCreated?->properties['context']['seoable_type']);

        $mediaDeleted = $activities->firstWhere('description', 'media.deleted');
        $this->assertSame('ready', $mediaDeleted?->properties['old']['status']);
        $this->assertSame('archived', $mediaDeleted?->properties['attributes']['status']);
    }
}
