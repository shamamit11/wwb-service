<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\UpdatePostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InlinePostMediaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_syncs_inline_media_from_html_and_delta(): void
    {
        $admin = User::factory()->create();
        $category = $this->createCategory($admin);
        $firstMedia = $this->createMedia($admin, 'media/2026/06/first.webp', 'first.webp');
        $secondMedia = $this->createMedia($admin, 'media/2026/06/second.webp', 'second.webp');

        $html = sprintf(
            '<p>Body</p><img src="%s" data-media-id="%d"><img src="%s">',
            $this->mediaUrl($firstMedia),
            $firstMedia->id,
            $this->mediaUrl($secondMedia),
        );

        $post = app(CreatePostService::class)->handle(new CreatePostCommandData(
            authorUserId: $admin->id,
            categoryId: $category->id,
            featuredMediaId: null,
            title: 'Inline media post',
            slug: 'inline-media-post',
            shortDescription: null,
            description: null,
            fullArticleHtml: $html,
            fullArticleDelta: [
                'ops' => [
                    ['insert' => ['image' => $this->mediaUrl($secondMedia)]],
                ],
            ],
            faq: [],
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            meta: null,
            tagIds: [],
        ));

        $this->assertEqualsCanonicalizing(
            [$firstMedia->id, $secondMedia->id],
            $post->inlineMedia()->pluck('media.id')->all(),
        );
    }

    public function test_update_post_resyncs_inline_media_when_article_images_change(): void
    {
        $admin = User::factory()->create();
        $category = $this->createCategory($admin);
        $firstMedia = $this->createMedia($admin, 'media/2026/06/first.webp', 'first.webp');
        $secondMedia = $this->createMedia($admin, 'media/2026/06/second.webp', 'second.webp');

        $post = app(CreatePostService::class)->handle(new CreatePostCommandData(
            authorUserId: $admin->id,
            categoryId: $category->id,
            featuredMediaId: null,
            title: 'Inline media post',
            slug: 'inline-media-post',
            shortDescription: null,
            description: null,
            fullArticleHtml: sprintf('<img src="%s" data-media-id="%d">', $this->mediaUrl($firstMedia), $firstMedia->id),
            fullArticleDelta: null,
            faq: [],
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            meta: null,
            tagIds: [],
        ));

        app(UpdatePostService::class)->handle($post, new UpdatePostCommandData(
            authorUserId: $admin->id,
            categoryId: $category->id,
            featuredMediaId: null,
            title: 'Inline media post',
            slug: 'inline-media-post',
            shortDescription: null,
            description: null,
            fullArticleHtml: sprintf('<img src="%s" data-media-id="%d">', $this->mediaUrl($secondMedia), $secondMedia->id),
            fullArticleDelta: null,
            faq: [],
            status: Post::STATUS_DRAFT,
            visibility: Post::VISIBILITY_PUBLIC,
            publishedAt: null,
            meta: null,
            tagIds: [],
        ));

        $post->refresh();

        $this->assertSame([$secondMedia->id], $post->inlineMedia()->pluck('media.id')->all());
    }

    private function createCategory(User $author): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => 'Inline Media',
            'slug' => 'inline-media',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function createMedia(User $author, string $objectKey, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => $objectKey,
            'original_filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 100,
            'width' => 1200,
            'height' => 800,
            'source_type' => 'uploaded',
            'status' => 'ready',
        ]);
    }

    private function mediaUrl(Media $media): string
    {
        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.$media->object_key;
    }
}
