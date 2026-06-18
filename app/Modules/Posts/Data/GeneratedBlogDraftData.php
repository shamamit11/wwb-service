<?php

namespace App\Modules\Posts\Data;

use App\Models\Post;
use App\Modules\Shared\Data\DataTransferObject;

final readonly class GeneratedBlogDraftData extends DataTransferObject
{
    public function __construct(
        public Post $post,
        public bool $wasGenerated,
    ) {}
}
