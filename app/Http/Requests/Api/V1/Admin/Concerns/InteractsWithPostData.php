<?php

namespace App\Http\Requests\Api\V1\Admin\Concerns;

use App\Modules\Posts\Data\PostBlockPayloadData;

trait InteractsWithPostData
{
    /**
     * @param  array<int, array{block_type:string,sort_order:int,content:array<string,mixed>,source_template_block_id?:int|null}>  $blocks
     * @return list<PostBlockPayloadData>
     */
    protected function mapBlocks(array $blocks): array
    {
        return array_map(
            fn (array $block): PostBlockPayloadData => new PostBlockPayloadData(
                blockType: $block['block_type'],
                sortOrder: $block['sort_order'],
                content: $block['content'],
                sourceTemplateBlockId: $block['source_template_block_id'] ?? null,
            ),
            $blocks,
        );
    }
}
