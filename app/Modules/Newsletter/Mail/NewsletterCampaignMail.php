<?php

namespace App\Modules\Newsletter\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewsletterCampaignMail extends Mailable
{
    use Queueable;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterCampaignRecipient $recipient,
        public string $htmlContent,
        public string $textContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
            from: new \Illuminate\Mail\Mailables\Address(
                config('newsletter.from.address'),
                config('newsletter.from.name'),
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlContent,
            textString: $this->textContent,
        );
    }
}
