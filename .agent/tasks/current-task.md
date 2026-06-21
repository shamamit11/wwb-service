# Current Task

## Task Summary

Reset the service toward a fresh-start editorial backend: remove template/content-brief/block-era schema and flows, keep topic discovery plus knowledge base support, and use a direct topic-to-article draft pipeline suitable for a Quill-based admin editor.

## Requested Outcome

- keep topic queue and topic discovery agent
- keep knowledge base support
- remove `ContentBrief`, `Template`, `TemplateBlock`, related APIs, workflows, jobs, and tests
- replace versioned prompt-template management with simple standard prompt instruction settings:
  - `topic_prompt_instructions`
  - `blog_prompt_instructions`
- simplify posts so the canonical content source is one article body, not block collections
- treat the database as a fresh application baseline for `php artisan migrate:fresh`
- plan the post body around Quill admin editing:
  - canonical article fields should be compatible with `full_article_html`
  - do not preserve block-based editor assumptions
- support post fields for:
  - `title`
  - `short_description`
  - `description`
  - `full_article_markdown` or `full_article_html`
  - `featured_media_id`
  - `faq`
  - `seo`
  - `meta`
- preserve draft-first publishing safety
- route high-scoring topics directly into draft generation when score is above `90`
- use fixed topic score dimensions:
  - `trend_score` 0-35
  - `knowledge_base_fit` 0-20
  - `business_value` 0-20
  - `originality_gap` 0-15
  - `execution_confidence` 0-10

## Scope Boundaries

- in scope: service-side schema, models, AI workflows, settings, routes, resources, and focused tests needed for the simplified pipeline
- in scope: removing obsolete migrations so fresh bootstrap does not recreate deleted legacy tables
- out of scope: sibling admin/frontend implementation work unless backend contract changes require later coordination
- out of scope: legacy data backfill because the application is now being treated as fresh-start

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Models/ContentTopic.php`
- `app/Models/Post.php`
- `app/Models/AiPromptTemplate.php`
- `app/Models/AiPromptTemplateVersion.php`
- `app/Models/SiteSettings.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Controllers/Api/V1/Admin/PostController.php`
- `app/Http/Resources/Api/V1/ContentTopicResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Services/AutoAdvanceHighPriorityTopicService.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/Ai/Services/ResolveAutoDraftGenerationDataService.php`
- `database/migrations/2026_06_16_190000_create_posts_table.php`
- `database/migrations/2026_06_18_120000_create_ai_prompt_templates_table.php`

## Plan

1. Collapse the database to a fresh-start baseline that never creates template/content-brief/post-block tables.
2. Keep topic scoring, low-score pruning, and high-score auto-draft generation intact.
3. Simplify posts into article-first fields that fit a future Quill admin editor.
4. Remove legacy routes, resources, and services that still advertise templates, briefs, or block editing.
5. Validate with `php artisan migrate:fresh --seed` and record remaining cleanup follow-ups.

## Changed Files

- `.agent/tasks/current-task.md`
- `database/migrations/2026_06_16_190000_create_posts_table.php`
- deleted template/content-brief/post-block migrations
- deleted `database/migrations/2026_06_17_194450_create_agent_conversations_table.php`
- post/article request, repository, service, and resource files
- topic-to-draft workflow and low-score cleanup files
- MCP prompt/tool files that referenced content briefs
- metadata/title helper workflows now use fixed internal prompts instead of selectable prompt templates
- deleted rewrite-only AI workflow files and obsolete legacy tests tied to briefs/templates/blocks
- removed duplicated prompt-instruction storage from `site_settings`; active versioned prompt templates are now the only topic/blog prompt source
- switched `.env`, `.env.example`, `config/cache.php`, and `config/queue.php` defaults away from Redis and onto database-backed queue/cache

## Validation

- `php artisan migrate:fresh --seed`
- `php artisan test --filter='HealthCheck|AdminStatus|Category'`
- `php artisan test --filter='SiteSettings|PublicSiteSettings|InternalLinkingService|RssFeedApi'`
- `php artisan test`

## Risks Or Follow-Ups

- fresh coverage still needs to be expanded around the simplified topic-to-article pipeline after deleting obsolete legacy tests
- Quill support is only planned for now; if lossless round-tripping is required later, storing Quill Delta alongside HTML should be considered deliberately

## Completion Notes

- in progress
