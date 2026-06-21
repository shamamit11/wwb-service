<?php

namespace App\Modules\News\Services;

use App\Infrastructure\News\Contracts\NewsContentExtractionClient;
use App\Models\NewsItem;
use App\Modules\News\Data\CreateNewsItemExtractionData;
use App\Modules\News\Repositories\NewsItemRepository;
use Illuminate\Support\Str;

class ExtractNewsItemContentService
{
    public function __construct(
        private readonly NewsContentExtractionClient $client,
        private readonly NewsItemRepository $items,
    ) {}

    public function handle(NewsItem $item): void
    {
        $result = $this->client->extract($item->canonical_url ?? $item->url);
        $entities = $result->entities ?? [];
        $entities['publisher_name'] = $item->publisher_name;
        $entities['category_slug'] = $item->category?->slug;

        $facts = array_filter([
            ...($result->facts ?? []),
            'news_item_id' => (int) $item->id,
            'published_at' => $item->published_at?->toISOString(),
            'author' => $item->author,
        ], static fn (mixed $value): bool => $value !== null);

        $claims = $result->claims;

        if ($claims === null && $result->contentText !== null) {
            $claims = array_values(array_filter(array_map(
                static fn (string $sentence): ?string => ($normalized = trim($sentence)) !== '' ? Str::limit($normalized, 220, '') : null,
                array_slice(preg_split('/(?<=[.!?])\s+/', $result->contentText) ?: [], 0, 5),
            )));
        }

        $this->items->saveExtraction($item, new CreateNewsItemExtractionData(
            extractor: 'firecrawl',
            contentMarkdown: $result->contentMarkdown,
            contentText: $result->contentText,
            excerpt: $result->excerpt,
            factsJson: $facts === [] ? null : $facts,
            entitiesJson: $entities === [] ? null : $entities,
            claimsJson: is_array($claims) && $claims !== [] ? ['items' => $claims] : null,
            extractedAt: now()->toDateTimeString(),
            metadata: $result->metadata,
        ));

        $this->items->markStatus($item, NewsItem::STATUS_EXTRACTED);
    }
}
