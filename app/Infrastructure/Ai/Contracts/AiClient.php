<?php

namespace App\Infrastructure\Ai\Contracts;

use App\Infrastructure\Ai\Data\GenerateTextRequest;
use App\Infrastructure\Ai\Data\TextGenerationResult;

interface AiClient
{
    public function generateText(GenerateTextRequest $request): TextGenerationResult;
}
