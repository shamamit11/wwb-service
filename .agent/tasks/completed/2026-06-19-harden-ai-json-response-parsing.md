# Task Record

## Task Summary

Harden AI agent JSON response parsing so queued AI workflows do not fail when providers wrap valid JSON in Markdown fences or surrounding prose.

## Requested Outcome

- stop topic discovery from failing on non-raw JSON provider responses
- apply the fix consistently across AI agents that expect structured JSON
- add a focused regression test for fenced JSON topic discovery output

## Scope Boundaries

- service repository only
- AI response parsing and targeted tests only
- no prompt redesign or provider swap in this change

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/skills/ai-orchestration.md`

## Repository Files Inspected

- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/Agents/DraftRewriteAgent.php`
- `app/AI/Agents/MetadataSuggestionAgent.php`
- `app/AI/Agents/TitleExcerptRefinementAgent.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/Infrastructure/Ai/Agents/GenericTextAgent.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Plan

1. Add a shared JSON extraction helper that accepts raw JSON, fenced JSON, and embedded JSON objects.
2. Update structured-output AI agents to use the shared helper.
3. Add a focused regression test and run the smallest relevant test command.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/Agents/DraftRewriteAgent.php`
- `app/AI/Agents/MetadataSuggestionAgent.php`
- `app/AI/Agents/TitleExcerptRefinementAgent.php`
- `app/AI/Support/DecodesJsonResponse.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Validation

- `php artisan test --filter=TopicDiscoveryAgentTest`

## Risks Or Follow-Ups

- prompts are still weak placeholders, so providers may still return structurally wrong JSON even after this parsing fix
- stricter schema enforcement at the provider layer would be a better long-term solution

## Completion Notes

- Added a shared JSON decoder that accepts raw JSON, fenced JSON, and JSON embedded in surrounding text.
- Updated all structured-output AI agents to use the shared decoder so the same failure mode does not repeat across topic discovery, brief generation, draft generation, rewrite, metadata, and title/excerpt refinement flows.
- Added a regression test proving topic discovery succeeds when the provider wraps valid JSON in a Markdown code fence.
