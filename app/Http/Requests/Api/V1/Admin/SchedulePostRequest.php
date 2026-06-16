<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Posts\Data\SchedulePostData;
use Illuminate\Foundation\Http\FormRequest;

class SchedulePostRequest extends FormRequest
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
            'scheduled_for' => ['required', 'date', 'after:now'],
        ];
    }

    public function toData(): SchedulePostData
    {
        /** @var array{scheduled_for:string} $validated */
        $validated = $this->validated();

        return new SchedulePostData(
            scheduledFor: $validated['scheduled_for'],
        );
    }
}
