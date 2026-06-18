<?php

namespace App\Modules\ContentBriefs\Services;

use App\AI\Agents\ContentBriefAgent;
use App\AI\DTO\ContentBriefInput;
use App\Models\ContentTopic;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use App\Modules\ContentBriefs\Exceptions\ContentBriefGenerationNotAllowedException;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeBaseEntryFiltersData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use App\Modules\Seo\Services\FindRelatedContentService;

class GenerateContentBriefFromTopicService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly ContentBriefAgent $agent,
        private readonly KnowledgeBaseEntryRepository $knowledgeBase,
        private readonly FindRelatedContentService $relatedContent,
    ) {}

    public function handle(ContentTopic $topic): GeneratedContentBriefData
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
            knowledgeBaseContext: $this->knowledgeContext(),
            existingPostContext: $this->existingPostContext($topic),
            internalLinkContext: [],
            editorialIntent: $topic->notes,
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
     * @return list<string>
     */
    private function knowledgeContext(): array
    {
        return $this->knowledgeBase->searchAdmin(new KnowledgeBaseEntryFiltersData(
            status: KnowledgeBaseEntry::STATUS_ACTIVE,
            sort: '-updated_at',
        ))
            ->take(10)
            ->map(function (KnowledgeBaseEntry $entry): ?string {
                $summary = trim((string) ($entry->summary ?? ''));
                $content = trim((string) ($entry->content_markdown ?? ''));
                $excerpt = $summary !== '' ? $summary : mb_substr($content, 0, 240);
                $excerpt = trim($excerpt);

                if ($excerpt === '') {
                    return null;
                }

                return "{$entry->title}: {$excerpt}";
            })
            ->filter(fn (?string $line): bool => is_string($line) && $line !== '')
            ->values()
            ->all();
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
