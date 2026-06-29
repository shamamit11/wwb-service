<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id',
    'article_video_id',
    'hook',
    'core_angle',
    'voiceover_text',
])]
class ArticleVideoMemoryEntry extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'article_video_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id')->withTrashed();
    }

    /**
     * @return BelongsTo<ArticleVideo, $this>
     */
    public function articleVideo(): BelongsTo
    {
        return $this->belongsTo(ArticleVideo::class, 'article_video_id');
    }
}
