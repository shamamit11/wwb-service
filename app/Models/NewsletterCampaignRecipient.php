<?php

namespace App\Models;

use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'newsletter_campaign_id',
    'newsletter_subscriber_id',
    'email',
    'status',
    'sent_at',
    'failed_at',
    'error_message',
    'opened_at',
    'clicked_at',
    'unsubscribed_at',
])]
class NewsletterCampaignRecipient extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsletterRecipientStatus::class,
            'newsletter_campaign_id' => 'integer',
            'newsletter_subscriber_id' => 'integer',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<NewsletterCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'newsletter_campaign_id');
    }

    /**
     * @return BelongsTo<NewsletterSubscriber, $this>
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'newsletter_subscriber_id');
    }
}
