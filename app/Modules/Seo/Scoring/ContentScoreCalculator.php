<?php

namespace App\Modules\Seo\Scoring;

use App\Models\Post;

class ContentScoreCalculator
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Post $post): array
    {
        $score = 0;
        $checks = [];
        $recommendations = [];
        $focusKeyword = mb_strtolower((string) ($post->seo?->focus_keyword ?? ''));
        $hasFocusKeyword = $focusKeyword !== '';
        $markdown = trim((string) ($post->full_article_markdown ?? ''));
        $wordCount = $this->wordCount($markdown);
        $hasHeading = preg_match('/^##?\s+/m', $markdown) === 1;
        $hasFaq = is_array($post->faq) && $post->faq !== [];

        if (($post->short_description ?? null) !== null) {
            $score += 5;
            $checks[] = $this->check('short_description', true, 5, 'Short description is present.');
        } else {
            $checks[] = $this->check('short_description', false, 0, 'Short description is missing.');
            $recommendations[] = 'Add a concise short description.';
        }

        if ($wordCount >= 600) {
            $score += 10;
            $checks[] = $this->check('word_count', true, 10, 'Word count is strong for editorial depth.');
        } elseif ($wordCount >= 300) {
            $score += 6;
            $checks[] = $this->check('word_count', true, 6, 'Word count is acceptable but could be deeper.');
            $recommendations[] = 'Expand the article depth beyond 600 words if appropriate.';
        } elseif ($wordCount > 0) {
            $score += 2;
            $checks[] = $this->check('word_count', true, 2, 'Word count is low for a complete article.');
            $recommendations[] = 'Increase the article depth.';
        } else {
            $checks[] = $this->check('word_count', false, 0, 'Article body is missing or empty.');
            $recommendations[] = 'Add enough content to support search intent.';
        }

        if ($hasHeading) {
            $score += 5;
            $checks[] = $this->check('headings', true, 5, 'Markdown heading structure is present.');
        } else {
            $checks[] = $this->check('headings', false, 0, 'No markdown headings were found.');
            $recommendations[] = 'Add heading structure to the article.';
        }

        if ($hasFaq) {
            $score += 5;
            $checks[] = $this->check('faq', true, 5, 'FAQ content is available.');
        } else {
            $checks[] = $this->check('faq', false, 0, 'FAQ content is not present.');
            $recommendations[] = 'Consider adding FAQ content when it fits the topic.';
        }

        if ($hasFocusKeyword && str_contains(mb_strtolower($post->title), $focusKeyword)) {
            $score += 5;
            $checks[] = $this->check('keyword_alignment', true, 5, 'Title aligns with the focus keyword.');
        } elseif ($hasFocusKeyword) {
            $checks[] = $this->check('keyword_alignment', false, 0, 'Title does not clearly align with the focus keyword.');
            $recommendations[] = 'Align the title more clearly with the focus keyword.';
        } else {
            $checks[] = $this->check('keyword_alignment', false, 0, 'Focus keyword is unavailable for alignment checks.');
        }

        return [
            'score' => $score,
            'max_score' => 30,
            'checks' => $checks,
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    private function wordCount(string $markdown): int
    {
        if ($markdown === '') {
            return 0;
        }

        preg_match_all('/\pL[\pL\pN\'_-]*/u', strip_tags($markdown), $matches);

        return count($matches[0]);
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
