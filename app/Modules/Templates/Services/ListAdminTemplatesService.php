<?php

namespace App\Modules\Templates\Services;

use App\Modules\Templates\Repositories\TemplateRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminTemplatesService
{
    public function __construct(
        private readonly TemplateRepository $templates,
    ) {}

    public function handle(): Collection
    {
        return $this->templates->getAllOrdered();
    }
}
