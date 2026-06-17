<?php

namespace App\Modules\Pages\Services;

use App\Models\Page;
use App\Modules\Pages\Data\CreatePageData;
use App\Modules\Pages\Repositories\PageRepository;
use App\Support\AuditActivityLogger;

class CreatePageService
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(CreatePageData $data): Page
    {
        $created = $this->pages->create(new CreatePageData(
            createdByUserId: $data->createdByUserId,
            updatedByUserId: $data->updatedByUserId,
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug),
            type: $data->type,
            status: $data->status,
            summary: $data->summary,
            contentMarkdown: $data->contentMarkdown,
            visibility: $data->visibility,
            publishedAt: $data->publishedAt,
            scheduledFor: $data->scheduledFor,
            meta: $data->meta,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'page.created',
            event: 'created',
            subject: $created,
            attributes: [
                'title' => $created->title,
                'slug' => $created->slug,
                'type' => $created->type,
                'status' => $created->status,
                'visibility' => $created->visibility,
            ],
        );

        return $created;
    }
}
