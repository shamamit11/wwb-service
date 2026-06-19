<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\Post;
use App\Modules\Ai\Data\QueuePostTitleExcerptRefinementData;

class QueuePostTitleExcerptRefinementService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(Post $post, QueuePostTitleExcerptRefinementData $data): AiJob
    {
        return $this->workflows->queuePostTitleExcerptRefinement($post, $data);
    }
}
