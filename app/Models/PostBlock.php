<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id',
    'block_key',
    'block_type',
    'sort_order',
    'content_markdown',
    'content_html_cache',
    'plain_text_cache',
    'settings',
    'source_template_block_id',
])]
class PostBlock extends Model
{
    use HasUlids;

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['block_key'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<TemplateBlock, $this>
     */
    public function sourceTemplateBlock(): BelongsTo
    {
        return $this->belongsTo(TemplateBlock::class, 'source_template_block_id');
    }
}
