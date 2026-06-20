# Task Record

## Task Summary

Normalize AI blog-writer `section` blocks into supported post blocks so auto-generated drafts do not fail validation during persistence.

## Requested Outcome

- stop auto-generated drafts from failing with `Unsupported block type [section]`
- normalize unsupported AI block payloads into the service’s supported block types
- add a focused regression test for `section` block output

## Scope Boundaries

- service repository only
- blog-writer block normalization and targeted tests only
- no admin app changes
- no provider integration changes

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`

## Repository Files Inspected

- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/DTO/AgentResult.php`
- `app/AI/DTO/AgentErrorData.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/Infrastructure/Ai/Exceptions/AiCallFailedException.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Jobs/AI/GenerateContentBriefJob.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `bootstrap/app.php`
- `app/Support/ApiErrorResponse.php`
- `config/ai.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/Modules/Posts/Services/PostBlockPayloadValidator.php`
- `app/Modules/Posts/Services/PostBlockPayloadMapper.php`
- `tests/Feature/BlogWriterAgentTest.php`
- `tests/Feature/GenerateBlogDraftJobTest.php`

## Plan

1. Confirm where unsupported block types enter the draft persistence path.
2. Normalize `section` AI output into supported post block types before validation.
3. Add focused regression coverage and run the smallest relevant test selection.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/AI/Agents/BlogWriterAgent.php`
- `tests/Feature/BlogWriterAgentTest.php`

## Validation

- `php artisan test --filter=BlogWriterAgentTest` passed
- `php artisan test --filter=GenerateBlogDraftJobTest` passed

## Risks Or Follow-Ups

- if the model starts returning other unsupported composite block types beyond `section`, they will need similar normalization rules

## Completion Notes

- Root cause confirmed: the blog writer can emit `content_blocks` with `block_type: section`, but the post layer only accepts `heading`, `paragraph`, `image`, `quote`, `list`, `code`, `faq`, and `callout`.
- `BlogWriterAgent::normalizeContentBlocks()` now expands `section` blocks into supported blocks, currently a heading plus optional paragraph and list.
- Draft persistence now succeeds for the reported payload shape instead of throwing `InvalidPostBlockPayloadException`.
