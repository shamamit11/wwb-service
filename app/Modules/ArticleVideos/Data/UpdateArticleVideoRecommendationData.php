<?php

namespace App\Modules\ArticleVideos\Data;

use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationFormat;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationPriority;
use App\Modules\ArticleVideos\Enums\ArticleVideoRecommendationStatus;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateArticleVideoRecommendationData extends DataTransferObject
{
    public function __construct(
        public int $score,
        public ArticleVideoRecommendationPriority $priority,
        public ArticleVideoRecommendationFormat $recommendedFormat,
        public string $reason,
        public string $suggestedHook,
        public ?string $riskNote,
        public ArticleVideoRecommendationStatus $status,
        public ?string $evaluatedAt = null,
    ) {}
}
