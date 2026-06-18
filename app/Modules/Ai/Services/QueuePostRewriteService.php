<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\Post;
use App\Modules\Ai\Data\QueuePostRewriteData;

class QueuePostRewriteService
{
    public function __construct(
        private readonly AiWorkflowOrchestrator $workflows,
    ) {}

    public function handle(Post $post, QueuePostRewriteData $data): AiJob
    {
        return $this->workflows->queuePostRewrite($post, $data);
    }
}
