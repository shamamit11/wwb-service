<?php

namespace App\Modules\Ai\Services;

use App\Jobs\AI\GeneratePostRewriteJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\Post;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\QueuePostRewriteData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Posts\Exceptions\PostRewriteNotAllowedException;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Posts\Services\RewritePostDraftService;
use RuntimeException;
use Throwable;

class DraftRewriteWorkflow
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly PostRepository $posts,
        private readonly RewritePostDraftService $rewritePostDraft,
    ) {}

    public function queue(Post $post, QueuePostRewriteData $data, ?int $retryOfAiJobId = null, int $attempts = 1): AiJob
    {
        $this->assertRewriteAllowed($post, $data);

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_EDITOR,
            status: AiJob::STATUS_QUEUED,
            entityType: 'post',
            entityId: (int) $post->id,
            inputPayload: [
                'post_id' => (int) $post->id,
                'scope' => $data->scope,
                'target_block_ids' => array_values($data->targetBlockIds),
                'instructions' => $data->instructions,
                'prompt_template_key' => $data->promptTemplateKey,
            ],
            attempts: max(1, $attempts),
            retryOfAiJobId: $retryOfAiJobId,
        ));

        GeneratePostRewriteJob::dispatch((int) $job->id);

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

            $this->assertRewriteAllowed($post, new QueuePostRewriteData(
                scope: $this->normalizeScope($payload['scope'] ?? null),
                targetBlockIds: $this->normalizeTargetBlockIds($payload['target_block_ids'] ?? null),
                instructions: is_string($payload['instructions'] ?? null) ? $payload['instructions'] : null,
                promptTemplateKey: is_string($payload['prompt_template_key'] ?? null) ? $payload['prompt_template_key'] : null,
            ));

            $this->rewritePostDraft->handle(
                post: $post,
                scope: $this->normalizeScope($payload['scope'] ?? null),
                targetBlockIds: $this->normalizeTargetBlockIds($payload['target_block_ids'] ?? null),
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

    public function normalizeScope(mixed $value): string
    {
        return match ($value) {
            RewritePostDraftService::SCOPE_SECTION => RewritePostDraftService::SCOPE_SECTION,
            RewritePostDraftService::SCOPE_PARAGRAPH => RewritePostDraftService::SCOPE_PARAGRAPH,
            default => RewritePostDraftService::SCOPE_FULL_DRAFT,
        };
    }

    /**
     * @return list<int>
     */
    private function normalizeTargetBlockIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ids = [];

        foreach ($value as $candidate) {
            if (is_int($candidate) && $candidate > 0) {
                $ids[] = $candidate;
                continue;
            }

            if (is_string($candidate) && ctype_digit($candidate) && (int) $candidate > 0) {
                $ids[] = (int) $candidate;
            }
        }

        return array_values(array_unique($ids));
    }

    private function requirePositiveInt(mixed $value, string $field): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        throw new RuntimeException("Queued post rewrite job is missing a valid [{$field}] value.");
    }

    private function assertRewriteAllowed(Post $post, QueuePostRewriteData $data): void
    {
        $meta = is_array($post->meta) ? $post->meta : [];
        $briefId = $meta['source_content_brief_id'] ?? null;
        $topicId = $meta['source_content_topic_id'] ?? null;

        if ($post->status !== Post::STATUS_DRAFT) {
            throw new PostRewriteNotAllowedException(
                postStatus: $post->status,
                message: "Post rewrite can only run against draft posts. Current status is [{$post->status}].",
            );
        }

        if (! is_numeric($briefId) || (int) $briefId <= 0 || ! is_numeric($topicId) || (int) $topicId <= 0) {
            throw new PostRewriteNotAllowedException(
                postStatus: $post->status,
                message: 'Post rewrite requires a draft created from an AI content brief and source topic.',
            );
        }

        if ($data->scope !== RewritePostDraftService::SCOPE_FULL_DRAFT && $data->targetBlockIds === []) {
            throw new RuntimeException('Partial draft rewrite requires one or more target_block_ids.');
        }
    }
}
