<?php

namespace App\Console\Commands;

use App\Jobs\AI\DeleteLowScoreTopicsJob;
use Illuminate\Console\Command;

class PruneLowScoreTopicsCommand extends Command
{
    protected $signature = 'ai:prune-low-score-topics {--sync : Run inline instead of dispatching the queue job}';

    protected $description = 'Delete content topics with scores below the automatic review threshold.';

    public function handle(): int
    {
        if ($this->option('sync')) {
            app(\App\Modules\ContentTopics\Services\DeleteLowScoreTopicsService::class)->handle();
            $this->info('Low-score topic prune completed synchronously.');

            return self::SUCCESS;
        }

        DeleteLowScoreTopicsJob::dispatch();
        $this->info('Low-score topic prune job dispatched.');

        return self::SUCCESS;
    }
}
