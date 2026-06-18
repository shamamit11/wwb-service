<?php

namespace App\Jobs\AI;

use App\Modules\Ai\Services\RunBlogDraftGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateBlogDraftJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $aiJobId,
    ) {
        $this->onQueue('ai');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(RunBlogDraftGenerationService $service): void
    {
        $service->handle($this->aiJobId);
    }
}
