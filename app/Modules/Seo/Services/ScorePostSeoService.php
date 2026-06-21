<?php

namespace App\Modules\Seo\Services;

use App\Models\Post;
use App\Modules\Seo\Scoring\ContentScoreCalculator;
use App\Modules\Seo\Scoring\InternalLinkingScoreCalculator;
use App\Modules\Seo\Scoring\MetadataScoreCalculator;
use App\Modules\Seo\Scoring\SchemaScoreCalculator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScorePostSeoService
{
    public function __construct(
        private readonly ResolveSeoableTargetService $resolver,
        private readonly MetadataScoreCalculator $metadata,
        private readonly ContentScoreCalculator $content,
        private readonly SchemaScoreCalculator $schema,
        private readonly InternalLinkingScoreCalculator $internalLinking,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(string $seoableType, int $seoableId): array
    {
        [$seoable, $normalizedType] = $this->resolver->handle($seoableType, $seoableId);

        if ($normalizedType !== 'post' || ! $seoable instanceof Post) {
            throw new NotFoundHttpException('SEO score target not found.');
        }

        $post = $seoable->loadMissing(['author', 'category', 'tags', 'seo.ogImageMedia']);

        $subscores = [
            'metadata' => $this->metadata->calculate($post),
            'content' => $this->content->calculate($post),
            'schema' => $this->schema->calculate($post),
            'internal_linking' => $this->internalLinking->calculate($post),
        ];

        $totalScore = array_sum(array_map(
            static fn (array $subscore): int => (int) $subscore['score'],
            $subscores,
        ));

        $recommendations = [];
        foreach ($subscores as $subscore) {
            $recommendations = array_merge($recommendations, $subscore['recommendations']);
        }

        return [
            'seoable_type' => 'post',
            'seoable_id' => $post->id,
            'advisory' => true,
            'total_score' => $totalScore,
            'max_score' => 100,
            'grade' => $this->grade($totalScore),
            'subscores' => $subscores,
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    private function grade(int $score): string
    {
        return match (true) {
            $score >= 85 => 'excellent',
            $score >= 70 => 'good',
            $score >= 50 => 'needs_work',
            default => 'poor',
        };
    }
}
