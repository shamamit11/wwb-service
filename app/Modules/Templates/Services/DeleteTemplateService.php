<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Repositories\TemplateRepository;
use App\Support\AuditActivityLogger;

class DeleteTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Template $template): void
    {
        $attributes = [
            'name' => $template->name,
            'slug' => $template->slug,
            'status' => $template->status,
            'template_type' => $template->template_type,
        ];
        $context = [
            'block_count' => $template->blocks()->count(),
        ];

        $this->templates->delete($template);

        $this->audit->log(
            logName: 'content',
            description: 'template.deleted',
            event: 'deleted',
            subject: $template,
            attributes: $attributes,
            context: $context,
        );
    }
}
