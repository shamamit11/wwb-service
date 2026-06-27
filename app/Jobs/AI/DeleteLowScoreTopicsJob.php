<?php

namespace App\Jobs\AI;

use App\Modules\ContentTopics\Services\DeleteLowScoreTopicsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteLowScoreTopicsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly bool $hardDelete = false,
    ) {
        $this->onQueue('ai');
    }

    public function handle(DeleteLowScoreTopicsService $service): void
    {
        $service->handle($this->hardDelete);
    }
}
