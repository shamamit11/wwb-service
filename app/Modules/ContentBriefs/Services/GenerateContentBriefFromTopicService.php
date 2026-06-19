<?php

namespace App\Modules\ContentBriefs\Services;

use App\AI\Agents\ContentBriefAgent;
use App\AI\DTO\ContentBriefInput;
use App\Models\ContentTopic;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use App\Modules\ContentBriefs\Exceptions\ContentBriefGenerationNotAllowedException;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use App\Modules\Seo\Services\FindRelatedContentService;

class GenerateContentBriefFromTopicService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly ContentBriefAgent $agent,
        private readonly KnowledgeContextService $knowledgeContext,
        private readonly FindRelatedContentService $relatedContent,
    ) {}

    public function handle(
        ContentTopic $topic,
        ?int $aiJobId = null,
        ?string $promptTemplateKey = null,
    ): GeneratedContentBriefData
    {
        if (! $topic->isApproved()) {
            throw new ContentBriefGenerationNotAllowedException(
                topicStatus: $topic->status,
                message: "Content brief can only be generated from approved topics. Current status is [{$topic->status}].",
            );
        }

        $existing = $this->briefs->findByTopicId((int) $topic->id);

        if ($existing !== null) {
            return new GeneratedContentBriefData($existing, false);
        }

        $result = $this->agent->run(new ContentBriefInput(
            contentTopicId: (int) $topic->id,
            topicTitle: $topic->title,
            cluster: $topic->cluster,
            primaryKeyword: $topic->primary_keyword,
            secondaryKeywords: $topic->secondary_keywords ?? [],
            searchIntent: $topic->search_intent,
            knowledgeBaseContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $topic->title,
                keywords: array_values(array_filter([
                    $topic->primary_keyword,
                    ...($topic->secondary_keywords ?? []),
                    $topic->cluster,
                ], static fn (mixed $value): bool => is_string($value) && $value !== '')),
                maxEntries: 6,
                maxEntryCharacters: 320,
                maxTotalCharacters: 1800,
            )),
            existingPostContext: $this->existingPostContext($topic),
            internalLinkContext: [],
            editorialIntent: $topic->notes,
            metadata: array_filter([
                'ai_job_id' => $aiJobId,
                'prompt_template_key' => $promptTemplateKey,
            ], static fn (mixed $value): bool => $value !== null),
        ));

        $briefId = $result->metadata['brief_id'] ?? null;
        $brief = is_int($briefId)
            ? $this->briefs->findById($briefId)
            : $this->briefs->findByTopicId((int) $topic->id);

        if ($brief === null) {
            throw new \RuntimeException('Content brief agent did not persist a content brief.');
        }

        return new GeneratedContentBriefData($brief, true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function existingPostContext(ContentTopic $topic): array
    {
        return $this->relatedContent->handleForContext(new \App\Modules\Seo\Data\InternalLinkContextData(
            title: $topic->title,
            excerpt: $topic->notes,
            tagNames: $topic->secondary_keywords ?? [],
            focusKeyword: $topic->primary_keyword,
        ), 5)->map(static fn ($candidate) => $candidate->toArray())->all();
    }
}
