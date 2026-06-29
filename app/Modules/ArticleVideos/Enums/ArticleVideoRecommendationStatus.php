<?php

namespace App\Modules\ArticleVideos\Enums;

enum ArticleVideoRecommendationStatus: string
{
    case Pending = 'pending';
    case Selected = 'selected';
    case Skipped = 'skipped';
    case Rejected = 'rejected';
}
