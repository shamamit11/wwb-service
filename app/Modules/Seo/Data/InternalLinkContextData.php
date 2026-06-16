<?php

namespace App\Modules\Seo\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class InternalLinkContextData extends DataTransferObject
{
    /**
     * @param  list<string>  $tagNames
     */
    public function __construct(
        public string $title,
        public ?string $excerpt = null,
        public ?int $categoryId = null,
        public array $tagNames = [],
        public ?string $focusKeyword = null,
        public ?int $excludePostId = null,
    ) {}
}
