<?php

namespace App\Modules\Posts\Repositories;

use App\Models\Post;
use App\Models\PostBlock;
use App\Modules\Posts\Data\CreatePostBlockData;
use Illuminate\Database\Eloquent\Collection;

class EloquentPostBlockRepository implements PostBlockRepository
{
    public function replaceForPost(Post $post, array $blocks): void
    {
        $this->deleteForPost($post);

        if ($blocks === []) {
            return;
        }

        $post->blocks()->createMany(array_map(
            fn (CreatePostBlockData $block): array => [
                'block_type' => $block->blockType,
                'sort_order' => $block->sortOrder,
                'content_markdown' => $block->contentMarkdown,
                'content_html_cache' => $block->contentHtmlCache,
                'plain_text_cache' => $block->plainTextCache,
                'settings' => $block->settings,
                'source_template_block_id' => $block->sourceTemplateBlockId,
            ],
            $blocks,
        ));
    }

    public function deleteForPost(Post $post): void
    {
        $post->blocks()->delete();
    }

    /**
     * @return Collection<int, PostBlock>
     */
    public function getOrderedForPost(Post $post): Collection
    {
        return $post->blocks()
            ->orderBy('sort_order')
            ->get();
    }
}
