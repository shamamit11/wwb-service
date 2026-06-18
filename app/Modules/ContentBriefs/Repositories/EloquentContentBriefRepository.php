<?php

namespace App\Modules\ContentBriefs\Repositories;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\ContentBriefFiltersData;
use App\Modules\ContentBriefs\Data\CreateContentBriefData;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use Illuminate\Database\Eloquent\Collection;

class EloquentContentBriefRepository implements ContentBriefRepository
{
    public function create(CreateContentBriefData $data): ContentBrief
    {
        return ContentBrief::query()->create([
            'content_topic_id' => $data->contentTopicId,
            'title' => $data->title,
            'slug' => $data->slug,
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'primary_keyword' => $data->primaryKeyword,
            'secondary_keywords' => $data->secondaryKeywords,
            'search_intent' => $data->searchIntent,
            'outline' => $data->outline,
            'headings' => $data->headings,
            'faq_suggestions' => $data->faqSuggestions,
            'internal_link_suggestions' => $data->internalLinkSuggestions,
            'image_suggestions' => $data->imageSuggestions,
            'status' => $data->status,
            'approved_at' => $data->approvedAt,
        ])->refresh()->load('topic');
    }

    public function update(ContentBrief $brief, UpdateContentBriefData $data): ContentBrief
    {
        $brief->update([
            'title' => $data->title,
            'slug' => $data->slug,
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'primary_keyword' => $data->primaryKeyword,
            'secondary_keywords' => $data->secondaryKeywords,
            'search_intent' => $data->searchIntent,
            'outline' => $data->outline,
            'headings' => $data->headings,
            'faq_suggestions' => $data->faqSuggestions,
            'internal_link_suggestions' => $data->internalLinkSuggestions,
            'image_suggestions' => $data->imageSuggestions,
            'status' => $data->status,
            'approved_at' => $data->status === ContentBrief::STATUS_APPROVED
                ? ($brief->approved_at?->toDateTimeString() ?? now()->toDateTimeString())
                : ($data->status !== null ? null : $brief->approved_at?->toDateTimeString()),
        ]);

        return $brief->refresh()->load('topic');
    }

    public function findById(int $id): ?ContentBrief
    {
        return ContentBrief::query()->with('topic')->find($id);
    }

    public function findByTopicId(int $contentTopicId): ?ContentBrief
    {
        return ContentBrief::query()
            ->with('topic')
            ->where('content_topic_id', $contentTopicId)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return ContentBrief::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, ContentBrief>
     */
    public function search(ContentBriefFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return ContentBrief::query()
            ->with('topic')
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('meta_title', 'like', "%{$search}%")
                        ->orWhere('primary_keyword', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->contentTopicId, fn ($query, int $contentTopicId) => $query->where('content_topic_id', $contentTopicId))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array{0:string,1:bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['created_at', 'updated_at', 'approved_at', 'title'];

        if (! in_array($field, $allowed, true)) {
            return ['created_at', true];
        }

        return [$field, $descending];
    }
}
