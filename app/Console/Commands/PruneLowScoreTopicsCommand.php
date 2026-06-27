<?php

namespace App\Console\Commands;

use App\Jobs\AI\DeleteLowScoreTopicsJob;
use App\Modules\ContentTopics\Services\DeleteLowScoreTopicsService;
use Illuminate\Console\Command;

class PruneLowScoreTopicsCommand extends Command
{
    protected $signature = 'ai:prune-low-score-topics {--sync : Run inline instead of dispatching the queue job} {--hard-delete : Permanently delete low-score topics instead of retaining them}';

    protected $description = 'Apply the low-score topic retention policy. Hard deletion is opt-in.';

    public function handle(): int
    {
        if ($this->option('sync')) {
            $deleted = app(DeleteLowScoreTopicsService::class)
                ->handle((bool) $this->option('hard-delete'));

            $this->info($this->option('hard-delete')
                ? "Low-score topic prune completed synchronously. Deleted {$deleted} topics."
                : 'Low-score topic retention policy applied synchronously. No topics were deleted.');

            return self::SUCCESS;
        }

        DeleteLowScoreTopicsJob::dispatch((bool) $this->option('hard-delete'));
        $this->info($this->option('hard-delete')
            ? 'Low-score topic hard-delete job dispatched.'
            : 'Low-score topic retention job dispatched. Topics will be retained by default.');

        return self::SUCCESS;
    }
}
