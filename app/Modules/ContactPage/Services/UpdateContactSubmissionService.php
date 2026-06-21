<?php

namespace App\Modules\ContactPage\Services;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Data\UpdateContactSubmissionData;
use App\Modules\ContactPage\Repositories\ContactSubmissionRepository;

class UpdateContactSubmissionService
{
    public function __construct(
        private readonly ContactSubmissionRepository $submissions,
    ) {}

    public function handle(ContactSubmission $submission, UpdateContactSubmissionData $data): ContactSubmission
    {
        return $this->submissions->update($submission, $data);
    }
}
