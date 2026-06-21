<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;

class DeleteLowScoreTopicsService
{
    public const MINIMUM_REVIEW_SCORE = 90.0;

    public function handle(): int
    {
        return ContentTopic::query()
            ->whereNotIn('status', [ContentTopic::STATUS_USED])
            ->whereNotNull('priority_score')
            ->where('priority_score', '<', self::MINIMUM_REVIEW_SCORE)
            ->delete();
    }
}
