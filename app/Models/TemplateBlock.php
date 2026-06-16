<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'template_id',
    'block_key',
    'block_type',
    'sort_order',
    'label',
    'default_markdown',
    'settings',
    'is_required',
])]
class TemplateBlock extends Model
{
    use HasUlids;

    public const TYPE_HEADING = 'heading';

    public const TYPE_PARAGRAPH = 'paragraph';

    public const TYPE_IMAGE = 'image';

    public const TYPE_QUOTE = 'quote';

    public const TYPE_LIST = 'list';

    public const TYPE_CODE = 'code';

    public const TYPE_FAQ = 'faq';

    public const TYPE_CALLOUT = 'callout';

    public const BLOCK_TYPES = [
        self::TYPE_HEADING,
        self::TYPE_PARAGRAPH,
        self::TYPE_IMAGE,
        self::TYPE_QUOTE,
        self::TYPE_LIST,
        self::TYPE_CODE,
        self::TYPE_FAQ,
        self::TYPE_CALLOUT,
    ];

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
            'settings' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
