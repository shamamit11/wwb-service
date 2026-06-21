<?php

namespace App\Modules\Ai\Services;

use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Posts\Services\GenerateBlogDraftFromTopicService;
use RuntimeException;
use Throwable;

class DraftGenerationWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly PostRepository $posts,
        private readonly GenerateBlogDraftFromTopicService $generateBlogDraft,
    ) {}

    public function queue(ContentTopic $topic, QueueBlogDraftGenerationData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        if (! $topic->canGenerateDraft() && $this->posts->findBySourceContentTopicId((int) $topic->id) === null) {
            throw new RuntimeException(
                "Blog draft can only be generated from approved topics. Current status is [{$topic->status}].",
            );
        }

        $activeJob = AiJob::query()
            ->where('type', AiPromptTemplate::TYPE_BLOG_WRITER)
            ->where('entity_type', 'content_topic')
            ->where('entity_id', (int) $topic->id)
            ->whereIn('status', [
                AiJob::STATUS_PENDING,
                AiJob::STATUS_QUEUED,
                AiJob::STATUS_PROCESSING,
            ])
            ->latest('id')
            ->first();

        if ($activeJob instanceof AiJob) {
            return $activeJob->loadCount('steps');
        }

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_BLOG_WRITER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_topic',
            entityId: (int) $topic->id,
            inputPayload: [
                'content_topic_id' => (int) $topic->id,
                'author_user_id' => $data->authorUserId,
                'category_id' => $data->categoryId,
                'featured_media_id' => $data->featuredMediaId,
                'visibility' => $data->visibility,
            ],
            attempts: max(1, $attempts),
            retryOfAiJobId: $retryOfAiJobId,
        ));

        GenerateBlogDraftJob::dispatch((int) $job->id);

        return $job;
    }

    public function runQueued(int $aiJobId): void
    {
        $job = $this->jobs->findById($aiJobId);

        if (! $job instanceof AiJob) {
            throw new RuntimeException("AI job [{$aiJobId}] could not be found.");
        }

        try {
            $payload = is_array($job->input_payload) ? $job->input_payload : [];
            $topicId = $this->requirePositiveInt($payload['content_topic_id'] ?? null, 'content_topic_id');
            $topic = ContentTopic::query()->find($topicId);

            if (! $topic instanceof ContentTopic) {
                throw new RuntimeException("Content topic [{$topicId}] could not be found.");
            }

            $existing = $this->posts->findBySourceContentTopicId((int) $topic->id);

            if ($existing instanceof Post) {
                $job = $this->trackAiJob->startJob($job);
                $this->trackAiJob->completeJob($job, [
                    'post_id' => (int) $existing->id,
                    'content_topic_id' => (int) $topic->id,
                    'reused_existing_post' => true,
                ]);

                return;
            }

            $this->generateBlogDraft->handle(
                topic: $topic,
                authorUserId: $this->nullablePositiveInt($payload['author_user_id'] ?? null) ?? 1,
                categoryId: $this->requirePositiveInt($payload['category_id'] ?? null, 'category_id'),
                featuredMediaId: $this->nullablePositiveInt($payload['featured_media_id'] ?? null),
                visibility: $this->normalizeVisibility($payload['visibility'] ?? null),
                aiJobId: (int) $job->id,
            );
        } catch (Throwable $throwable) {
            $job = $this->jobs->findById($aiJobId);

            if ($job instanceof AiJob && $job->status !== AiJob::STATUS_FAILED && $job->status !== AiJob::STATUS_COMPLETED) {
                if ($job->started_at === null) {
                    $job = $this->trackAiJob->startJob($job);
                }

                $this->trackAiJob->failJob($job, $throwable->getMessage(), [
                    'error' => [
                        'class' => $throwable::class,
                        'message' => $throwable->getMessage(),
                    ],
                ]);
            }

            throw $throwable;
        }
    }

    private function requirePositiveInt(mixed $value, string $field): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        throw new RuntimeException("Queued blog draft job is missing a valid [{$field}] value.");
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->requirePositiveInt($value, 'value');
    }

    private function normalizeVisibility(mixed $value): string
    {
        if (is_string($value) && in_array($value, Post::VISIBILITIES, true)) {
            return $value;
        }

        return Post::VISIBILITY_PUBLIC;
    }
}
