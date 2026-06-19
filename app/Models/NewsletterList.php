<?php

namespace App\Models;

use App\Modules\Newsletter\Enums\NewsletterListStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'status',
])]
class NewsletterList extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsletterListStatus::class,
        ];
    }

    /**
     * @return BelongsToMany<NewsletterSubscriber, $this>
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterSubscriber::class, 'newsletter_list_subscriber')
            ->withPivot(['subscribed_at', 'unsubscribed_at'])
            ->withTimestamps();
    }
}
