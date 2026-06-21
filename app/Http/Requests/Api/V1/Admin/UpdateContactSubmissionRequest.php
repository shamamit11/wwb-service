<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ContactSubmission;
use App\Modules\ContactPage\Data\UpdateContactSubmissionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ContactSubmission::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function toData(int $userId): UpdateContactSubmissionData
    {
        /** @var array{status:string,admin_notes?:string|null,metadata?:array<string,mixed>|null} $validated */
        $validated = $this->validated();

        return new UpdateContactSubmissionData(
            reviewedByUserId: $userId,
            status: $validated['status'],
            adminNotes: $validated['admin_notes'] ?? null,
            metadata: $validated['metadata'] ?? null,
        );
    }
}
