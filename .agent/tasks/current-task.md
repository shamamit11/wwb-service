# Task Record

## Task Summary

Diagnose and fix the admin `Generate Brief` failure so backend AI errors are surfaced instead of returning a generic 500.

## Requested Outcome

- identify why `POST /api/v1/admin/content-topics/{id}/generate-brief` still returns 500
- surface the underlying AI workflow failure message through the API
- add a focused regression test for failed content brief generation

## Scope Boundaries

- service repository only
- content brief generation failure handling and targeted tests only
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
- `bootstrap/app.php`
- `app/Support/ApiErrorResponse.php`
- `config/ai.php`
- `tests/Feature/ContentBriefApiTest.php`

## Plan

1. Confirm the failing `generate-brief` path and identify the underlying AI error.
2. Convert failed content brief agent runs into an explicit API error instead of a generic runtime 500.
3. Add a regression test for the failed brief generation path and run the smallest relevant test selection.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Ai/Exceptions/AiWorkflowFailedException.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `bootstrap/app.php`
- `tests/Feature/ContentBriefApiTest.php`

## Validation

- `php artisan test --filter=ContentBriefApiTest` passed
- direct `php artisan tinker` repro now throws `App\Modules\Ai\Exceptions\AiWorkflowFailedException` with the underlying OpenAI 401 message instead of `RuntimeException('Content brief agent did not persist a content brief.')`

## Risks Or Follow-Ups

- admin UI may still need a small client-side improvement if it only shows a generic toast for non-2xx responses
- provider failures during synchronous generation still depend on prompt/config correctness and valid credentials

## Completion Notes

- Root cause confirmed locally: all configured AI provider keys were missing, so content brief generation failed with an OpenAI 401.
- The API bug was separate: the failed agent result was ignored and replaced with a generic runtime exception, which the API layer converted into a generic 500 response.
- The service now surfaces brief-generation failures as `AI_WORKFLOW_FAILED` with the underlying message and preserves the AI job record for diagnosis.
