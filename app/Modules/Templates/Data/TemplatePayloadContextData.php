<?php

namespace App\Modules\Templates\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class TemplatePayloadContextData extends DataTransferObject
{
    public function __construct(
        public ?string $title = null,
        public ?string $topic = null,
    ) {}
}
