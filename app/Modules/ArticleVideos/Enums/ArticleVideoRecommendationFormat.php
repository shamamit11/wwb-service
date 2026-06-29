<?php

namespace App\Modules\ArticleVideos\Enums;

enum ArticleVideoRecommendationFormat: string
{
    case Explainer = 'explainer';
    case Checklist = 'checklist';
    case Opinion = 'opinion';
    case NewsPulse = 'news_pulse';
}
