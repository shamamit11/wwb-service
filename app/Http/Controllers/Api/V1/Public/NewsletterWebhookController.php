<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\ProcessNewsletterWebhookRequest;
use App\Modules\Newsletter\Services\ProcessNewsletterWebhookEventService;
use Illuminate\Http\JsonResponse;

class NewsletterWebhookController extends Controller
{
    public function __invoke(
        ProcessNewsletterWebhookRequest $request,
        ProcessNewsletterWebhookEventService $service,
    ): JsonResponse {
        $service->handle($request->toData());

        return response()->json([
            'data' => [
                'processed' => true,
            ],
        ]);
    }
}
