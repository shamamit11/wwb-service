<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Repositories\TemplateRepository;
use App\Support\AuditActivityLogger;

class CreateTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly TemplateSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(CreateTemplateData $data): Template
    {
        $template = $this->templates->create(new CreateTemplateData(
            createdByUserId: $data->createdByUserId,
            updatedByUserId: $data->updatedByUserId,
            name: $data->name,
            slug: $this->slugResolver->resolve($data->name, $data->slug),
            templateType: $data->templateType,
            description: $data->description,
            status: $data->status,
            defaultExcerptPrompt: $data->defaultExcerptPrompt,
            defaultMeta: $data->defaultMeta,
            blocks: $data->blocks,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'template.created',
            event: 'created',
            subject: $template,
            attributes: [
                'name' => $template->name,
                'slug' => $template->slug,
                'status' => $template->status,
                'template_type' => $template->template_type,
            ],
            context: [
                'block_count' => $template->blocks->count(),
            ],
        );

        return $template;
    }
}
