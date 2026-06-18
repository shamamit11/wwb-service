<?php

namespace App\Modules\Posts\Services;

use App\AI\Agents\BlogWriterAgent;
use App\AI\DTO\BlogDraftInput;
use App\Models\ContentBrief;
use App\Models\Post;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use App\Modules\Posts\Data\GeneratedBlogDraftData;
use App\Modules\Posts\Exceptions\BlogDraftGenerationNotAllowedException;
use App\Modules\Posts\Repositories\PostRepository;

class GenerateBlogDraftFromBriefService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly BlogWriterAgent $agent,
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function handle(
        ContentBrief $brief,
        int $authorUserId,
        int $categoryId,
        ?int $templateId = null,
        ?int $featuredMediaId = null,
        string $visibility = Post::VISIBILITY_PUBLIC,
        ?int $aiJobId = null,
        ?string $promptTemplateKey = null,
        ?string $generationMode = null,
    ): GeneratedBlogDraftData {
        $existing = $this->posts->findBySourceContentBriefId((int) $brief->id);

        if ($existing instanceof Post) {
            return new GeneratedBlogDraftData($existing, false);
        }

        if (! $brief->canGenerateDraft()) {
            throw new BlogDraftGenerationNotAllowedException(
                briefStatus: $brief->status,
                message: "Blog draft can only be generated from approved content briefs. Current status is [{$brief->status}].",
            );
        }

        $topic = $brief->topic;

        if ($topic === null) {
            throw new \RuntimeException('Content brief is missing its source topic.');
        }

        $result = $this->agent->run(new BlogDraftInput(
            contentBriefId: (int) $brief->id,
            contentTopicId: (int) $topic->id,
            title: $brief->title,
            slug: $brief->slug,
            generationMode: $generationMode,
            primaryKeyword: $brief->primary_keyword,
            secondaryKeywords: $brief->secondary_keywords ?? [],
            searchIntent: $brief->search_intent,
            introAngle: $brief->outline[0]['purpose'] ?? null,
            targetAudience: null,
            outline: $brief->outline ?? [],
            headingStructure: $brief->headings ?? [],
            faqSuggestions: $brief->faq_suggestions ?? [],
            knowledgeBaseContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $brief->title,
                keywords: array_values(array_filter([
                    $brief->primary_keyword,
                    ...($brief->secondary_keywords ?? []),
                    $topic->cluster,
                ], static fn (mixed $value): bool => is_string($value) && $value !== '')),
                maxEntries: 8,
                maxEntryCharacters: 340,
                maxTotalCharacters: 2200,
            )),
            imageSuggestions: $brief->image_suggestions ?? [],
            metadata: array_filter([
                'ai_job_id' => $aiJobId,
                'author_user_id' => $authorUserId,
                'category_id' => $categoryId,
                'template_id' => $templateId,
                'featured_media_id' => $featuredMediaId,
                'visibility' => $visibility,
                'prompt_template_key' => $promptTemplateKey,
                'generation_mode' => $generationMode,
            ], static fn (mixed $value): bool => $value !== null),
        ));

        if (! $result->isSuccessful()) {
            throw new \RuntimeException($result->error?->message ?? 'Blog writer agent failed to generate a draft post.');
        }

        $postId = $result->metadata['post_id'] ?? null;
        $post = is_int($postId)
            ? $this->posts->findById($postId)
            : $this->posts->findBySourceContentBriefId((int) $brief->id);

        if (! $post instanceof Post) {
            throw new \RuntimeException('Blog writer agent did not persist a draft post.');
        }

        return new GeneratedBlogDraftData($post, true);
    }
}
