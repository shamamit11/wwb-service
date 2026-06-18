<?php

namespace App\Modules\Ai\Services;

use App\Models\AiJob;
use App\Models\Post;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Posts\Services\GenerateBlogDraftFromBriefService;
use RuntimeException;
use Throwable;

class RunBlogDraftGenerationService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly TrackAiJobService $trackAiJob,
        private readonly ContentBriefRepository $briefs,
        private readonly PostRepository $posts,
        private readonly GenerateBlogDraftFromBriefService $generateBlogDraft,
    ) {}

    public function handle(int $aiJobId): void
    {
        $job = $this->jobs->findById($aiJobId);

        if (! $job instanceof AiJob) {
            throw new RuntimeException("AI job [{$aiJobId}] could not be found.");
        }

        try {
            $payload = is_array($job->input_payload) ? $job->input_payload : [];
            $briefId = $this->requirePositiveInt($payload['content_brief_id'] ?? null, 'content_brief_id');
            $brief = $this->briefs->findById($briefId);

            if ($brief === null) {
                throw new RuntimeException("Content brief [{$briefId}] could not be found.");
            }

            $existing = $this->posts->findBySourceContentBriefId((int) $brief->id);

            if ($existing instanceof Post) {
                $job = $this->trackAiJob->startJob($job);
                $this->trackAiJob->completeJob($job, [
                    'post_id' => (int) $existing->id,
                    'content_topic_id' => (int) $brief->content_topic_id,
                    'reused_existing_post' => true,
                ]);

                return;
            }

            $this->generateBlogDraft->handle(
                brief: $brief,
                authorUserId: $this->nullablePositiveInt($payload['author_user_id'] ?? null) ?? 1,
                categoryId: $this->requirePositiveInt($payload['category_id'] ?? null, 'category_id'),
                templateId: $this->nullablePositiveInt($payload['template_id'] ?? null),
                featuredMediaId: $this->nullablePositiveInt($payload['featured_media_id'] ?? null),
                visibility: $this->normalizeVisibility($payload['visibility'] ?? null),
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
