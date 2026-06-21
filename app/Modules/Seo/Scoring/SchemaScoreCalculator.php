<?php

namespace App\Modules\Seo\Scoring;

use App\Models\Post;
use App\Modules\Seo\Services\GenerateSchemaPayloadService;

class SchemaScoreCalculator
{
    public function __construct(
        private readonly GenerateSchemaPayloadService $schemas,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function calculate(Post $post): array
    {
        $payload = $this->schemas->handle('post', $post->id);
        $graph = $payload['@graph'] ?? [];
        $types = array_values(array_filter(array_map(
            static fn (mixed $node): ?string => is_array($node) && is_string($node['@type'] ?? null) ? $node['@type'] : null,
            is_array($graph) ? $graph : [],
        )));

        $score = 0;
        $checks = [];
        $recommendations = [];
        $hasFaq = is_array($post->faq) && $post->faq !== [];

        if (array_intersect($types, ['Article', 'TechArticle'])) {
            $score += 10;
            $checks[] = $this->check('article_schema', true, 10, 'Article schema is present.');
        } else {
            $checks[] = $this->check('article_schema', false, 0, 'Article schema is missing.');
            $recommendations[] = 'Ensure article schema can be generated.';
        }

        if (in_array('BreadcrumbList', $types, true)) {
            $score += 5;
            $checks[] = $this->check('breadcrumb_schema', true, 5, 'Breadcrumb schema is present.');
        } else {
            $checks[] = $this->check('breadcrumb_schema', false, 0, 'Breadcrumb schema is missing.');
            $recommendations[] = 'Provide breadcrumb schema.';
        }

        if ($hasFaq && in_array('FAQPage', $types, true)) {
            $score += 5;
            $checks[] = $this->check('faq_schema', true, 5, 'FAQ schema matches the article content.');
        } elseif (! $hasFaq) {
            $score += 5;
            $checks[] = $this->check('faq_schema', true, 5, 'FAQ schema is optional because no FAQ content exists.');
        } else {
            $checks[] = $this->check('faq_schema', false, 0, 'FAQ content exists but FAQ schema is missing.');
            $recommendations[] = 'Map FAQ content into FAQ schema.';
        }

        return [
            'score' => $score,
            'max_score' => 20,
            'checks' => $checks,
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function check(string $key, bool $passed, int $points, string $message): array
    {
        return [
            'key' => $key,
            'passed' => $passed,
            'points' => $points,
            'message' => $message,
        ];
    }
}
