<?php

namespace App\Modules\Pages\Services;

use App\Models\Page;
use App\Modules\Pages\Data\UpdatePageData;
use App\Modules\Pages\Repositories\PageRepository;
use App\Support\AuditActivityLogger;

class UpdatePageService
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly PageSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Page $page, UpdatePageData $data): Page
    {
        $old = [
            'title' => $page->title,
            'slug' => $page->slug,
            'type' => $page->type,
            'status' => $page->status,
            'visibility' => $page->visibility,
        ];

        $updated = $this->pages->update($page, new UpdatePageData(
            updatedByUserId: $data->updatedByUserId,
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug, $page->id),
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
            description: 'page.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'title' => $updated->title,
                'slug' => $updated->slug,
                'type' => $updated->type,
                'status' => $updated->status,
                'visibility' => $updated->visibility,
            ],
            old: $old,
        );

        return $updated;
    }
}
