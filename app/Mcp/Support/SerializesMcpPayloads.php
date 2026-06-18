<?php

namespace App\Mcp\Support;

use App\Models\AiJob;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;

trait SerializesMcpPayloads
{
    /**
     * @param  array<int, mixed>  $values
     * @return list<string>
     */
    protected function normalizeStringList(array $values): array
    {
        return array_values(array_filter(
            array_map(static fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null, $values),
            static fn (?string $value): bool => $value !== null,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeKnowledgeBaseEntry(KnowledgeBaseEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'ulid' => $entry->ulid,
            'title' => $entry->title,
            'slug' => $entry->slug,
            'entry_type' => $entry->entry_type,
            'status' => $entry->status,
            'summary' => $entry->summary,
            'source_url' => $entry->source_url,
            'metadata' => is_array($entry->metadata) ? $entry->metadata : [],
            'updated_at' => $entry->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeTopic(ContentTopic $topic): array
    {
        return [
            'id' => $topic->id,
            'title' => $topic->title,
            'slug' => $topic->slug,
            'cluster' => $topic->cluster,
            'primary_keyword' => $topic->primary_keyword,
            'secondary_keywords' => $topic->secondary_keywords ?? [],
            'search_intent' => $topic->search_intent,
            'priority_score' => $topic->priority_score,
            'difficulty_note' => $topic->difficulty_note,
            'source' => $topic->source,
            'status' => $topic->status,
            'notes' => $topic->notes,
            'can_generate_content_brief' => $topic->canGenerateContentBrief(),
            'approved_at' => $topic->approved_at?->toISOString(),
            'rejected_at' => $topic->rejected_at?->toISOString(),
            'used_at' => $topic->used_at?->toISOString(),
            'created_at' => $topic->created_at?->toISOString(),
            'updated_at' => $topic->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeBrief(ContentBrief $brief): array
    {
        return [
            'id' => $brief->id,
            'content_topic_id' => $brief->content_topic_id,
            'title' => $brief->title,
            'slug' => $brief->slug,
            'meta_title' => $brief->meta_title,
            'meta_description' => $brief->meta_description,
            'primary_keyword' => $brief->primary_keyword,
            'secondary_keywords' => $brief->secondary_keywords ?? [],
            'search_intent' => $brief->search_intent,
            'outline' => $brief->outline ?? [],
            'headings' => $brief->headings ?? [],
            'faq_suggestions' => $brief->faq_suggestions ?? [],
            'internal_link_suggestions' => $brief->internal_link_suggestions ?? [],
            'image_suggestions' => $brief->image_suggestions ?? [],
            'status' => $brief->status,
            'can_generate_draft' => $brief->canGenerateDraft(),
            'approved_at' => $brief->approved_at?->toISOString(),
            'created_at' => $brief->created_at?->toISOString(),
            'updated_at' => $brief->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeAiJob(AiJob $job): array
    {
        $aggregateCost = $job->relationLoaded('costs')
            ? $job->costs->first(fn ($cost): bool => $cost->ai_generation_step_id === null)
            : null;

        return [
            'id' => $job->id,
            'type' => $job->type,
            'status' => $job->status,
            'entity_type' => $job->entity_type,
            'entity_id' => $job->entity_id,
            'provider' => $job->provider,
            'model' => $job->model,
            'input_payload' => $job->input_payload,
            'output_payload' => $job->output_payload,
            'usage_payload' => $job->usage_payload,
            'error_message' => $job->error_message,
            'attempts' => $job->attempts,
            'retry_of_ai_job_id' => $job->retry_of_ai_job_id,
            'can_retry' => $job->canRetry(),
            'steps_count' => $job->steps_count ?? ($job->relationLoaded('steps') ? $job->steps->count() : null),
            'cost_summary' => $aggregateCost ? [
                'input_tokens' => $aggregateCost->input_tokens,
                'output_tokens' => $aggregateCost->output_tokens,
                'total_tokens' => $aggregateCost->total_tokens,
                'estimated_cost' => $aggregateCost->estimated_cost,
                'actual_cost' => $aggregateCost->actual_cost,
                'currency' => $aggregateCost->currency,
            ] : null,
            'started_at' => $job->started_at?->toISOString(),
            'completed_at' => $job->completed_at?->toISOString(),
            'failed_at' => $job->failed_at?->toISOString(),
            'created_at' => $job->created_at?->toISOString(),
            'updated_at' => $job->updated_at?->toISOString(),
        ];
    }
}
