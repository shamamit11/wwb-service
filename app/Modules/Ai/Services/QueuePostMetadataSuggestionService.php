<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\Post;
use App\Modules\Ai\Data\QueuePostMetadataSuggestionData;

class QueuePostMetadataSuggestionService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(Post $post, QueuePostMetadataSuggestionData $data): AiJob
    {
        return $this->workflows->queuePostMetadataSuggestions($post, $data);
    }
}
