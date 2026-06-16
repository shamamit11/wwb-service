<?php

namespace App\Modules\Seo\Scoring;

use App\Models\Post;
use App\Modules\Seo\Services\SuggestInternalLinksService;

class InternalLinkingScoreCalculator
{
    public function __construct(
        private readonly SuggestInternalLinksService $links,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function calculate(Post $post): array
    {
        $suggestions = $this->links->handleForPost($post, 5);
        $score = 0;
        $checks = [];
        $recommendations = [];

        if ($suggestions->isNotEmpty()) {
            $score += 5;
            $checks[] = $this->check('related_candidates', true, 5, 'Related content candidates were found.');
        } else {
            $checks[] = $this->check('related_candidates', false, 0, 'No related content candidates were found.');
            $recommendations[] = 'Increase topical overlap or supporting content to improve link opportunities.';
        }

        if ($suggestions->count() >= 2) {
            $score += 5;
            $checks[] = $this->check('link_suggestions', true, 5, 'Multiple internal link suggestions are available.');
        } elseif ($suggestions->count() === 1) {
            $score += 2;
            $checks[] = $this->check('link_suggestions', true, 2, 'Only one internal link suggestion is available.');
            $recommendations[] = 'Add more related content to widen link options.';
        } else {
            $checks[] = $this->check('link_suggestions', false, 0, 'No internal link suggestions are available.');
        }

        if ($post->tags->isNotEmpty() || $post->category !== null) {
            $score += 5;
            $checks[] = $this->check('topical_signals', true, 5, 'Category or tag signals support internal linking.');
        } else {
            $checks[] = $this->check('topical_signals', false, 0, 'Category and tag signals are too weak for internal linking.');
            $recommendations[] = 'Add category or tag signals to improve internal linking quality.';
        }

        return [
            'score' => $score,
            'max_score' => 15,
            'checks' => $checks,
            'recommendations' => array_values(array_unique($recommendations)),
            'suggestion_count' => $suggestions->count(),
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
