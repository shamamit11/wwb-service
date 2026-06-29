<?php

namespace App\Modules\Posts\Repositories;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Data\PostFiltersData;
use App\Modules\Posts\Data\PostStateTransitionData;
use App\Modules\Posts\Data\UpdatePostData;
use Illuminate\Database\Eloquent\Collection;

interface PostRepository
{
    public function create(CreatePostData $data): Post;

    public function update(Post $post, UpdatePostData $data): Post;

    public function transition(Post $post, PostStateTransitionData $data): Post;

    public function delete(Post $post): void;

    public function findById(int $id): ?Post;

    public function findByUlid(string $ulid): ?Post;

    public function findBySourceContentTopicId(int $contentTopicId): ?Post;

    public function findBySlug(string $slug): ?Post;

    public function findPublishedBySlug(string $slug): ?Post;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    public function existsPotentialDuplicate(string $title, ?string $slug = null): bool;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function updateMeta(Post $post, array $meta): Post;

    /**
     * @return Collection<int, Post>
     */
    public function getAdminOrdered(): Collection;

    /**
     * @param  list<string>  $keywords
     * @return Collection<int, Post>
     */
    public function getOriginalityComparisonCandidates(Post $post, array $keywords = [], int $limit = 25): Collection;

    /**
     * @return Collection<int, Post>
     */
    public function searchAdmin(PostFiltersData $filters): Collection;

    /**
     * @return Collection<int, Post>
     */
    public function getPublishedOrdered(?string $categorySlug = null, bool $featuredOnly = false): Collection;
}
