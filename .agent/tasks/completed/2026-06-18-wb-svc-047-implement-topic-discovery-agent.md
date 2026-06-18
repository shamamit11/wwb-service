# Task Summary

Implement `WB-SVC-047` by adding the first real AI workflow agent, `TopicDiscoveryAgent`, and integrating it with prompt rendering, duplicate checks, topic persistence, and AI job/step tracking.

## Requested Outcome

- create `TopicDiscoveryAgent`
- align topic discovery input and output DTOs with the story contract
- support database-backed prompt template rendering
- support knowledge base context in prompt variables
- prevent duplicate topic suggestions from being saved
- persist generated topic suggestions in `content_topics`
- track execution in `ai_jobs` and `ai_generation_steps`

## Scope Boundaries

- in scope: service-side topic discovery agent flow, supporting AI tools, DTO updates, repository/service integration, and tests
- out of scope: admin UI changes, sibling app changes, later agents such as content brief or blog writer

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/product.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `docs/AI_CONTENT_ENGINE.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `app/AI/Contracts/ContentAgentInterface.php`
- `app/AI/DTO/TopicDiscoveryInput.php`
- `app/AI/DTO/TopicDiscoveryResult.php`
- `app/AI/DTO/AgentInput.php`
- `app/AI/DTO/AgentResult.php`
- `app/Infrastructure/Ai/Contracts/AiClient.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/Infrastructure/Ai/Data/TextGenerationResult.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Modules/Ai/Services/RecordAiUsageService.php`
- `app/Modules/Ai/Services/RenderAiPromptTemplateService.php`
- `app/Modules/Ai/Repositories/AiPromptTemplateRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiPromptTemplateRepository.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Repositories/ContentTopicRepository.php`
- `app/Modules/ContentTopics/Repositories/EloquentContentTopicRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Models/ContentTopic.php`
- `app/Models/Post.php`
- `database/migrations/2026_06_18_130000_create_content_topics_table.php`
- `database/migrations/2026_06_18_100000_create_ai_jobs_table.php`
- `database/migrations/2026_06_18_100100_create_ai_generation_steps_table.php`
- `tests/Feature/AiContentAgentContractsTest.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/AiPromptTemplateApiTest.php`
- `tests/Feature/AiJobTrackingServiceTest.php`
- `tests/Feature/AiUsageCostTrackingServiceTest.php`

## Plan

1. Inspect the existing AI DTO, client, prompt template, and tracking APIs that `TopicDiscoveryAgent` must compose.
2. Implement the agent and internal tools for duplicate checking and topic saving using current content topic services.
3. Add or adapt tests for DTO shape, prompt integration, persistence, and AI job/step tracking behavior.
4. Run targeted validation and record outcomes.

## Changed Files

- `.agent/tasks/completed/2026-06-18-wb-svc-047-implement-topic-discovery-agent.md`
- `.agent/tasks/current-task.md`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/DTO/TopicDiscoveryInput.php`
- `app/AI/DTO/TopicDiscoveryResult.php`
- `app/AI/DTO/TopicSuggestionData.php`
- `app/AI/Tools/CheckDuplicateTopicTool.php`
- `app/AI/Tools/SaveTopicIdeaTool.php`
- `app/Modules/Ai/Repositories/AiPromptTemplateRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiPromptTemplateRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `tests/Feature/AiContentAgentContractsTest.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Validation

- `php artisan test tests/Feature/AiContentAgentContractsTest.php` ✅
- `php artisan test tests/Feature/TopicDiscoveryAgentTest.php` ✅
- `php artisan test` ⚠️ failed in existing `Tests\Feature\ActivityLogTest::test_editorial_mutations_are_recorded_with_curated_audit_payloads` because media storage attempted to fetch instance-profile credentials from `169.254.169.254`

## Risks Or Follow-Ups

- topic summaries are persisted into `content_topics.notes` because the current schema has no dedicated `summary` column
- prompt template seeding or admin setup for `topic_discovery_default` may still need follow-up if environments do not already provide an active topic discovery template
- full-suite validation remains blocked by the unrelated activity-log/media credential failure noted above

## Completion Notes

- `TopicDiscoveryAgent` now renders a database-backed prompt, calls the AI client, filters duplicates against posts and content topics, saves suggested topics, and tracks the run in `ai_jobs`, `ai_generation_steps`, and `ai_job_costs`
