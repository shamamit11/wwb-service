<?php

namespace App\Modules\Pages\Services;

use App\Models\Page;
use App\Modules\Pages\Repositories\PageRepository;
use App\Support\AuditActivityLogger;

class DeletePageService
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Page $page): void
    {
        $attributes = [
            'title' => $page->title,
            'slug' => $page->slug,
            'type' => $page->type,
            'status' => $page->status,
            'visibility' => $page->visibility,
        ];

        $this->pages->delete($page);

        $this->audit->log(
            logName: 'content',
            description: 'page.deleted',
            event: 'deleted',
            subject: $page,
            attributes: $attributes,
        );
    }
}
