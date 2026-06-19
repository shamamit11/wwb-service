<?php

namespace App\Modules\Ai\Data;

use App\Models\Post;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class QueueBlogDraftGenerationData extends DataTransferObject
{
    public function __construct(
        public ?int $authorUserId,
        public int $categoryId,
        public ?int $templateId = null,
        public ?int $featuredMediaId = null,
        public string $visibility = Post::VISIBILITY_PUBLIC,
        public ?string $promptTemplateKey = null,
        public ?string $generationMode = null,
    ) {}
}
