<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Repositories\TemplateRepository;

class DeleteTemplateService
{
    public function __construct(
        private readonly TemplateRepository $templates,
    ) {}

    public function handle(Template $template): void
    {
        $this->templates->delete($template);
    }
}
