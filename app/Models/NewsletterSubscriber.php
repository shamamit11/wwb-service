<?php

namespace App\Models;

use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'email',
    'name',
    'status',
    'source',
    'subscribed_at',
    'unsubscribed_at',
    'unsubscribe_token',
    'metadata',
])]
class NewsletterSubscriber extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsletterSubscriberStatus::class,
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<NewsletterList, $this>
     */
    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterList::class, 'newsletter_list_subscriber')
            ->withPivot(['subscribed_at', 'unsubscribed_at'])
            ->withTimestamps();
    }
}
