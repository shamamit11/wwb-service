<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Data\CreateNewsletterListData;
use App\Modules\Newsletter\Data\UpdateNewsletterListData;
use Illuminate\Database\Eloquent\Collection;

interface NewsletterListRepository
{
    public function create(CreateNewsletterListData $data): NewsletterList;

    public function update(NewsletterList $list, UpdateNewsletterListData $data): NewsletterList;

    public function delete(NewsletterList $list): void;

    public function findById(int $id): ?NewsletterList;

    public function findBySlug(string $slug): ?NewsletterList;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, NewsletterList>
     */
    public function getAllOrdered(): Collection;
}
