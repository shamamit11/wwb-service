<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateContactPageRequest;
use App\Http\Resources\Api\V1\ContactPageResource;
use App\Modules\ContactPage\Services\ReadContactPageService;
use App\Modules\ContactPage\Services\UpdateContactPageService;
use Illuminate\Http\JsonResponse;

class ContactPageController extends Controller
{
    public function show(ReadContactPageService $service): JsonResponse
    {
        return (new ContactPageResource($service->handle()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(
        UpdateContactPageRequest $request,
        UpdateContactPageService $service,
    ): JsonResponse {
        return (new ContactPageResource($service->handle($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(200);
    }
}
