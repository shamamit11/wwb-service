<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ListPostsRequest;
use App\Http\Requests\Api\V1\Admin\PublishPostRequest;
use App\Http\Requests\Api\V1\Admin\QueuePostMetadataSuggestionRequest;
use App\Http\Requests\Api\V1\Admin\QueuePostRewriteRequest;
use App\Http\Requests\Api\V1\Admin\QueuePostTitleExcerptRefinementRequest;
use App\Http\Requests\Api\V1\Admin\SchedulePostRequest;
use App\Http\Requests\Api\V1\Admin\StorePostRequest;
use App\Http\Requests\Api\V1\Admin\UnpublishPostRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePostRequest;
use App\Http\Resources\Api\V1\AiJobResource;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Modules\Ai\Services\QueuePostMetadataSuggestionService;
use App\Modules\Ai\Services\QueuePostRewriteService;
use App\Modules\Ai\Services\QueuePostTitleExcerptRefinementService;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\DeletePostService;
use App\Modules\Posts\Services\ListAdminPostsService;
use App\Modules\Posts\Services\PublishPostService;
use App\Modules\Posts\Services\SchedulePostService;
use App\Modules\Posts\Services\UnpublishPostService;
use App\Modules\Posts\Services\UpdatePostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function index(
        ListPostsRequest $request,
        ListAdminPostsService $service,
    ): AnonymousResourceCollection {
        return PostResource::collection($service->handle($request->toData()));
    }

    public function store(
        StorePostRequest $request,
        CreatePostService $service,
    ): JsonResponse {
        $post = $service->handle($request->toData((int) $request->user()->id));

        return (new PostResource($post))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->loadMissing(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock']));
    }

    public function update(
        UpdatePostRequest $request,
        Post $post,
        UpdatePostService $service,
    ): PostResource {
        return new PostResource(
            $service->handle($post, $request->toData((int) $request->user()->id)),
        );
    }

    public function destroy(
        Post $post,
        DeletePostService $service,
    ): Response {
        $service->handle($post);

        return response()->noContent();
    }

    public function publish(
        PublishPostRequest $request,
        Post $post,
        PublishPostService $service,
    ): PostResource {
        return new PostResource($service->handle($post));
    }

    public function schedule(
        SchedulePostRequest $request,
        Post $post,
        SchedulePostService $service,
    ): PostResource {
        return new PostResource($service->handle($post, $request->toData()));
    }

    public function unpublish(
        UnpublishPostRequest $request,
        Post $post,
        UnpublishPostService $service,
    ): PostResource {
        return new PostResource($service->handle($post));
    }

    public function rewrite(
        QueuePostRewriteRequest $request,
        Post $post,
        QueuePostRewriteService $service,
    ): JsonResponse {
        return (new AiJobResource($service->handle($post, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function suggestMetadata(
        QueuePostMetadataSuggestionRequest $request,
        Post $post,
        QueuePostMetadataSuggestionService $service,
    ): JsonResponse {
        return (new AiJobResource($service->handle($post, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function refineTitleExcerpt(
        QueuePostTitleExcerptRefinementRequest $request,
        Post $post,
        QueuePostTitleExcerptRefinementService $service,
    ): JsonResponse {
        return (new AiJobResource($service->handle($post, $request->toData())))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
