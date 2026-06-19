<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreNewsletterSubscriberRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNewsletterSubscriberRequest;
use App\Http\Resources\Api\V1\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Services\ListAdminNewsletterSubscribersService;
use App\Modules\Newsletter\Services\NewsletterSubscriptionService;
use App\Modules\Newsletter\Services\ResubscribeNewsletterSubscriberService;
use App\Modules\Newsletter\Services\UnsubscribeNewsletterSubscriberService;
use App\Modules\Newsletter\Services\UpdateNewsletterSubscriberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class NewsletterSubscriberController extends Controller
{
    public function index(ListAdminNewsletterSubscribersService $service): AnonymousResourceCollection
    {
        return NewsletterSubscriberResource::collection($service->handle());
    }

    public function store(
        StoreNewsletterSubscriberRequest $request,
        NewsletterSubscriptionService $service,
    ): JsonResponse {
        $payload = $request->toPayload();

        return (new NewsletterSubscriberResource($service->createActiveSubscriber(
            email: $payload['email'],
            name: $payload['name'] ?? null,
            source: $payload['source'] ?? null,
            metadata: $payload['metadata'] ?? null,
        )))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsletterSubscriber $newsletterSubscriber): NewsletterSubscriberResource
    {
        return new NewsletterSubscriberResource($newsletterSubscriber->loadMissing('lists'));
    }

    public function update(
        UpdateNewsletterSubscriberRequest $request,
        NewsletterSubscriber $newsletterSubscriber,
        UpdateNewsletterSubscriberService $service,
    ): NewsletterSubscriberResource {
        return new NewsletterSubscriberResource($service->handle($newsletterSubscriber, $request->toData()));
    }

    public function unsubscribe(
        NewsletterSubscriber $newsletterSubscriber,
        UnsubscribeNewsletterSubscriberService $service,
    ): NewsletterSubscriberResource {
        return new NewsletterSubscriberResource($service->handle($newsletterSubscriber));
    }

    public function resubscribe(
        NewsletterSubscriber $newsletterSubscriber,
        ResubscribeNewsletterSubscriberService $service,
    ): NewsletterSubscriberResource {
        return new NewsletterSubscriberResource($service->handle($newsletterSubscriber));
    }
}
