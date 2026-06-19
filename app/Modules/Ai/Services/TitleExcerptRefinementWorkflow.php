<?php

namespace App\Modules\Ai\Services;

use App\Jobs\AI\GeneratePostTitleExcerptRefinementJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Post;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\QueuePostTitleExcerptRefinementData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Posts\Repositories\PostRepository;
use RuntimeException;
use Throwable;

class TitleExcerptRefinementWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly PostRepository $posts,
        private readonly SuggestPostTitleExcerptRefinementService $refinements,
    ) {}

    public function queue(Post $post, QueuePostTitleExcerptRefinementData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_EDITORIAL_REFINER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'instructions' => $data->instructions,
                'prompt_template_key' => $data->promptTemplateKey,
            ],
            attempts: max(1, $attempts),
            retryOfAiJobId: $retryOfAiJobId,
        ));

        GeneratePostTitleExcerptRefinementJob::dispatch((int) $job->id);

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
            $postId = $this->requirePositiveInt($payload['post_id'] ?? null, 'post_id');
            $post = $this->posts->findById($postId);

            if (! $post instanceof Post) {
                throw new RuntimeException("Post [{$postId}] could not be found.");
            }

            $this->refinements->handle(
                post: $post,
                instructions: is_string($payload['instructions'] ?? null) ? $payload['instructions'] : null,
                aiJobId: (int) $job->id,
                promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) ? $payload['prompt_template_key'] : null,
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

        throw new RuntimeException("Queued title/excerpt refinement job is missing a valid [{$field}] value.");
    }
}
