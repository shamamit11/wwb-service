<?php

namespace App\Modules\Newsletter\Data;

readonly class UpdateNewsletterListData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public string $status,
    ) {}
}
