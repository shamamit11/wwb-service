<?php

namespace App\Infrastructure\News\Contracts;

use App\Infrastructure\News\Data\DiscoveredNewsArticleData;
use App\Models\Category;

interface NewsDiscoveryClient
{
    /**
     * @return list<DiscoveredNewsArticleData>
     */
    public function search(Category $category, string $query, int $limit): array;
}
