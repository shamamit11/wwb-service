<?php

namespace App\Modules\ContactPage\Repositories;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Data\UpdateContactSubmissionData;
use Illuminate\Database\Eloquent\Collection;

interface ContactSubmissionRepository
{
    public function create(array $attributes): ContactSubmission;

    /**
     * @return Collection<int, ContactSubmission>
     */
    public function listAdmin(): Collection;

    public function update(ContactSubmission $submission, UpdateContactSubmissionData $data): ContactSubmission;
}
