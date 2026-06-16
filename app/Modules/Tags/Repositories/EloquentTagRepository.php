<?php

namespace App\Modules\Tags\Repositories;

use App\Models\Tag;
use App\Modules\Tags\Data\CreateTagData;
use App\Modules\Tags\Data\UpdateTagData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTagRepository implements TagRepository
{
    public function create(CreateTagData $data): Tag
    {
        return Tag::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'is_active' => $data->isActive,
        ]);
    }

    public function update(Tag $tag, UpdateTagData $data): Tag
    {
        $tag->update([
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'is_active' => $data->isActive,
        ]);

        return $tag->refresh();
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }

    public function findById(int $id): ?Tag
    {
        return Tag::query()->find($id);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::query()
            ->where('slug', $slug)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Tag::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getAllOrdered(): Collection
    {
        return Tag::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getActiveOrdered(): Collection
    {
        return Tag::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  list<int>  $tagIds
     */
    public function syncPostTags(int $postId, array $tagIds): void
    {
        $normalizedTagIds = array_values(array_unique($tagIds));

        DB::table('post_tags')
            ->where('post_id', $postId)
            ->delete();

        if ($normalizedTagIds === []) {
            return;
        }

        $timestamp = now();

        DB::table('post_tags')->insert(
            array_map(
                fn (int $tagId): array => [
                    'post_id' => $postId,
                    'tag_id' => $tagId,
                    'created_at' => $timestamp,
                ],
                $normalizedTagIds,
            ),
        );
    }

    /**
     * @return list<int>
     */
    public function getTagIdsForPost(int $postId): array
    {
        /** @var list<int> $tagIds */
        $tagIds = DB::table('post_tags')
            ->where('post_id', $postId)
            ->orderBy('tag_id')
            ->pluck('tag_id')
            ->map(static fn (mixed $tagId): int => (int) $tagId)
            ->all();

        return $tagIds;
    }
}
