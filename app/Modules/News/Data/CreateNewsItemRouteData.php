<?php

namespace App\Modules\News\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateNewsItemRouteData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $route,
        public ?int $knowledgeBaseEntryId,
        public ?int $contentTopicId,
        public ?int $postId,
        public ?string $routedAt,
        public ?array $metadata = null,
    ) {}
}
