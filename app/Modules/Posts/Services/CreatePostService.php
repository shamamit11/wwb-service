<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Repositories\PostBlockRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class CreatePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostBlockRepository $blocks,
        private readonly PostSlugResolver $slugResolver,
        private readonly PostBlockPayloadMapper $blockPayloadMapper,
        private readonly PostBlockPayloadValidator $blockPayloadValidator,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(CreatePostCommandData $data): Post
    {
        $this->blockPayloadValidator->validate($data->blocks);

        return DB::transaction(function () use ($data): Post {
            $post = $this->posts->create(new CreatePostData(
                authorUserId: $data->authorUserId,
                categoryId: $data->categoryId,
                templateId: $data->templateId,
                featuredMediaId: $data->featuredMediaId,
                title: $data->title,
                slug: $this->slugResolver->resolve($data->title, $data->slug),
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

            $this->blocks->replaceForPost($post, $this->blockPayloadMapper->mapMany($data->blocks));

            $created = $post->refresh()->load(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock']);

            $this->audit->log(
                logName: 'content',
                description: 'post.created',
                event: 'created',
                subject: $created,
                attributes: [
                    'title' => $created->title,
                    'slug' => $created->slug,
                    'status' => $created->status,
                    'visibility' => $created->visibility,
                    'tag_ids' => $created->tags->modelKeys(),
                ],
                context: [
                    'block_count' => $created->blocks->count(),
                ],
            );

            return $created;
        });
    }
}
