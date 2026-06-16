<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Data\UpdatePostData;
use App\Modules\Posts\Repositories\PostBlockRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class UpdatePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostBlockRepository $blocks,
        private readonly PostSlugResolver $slugResolver,
        private readonly PostBlockPayloadMapper $blockPayloadMapper,
        private readonly PostBlockPayloadValidator $blockPayloadValidator,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Post $post, UpdatePostCommandData $data): Post
    {
        $this->blockPayloadValidator->validate($data->blocks);

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
                templateId: $data->templateId,
                featuredMediaId: $data->featuredMediaId,
                title: $data->title,
                slug: $this->slugResolver->resolve($data->title, $data->slug, $post->id),
                excerpt: $data->excerpt,
                status: $data->status,
                visibility: $data->visibility,
                publishedAt: $data->publishedAt,
                scheduledFor: $data->scheduledFor,
                contentVersion: $data->contentVersion,
                readingTimeMinutes: $data->readingTimeMinutes,
                wordCount: $data->wordCount,
                isFeatured: $data->isFeatured,
                meta: $data->meta,
                tagIds: $data->tagIds,
            ));

            $this->blocks->replaceForPost($updated, $this->blockPayloadMapper->mapMany($data->blocks));

            $result = $updated->refresh()->load(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock']);

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
                context: [
                    'block_count' => $result->blocks->count(),
                ],
            );

            return $result;
        });
    }
}
