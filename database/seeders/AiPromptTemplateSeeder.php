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
                'name' => 'Topic Discovery Default',
                'key' => 'topic_discovery_default',
                'type' => AiPromptTemplate::TYPE_TOPIC_DISCOVERY,
                'description' => 'Default prompt for discovering editorial topics inside approved clusters.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You suggest grounded editorial topics for Wide Web Blog.',
                    'user_prompt' => 'Cluster {{cluster}} Audience {{audience}} Existing {{existing_topics}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['cluster', 'audience', 'existing_topics', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Content Brief Default',
                'key' => 'content_brief_default',
                'type' => AiPromptTemplate::TYPE_CONTENT_BRIEF,
                'description' => 'Default prompt for generating structured content briefs.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You generate grounded editorial content briefs.',
                    'user_prompt' => 'Topic {{topic_title}} Keyword {{primary_keyword}} Knowledge {{knowledge_context}} Existing {{existing_post_context}} Links {{internal_link_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['topic_title', 'primary_keyword', 'knowledge_context', 'existing_post_context', 'internal_link_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Blog Writer Default',
                'key' => 'blog_writer_default',
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'Default prompt for generating draft blog posts.',
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
            [
                'name' => 'Blog Writer Tutorial',
                'key' => 'blog_writer_tutorial',
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'Tutorial-mode draft prompt.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You write step-by-step tutorial drafts.',
                    'user_prompt' => 'Tutorial {{title}} Guidance {{generation_mode_guidance}} Outline {{outline}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['title', 'generation_mode_guidance', 'outline', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Blog Writer Comparison',
                'key' => 'blog_writer_comparison',
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'Comparison-mode draft prompt.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You write balanced comparison drafts.',
                    'user_prompt' => 'Comparison {{title}} Guidance {{generation_mode_guidance}} Outline {{outline}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['title', 'generation_mode_guidance', 'outline', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Blog Writer Opinionated Analysis',
                'key' => 'blog_writer_opinionated_analysis',
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'Opinionated-analysis draft prompt.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You write opinionated analysis drafts with defensible arguments.',
                    'user_prompt' => 'Analysis {{title}} Guidance {{generation_mode_guidance}} Outline {{outline}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['title', 'generation_mode_guidance', 'outline', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Blog Writer Checklist',
                'key' => 'blog_writer_checklist',
                'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
                'description' => 'Checklist-mode draft prompt.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You write checklist-driven drafts with actionable sections.',
                    'user_prompt' => 'Checklist {{title}} Guidance {{generation_mode_guidance}} Outline {{outline}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['title', 'generation_mode_guidance', 'outline', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Post Rewrite Default',
                'key' => 'post_rewrite_default',
                'type' => AiPromptTemplate::TYPE_EDITOR,
                'description' => 'Default prompt for draft rewrite and regeneration.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You rewrite only the requested draft scope.',
                    'user_prompt' => 'Scope {{scope}} Instructions {{instructions}} Existing {{existing_blocks}} Knowledge {{knowledge_context}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['scope', 'instructions', 'existing_blocks', 'knowledge_context'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Post Metadata Suggestion Default',
                'key' => 'post_metadata_suggestion_default',
                'type' => AiPromptTemplate::TYPE_SEO_OPTIMIZER,
                'description' => 'Default prompt for review-only metadata suggestions.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You suggest review-only metadata improvements.',
                    'user_prompt' => 'Title {{post_title}} Excerpt {{post_excerpt}} Keyword {{primary_keyword}} Existing {{existing_meta_title}} {{existing_meta_description}} Body {{existing_markdown_body}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['post_title', 'post_excerpt', 'primary_keyword', 'existing_meta_title', 'existing_meta_description', 'existing_markdown_body'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
            [
                'name' => 'Post Title Excerpt Refinement Default',
                'key' => 'post_title_excerpt_refinement_default',
                'type' => AiPromptTemplate::TYPE_EDITORIAL_REFINER,
                'description' => 'Default prompt for review-only title and excerpt refinement.',
                'status' => AiPromptTemplate::STATUS_ACTIVE,
                'versions' => [[
                    'version' => 1,
                    'system_prompt' => 'You suggest review-only title and excerpt improvements.',
                    'user_prompt' => 'Title {{post_title}} Excerpt {{post_excerpt}} Keyword {{primary_keyword}} Body {{existing_markdown_body}} Instructions {{instructions}}',
                    'output_schema' => ['type' => 'object'],
                    'variables' => ['post_title', 'post_excerpt', 'primary_keyword', 'existing_markdown_body', 'instructions'],
                    'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
                ]],
            ],
        ];
    }
}
