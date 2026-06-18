<?php

namespace App\Modules\Ai\Services;

use App\Jobs\AI\GenerateBlogDraftJob;
use App\Models\AiJob;
use App\Models\AiPromptTemplate;
use App\Models\ContentBrief;
use App\Modules\Ai\Data\CreateAiJobData;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use App\Modules\Ai\Repositories\AiJobRepository;
use App\Modules\Posts\Repositories\PostRepository;
use RuntimeException;

class QueueBlogDraftGenerationService
{
    public function __construct(
        private readonly AiJobRepository $jobs,
        private readonly PostRepository $posts,
    ) {}

    public function handle(ContentBrief $brief, QueueBlogDraftGenerationData $data): AiJob
    {
        if (! $brief->canGenerateDraft() && $this->posts->findBySourceContentBriefId((int) $brief->id) === null) {
            throw new \App\Modules\Posts\Exceptions\BlogDraftGenerationNotAllowedException(
                briefStatus: $brief->status,
                message: "Blog draft can only be generated from approved content briefs. Current status is [{$brief->status}].",
            );
        }

        $job = $this->jobs->create(new CreateAiJobData(
            type: AiPromptTemplate::TYPE_BLOG_WRITER,
            status: AiJob::STATUS_QUEUED,
            entityType: 'content_brief',
            entityId: (int) $brief->id,
            inputPayload: [
                'content_brief_id' => (int) $brief->id,
                'author_user_id' => $data->authorUserId,
                'category_id' => $data->categoryId,
                'template_id' => $data->templateId,
                'featured_media_id' => $data->featuredMediaId,
                'visibility' => $data->visibility,
                'prompt_template_key' => $data->promptTemplateKey,
            ],
        ));

        GenerateBlogDraftJob::dispatch((int) $job->id);

        return $job;
    }
}
