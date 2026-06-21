<?php

namespace App\Modules\News\Services;

use App\Models\KnowledgeBaseEntry;
use App\Models\NewsItem;
use App\Models\NewsItemExtraction;
use App\Models\User;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use App\Modules\KnowledgeBase\Services\CreateKnowledgeBaseEntryService;
use Illuminate\Support\Str;

class GenerateKnowledgeBaseFromNewsService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly CreateKnowledgeBaseEntryService $createEntry,
    ) {}

    public function handle(NewsItem $item, ?NewsItemExtraction $extraction): KnowledgeBaseEntry
    {
        $existing = KnowledgeBaseEntry::query()
            ->where('source_url', $item->canonical_url ?? $item->url)
            ->latest('id')
            ->first();

        if ($existing instanceof KnowledgeBaseEntry) {
            return $existing;
        }

        $authorId = $this->resolveAuthorUserId();
        $summary = $extraction?->excerpt ?? $item->description ?? Str::limit($item->title, 220);
        $content = trim(implode("\n\n", array_filter([
            "# {$item->title}",
            $summary,
            $extraction?->content_markdown,
        ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '')));

        return $this->createEntry->handle(new CreateKnowledgeBaseEntryData(
            createdByUserId: $authorId,
            updatedByUserId: null,
            title: $item->title,
            slug: Str::slug($item->title),
            entryType: KnowledgeBaseEntry::TYPE_REFERENCE,
            status: KnowledgeBaseEntry::STATUS_ACTIVE,
            summary: $summary,
            contentMarkdown: $content,
            sourceUrl: $item->canonical_url ?? $item->url,
            featuredMediaId: null,
            metadata: [
                'news_item_id' => (int) $item->id,
                'publisher_name' => $item->publisher_name,
                'published_at' => $item->published_at?->toISOString(),
            ],
        ));
    }

    private function resolveAuthorUserId(): int
    {
        return (int) (User::query()->where('is_admin', true)->value('id')
            ?? User::query()->value('id')
            ?? 1);
    }
}
