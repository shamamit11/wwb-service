<?php

namespace App\Modules\ArticleVideos\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateArticleVideoMemoryEntryData extends DataTransferObject
{
    public function __construct(
        public int $postId,
        public ?int $articleVideoId,
        public string $hook,
        public ?string $coreAngle,
        public string $voiceoverText,
    ) {}
}
