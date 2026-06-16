<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\BuildTemplatePreviewRequest;
use App\Http\Requests\Api\V1\Admin\BuildTemplateSeedPostRequest;
use App\Http\Requests\Api\V1\Admin\StoreTemplateRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTemplateRequest;
use App\Http\Resources\Api\V1\TemplatePreviewResource;
use App\Http\Resources\Api\V1\TemplateResource;
use App\Http\Resources\Api\V1\TemplateSeedPostPayloadResource;
use App\Models\Template;
use App\Modules\Templates\Services\BuildTemplatePreviewPayloadService;
use App\Modules\Templates\Services\BuildTemplateSeedPostPayloadService;
use App\Modules\Templates\Services\CreateTemplateService;
use App\Modules\Templates\Services\DeleteTemplateService;
use App\Modules\Templates\Services\ListAdminTemplatesService;
use App\Modules\Templates\Services\UpdateTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    public function index(ListAdminTemplatesService $service): AnonymousResourceCollection
    {
        return TemplateResource::collection($service->handle());
    }

    public function store(
        StoreTemplateRequest $request,
        CreateTemplateService $service,
    ): JsonResponse {
        $template = $service->handle(
            $request->toData((int) $request->user()->id),
        );

        return (new TemplateResource($template))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Template $template): TemplateResource
    {
        return new TemplateResource($template->loadMissing('blocks'));
    }

    public function update(
        UpdateTemplateRequest $request,
        Template $template,
        UpdateTemplateService $service,
    ): TemplateResource {
        return new TemplateResource(
            $service->handle($template, $request->toData((int) $request->user()->id)),
        );
    }

    public function destroy(
        Template $template,
        DeleteTemplateService $service,
    ): Response {
        $service->handle($template);

        return response()->noContent();
    }

    public function preview(
        BuildTemplatePreviewRequest $request,
        Template $template,
        BuildTemplatePreviewPayloadService $service,
    ): TemplatePreviewResource {
        $template->loadMissing('blocks');

        return new TemplatePreviewResource([
            'template' => $template,
            'preview' => $service->handle($template, $request->toData()),
        ]);
    }

    public function seedPost(
        BuildTemplateSeedPostRequest $request,
        Template $template,
        BuildTemplateSeedPostPayloadService $service,
    ): TemplateSeedPostPayloadResource {
        $template->loadMissing('blocks');

        return new TemplateSeedPostPayloadResource([
            'template' => $template,
            'post' => $service->handle($template, $request->toData()),
        ]);
    }
}
