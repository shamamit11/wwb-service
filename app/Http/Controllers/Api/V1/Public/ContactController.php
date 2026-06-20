<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\SubmitContactMessageRequest;
use App\Http\Resources\Api\V1\ContactSubmissionReceiptResource;
use App\Http\Resources\Api\V1\PublicContactPageResource;
use App\Modules\ContactPage\Services\BuildPublicContactPageService;
use App\Modules\ContactPage\Services\SubmitContactMessageService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function show(BuildPublicContactPageService $service): PublicContactPageResource
    {
        return new PublicContactPageResource($service->handle());
    }

    public function submit(
        SubmitContactMessageRequest $request,
        SubmitContactMessageService $service,
    ): JsonResponse {
        $submission = $service->handle($request->toPayload());

        return (new ContactSubmissionReceiptResource([
            'message' => 'Your message has been received.',
            'submitted_at' => $submission->submitted_at?->toISOString(),
        ]))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
