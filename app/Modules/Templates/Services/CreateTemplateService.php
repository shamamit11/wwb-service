<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Repositories\TemplateRepository;

class CreateTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
        private readonly TemplateSlugResolver $slugResolver,
    ) {}

    public function handle(CreateTemplateData $data): Template
    {
        return $this->templates->create(new CreateTemplateData(
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
    }
}
