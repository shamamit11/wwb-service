<?php

namespace App\Modules\Newsletter\Providers;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use App\Modules\Newsletter\Contracts\NewsletterDeliveryProvider;
use App\Modules\Newsletter\DTO\NewsletterDeliveryResult;
use App\Modules\Newsletter\Mail\NewsletterCampaignMail;
use App\Modules\Newsletter\Services\NewsletterTrackingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MailNewsletterDeliveryProvider implements NewsletterDeliveryProvider
{
    public function __construct(
        private readonly NewsletterTrackingService $tracking,
    ) {}

    public function send(NewsletterCampaign $campaign, NewsletterCampaignRecipient $recipient): NewsletterDeliveryResult
    {
        $html = $this->tracking->trackedHtml($campaign, $recipient);
        $text = $this->tracking->trackedText($campaign, $recipient);

        Mail::to($recipient->email)->send(new NewsletterCampaignMail(
            campaign: $campaign,
            recipient: $recipient,
            htmlContent: $html,
            textContent: $text,
        ));

        return new NewsletterDeliveryResult(
            provider: 'mail',
            metadata: [
                'mailer' => config('mail.default'),
                'recipient' => $recipient->email,
                'content_hash' => sha1($campaign->subject.'|'.$recipient->email.'|'.Str::length($html)),
            ],
        );
    }
}
