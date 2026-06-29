<?php

namespace App\Modules\Posts\Services;

use App\Models\Post;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class AssessPostOriginalityService
{
    private const MAX_COMPARISON_POSTS = 25;

    private const MIN_SENTENCE_WORDS = 8;

    private const MIN_SENTENCE_CHARACTERS = 60;

    private const MAX_MATCH_SNIPPETS = 5;

    private const FLAGGED_MATCH_COUNT = 2;

    private const FLAGGED_OVERLAP_RATIO = 0.2;

    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function handle(Post $post): Post
    {
        $meta = $post->getAttributeValue('meta');
        $meta = is_array($meta) ? $meta : [];
        $review = $this->buildReview($post);

        $meta['needs_originality_review'] = $review['needs_originality_review'];
        $meta['originality_review'] = $review['originality_review'];

        return $this->posts->updateMeta($post, $meta);
    }

    /**
     * @return array{needs_originality_review: bool, originality_review: array<string, mixed>}
     */
    private function buildReview(Post $post): array
    {
        $sentences = $this->extractComparableSentences($post->full_article_html);

        if ($sentences === []) {
            return [
                'needs_originality_review' => false,
                'originality_review' => [
                    'status' => 'skipped',
                    'reason' => 'missing_comparable_content',
                    'checked_at' => now()->toISOString(),
                    'matched_post_ids' => [],
                    'matched_sentences' => [],
                    'matched_sentence_count' => 0,
                    'total_sentence_count' => 0,
                    'overlap_ratio' => 0.0,
                    'flag_reasons' => [],
                ],
            ];
        }

        $candidatePosts = $this->posts->getOriginalityComparisonCandidates(
            post: $post,
            keywords: $this->extractKeywords($post),
            limit: self::MAX_COMPARISON_POSTS,
        );

        [$matchedSentenceMap, $matchedPostIds, $flagReasons] = $this->compareAgainstCandidates($post, $sentences, $candidatePosts);

        $matchedSentences = array_keys($matchedSentenceMap);
        $matchedSentenceCount = count($matchedSentences);
        $totalSentenceCount = count($sentences);
        $overlapRatio = round($matchedSentenceCount / $totalSentenceCount, 4);

        $needsReview = $matchedSentenceCount >= self::FLAGGED_MATCH_COUNT
            || $overlapRatio >= self::FLAGGED_OVERLAP_RATIO
            || $flagReasons !== [];

        return [
            'needs_originality_review' => $needsReview,
            'originality_review' => [
                'status' => $needsReview ? 'flagged' : 'clear',
                'checked_at' => now()->toISOString(),
                'matched_post_ids' => array_values(array_unique($matchedPostIds)),
                'matched_sentences' => array_slice($matchedSentences, 0, self::MAX_MATCH_SNIPPETS),
                'matched_sentence_count' => $matchedSentenceCount,
                'total_sentence_count' => $totalSentenceCount,
                'overlap_ratio' => $overlapRatio,
                'flag_reasons' => array_values(array_unique($flagReasons)),
            ],
        ];
    }

    /**
     * @param  array<string, string>  $sentences
     * @param  Collection<int, Post>  $candidatePosts
     * @return array{0: array<string, true>, 1: list<int>, 2: list<string>}
     */
    private function compareAgainstCandidates(Post $post, array $sentences, Collection $candidatePosts): array
    {
        $matchedSentenceMap = [];
        $matchedPostIds = [];
        $flagReasons = [];
        $normalizedTitle = $this->normalizeShortString($post->title);

        foreach ($candidatePosts as $candidatePost) {
            $candidateTitle = $this->normalizeShortString($candidatePost->title);

            if ($normalizedTitle !== '' && $normalizedTitle === $candidateTitle) {
                $matchedPostIds[] = (int) $candidatePost->id;
                $flagReasons[] = 'matching_title';
            }

            $candidateSentences = $this->extractComparableSentences($candidatePost->full_article_html);
            $sharedSentences = array_intersect_key($sentences, $candidateSentences);

            if ($sharedSentences === []) {
                continue;
            }

            $matchedPostIds[] = (int) $candidatePost->id;

            foreach (array_keys($sharedSentences) as $sentence) {
                $matchedSentenceMap[$sentence] = true;
            }
        }

        return [$matchedSentenceMap, $matchedPostIds, $flagReasons];
    }

    /**
     * @return array<string, string>
     */
    private function extractComparableSentences(?string $html): array
    {
        $text = $this->normalizeBodyText($html);

        if ($text === '') {
            return [];
        }

        $parts = preg_split('/(?<=[.!?])\s+/u', $text) ?: [];
        $sentences = [];

        foreach ($parts as $part) {
            $sentence = $this->normalizeSentence($part);

            if ($sentence === '') {
                continue;
            }

            $sentences[$sentence] = $sentence;
        }

        return $sentences;
    }

    /**
     * @return list<string>
     */
    private function extractKeywords(Post $post): array
    {
        $meta = $post->getAttributeValue('meta');
        $meta = is_array($meta) ? $meta : [];
        $keywords = [$post->title];

        $primaryKeyword = $meta['primary_keyword'] ?? null;

        if (is_string($primaryKeyword) && $primaryKeyword !== '') {
            $keywords[] = $primaryKeyword;
        }

        $secondaryKeywords = $meta['secondary_keywords'] ?? [];

        if (is_array($secondaryKeywords)) {
            foreach ($secondaryKeywords as $keyword) {
                if (is_string($keyword) && $keyword !== '') {
                    $keywords[] = $keyword;
                }
            }
        }

        $normalizedKeywords = [];

        foreach ($keywords as $keyword) {
            $normalizedKeyword = trim($keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            $normalizedKeywords[] = $normalizedKeyword;
        }

        return array_values(array_unique($normalizedKeywords));
    }

    private function normalizeBodyText(?string $html): string
    {
        if (! is_string($html) || trim($html) === '') {
            return '';
        }

        $text = preg_replace('/<[^>]+>/u', ' ', $html) ?? '';
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    private function normalizeSentence(string $sentence): string
    {
        $normalized = mb_strtolower(trim($sentence));
        $normalized = preg_replace('/[[:punct:]]+/u', ' ', $normalized) ?? '';
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? '';
        $normalized = trim($normalized);

        if ($normalized === '') {
            return '';
        }

        if (mb_strlen($normalized) < self::MIN_SENTENCE_CHARACTERS) {
            return '';
        }

        if (count(array_filter(explode(' ', $normalized), static fn (string $word): bool => $word !== '')) < self::MIN_SENTENCE_WORDS) {
            return '';
        }

        return $normalized;
    }

    private function normalizeShortString(?string $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $normalized = mb_strtolower(trim($value));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? '';

        return trim($normalized);
    }
}
