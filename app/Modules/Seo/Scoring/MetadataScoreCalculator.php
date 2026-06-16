<?php

namespace App\Modules\Seo\Scoring;

use App\Models\Post;

class MetadataScoreCalculator
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Post $post): array
    {
        $metadata = $post->seo;
        $score = 0;
        $checks = [];
        $recommendations = [];

        $titleLength = mb_strlen((string) ($metadata?->meta_title ?? ''));
        if ($titleLength >= 20 && $titleLength <= 70) {
            $score += 10;
            $checks[] = $this->check('meta_title', true, 10, 'Meta title is within a healthy length.');
        } elseif ($titleLength > 0) {
            $score += 5;
            $checks[] = $this->check('meta_title', true, 5, 'Meta title exists but should be closer to 20-70 characters.');
            $recommendations[] = 'Tighten the meta title length toward 20-70 characters.';
        } else {
            $checks[] = $this->check('meta_title', false, 0, 'Meta title is missing.');
            $recommendations[] = 'Add a focused meta title.';
        }

        $descriptionLength = mb_strlen((string) ($metadata?->meta_description ?? ''));
        if ($descriptionLength >= 50 && $descriptionLength <= 160) {
            $score += 10;
            $checks[] = $this->check('meta_description', true, 10, 'Meta description is within a healthy length.');
        } elseif ($descriptionLength > 0) {
            $score += 5;
            $checks[] = $this->check('meta_description', true, 5, 'Meta description exists but should be closer to 50-160 characters.');
            $recommendations[] = 'Refine the meta description length toward 50-160 characters.';
        } else {
            $checks[] = $this->check('meta_description', false, 0, 'Meta description is missing.');
            $recommendations[] = 'Add a meta description for search snippets.';
        }

        if (($metadata?->canonical_url ?? null) !== null) {
            $score += 5;
            $checks[] = $this->check('canonical_url', true, 5, 'Canonical URL is available.');
        } else {
            $checks[] = $this->check('canonical_url', false, 0, 'Canonical URL is missing.');
            $recommendations[] = 'Set or derive a canonical URL.';
        }

        if (($metadata?->focus_keyword ?? null) !== null) {
            $score += 5;
            $checks[] = $this->check('focus_keyword', true, 5, 'Focus keyword is defined.');
        } else {
            $checks[] = $this->check('focus_keyword', false, 0, 'Focus keyword is missing.');
            $recommendations[] = 'Define a focus keyword to align editorial intent.';
        }

        if (($metadata?->og_title ?? null) !== null && ($metadata?->og_description ?? null) !== null) {
            $score += 5;
            $checks[] = $this->check('open_graph', true, 5, 'Open Graph title and description are present.');
        } elseif (($metadata?->og_title ?? null) !== null || ($metadata?->og_description ?? null) !== null) {
            $score += 2;
            $checks[] = $this->check('open_graph', true, 2, 'Open Graph metadata is partial.');
            $recommendations[] = 'Complete the Open Graph title and description.';
        } else {
            $checks[] = $this->check('open_graph', false, 0, 'Open Graph metadata is missing.');
            $recommendations[] = 'Add Open Graph title and description.';
        }

        return [
            'score' => $score,
            'max_score' => 35,
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
