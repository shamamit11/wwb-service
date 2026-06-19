<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\Newsletter\Data\QueueNewsletterCampaignSendData;
use Illuminate\Foundation\Http\FormRequest;

class SendNewsletterCampaignRequest extends FormRequest
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
        return [];
    }

    public function toData(int $userId): QueueNewsletterCampaignSendData
    {
        return new QueueNewsletterCampaignSendData(
            requestedByUserId: $userId,
        );
    }
}
