<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Repositories\PostRepository;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Facades\DB;

class CreatePostService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly PostSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(CreatePostCommandData $data): Post
    {
        return DB::transaction(function () use ($data): Post {
            $post = $this->posts->create(new CreatePostData(
                authorUserId: $data->authorUserId,
                categoryId: $data->categoryId,
                featuredMediaId: $data->featuredMediaId,
                title: $data->title,
                slug: $this->slugResolver->resolve($data->title, $data->slug),
                shortDescription: $data->shortDescription,
                description: $data->description,
                fullArticleMarkdown: $data->fullArticleMarkdown,
                fullArticleHtml: $data->fullArticleHtml,
                faq: $data->faq,
                status: $data->status,
                visibility: $data->visibility,
                publishedAt: $data->publishedAt,
                meta: $data->meta,
                tagIds: $data->tagIds,
            ));

            $created = $post->refresh()->load(['author', 'category', 'featuredMedia', 'tags']);

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
            );

            return $created;
        });
    }
}
