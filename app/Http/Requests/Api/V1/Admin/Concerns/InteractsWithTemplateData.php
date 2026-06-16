<?php

namespace App\Http\Requests\Api\V1\Admin\Concerns;

use App\Modules\Templates\Data\CreateTemplateBlockData;

trait InteractsWithTemplateData
{
    /**
     * @param  array<int, array{block_type:string,sort_order:int,label?:string|null,default_markdown?:string|null,settings?:array<string,mixed>|null,is_required?:bool}>  $blocks
     * @return list<CreateTemplateBlockData>
     */
    protected function mapBlocks(array $blocks): array
    {
        return array_map(
            fn (array $block): CreateTemplateBlockData => new CreateTemplateBlockData(
                blockType: $block['block_type'],
                sortOrder: $block['sort_order'],
                label: $block['label'] ?? null,
                defaultMarkdown: $block['default_markdown'] ?? null,
                settings: $block['settings'] ?? null,
                isRequired: $block['is_required'] ?? false,
            ),
            $blocks,
        );
    }
}
