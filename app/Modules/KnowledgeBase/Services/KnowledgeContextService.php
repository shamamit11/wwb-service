<?php

namespace App\Modules\KnowledgeBase\Services;

use App\AI\Context\KnowledgeContextFormatter;
use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use Illuminate\Support\Collection;

class KnowledgeContextService
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
        private readonly KnowledgeContextFormatter $formatter,
    ) {}

    /**
     * @return Collection<int, KnowledgeBaseEntry>
     */
    public function search(KnowledgeContextQueryData $query): Collection
    {
        $terms = $this->buildTerms($query);

        return $this->entries->findActiveForContext($query)
            ->filter(fn (KnowledgeBaseEntry $entry): bool => $this->matchesMetadataFilters($entry, $query->metadataFilters))
            ->map(fn (KnowledgeBaseEntry $entry): array => [
                'entry' => $entry,
                'score' => $this->score($entry, $terms),
            ])
            ->sort(function (array $left, array $right): int {
                $scoreComparison = $right['score'] <=> $left['score'];

                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                return strcmp(
                    (string) $right['entry']->updated_at?->toIso8601String(),
                    (string) $left['entry']->updated_at?->toIso8601String(),
                );
            })
            ->pluck('entry')
            ->values();
    }

    /**
     * @return list<string>
     */
    public function forPrompt(KnowledgeContextQueryData $query): array
    {
        return $this->formatter->format($this->search($query), $query);
    }

    /**
     * @return list<string>
     */
    private function buildTerms(KnowledgeContextQueryData $query): array
    {
        $terms = [];

        foreach ([$query->subject, ...$query->keywords] as $value) {
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $terms[] = mb_strtolower(trim($value));

            preg_match_all('/[\pL\pN]{3,}/u', mb_strtolower($value), $matches);

            foreach ($matches[0] ?? [] as $term) {
                $terms[] = $term;
            }
        }

        $uniqueTerms = [];

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            $uniqueTerms[$term] = true;
        }

        return array_keys($uniqueTerms);
    }

    /**
     * @param  array<string, scalar|list<scalar>|null>  $metadataFilters
     */
    private function matchesMetadataFilters(KnowledgeBaseEntry $entry, array $metadataFilters): bool
    {
        if ($metadataFilters === []) {
            return true;
        }

        $metadata = is_array($entry->metadata) ? $entry->metadata : [];

        foreach ($metadataFilters as $key => $expected) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $actual = $metadata[$key] ?? null;

            if (! $this->metadataValueMatches($actual, $expected)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  scalar|list<scalar>|null  $expected
     */
    private function metadataValueMatches(mixed $actual, mixed $expected): bool
    {
        if (is_array($expected)) {
            $expectedValues = array_values(array_filter($expected, static fn (mixed $value): bool => is_scalar($value)));

            if ($expectedValues === []) {
                return true;
            }

            if (is_array($actual)) {
                foreach ($expectedValues as $value) {
                    if (! in_array($value, $actual, true)) {
                        return false;
                    }
                }

                return true;
            }

            return in_array($actual, $expectedValues, true);
        }

        if (is_array($actual)) {
            return in_array($expected, $actual, true);
        }

        return $actual === $expected;
    }

    /**
     * @param  list<string>  $terms
     */
    private function score(KnowledgeBaseEntry $entry, array $terms): int
    {
        if ($terms === []) {
            return 0;
        }

        $title = mb_strtolower($entry->title);
        $summary = mb_strtolower((string) ($entry->summary ?? ''));
        $content = mb_strtolower((string) ($entry->content_markdown ?? ''));
        $metadata = mb_strtolower(json_encode($entry->metadata ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');

        $score = 0;

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (str_contains($title, $term)) {
                $score += 12;
            }

            if ($summary !== '' && str_contains($summary, $term)) {
                $score += 7;
            }

            if ($content !== '' && str_contains($content, $term)) {
                $score += 4;
            }

            if ($metadata !== '' && str_contains($metadata, $term)) {
                $score += 2;
            }
        }

        return $score;
    }
}
