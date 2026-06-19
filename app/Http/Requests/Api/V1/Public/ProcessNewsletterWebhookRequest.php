<?php

namespace App\Http\Requests\Api\V1\Public;

use App\Modules\Newsletter\Data\ProcessNewsletterWebhookEventData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProcessNewsletterWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $secret = (string) config('newsletter.webhook_secret');

        if ($secret === '') {
            return true;
        }

        if ($this->header('X-Newsletter-Webhook-Secret') !== $secret) {
            throw new AccessDeniedHttpException('Invalid newsletter webhook secret.');
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(['delivered', 'opened', 'clicked', 'bounced', 'complained', 'unsubscribed'])],
            'recipient_id' => ['nullable', 'integer', 'exists:newsletter_campaign_recipients,id'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
            'target_url' => ['nullable', 'url', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $payload = $this->validated();

            if (! isset($payload['recipient_id']) && ! isset($payload['email'])) {
                $validator->errors()->add('recipient_id', 'Provide recipient_id or email.');
            }
        });
    }

    public function toData(): ProcessNewsletterWebhookEventData
    {
        /** @var array{event_type:string,recipient_id?:int|null,email?:string|null,occurred_at?:string|null,target_url?:string|null} $validated */
        $validated = $this->validated();

        return new ProcessNewsletterWebhookEventData(
            eventType: $validated['event_type'],
            recipientId: $validated['recipient_id'] ?? null,
            email: $validated['email'] ?? null,
            occurredAt: $validated['occurred_at'] ?? null,
            targetUrl: $validated['target_url'] ?? null,
        );
    }
}
