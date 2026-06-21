<?php

namespace App\Modules\Ai\Services;

use App\Models\Category;
use App\Models\ContentTopic;

class ResolveTopicDiscoveryClusterService
{
    public function forCategory(Category $category): ?string
    {
        return match ($category->slug) {
            'ai-tools' => ContentTopic::CLUSTER_AI_TOOLS,
            'ai-agents' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'seo' => ContentTopic::CLUSTER_SEO,
            'content-marketing' => ContentTopic::CLUSTER_CONTENT_MARKETING,
            'productivity-automation' => ContentTopic::CLUSTER_PRODUCTIVITY_AUTOMATION,
            'developer-ai' => ContentTopic::CLUSTER_DEVELOPER_AI,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public function supportedCategorySlugs(): array
    {
        return [
            'ai-tools',
            'ai-agents',
            'seo',
            'content-marketing',
            'productivity-automation',
            'developer-ai',
        ];
    }
}
