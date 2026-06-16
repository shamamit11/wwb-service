<?php

namespace App\Modules\Tags\Repositories;

use App\Models\Tag;
use App\Modules\Tags\Data\CreateTagData;
use App\Modules\Tags\Data\UpdateTagData;
use Illuminate\Database\Eloquent\Collection;

interface TagRepository
{
    public function create(CreateTagData $data): Tag;

    public function update(Tag $tag, UpdateTagData $data): Tag;

    public function delete(Tag $tag): void;

    public function findById(int $id): ?Tag;

    public function findBySlug(string $slug): ?Tag;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, Tag>
     */
    public function getAllOrdered(): Collection;

    /**
     * @return Collection<int, Tag>
     */
    public function getActiveOrdered(): Collection;

    /**
     * @param  list<int>  $tagIds
     */
    public function syncPostTags(int $postId, array $tagIds): void;

    /**
     * @return list<int>
     */
    public function getTagIdsForPost(int $postId): array;
}
