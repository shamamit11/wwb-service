<?php

namespace App\Modules\News\Repositories;

use App\Models\NewsItem;
use App\Models\NewsItemExtraction;
use App\Models\NewsItemRoute;
use App\Models\NewsItemScore;
use App\Modules\News\Data\CreateNewsItemExtractionData;
use App\Modules\News\Data\CreateNewsItemRouteData;
use App\Modules\News\Data\CreateNewsItemScoreData;
use App\Modules\News\Data\NewsItemFiltersData;
use App\Modules\News\Data\UpsertNewsItemData;
use Illuminate\Database\Eloquent\Collection;

class EloquentNewsItemRepository implements NewsItemRepository
{
    public function upsertDiscovered(UpsertNewsItemData $data): NewsItem
    {
        $existing = NewsItem::query()
            ->when(
                $data->externalId !== null && $data->externalId !== '',
                fn ($query) => $query->where('provider', $data->provider)->where('external_id', $data->externalId),
                function ($query) use ($data): void {
                    $query
                        ->when($data->canonicalUrl, fn ($inner) => $inner->where('canonical_url', $data->canonicalUrl))
                        ->orWhere('url', $data->url)
                        ->orWhere(function ($inner) use ($data): void {
                            $inner->where('normalized_title', $data->normalizedTitle)
                                ->where('category_id', $data->categoryId);
                        });
                },
            )
            ->latest('id')
            ->first();

        if ($existing instanceof NewsItem) {
            $existing->update([
                'source_id' => $data->sourceId,
                'category_id' => $data->categoryId,
                'publisher_name' => $data->publisherName,
                'title' => $data->title,
                'normalized_title' => $data->normalizedTitle,
                'url' => $data->url,
                'canonical_url' => $data->canonicalUrl,
                'description' => $data->description,
                'author' => $data->author,
                'language' => $data->language,
                'country' => $data->country,
                'published_at' => $data->publishedAt,
                'discovered_at' => $data->discoveredAt,
                'metadata' => $data->metadata,
            ]);

            return $this->refresh($existing);
        }

        return $this->refresh(NewsItem::query()->create([
            'external_id' => $data->externalId,
            'provider' => $data->provider,
            'source_id' => $data->sourceId,
            'category_id' => $data->categoryId,
            'publisher_name' => $data->publisherName,
            'title' => $data->title,
            'normalized_title' => $data->normalizedTitle,
            'url' => $data->url,
            'canonical_url' => $data->canonicalUrl,
            'description' => $data->description,
            'author' => $data->author,
            'language' => $data->language,
            'country' => $data->country,
            'published_at' => $data->publishedAt,
            'discovered_at' => $data->discoveredAt,
            'status' => $data->status,
            'metadata' => $data->metadata,
        ]));
    }

    public function findById(int $id): ?NewsItem
    {
        return NewsItem::query()
            ->with($this->relations())
            ->find($id);
    }

    public function markStatus(NewsItem $item, string $status, ?array $metadata = null): NewsItem
    {
        $item->update([
            'status' => $status,
            'metadata' => $metadata ?? $item->metadata,
        ]);

        return $this->refresh($item);
    }

    public function saveExtraction(NewsItem $item, CreateNewsItemExtractionData $data): NewsItemExtraction
    {
        return $item->extractions()->create([
            'extractor' => $data->extractor,
            'content_markdown' => $data->contentMarkdown,
            'content_text' => $data->contentText,
            'excerpt' => $data->excerpt,
            'facts_json' => $data->factsJson,
            'entities_json' => $data->entitiesJson,
            'claims_json' => $data->claimsJson,
            'extracted_at' => $data->extractedAt,
            'metadata' => $data->metadata,
        ]);
    }

    public function saveScore(NewsItem $item, CreateNewsItemScoreData $data): NewsItemScore
    {
        return $item->scores()->create([
            'relevance_score' => $data->relevanceScore,
            'freshness_score' => $data->freshnessScore,
            'credibility_score' => $data->credibilityScore,
            'pillar_fit_score' => $data->pillarFitScore,
            'evergreen_potential_score' => $data->evergreenPotentialScore,
            'novelty_score' => $data->noveltyScore,
            'business_value_score' => $data->businessValueScore,
            'total_score' => $data->totalScore,
            'decision' => $data->decision,
            'reasoning' => $data->reasoning,
            'scored_at' => $data->scoredAt,
        ]);
    }

    public function saveRoute(NewsItem $item, CreateNewsItemRouteData $data): NewsItemRoute
    {
        return $item->routes()->create([
            'route' => $data->route,
            'knowledge_base_entry_id' => $data->knowledgeBaseEntryId,
            'content_topic_id' => $data->contentTopicId,
            'post_id' => $data->postId,
            'routed_at' => $data->routedAt,
            'metadata' => $data->metadata,
        ]);
    }

    /**
     * @return Collection<int, NewsItem>
     */
    public function searchAdmin(NewsItemFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return NewsItem::query()
            ->with($this->relations())
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('normalized_title', 'like', "%{$search}%")
                        ->orWhere('publisher_name', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('canonical_url', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->categoryId, fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters->decision, fn ($query, string $decision) => $query->whereHas('latestScore', fn ($inner) => $inner->where('decision', $decision)))
            ->when($filters->route, fn ($query, string $route) => $query->whereHas('latestRoute', fn ($inner) => $inner->where('route', $route)))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, NewsItem>
     */
    public function latestByStatus(string $status, int $limit = 50): Collection
    {
        return NewsItem::query()
            ->with($this->relations())
            ->where('status', $status)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();
    }

    private function refresh(NewsItem $item): NewsItem
    {
        return $item->refresh()->load($this->relations());
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['source', 'category', 'latestExtraction', 'latestScore', 'latestRoute.knowledgeBaseEntry', 'latestRoute.contentTopic', 'latestRoute.post'];
    }

    /**
     * @return array{0:string,1:bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['published_at', 'discovered_at', 'created_at', 'updated_at', 'title'];

        if (! in_array($field, $allowed, true)) {
            return ['published_at', true];
        }

        return [$field, $descending];
    }
}
