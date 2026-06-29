<?php

namespace App\Modules\ArticleVideos\Enums;

enum ArticleVideoStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Rendering = 'rendering';
    case Rendered = 'rendered';
    case Failed = 'failed';
    case Rejected = 'rejected';
}
