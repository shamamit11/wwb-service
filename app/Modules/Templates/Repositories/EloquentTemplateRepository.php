<?php

namespace App\Modules\Templates\Repositories;

use App\Models\Template;
use App\Models\TemplateBlock;
use App\Modules\Templates\Data\CreateTemplateBlockData;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Data\UpdateTemplateData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTemplateRepository implements TemplateRepository
{
    public function create(CreateTemplateData $data): Template
    {
        return DB::transaction(function () use ($data): Template {
            $template = Template::query()->create([
                'created_by_user_id' => $data->createdByUserId,
                'updated_by_user_id' => $data->updatedByUserId,
                'name' => $data->name,
                'slug' => $data->slug,
                'template_type' => $data->templateType,
                'description' => $data->description,
                'status' => $data->status,
                'default_excerpt_prompt' => $data->defaultExcerptPrompt,
                'default_meta' => $data->defaultMeta,
            ]);

            $this->replaceBlocks($template, $data->blocks);

            return $template->load('blocks');
        });
    }

    public function update(Template $template, UpdateTemplateData $data): Template
    {
        return DB::transaction(function () use ($template, $data): Template {
            $template->update([
                'updated_by_user_id' => $data->updatedByUserId,
                'name' => $data->name,
                'slug' => $data->slug,
                'template_type' => $data->templateType,
                'description' => $data->description,
                'status' => $data->status,
                'default_excerpt_prompt' => $data->defaultExcerptPrompt,
                'default_meta' => $data->defaultMeta,
            ]);

            $this->replaceBlocks($template, $data->blocks);

            return $template->refresh()->load('blocks');
        });
    }

    public function delete(Template $template): void
    {
        DB::transaction(function () use ($template): void {
            $template->blocks()->delete();
            $template->delete();
        });
    }

    public function findById(int $id): ?Template
    {
        return Template::query()
            ->with('blocks')
            ->find($id);
    }

    public function findBySlug(string $slug): ?Template
    {
        return Template::query()
            ->with('blocks')
            ->where('slug', $slug)
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Template::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, Template>
     */
    public function getAllOrdered(): Collection
    {
        return Template::query()
            ->with('blocks')
            ->orderBy('template_type')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, TemplateBlock>
     */
    public function getOrderedBlocks(Template $template): Collection
    {
        return TemplateBlock::query()
            ->whereBelongsTo($template)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  list<CreateTemplateBlockData>  $blocks
     */
    private function replaceBlocks(Template $template, array $blocks): void
    {
        $template->blocks()->delete();

        if ($blocks === []) {
            return;
        }

        $template->blocks()->createMany(array_map(
            fn (CreateTemplateBlockData $block): array => [
                'block_type' => $block->blockType,
                'sort_order' => $block->sortOrder,
                'label' => $block->label,
                'default_markdown' => $block->defaultMarkdown,
                'settings' => $block->settings,
                'is_required' => $block->isRequired,
            ],
            $blocks,
        ));
    }
}
