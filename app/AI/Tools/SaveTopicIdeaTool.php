<?php

namespace App\AI\Tools;

use App\AI\DTO\TopicSuggestionData;
use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Services\CreateContentTopicService;

class SaveTopicIdeaTool
{
    public function __construct(
        private readonly CreateContentTopicService $createTopic,
    ) {}

    public function save(TopicSuggestionData $topic, int $categoryId, ?string $audience = null): ContentTopic
    {
        return $this->createTopic->handle(new CreateContentTopicData(
            categoryId: $categoryId,
            title: $topic->title,
            slug: $topic->slug,
            cluster: $topic->cluster,
            primaryKeyword: $topic->primaryKeyword,
            secondaryKeywords: $topic->secondaryKeywords,
            searchIntent: $topic->searchIntent,
            priorityScore: $topic->priorityScore,
            scoreBreakdown: $topic->scoreBreakdown,
            difficultyNote: $topic->difficultyNote,
            source: ContentTopic::SOURCE_AI_SUGGESTED,
            status: ContentTopic::STATUS_SUGGESTED,
            notes: $this->buildNotes($topic, $audience),
        ));
    }

    private function buildNotes(TopicSuggestionData $topic, ?string $audience): ?string
    {
        $parts = array_values(array_filter([
            $topic->summary ? "AI summary: {$topic->summary}" : null,
            $audience ? "Audience: {$audience}" : null,
        ]));

        if ($parts === []) {
            return null;
        }

        return implode("\n\n", $parts);
    }
}
