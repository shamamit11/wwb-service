<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateContactSubmissionRequest;
use App\Http\Resources\Api\V1\ContactSubmissionResource;
use App\Models\ContactSubmission;
use App\Modules\ContactPage\Services\ListAdminContactSubmissionsService;
use App\Modules\ContactPage\Services\UpdateContactSubmissionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactSubmissionController extends Controller
{
    public function index(ListAdminContactSubmissionsService $service): AnonymousResourceCollection
    {
        return ContactSubmissionResource::collection($service->handle());
    }

    public function show(ContactSubmission $contactSubmission): ContactSubmissionResource
    {
        return new ContactSubmissionResource($contactSubmission->loadMissing('reviewedBy'));
    }

    public function update(
        UpdateContactSubmissionRequest $request,
        ContactSubmission $contactSubmission,
        UpdateContactSubmissionService $service,
    ): ContactSubmissionResource {
        return new ContactSubmissionResource($service->handle($contactSubmission, $request->toData((int) $request->user()->id)));
    }
}
