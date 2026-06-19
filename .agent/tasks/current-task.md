# Task Record

## Task Summary

Expose underlying provider failure details on failed AI jobs so production AI call errors are diagnosable.

## Requested Outcome

- include the real provider exception message in failed AI job error messages
- preserve useful nested exception details in AI error context
- add a focused regression test for the topic discovery failure path

## Scope Boundaries

- service repository only
- AI exception reporting and targeted tests only
- no provider integration changes in this task

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`

## Repository Files Inspected

- `app/Infrastructure/Ai/Exceptions/AiCallFailedException.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/AI/DTO/AgentErrorData.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Plan

1. Preserve underlying throwable details in AI call failure messages and context.
2. Add a focused regression test through topic discovery failure handling.
3. Run the smallest relevant test selection and record the result.

## Changed Files

- `.agent/tasks/current-task.md`

## Validation

- pending

## Risks Or Follow-Ups

- provider SDK exceptions may still omit some HTTP response body details
- longer term, raw provider diagnostics may be worth storing in structured output payloads for failed jobs

## Completion Notes

- pending
