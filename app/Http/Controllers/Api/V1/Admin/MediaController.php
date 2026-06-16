<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\BatchStoreMediaRequest;
use App\Http\Requests\Api\V1\Admin\ListMediaRequest;
use App\Http\Requests\Api\V1\Admin\StoreMediaRequest;
use App\Http\Requests\Api\V1\Admin\UpdateMediaRequest;
use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Media;
use App\Modules\Media\Services\BatchUploadMediaService;
use App\Modules\Media\Services\Contracts\MediaUploader;
use App\Modules\Media\Services\ListAdminMediaService;
use App\Modules\Media\Services\SafeDeleteMediaService;
use App\Modules\Media\Services\UpdateMediaMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function index(
        ListMediaRequest $request,
        ListAdminMediaService $service,
    ): AnonymousResourceCollection {
        $media = $service->handle($request->toData());
        $filters = $request->toData();

        if ($filters->used !== null) {
            $media = $media->filter(function ($item) use ($filters): bool {
                if (! $item instanceof Media) {
                    return false;
                }

                $usageCount = (new MediaResource($item))->resolve()['usage_count'];

                return $filters->used ? $usageCount > 0 : $usageCount === 0;
            })->values();
        }

        return MediaResource::collection($media);
    }

    public function store(
        StoreMediaRequest $request,
        MediaUploader $uploader,
    ): JsonResponse {
        return (new MediaResource($uploader->upload($request->toData((int) $request->user()->id))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function batch(
        BatchStoreMediaRequest $request,
        BatchUploadMediaService $service,
    ): AnonymousResourceCollection {
        return MediaResource::collection($service->handle($request->toData((int) $request->user()->id)));
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }

    public function update(
        UpdateMediaRequest $request,
        Media $media,
        UpdateMediaMetadataService $service,
    ): MediaResource {
        return new MediaResource($service->handle($media, $request->toData()));
    }

    public function destroy(
        Media $media,
        SafeDeleteMediaService $deleter,
    ): Response {
        $deleter->handle($media);

        return response()->noContent();
    }
}
