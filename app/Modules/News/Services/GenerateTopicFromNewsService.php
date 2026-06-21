<?php

namespace App\Modules\News\Services;

use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\NewsItem;
use App\Models\NewsItemScore;
use App\Modules\Ai\Services\ResolveTopicDiscoveryClusterService;
use App\Modules\ContentTopics\Data\CreateContentTopicData;
use App\Modules\ContentTopics\Services\CreateContentTopicService;

class GenerateTopicFromNewsService
{
    public function __construct(
        private readonly CreateContentTopicService $createTopic,
        private readonly ResolveTopicDiscoveryClusterService $clusters,
    ) {}

    public function handle(NewsItem $item, NewsItemScore $score): ?ContentTopic
    {
        $category = $item->category;

        if (! $category instanceof Category) {
            return null;
        }

        $existing = ContentTopic::query()
            ->where('category_id', $category->id)
            ->whereRaw('LOWER(title) = ?', [mb_strtolower(trim($item->title))])
            ->latest('id')
            ->first();

        if ($existing instanceof ContentTopic) {
            return $existing;
        }

        $cluster = $this->clusters->forCategory($category);

        if (! is_string($cluster) || $cluster === '') {
            return null;
        }

        return $this->createTopic->handle(new CreateContentTopicData(
            categoryId: (int) $category->id,
            title: $item->title,
            slug: null,
            cluster: $cluster,
            primaryKeyword: $this->resolvePrimaryKeyword($item),
            secondaryKeywords: [],
            searchIntent: 'informational',
            priorityScore: (string) $score->total_score,
            difficultyNote: $item->description,
            source: ContentTopic::SOURCE_NEWS_SIGNAL,
            status: ContentTopic::STATUS_SUGGESTED,
            notes: $this->buildNotes($item),
        ));
    }

    private function resolvePrimaryKeyword(NewsItem $item): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strtolower($item->title)));
    }

    private function buildNotes(NewsItem $item): string
    {
        return implode("\n\n", array_filter([
            $item->description ? "News summary: {$item->description}" : null,
            ($item->canonical_url ?? $item->url) !== '' ? 'Source URL: '.($item->canonical_url ?? $item->url) : null,
            $item->publisher_name ? "Publisher: {$item->publisher_name}" : null,
        ]));
    }
}
