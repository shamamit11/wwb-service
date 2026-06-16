<?php

namespace App\Modules\Templates\Repositories;

use App\Models\Template;
use App\Models\TemplateBlock;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Data\UpdateTemplateData;
use Illuminate\Database\Eloquent\Collection;

interface TemplateRepository
{
    public function create(CreateTemplateData $data): Template;

    public function update(Template $template, UpdateTemplateData $data): Template;

    public function delete(Template $template): void;

    public function findById(int $id): ?Template;

    public function findBySlug(string $slug): ?Template;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, Template>
     */
    public function getAllOrdered(): Collection;

    /**
     * @return Collection<int, TemplateBlock>
     */
    public function getOrderedBlocks(Template $template): Collection;
}
