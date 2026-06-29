<?php

namespace App\Modules\ArticleVideos\Data;

use App\Modules\ArticleVideos\Enums\ArticleVideoRenderMode;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateArticleVideoData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $scriptJson
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $captionsJson
     */
    public function __construct(
        public ?int $articleVideoRecommendationId,
        public ArticleVideoStatus $status,
        public ArticleVideoRenderMode $renderMode,
        public ?string $hook,
        public ?string $voiceoverText,
        public ?array $scriptJson,
        public ?array $captionsJson,
        public ?string $voiceoverPath,
        public ?string $captionsPath,
        public ?string $thumbnailPath,
        public ?string $videoPath,
        public ?int $durationSeconds,
        public ?string $approvedAt,
        public ?string $renderedAt,
        public ?string $errorMessage,
    ) {}
}
