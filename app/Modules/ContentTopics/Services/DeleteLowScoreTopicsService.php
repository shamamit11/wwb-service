<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;

class DeleteLowScoreTopicsService
{
    public const MINIMUM_REVIEW_SCORE = 70.0;

    public function handle(bool $hardDelete = false): int
    {
        if (! $hardDelete) {
            return 0;
        }

        return ContentTopic::query()
            ->whereNotIn('status', [ContentTopic::STATUS_USED])
            ->whereNotNull('priority_score')
            ->where('priority_score', '<', self::MINIMUM_REVIEW_SCORE)
            ->delete();
    }
}
