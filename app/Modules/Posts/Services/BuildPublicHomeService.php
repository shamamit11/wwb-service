<?php

namespace App\Modules\Posts\Services;

use App\Models\Homepage;
use App\Modules\Categories\Services\ListPublicCategorySummariesService;
use App\Modules\Homepage\Repositories\HomepageRepository;
use App\Modules\Posts\Repositories\PostRepository;

class BuildPublicHomeService
{
    public function __construct(
        private readonly HomepageRepository $homepages,
        private readonly PostRepository $posts,
        private readonly ListPublicCategorySummariesService $categories,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $homepage = $this->homepages->getSingleton();

        $featuredLimit = max(1, (int) ($homepage->featured_editorial['limit'] ?? 3));
        $recentLimit = max(1, (int) ($homepage->guide_section['limit'] ?? 6));

        $featuredPosts = $this->posts->getPublishedOrdered(featuredOnly: true)
            ->take($featuredLimit)
            ->values();

        if ($featuredPosts->isEmpty()) {
            $featuredPosts = $this->posts->getPublishedOrdered()
                ->take($featuredLimit)
                ->values();
        }

        $recentPosts = $this->posts->getPublishedOrdered()
            ->take($recentLimit)
            ->values();

        $categories = $this->categories->handle()->values();

        return [
            'hero' => $homepage->hero,
            'featured_editorial' => [
                ...$homepage->featured_editorial,
                'mode' => Homepage::SECTION_MODE_AUTOMATIC,
                'post_ids' => $featuredPosts->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
                'category_ids' => null,
                'posts' => $featuredPosts,
            ],
            'guide_section' => [
                ...$homepage->guide_section,
                'title' => 'Recent Articles',
                'mode' => Homepage::SECTION_MODE_AUTOMATIC,
                'post_ids' => $recentPosts->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
                'category_ids' => null,
                'posts' => $recentPosts,
            ],
            'topic_section' => [
                ...$homepage->topic_section,
                'category_ids' => $categories->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
                'categories' => $categories,
            ],
            'promo_section' => $homepage->promo_section,
            'newsletter_section' => $homepage->newsletter_section,
            'seo' => $homepage->seo,
        ];
    }
}
