<?php

namespace App\Models;

use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'subject',
    'preview_text',
    'content_markdown',
    'content_html',
    'status',
    'scheduled_at',
    'sent_at',
    'created_by',
    'metadata',
])]
class NewsletterCampaign extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsletterCampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'created_by' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<NewsletterCampaignRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(NewsletterCampaignRecipient::class)
            ->orderBy('id');
    }
}
