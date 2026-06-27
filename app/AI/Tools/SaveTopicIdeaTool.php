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
        $discoveryMetadata = $topic->discoveryMetadata;

        if ($audience !== null && $audience !== '') {
            $discoveryMetadata['audience'] = $audience;
        }

        if ($topic->summary !== null && $topic->summary !== '') {
            $discoveryMetadata['summary'] = $topic->summary;
        }

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
            discoveryMetadata: $discoveryMetadata !== [] ? $discoveryMetadata : null,
            difficultyNote: $topic->difficultyNote,
            source: ContentTopic::SOURCE_AI_SUGGESTED,
            status: ContentTopic::STATUS_SUGGESTED,
        ));
    }
}
