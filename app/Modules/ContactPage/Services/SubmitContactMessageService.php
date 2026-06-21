<?php

namespace App\Modules\ContactPage\Services;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Repositories\ContactSubmissionRepository;

class SubmitContactMessageService
{
    public function __construct(
        private readonly ContactSubmissionRepository $submissions,
    ) {}

    public function handle(array $payload): ContactSubmission
    {
        return $this->submissions->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'topic' => $payload['topic'],
            'message' => $payload['message'],
            'status' => ContactSubmission::STATUS_NEW,
            'admin_notes' => null,
            'metadata' => $payload['metadata'] ?? null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by_user_id' => null,
        ]);
    }
}
