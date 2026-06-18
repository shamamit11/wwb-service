<?php

namespace App\Modules\ContentTopics\Repositories;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Data\ContentTopicStateTransitionData;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Data\UpdateContentTopicData;
use Illuminate\Database\Eloquent\Collection;

interface ContentTopicRepository
{
    public function create(CreateContentTopicData $data): ContentTopic;

    public function update(ContentTopic $topic, UpdateContentTopicData $data): ContentTopic;

    public function transition(ContentTopic $topic, ContentTopicStateTransitionData $data): ContentTopic;

    public function delete(ContentTopic $topic): void;

    public function findById(int $id): ?ContentTopic;

    public function findBySlug(string $slug): ?ContentTopic;

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool;

    public function existsDuplicate(string $title, string $cluster, ?string $primaryKeyword = null, ?int $ignoreId = null): bool;

    /**
     * @return Collection<int, ContentTopic>
     */
    public function search(ContentTopicFiltersData $filters): Collection;
}
