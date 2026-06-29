<?php

namespace App\Modules\ArticleVideos\Data;

use App\Modules\ArticleVideos\Enums\ArticleVideoRenderMode;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateArticleVideoData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $scriptJson
     * @param  array<string, mixed>|list<array<string, mixed>>|null  $captionsJson
     */
    public function __construct(
        public int $postId,
        public ?int $articleVideoRecommendationId,
        public ArticleVideoStatus $status = ArticleVideoStatus::Draft,
        public ArticleVideoRenderMode $renderMode = ArticleVideoRenderMode::TextOnly,
        public ?string $hook = null,
        public ?string $voiceoverText = null,
        public ?array $scriptJson = null,
        public ?array $captionsJson = null,
        public ?string $voiceoverPath = null,
        public ?string $captionsPath = null,
        public ?string $thumbnailPath = null,
        public ?string $videoPath = null,
        public ?int $durationSeconds = null,
        public ?string $approvedAt = null,
        public ?string $renderedAt = null,
        public ?string $errorMessage = null,
    ) {}
}
