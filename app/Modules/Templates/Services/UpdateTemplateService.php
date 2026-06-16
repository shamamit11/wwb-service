<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Data\UpdateTemplateData;
use App\Modules\Templates\Repositories\TemplateRepository;
use App\Support\AuditActivityLogger;

class UpdateTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly TemplateSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Template $template, UpdateTemplateData $data): Template
    {
        $old = [
            'name' => $template->name,
            'slug' => $template->slug,
            'status' => $template->status,
            'template_type' => $template->template_type,
        ];

        $updated = $this->templates->update($template, new UpdateTemplateData(
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug, $template->id),
            templateType: $data->templateType,
            description: $data->description,
            status: $data->status,
            defaultExcerptPrompt: $data->defaultExcerptPrompt,
            defaultMeta: $data->defaultMeta,
            blocks: $data->blocks,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'template.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'name' => $updated->name,
                'slug' => $updated->slug,
                'status' => $updated->status,
                'template_type' => $updated->template_type,
            ],
            old: $old,
            context: [
                'block_count' => $updated->blocks->count(),
            ],
        );

        return $updated;
    }
}
