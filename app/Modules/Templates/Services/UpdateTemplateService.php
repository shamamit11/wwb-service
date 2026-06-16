<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Data\UpdateTemplateData;
use App\Modules\Templates\Repositories\TemplateRepository;

class UpdateTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly TemplateSlugResolver $slugResolver,
    ) {}

    public function handle(Template $template, UpdateTemplateData $data): Template
    {
        return $this->templates->update($template, new UpdateTemplateData(
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
    }
}
