<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;

class ReadContentTopicService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
    ) {}

    public function handle(int $id): ContentTopic
    {
        return $this->topics->findById($id) ?? (new ContentTopic)->newModelQuery()->whereKey($id)->firstOrFail();
    }
}
