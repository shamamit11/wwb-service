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

interface NewsItemRepository
{
    public function upsertDiscovered(UpsertNewsItemData $data): NewsItem;

    public function findById(int $id): ?NewsItem;

    public function markStatus(NewsItem $item, string $status, ?array $metadata = null): NewsItem;

    public function saveExtraction(NewsItem $item, CreateNewsItemExtractionData $data): NewsItemExtraction;

    public function saveScore(NewsItem $item, CreateNewsItemScoreData $data): NewsItemScore;

    public function saveRoute(NewsItem $item, CreateNewsItemRouteData $data): NewsItemRoute;

    /**
     * @return Collection<int, NewsItem>
     */
    public function searchAdmin(NewsItemFiltersData $filters): Collection;

    /**
     * @return Collection<int, NewsItem>
     */
    public function latestByStatus(string $status, int $limit = 50): Collection;
}
