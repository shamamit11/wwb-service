<?php

namespace Database\Seeders;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use Illuminate\Database\Seeder;

class AiPromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $templateAttributes) {
            $versions = $templateAttributes['versions'];
            unset($templateAttributes['versions']);

            $template = AiPromptTemplate::query()->updateOrCreate(
                ['key' => $templateAttributes['key']],
                $templateAttributes,
            );

            $activeVersionId = null;

            foreach ($versions as $versionAttributes) {
                $version = AiPromptTemplateVersion::query()->updateOrCreate(
                    [
                        'prompt_template_id' => $template->id,
                        'version' => $versionAttributes['version'],
                    ],
                    [
                        'system_prompt' => $versionAttributes['system_prompt'],
                        'user_prompt' => $versionAttributes['user_prompt'],
                        'output_schema' => $versionAttributes['output_schema'] ?? null,
                        'variables' => $versionAttributes['variables'] ?? [],
                        'status' => $versionAttributes['status'],
                    ],
                );

                if ($versionAttributes['status'] === AiPromptTemplateVersion::STATUS_ACTIVE) {
                    $activeVersionId = $version->id;
                }
            }

            $template->forceFill([
                'active_version_id' => $activeVersionId,
            ])->save();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array
    {
        return [
            [
                'name' => 'Topic Standard Prompt',
                'key' => AiPromptTemplate::KEY_TOPIC_STANDARD,
                'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
                'description' => 'The single standard prompt family for topic discovery.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You suggest grounded editorial topics for Wide Web Blog.',
                    'user_prompt' => 'Cluster {{cluster}} Category brief {{category_brief}} Audience {{audience}} Existing {{existing_topics}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['cluster', 'category_brief', 'audience', 'existing_topics', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Blog Standard Prompt',
                'key' => AiPromptTemplate::KEY_BLOG_STANDARD,
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'The single standard prompt family for full article draft generation.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You write reviewable draft blog posts for Wide Web Blog.',
                    'user_prompt' => 'Title {{title}} Mode {{generation_mode}} Guidance {{generation_mode_guidance}} Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}} Outline {{outline}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['title', 'generation_mode', 'generation_mode_guidance', 'knowledge_context', 'existing_post_context', 'internal_link_context', 'outline'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
        ];
    }
}
