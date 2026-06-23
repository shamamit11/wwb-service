<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentTopic;
use App\Models\Post;
use Carbon\CarbonImmutable;

class AiAutomationDailyLimitService
{
    public const HIGH_PRIORITY_TOPIC_THRESHOLD = 85.0;

    public const MAX_HIGH_PRIORITY_TOPICS_PER_DAY = 2;

    public const MAX_AUTO_GENERATED_POSTS_PER_DAY = 2;

    public function canPersistAiSuggestedTopic(?string $priorityScore): bool
    {
        if (! $this->isHighPriorityScore($priorityScore)) {
            return true;
        }

        return $this->countHighPriorityAiSuggestedTopicsToday() < self::MAX_HIGH_PRIORITY_TOPICS_PER_DAY;
    }

    public function canQueueAutomaticDraft(): bool
    {
        return $this->countQueuedOrGeneratedDraftsToday() < self::MAX_AUTO_GENERATED_POSTS_PER_DAY;
    }

    public function isHighPriorityScore(mixed $priorityScore): bool
    {
        return is_numeric($priorityScore) && (float) $priorityScore >= self::HIGH_PRIORITY_TOPIC_THRESHOLD;
    }

    private function countHighPriorityAiSuggestedTopicsToday(): int
    {
        [$startOfDay, $endOfDay] = $this->todayRange();

        return ContentTopic::query()
            ->where('source', ContentTopic::SOURCE_AI_SUGGESTED)
            ->whereNotNull('priority_score')
            ->where('priority_score', '>=', self::HIGH_PRIORITY_TOPIC_THRESHOLD)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
    }

    private function countQueuedOrGeneratedDraftsToday(): int
    {
        [$startOfDay, $endOfDay] = $this->todayRange();

        $queuedDraftJobs = AiJob::query()
            ->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)
            ->whereNull('retry_of_ai_job_id')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        $generatedPosts = Post::query()
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->get()
            ->filter(static function (Post $post): bool {
                return is_array($post->meta)
                    && array_key_exists('source_content_topic_id', $post->meta)
                    && $post->meta['source_content_topic_id'] !== null;
            })
            ->count();

        return max($queuedDraftJobs, $generatedPosts);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function todayRange(): array
    {
        $now = CarbonImmutable::now();

        return [$now->startOfDay(), $now->endOfDay()];
    }
}
