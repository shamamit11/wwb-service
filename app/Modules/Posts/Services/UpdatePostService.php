<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Data\UpdatePostData;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class UpdatePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
        private readonly SyncPostInlineMediaService $syncInlineMedia,
    ) {}

    public function handle(Post $post, UpdatePostCommandData $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $old = [
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'visibility' => $post->visibility,
                'tag_ids' => $post->tags()->pluck('tags.id')->all(),
            ];

            $updated = $this->posts->update($post, new UpdatePostData(
                authorUserId: $data->authorUserId,
                categoryId: $data->categoryId,
                featuredMediaId: $data->featuredMediaId,
                title: $data->title,
                slug: $this->slugResolver->resolve($data->title, $data->slug, $post->id),
                shortDescription: $data->shortDescription,
                description: $data->description,
                fullArticleHtml: $data->fullArticleHtml,
                fullArticleDelta: $data->fullArticleDelta,
                faq: $data->faq,
                status: $data->status,
                visibility: $data->visibility,
                publishedAt: $data->publishedAt,
                meta: $data->meta,
                tagIds: $data->tagIds,
            ));

            $result = $updated->refresh()->load(['author', 'category', 'featuredMedia', 'tags']);
            $this->syncInlineMedia->handle($result);

            $this->audit->log(
                logName: 'content',
                description: 'post.updated',
                event: 'updated',
                subject: $result,
                attributes: [
                    'title' => $result->title,
                    'slug' => $result->slug,
                    'status' => $result->status,
                    'visibility' => $result->visibility,
                    'tag_ids' => $result->tags->modelKeys(),
                ],
                old: $old,
            );

            return $result;
        });
    }
}
