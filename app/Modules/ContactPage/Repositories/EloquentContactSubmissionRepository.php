<?php

namespace App\Modules\ContactPage\Repositories;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Data\UpdateContactSubmissionData;
use Illuminate\Database\Eloquent\Collection;

class EloquentContactSubmissionRepository implements ContactSubmissionRepository
{
    public function create(array $attributes): ContactSubmission
    {
        return ContactSubmission::query()->create($attributes)->load('reviewedBy');
    }

    public function listAdmin(): Collection
    {
        return ContactSubmission::query()
            ->with('reviewedBy')
            ->orderByRaw("case when status = 'new' then 0 when status = 'read' then 1 else 2 end")
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();
    }

    public function update(ContactSubmission $submission, UpdateContactSubmissionData $data): ContactSubmission
    {
        $submission->update([
            'status' => $data->status,
            'admin_notes' => $data->adminNotes,
            'metadata' => $data->metadata,
            'reviewed_at' => $data->status === ContactSubmission::STATUS_NEW ? null : now(),
            'reviewed_by_user_id' => $data->status === ContactSubmission::STATUS_NEW ? null : $data->reviewedByUserId,
        ]);

        return $submission->refresh()->load('reviewedBy');
    }
}
