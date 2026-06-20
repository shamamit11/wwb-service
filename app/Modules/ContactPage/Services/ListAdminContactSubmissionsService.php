<?php

namespace App\Modules\ContactPage\Services;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Repositories\ContactSubmissionRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminContactSubmissionsService
{
    public function __construct(
        private readonly ContactSubmissionRepository $submissions,
    ) {}

    /**
     * @return Collection<int, ContactSubmission>
     */
    public function handle(): Collection
    {
        return $this->submissions->listAdmin();
    }
}
