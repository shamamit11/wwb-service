<?php

namespace App\Modules\ContentTopics\Services;

use App\Models\ContentTopic;
use App\Modules\ContentTopics\Data\UpdateContentTopicData;
use App\Modules\ContentTopics\Exceptions\DuplicateContentTopicException;
use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Support\AuditActivityLogger;

class UpdateContentTopicService
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly ContentTopicSlugResolver $slugResolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentTopic $topic, UpdateContentTopicData $data): ContentTopic
    {
        $this->guardAgainstDuplicate($topic, $data);

        $old = $this->auditAttributes($topic);

        $updated = $this->topics->update($topic, new UpdateContentTopicData(
            title: $data->title,
            slug: $this->slugResolver->resolve($data->title, $data->slug, (int) $topic->id),
            cluster: $data->cluster,
            primaryKeyword: $data->primaryKeyword,
            secondaryKeywords: $data->secondaryKeywords,
            searchIntent: $data->searchIntent,
            priorityScore: $data->priorityScore,
            difficultyNote: $data->difficultyNote,
            source: $data->source,
            notes: $data->notes,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-topic.updated',
            event: 'updated',
            subject: $updated,
            attributes: $this->auditAttributes($updated),
            old: $old,
        );

        return $updated;
    }

    private function guardAgainstDuplicate(ContentTopic $topic, UpdateContentTopicData $data): void
    {
        if (! $this->topics->existsDuplicate($data->title, $data->cluster, $data->primaryKeyword, (int) $topic->id)) {
            return;
        }

        throw new DuplicateContentTopicException(
            title: $data->title,
            cluster: $data->cluster,
            message: "A similar topic already exists in the [{$data->cluster}] cluster.",
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
