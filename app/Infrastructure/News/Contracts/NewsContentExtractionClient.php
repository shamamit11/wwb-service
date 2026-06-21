<?php

namespace App\Infrastructure\News\Contracts;

use App\Infrastructure\News\Data\ExtractedNewsContentData;

interface NewsContentExtractionClient
{
    public function extract(string $url): ExtractedNewsContentData;
}
