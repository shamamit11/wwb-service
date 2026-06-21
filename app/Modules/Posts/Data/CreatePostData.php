<?php

namespace App\Modules\Posts\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreatePostData extends DataTransferObject
{
    /**
     * @param  list<int>  $tagIds
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public int $authorUserId,
        public int $categoryId,
        public ?int $featuredMediaId,
        public string $title,
        public string $slug,
        public ?string $shortDescription,
        public ?string $description,
        public ?string $fullArticleMarkdown,
        public ?string $fullArticleHtml,
        public ?array $faq,
        public string $status,
        public string $visibility,
        public ?string $publishedAt,
        public ?array $meta,
        public array $tagIds = [],
    ) {}
}
