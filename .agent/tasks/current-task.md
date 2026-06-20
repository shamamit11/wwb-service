# Task Record

## Task Summary

Reduce manual AI content handoffs by auto-advancing high-priority topics from topic approval through brief approval into queued draft generation.

## Requested Outcome

- lower manual editorial steps by auto-approving topics with priority scores above `90`
- keep AI outputs in draft state until human publish approval
- preserve existing topic-only and brief-only approval flows
- add focused regression coverage for the auto-advance behavior

## Scope Boundaries

- primary repository remains `service`
- no sibling app reads are required for this backend workflow change
- no publish automation
- no frontend/admin implementation work in this task

## Cross-App Reason

- none

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/testing.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Requests/Api/V1/Admin/TransitionContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `app/Http/Resources/Api/V1/ContentTopicResource.php`
- `app/Http/Resources/Api/V1/ContentBriefResource.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Services/UpdateContentTopicService.php`
- `app/Modules/ContentBriefs/Services/ApproveContentBriefService.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/Ai/Services/ResolveAutoDraftGenerationDataService.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/AI/Tools/SaveTopicIdeaTool.php`
- `tests/Feature/InternalAiToolsTest.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`

## Plan

1. Add a high-priority auto-advance service for topic create and update flows.
2. Carry continuation intent through queued content-brief jobs so the async workflow can auto-approve the brief and queue the draft.
3. Add targeted coverage for create, topic discovery, and queued brief auto-advance behavior.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/ContentTopics/Services/AutoAdvanceHighPriorityTopicService.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Services/UpdateContentTopicService.php`
- `app/Modules/Ai/Services/ContentBriefWorkflow.php`
- `app/Modules/ContentBriefs/Data/ContinueContentBriefToDraftResultData.php`
- `app/Modules/ContentBriefs/Services/ContinueContentBriefToDraftService.php`
- `tests/Feature/ContentTopicApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `tests/Feature/InternalAiToolsTest.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`
- `tests/Feature/TopicDiscoveryExecutionTest.php`

## Validation

- `php artisan test tests/Feature/ContentTopicApiTest.php` passed
- `php artisan test tests/Feature/InternalAiToolsTest.php` passed
- `php artisan test tests/Feature/TopicDiscoveryAgentTest.php` passed
- `php artisan test tests/Feature/TopicDiscoveryExecutionTest.php` passed
- `php artisan test tests/Feature/ContentBriefApiTest.php` passed

## Risks Or Follow-Ups

- the admin UI may still show topics and briefs as manually reviewable steps even though high-priority topics now auto-advance on the service side

## Completion Notes

- Topics with `priority_score > 90` now auto-advance from create and update flows.
- Auto-advance approves suggested or rejected topics, queues content brief generation, and sets a continuation flag on the queued brief job.
- When the queued content brief finishes, the service now auto-approves the generated brief and queues draft generation when an active category can be resolved.
- Drafts still remain in `draft` status for manual editorial review and publish approval.
