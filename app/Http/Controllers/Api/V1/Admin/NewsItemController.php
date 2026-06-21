<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\DiscoverNewsItemsRequest;
use App\Http\Requests\Api\V1\Admin\ListNewsItemsRequest;
use App\Http\Resources\Api\V1\NewsItemResource;
use App\Jobs\News\DiscoverNewsItemsJob;
use App\Jobs\News\ExtractNewsItemContentJob;
use App\Jobs\News\RouteNewsItemJob;
use App\Models\Category;
use App\Models\NewsItem;
use App\Models\NewsItemScore;
use App\Modules\News\Services\ExtractNewsItemContentService;
use App\Modules\News\Services\ListAdminNewsItemsService;
use App\Modules\News\Services\NewsDiscoveryService;
use App\Modules\News\Services\NewsRoutingService;
use App\Modules\News\Services\NewsScoringService;
use App\Modules\News\Services\ReadNewsItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class NewsItemController extends Controller
{
    public function index(
        ListNewsItemsRequest $request,
        ListAdminNewsItemsService $service,
    ): AnonymousResourceCollection {
        return NewsItemResource::collection($service->handle($request->toData()));
    }

    public function show(
        NewsItem $newsItem,
        ReadNewsItemService $service,
    ): NewsItemResource {
        return new NewsItemResource($service->handle((int) $newsItem->id) ?? $newsItem->loadMissing([
            'source',
            'category',
            'latestExtraction',
            'latestScore',
            'latestRoute.knowledgeBaseEntry',
            'latestRoute.contentTopic',
            'latestRoute.post',
        ]));
    }

    public function discover(
        DiscoverNewsItemsRequest $request,
        NewsDiscoveryService $discovery,
    ): JsonResponse {
        $category = Category::query()
            ->where('is_active', true)
            ->findOrFail($request->categoryId());

        if ($request->sync()) {
            $items = $discovery->handle($category, $request->limit(), [
                'trigger' => 'admin_api_sync',
                'user_id' => (int) $request->user()->id,
            ]);

            return NewsItemResource::collection(collect($items))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        }

        DiscoverNewsItemsJob::dispatch((int) $category->id, $request->limit(), [
            'trigger' => 'admin_api_queue',
            'user_id' => (int) $request->user()->id,
        ]);

        return response()->json([
            'data' => [
                'queued' => true,
                'category_id' => (int) $category->id,
                'limit' => $request->limit(),
            ],
        ], Response::HTTP_ACCEPTED);
    }

    public function score(
        NewsItem $newsItem,
        NewsScoringService $service,
    ): JsonResponse {
        $score = $service->handle($newsItem);

        if ($score->decision === NewsItemScore::DECISION_IGNORE) {
            RouteNewsItemJob::dispatch((int) $newsItem->id);
        } else {
            ExtractNewsItemContentJob::dispatch((int) $newsItem->id);
        }

        return (new NewsItemResource($newsItem->fresh([
            'source',
            'category',
            'latestExtraction',
            'latestScore',
            'latestRoute.knowledgeBaseEntry',
            'latestRoute.contentTopic',
            'latestRoute.post',
        ])))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function extract(
        NewsItem $newsItem,
        ExtractNewsItemContentService $service,
    ): JsonResponse {
        $service->handle($newsItem);
        RouteNewsItemJob::dispatch((int) $newsItem->id);

        return (new NewsItemResource($newsItem->fresh([
            'source',
            'category',
            'latestExtraction',
            'latestScore',
            'latestRoute.knowledgeBaseEntry',
            'latestRoute.contentTopic',
            'latestRoute.post',
        ])))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function route(
        NewsItem $newsItem,
        NewsRoutingService $service,
    ): JsonResponse {
        $service->handle($newsItem);

        return (new NewsItemResource($newsItem->fresh([
            'source',
            'category',
            'latestExtraction',
            'latestScore',
            'latestRoute.knowledgeBaseEntry',
            'latestRoute.contentTopic',
            'latestRoute.post',
        ])))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
