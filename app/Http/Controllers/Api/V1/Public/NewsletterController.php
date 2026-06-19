<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\SubscribeNewsletterRequest;
use App\Http\Requests\Api\V1\Public\UnsubscribeNewsletterRequest;
use App\Http\Resources\Api\V1\NewsletterSubscriptionStatusResource;
use App\Modules\Newsletter\Services\PublicSubscribeNewsletterService;
use App\Modules\Newsletter\Services\PublicUnsubscribeNewsletterService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends Controller
{
    public function subscribe(
        SubscribeNewsletterRequest $request,
        PublicSubscribeNewsletterService $service,
    ): JsonResponse {
        return (new NewsletterSubscriptionStatusResource($service->handle($request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function unsubscribe(
        UnsubscribeNewsletterRequest $request,
        PublicUnsubscribeNewsletterService $service,
    ): NewsletterSubscriptionStatusResource {
        return new NewsletterSubscriptionStatusResource($service->handle($request->toData()->unsubscribeToken));
    }
}
