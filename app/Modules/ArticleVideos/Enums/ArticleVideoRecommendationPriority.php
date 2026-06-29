<?php

namespace App\Modules\ArticleVideos\Enums;

enum ArticleVideoRecommendationPriority: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
