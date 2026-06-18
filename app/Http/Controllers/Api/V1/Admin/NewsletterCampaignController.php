<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\SendNewsletterCampaignRequest;
use App\Http\Requests\Api\V1\Admin\StageNewsletterCampaignRecipientsRequest;
use App\Http\Requests\Api\V1\Admin\StoreNewsletterCampaignRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNewsletterCampaignRequest;
use App\Http\Resources\Api\V1\NewsletterCampaignRecipientResource;
use App\Http\Resources\Api\V1\NewsletterCampaignResource;
use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Services\DeleteNewsletterCampaignService;
use App\Modules\Newsletter\Services\ListAdminNewsletterCampaignsService;
use App\Modules\Newsletter\Services\NewsletterCampaignService;
use App\Modules\Newsletter\Services\QueueNewsletterCampaignSendService;
use App\Modules\Newsletter\Services\StageNewsletterCampaignRecipientsService;
use App\Modules\Newsletter\Services\UpdateNewsletterCampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class NewsletterCampaignController extends Controller
{
    public function index(ListAdminNewsletterCampaignsService $service): AnonymousResourceCollection
    {
        return NewsletterCampaignResource::collection($service->handle());
    }

    public function store(
        StoreNewsletterCampaignRequest $request,
        NewsletterCampaignService $service,
    ): JsonResponse {
        $data = $request->toData((int) $request->user()->id);

        $campaign = $service->createDraft(
            title: $data->title,
            subject: $data->subject,
            previewText: $data->previewText,
            contentMarkdown: $data->contentMarkdown,
            contentHtml: $data->contentHtml,
            createdBy: $data->createdBy,
            metadata: $data->metadata,
        );

        if ($data->status !== 'draft' || $data->scheduledAt !== null || $data->sentAt !== null) {
            $campaign = $service->updateCampaign(
                campaign: $campaign,
                title: $data->title,
                subject: $data->subject,
                previewText: $data->previewText,
                contentMarkdown: $data->contentMarkdown,
                contentHtml: $data->contentHtml,
                status: $data->status,
                scheduledAt: $data->scheduledAt,
                sentAt: $data->sentAt,
                metadata: $data->metadata,
            );
        }

        return (new NewsletterCampaignResource($campaign))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsletterCampaign $newsletterCampaign): NewsletterCampaignResource
    {
        return new NewsletterCampaignResource($newsletterCampaign->loadMissing(['creator', 'recipients.subscriber'])->loadCount('recipients'));
    }

    public function update(
        UpdateNewsletterCampaignRequest $request,
        NewsletterCampaign $newsletterCampaign,
        UpdateNewsletterCampaignService $service,
    ): NewsletterCampaignResource {
        return new NewsletterCampaignResource($service->handle($newsletterCampaign, $request->toData()));
    }

    public function destroy(
        NewsletterCampaign $newsletterCampaign,
        DeleteNewsletterCampaignService $service,
    ): Response {
        $service->handle($newsletterCampaign);

        return response()->noContent();
    }

    public function recipients(NewsletterCampaign $newsletterCampaign): AnonymousResourceCollection
    {
        return NewsletterCampaignRecipientResource::collection(
            $newsletterCampaign->loadMissing('recipients.subscriber')->recipients,
        );
    }

    public function stageRecipients(
        StageNewsletterCampaignRecipientsRequest $request,
        NewsletterCampaign $newsletterCampaign,
        StageNewsletterCampaignRecipientsService $service,
    ): AnonymousResourceCollection {
        return NewsletterCampaignRecipientResource::collection(
            $service->handle($newsletterCampaign, $request->toData()),
        );
    }

    public function send(
        SendNewsletterCampaignRequest $request,
        NewsletterCampaign $newsletterCampaign,
        QueueNewsletterCampaignSendService $service,
    ): NewsletterCampaignResource {
        $request->toData((int) $request->user()->id);

        return new NewsletterCampaignResource($service->handle($newsletterCampaign));
    }
}
