<?php

namespace App\Modules\Templates\Services;

use App\Models\Template;
use App\Modules\Templates\Data\TemplatePayloadContextData;

class BuildTemplatePreviewPayloadService
{
    public function __construct(
        private readonly TemplatePayloadFactory $payloadFactory,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Template $template, TemplatePayloadContextData $context): array
    {
        return $this->payloadFactory->buildPreviewPayload($template, $context);
    }
}
