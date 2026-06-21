<?php

namespace App\Modules\News\Services;

use App\Models\NewsItem;
use App\Models\NewsItemExtraction;
use App\Models\NewsItemRoute;
use App\Models\NewsItemScore;
use App\Modules\KnowledgeBase\Data\LinkKnowledgeBaseEntryToTopicData;
use App\Modules\KnowledgeBase\Services\LinkKnowledgeBaseEntryToTopicService;
use App\Modules\News\Data\CreateNewsItemRouteData;
use App\Modules\News\Repositories\NewsItemRepository;
use App\Modules\Posts\Repositories\PostRepository;

class NewsRoutingService
{
    public function __construct(
        private readonly NewsItemRepository $items,
        private readonly GenerateKnowledgeBaseFromNewsService $generateKnowledgeBase,
        private readonly GenerateTopicFromNewsService $generateTopic,
        private readonly LinkKnowledgeBaseEntryToTopicService $linkKnowledgeBaseToTopic,
        private readonly PostRepository $posts,
    ) {}

    public function handle(NewsItem $item): NewsItemRoute
    {
        $item = $this->items->findById((int) $item->id) ?? $item;
        $score = $item->latestScore;
        $extraction = $item->latestExtraction;

        if (! $score instanceof NewsItemScore) {
            $route = $this->items->saveRoute($item, new CreateNewsItemRouteData(
                route: NewsItemRoute::ROUTE_IGNORE,
                knowledgeBaseEntryId: null,
                contentTopicId: null,
                postId: null,
                routedAt: now()->toDateTimeString(),
                metadata: ['reason' => 'missing_score'],
            ));

            $this->items->markStatus($item, NewsItem::STATUS_IGNORED);

            return $route;
        }

        if ($score->decision === NewsItemScore::DECISION_IGNORE) {
            $route = $this->items->saveRoute($item, new CreateNewsItemRouteData(
                route: NewsItemRoute::ROUTE_IGNORE,
                knowledgeBaseEntryId: null,
                contentTopicId: null,
                postId: null,
                routedAt: now()->toDateTimeString(),
                metadata: ['reason' => 'score_below_threshold'],
            ));

            $this->items->markStatus($item, NewsItem::STATUS_IGNORED);

            return $route;
        }

        $knowledgeBase = null;
        $topic = null;

        if (in_array($score->decision, [NewsItemScore::DECISION_KNOWLEDGE_BASE, NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC], true)) {
            $knowledgeBase = $this->generateKnowledgeBase->handle($item, $extraction instanceof NewsItemExtraction ? $extraction : null);
        }

        if (in_array($score->decision, [NewsItemScore::DECISION_TOPIC, NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC], true)) {
            $topic = $this->generateTopic->handle($item, $score);
        }

        if ($knowledgeBase !== null && $topic !== null) {
            $this->linkKnowledgeBaseToTopic->handle($knowledgeBase, new LinkKnowledgeBaseEntryToTopicData($topic->id));
        }

        $post = $topic !== null ? $this->posts->findBySourceContentTopicId((int) $topic->id) : null;
        $routeValue = match ($score->decision) {
            NewsItemScore::DECISION_KNOWLEDGE_BASE => NewsItemRoute::ROUTE_KNOWLEDGE_BASE,
            NewsItemScore::DECISION_TOPIC => NewsItemRoute::ROUTE_TOPIC,
            NewsItemScore::DECISION_KNOWLEDGE_BASE_AND_TOPIC => NewsItemRoute::ROUTE_KNOWLEDGE_BASE_AND_TOPIC,
            default => NewsItemRoute::ROUTE_IGNORE,
        };

        $route = $this->items->saveRoute($item, new CreateNewsItemRouteData(
            route: $routeValue,
            knowledgeBaseEntryId: $knowledgeBase?->id,
            contentTopicId: $topic?->id,
            postId: $post?->id,
            routedAt: now()->toDateTimeString(),
            metadata: [
                'decision' => $score->decision,
                'total_score' => $score->total_score,
            ],
        ));

        $this->items->markStatus($item, NewsItem::STATUS_ROUTED);

        return $route;
    }
}
