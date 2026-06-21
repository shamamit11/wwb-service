<?php

namespace App\Modules\News\Services;

use App\Infrastructure\News\Contracts\NewsDiscoveryClient;
use App\Models\Category;
use App\Models\NewsItem;
use App\Modules\News\Data\CreateNewsSourceData;
use App\Modules\News\Data\UpsertNewsItemData;
use App\Modules\News\Repositories\NewsItemRepository;
use App\Modules\News\Repositories\NewsSourceRepository;
use Illuminate\Support\Str;

class NewsDiscoveryService
{
    public function __construct(
        private readonly NewsDiscoveryClient $client,
        private readonly NewsSourceRepository $sources,
        private readonly NewsItemRepository $items,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     * @return list<NewsItem>
     */
    public function handle(Category $category, ?int $limit = null, array $metadata = []): array
    {
        $queries = config("news.discovery.category_queries.{$category->slug}", []);

        if (! is_array($queries) || $queries === []) {
            return [];
        }

        $targetLimit = max(1, $limit ?? (int) config('news.discovery.default_limit', 10));
        $perQuery = max(1, (int) ceil($targetLimit / max(1, count($queries))));
        $discovered = [];

        foreach ($queries as $query) {
            if (! is_string($query) || trim($query) === '') {
                continue;
            }

            foreach ($this->client->search($category, $query, $perQuery) as $article) {
                $sourceId = null;

                if ($article->publisherName !== null && $article->publisherName !== '') {
                    $source = $this->sources->upsert(new CreateNewsSourceData(
                        name: $article->publisherName,
                        slug: Str::slug($article->publisherName),
                        baseUrl: $article->publisherDomain !== null ? 'https://'.$article->publisherDomain : null,
                        trustScore: number_format($this->resolveTrustScore($article->publisherName), 2, '.', ''),
                        metadata: ['provider' => $article->provider],
                    ));

                    $sourceId = (int) $source->id;
                }

                $item = $this->items->upsertDiscovered(new UpsertNewsItemData(
                    externalId: $article->externalId,
                    provider: $article->provider,
                    sourceId: $sourceId,
                    categoryId: (int) $category->id,
                    publisherName: $article->publisherName,
                    title: $article->title,
                    normalizedTitle: Str::lower(Str::squish($article->title)),
                    url: $article->url,
                    canonicalUrl: $article->canonicalUrl,
                    description: $article->description,
                    author: $article->author,
                    language: $article->language,
                    country: $article->country,
                    publishedAt: $article->publishedAt,
                    discoveredAt: now()->toDateTimeString(),
                    metadata: array_filter([
                        ...($article->metadata ?? []),
                        'query' => $query,
                        'trigger_metadata' => $metadata,
                    ], static fn (mixed $value): bool => $value !== null),
                ));

                $discovered[(string) $item->id] = $item;

                if (count($discovered) >= $targetLimit) {
                    break 2;
                }
            }
        }

        return array_values($discovered);
    }

    private function resolveTrustScore(string $publisherName): int
    {
        $trusted = config('news.scoring.trusted_publishers', []);

        if (! is_array($trusted)) {
            return 0;
        }

        foreach ($trusted as $name => $score) {
            if (Str::lower($name) === Str::lower($publisherName)) {
                return (int) $score;
            }
        }

        return 8;
    }
}
