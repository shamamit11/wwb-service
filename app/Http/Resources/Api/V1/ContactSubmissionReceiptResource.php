<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class ContactSubmissionReceiptResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'submitted',
            'message' => $this->resource['message'],
            'submitted_at' => $this->resource['submitted_at'],
        ];
    }
}
