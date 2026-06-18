<?php

namespace App\Modules\ContentTopics\Services;

use App\Modules\ContentTopics\Data\ContentTopicFiltersData;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminContentTopicsService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
    ) {}

    public function handle(ContentTopicFiltersData $filters): Collection
    {
        return $this->topics->search($filters);
    }
}
