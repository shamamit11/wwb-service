<?php

namespace App\Modules\ArticleVideos\Data;

use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationFormat;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationPriority;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class RefreshPendingArticleVideoRecommendationData extends DataTransferObject
{
    public function __construct(
        public int $postId,
        public int $score,
        public ArticleVideoRecommendationPriority $priority,
        public ArticleVideoRecommendationFormat $recommendedFormat,
        public string $reason,
        public string $suggestedHook,
        public ?string $riskNote,
        public ?string $evaluatedAt = null,
    ) {}
}
