<?php

namespace App\Modules\News\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateNewsItemScoreData extends DataTransferObject
{
    public function __construct(
        public int $relevanceScore,
        public int $freshnessScore,
        public int $credibilityScore,
        public int $pillarFitScore,
        public int $evergreenPotentialScore,
        public int $noveltyScore,
        public int $businessValueScore,
        public int $totalScore,
        public string $decision,
        public ?string $reasoning,
        public ?string $scoredAt,
    ) {}
}
