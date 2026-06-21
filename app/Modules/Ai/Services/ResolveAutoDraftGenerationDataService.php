<?php

namespace App\Modules\Ai\Services;

use App\Models\Category;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;

class ResolveAutoDraftGenerationDataService
{
    public function handle(ContentTopic $topic): ?QueueBlogDraftGenerationData
    {
        $category = $this->resolveCategory($topic);

        if (! $category instanceof Category) {
            return null;
        }

        return new QueueBlogDraftGenerationData(
            authorUserId: null,
            categoryId: (int) $category->id,
        );
    }

    private function resolveCategory(?ContentTopic $topic): ?Category
    {
        if (! $topic instanceof ContentTopic) {
            return null;
        }

        return Category::query()
            ->where('is_active', true)
            ->whereKey($topic->category_id)
            ->first();
    }
}
