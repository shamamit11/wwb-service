<?php

namespace App\Modules\News\Services;

use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\NewsItem;
use App\Models\NewsItemScore;
use App\Models\Post;
use App\Modules\News\Data\CreateNewsItemScoreData;
use App\Modules\News\Repositories\NewsItemRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class NewsScoringService
{
    public function __construct(
        private readonly NewsItemRepository $items,
    ) {}

    public function handle(NewsItem $item): NewsItemScore
    {
        $relevanceScore = $this->relevanceScore($item);
        $freshnessScore = $this->freshnessScore($item);
        $credibilityScore = $this->credibilityScore($item);
        $pillarFitScore = $this->pillarFitScore($item);
        $evergreenPotentialScore = $this->evergreenPotentialScore($item);
        $noveltyScore = $this->noveltyScore($item);
        $businessValueScore = $this->businessValueScore($item);
        $totalScore = min(100, $relevanceScore + $freshnessScore + $credibilityScore + $pillarFitScore + $evergreenPotentialScore + $noveltyScore + $businessValueScore);
        $decision = $this->decision($totalScore);

        $score = $this->items->saveScore($item, new CreateNewsItemScoreData(
            relevanceScore: $relevanceScore,
            freshnessScore: $freshnessScore,
            credibilityScore: $credibilityScore,
            pillarFitScore: $pillarFitScore,
            evergreenPotentialScore: $evergreenPotentialScore,
            noveltyScore: $noveltyScore,
            businessValueScore: $businessValueScore,
            totalScore: $totalScore,
            decision: $decision,
            reasoning: "relevance={$relevanceScore}; freshness={$freshnessScore}; credibility={$credibilityScore}; pillar_fit={$pillarFitScore}; evergreen={$evergreenPotentialScore}; novelty={$noveltyScore}; business_value={$businessValueScore}",
            scoredAt: now()->toDateTimeString(),
        ));

        $this->items->markStatus($item, NewsItem::STATUS_SCREENED);

        return $score;
    }

    private function relevanceScore(NewsItem $item): int
    {
        $terms = $this->categoryTerms($item);
        $haystack = Str::lower(Str::squish($item->title.' '.$item->description));
        $matches = collect($terms)->filter(fn (string $term): bool => str_contains($haystack, Str::lower($term)))->count();

        return min(30, $matches * 6);
    }

    private function freshnessScore(NewsItem $item): int
    {
        if ($item->published_at === null) {
            return 5;
        }

        $hours = CarbonImmutable::now()->diffInHours(CarbonImmutable::instance($item->published_at));

        return match (true) {
            $hours <= 24 => 15,
            $hours <= 72 => 10,
            $hours <= 168 => 6,
            default => 2,
        };
    }

    private function credibilityScore(NewsItem $item): int
    {
        $trusted = config('news.scoring.trusted_publishers', []);

        if (is_array($trusted)) {
            foreach ($trusted as $publisher => $score) {
                if ($item->publisher_name !== null && Str::lower($publisher) === Str::lower($item->publisher_name)) {
                    return min(20, (int) $score);
                }
            }
        }

        return str_starts_with($item->url, 'https://') ? 10 : 5;
    }

    private function pillarFitScore(NewsItem $item): int
    {
        $signals = config('news.scoring.implementation_signals', []);
        $haystack = Str::lower(Str::squish($item->title.' '.$item->description));
        $matches = is_array($signals)
            ? collect($signals)->filter(fn (string $signal): bool => str_contains($haystack, $signal))->count()
            : 0;

        return min(15, 3 + ($matches * 2));
    }

    private function evergreenPotentialScore(NewsItem $item): int
    {
        $title = Str::lower($item->title);
        $score = 0;

        foreach (['how', 'why', 'what', 'guide', 'tutorial', 'workflow', 'api', 'framework', 'integration', 'release', 'update'] as $signal) {
            if (str_contains($title, $signal)) {
                $score += 3;
            }
        }

        foreach ((array) config('news.scoring.low_value_signals', []) as $signal) {
            if (is_string($signal) && str_contains($title, Str::lower($signal))) {
                $score -= 5;
            }
        }

        return max(0, min(15, $score));
    }

    private function noveltyScore(NewsItem $item): int
    {
        $normalized = Str::lower(Str::squish($item->title));

        $duplicate = KnowledgeBaseEntry::query()->whereRaw('LOWER(title) = ?', [$normalized])->exists()
            || ContentTopic::query()->whereRaw('LOWER(title) = ?', [$normalized])->exists()
            || Post::query()->whereRaw('LOWER(title) = ?', [$normalized])->exists();

        return $duplicate ? 0 : 10;
    }

    private function businessValueScore(NewsItem $item): int
    {
        $haystack = Str::lower(Str::squish($item->title.' '.$item->description));
        $score = 4;

        foreach (['laravel', 'seo', 'agent', 'mcp', 'automation', 'developer', 'api'] as $signal) {
            if (str_contains($haystack, $signal)) {
                $score += 1;
            }
        }

        return min(10, $score);
    }

    private function decision(int $totalScore): string
    {
        if ($totalScore < (int) config('news.scoring.ignore_below', 45)) {
            return NewsItemScore::DECISION_IGNORE;
        }

        if ($totalScore >= (int) config('news.scoring.both_threshold', 80)) {
            return NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC;
        }

        if ($totalScore >= (int) config('news.scoring.topic_threshold', 65)) {
            return NewsItemScore::DECISION_TOPIC;
        }

        return NewsItemScore::DECISION_KNOWLEDGE_BASE;
    }

    /**
     * @return list<string>
     */
    private function categoryTerms(NewsItem $item): array
    {
        $queries = config("news.discovery.category_queries.{$item->category?->slug}", []);

        if (! is_array($queries)) {
            return [];
        }

        $terms = [$item->category?->name, $item->category?->slug];

        foreach ($queries as $query) {
            if (! is_string($query)) {
                continue;
            }

            foreach (preg_split('/\s+/', Str::lower($query)) ?: [] as $term) {
                if ($term !== '' && $term !== 'or') {
                    $terms[] = $term;
                }
            }
        }

        return array_values(array_unique(array_filter($terms, fn (mixed $term): bool => is_string($term) && $term !== '')));
    }
}
