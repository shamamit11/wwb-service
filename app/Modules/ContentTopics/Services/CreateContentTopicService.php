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
        $this->guardAgainstDuplicate($data->title, $data->cluster, $data->primaryKeyword);

        $topic = $this->topics->create(new CreateContentTopicData(
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug),
            cluster: $data->cluster,
            primaryKeyword: $data->primaryKeyword,
            secondaryKeywords: $data->secondaryKeywords,
            searchIntent: $data->searchIntent,
            priorityScore: $data->priorityScore,
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

    private function guardAgainstDuplicate(string $title, string $cluster, ?string $primaryKeyword): void
    {
        if (! $this->topics->existsDuplicate($title, $cluster, $primaryKeyword)) {
            return;
        }

        throw new DuplicateContentTopicException(
            title: $title,
            cluster: $cluster,
            message: "A similar topic already exists in the [{$cluster}] cluster.",
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function auditAttributes(ContentTopic $topic): array
    {
        return [
            'title' => $topic->title,
            'slug' => $topic->slug,
            'cluster' => $topic->cluster,
            'primary_keyword' => $topic->primary_keyword,
            'status' => $topic->status,
            'source' => $topic->source,
        ];
    }
}
