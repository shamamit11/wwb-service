<?php

namespace App\Modules\Ai\Services;

use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;

class ResolveAutoDraftGenerationDataService
{
    public function handle(ContentBrief $brief): ?QueueBlogDraftGenerationData
    {
        $topic = $brief->topic;
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
        $preferredSlug = $this->preferredCategorySlug($topic);

        if ($preferredSlug !== null) {
            $preferred = Category::query()
                ->where('is_active', true)
                ->where('slug', $preferredSlug)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if ($preferred instanceof Category) {
                return $preferred;
            }
        }

        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    private function preferredCategorySlug(?ContentTopic $topic): ?string
    {
        if (! $topic instanceof ContentTopic) {
            return null;
        }

        return match ($topic->cluster) {
            ContentTopic::CLUSTER_AI_TOOLS => 'ai-tools',
            ContentTopic::CLUSTER_AI_FOR_BLOGGING, ContentTopic::CLUSTER_CONTENT_MARKETING => 'content-marketing',
            ContentTopic::CLUSTER_SEO => 'seo',
            ContentTopic::CLUSTER_PRODUCTIVITY_AUTOMATION => 'productivity-automation',
            ContentTopic::CLUSTER_DEVELOPER_AI => 'developer-ai',
            default => null,
        };
    }
}
