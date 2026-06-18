<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'prompt_template_id',
    'version',
    'system_prompt',
    'user_prompt',
    'output_schema',
    'variables',
    'status',
])]
class AiPromptTemplateVersion extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'output_schema' => 'array',
            'variables' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AiPromptTemplate, $this>
     */
    public function promptTemplate(): BelongsTo
    {
        return $this->belongsTo(AiPromptTemplate::class, 'prompt_template_id');
    }
}
