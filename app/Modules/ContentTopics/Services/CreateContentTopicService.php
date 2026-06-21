<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Exceptions\DuplicateContentTopicException;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\AuditActivityLogger;

class CreateContentTopicService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly ContentTopicSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
        private readonly AutoAdvanceHighPriorityTopicService $autoAdvanceHighPriorityTopic,
    ) {}

    public function handle(CreateContentTopicData $data): ContentTopic
    {
        $this->guardAgainstDuplicate($data->title, $data->categoryId, $data->primaryKeyword);

        $topic = $this->topics->create(new CreateContentTopicData(
            categoryId: $data->categoryId,
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug),
            cluster: $data->cluster,
            primaryKeyword: $data->primaryKeyword,
            secondaryKeywords: $data->secondaryKeywords,
            searchIntent: $data->searchIntent,
            priorityScore: $data->priorityScore,
            scoreBreakdown: $data->scoreBreakdown,
            difficultyNote: $data->difficultyNote,
            source: $data->source,
            status: $data->status,
            notes: $data->notes,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-topic.created',
            event: 'created',
            subject: $topic,
            attributes: $this->auditAttributes($topic),
        );

        return $this->autoAdvanceHighPriorityTopic->handle($topic);
    }

    private function guardAgainstDuplicate(string $title, int $categoryId, ?string $primaryKeyword): void
    {
        if (! $this->topics->existsDuplicate($title, $categoryId, $primaryKeyword)) {
            return;
        }

        throw new DuplicateContentTopicException(
            title: $title,
            cluster: (string) $categoryId,
            message: 'A similar topic already exists in this category.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function auditAttributes(ContentTopic $topic): array
    {
        return [
            'category_id' => $topic->category_id,
            'title' => $topic->title,
            'slug' => $topic->slug,
            'cluster' => $topic->cluster,
            'primary_keyword' => $topic->primary_keyword,
            'status' => $topic->status,
            'source' => $topic->source,
        ];
    }
}
