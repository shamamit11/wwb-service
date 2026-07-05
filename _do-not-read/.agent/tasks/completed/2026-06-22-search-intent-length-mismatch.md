# Current Task

## Task Summary

Fix the `content_topics.search_intent` length mismatch causing MySQL `Data too long` failures during topic creation.

## Requested Outcome

- topic creation accepts the longer `search_intent` strings already produced by AI topic discovery and accepted by validation
- schema, validation, and tests stay aligned so the mismatch does not recur

## Scope Boundaries

- in scope: `content_topics` schema, related validation, targeted regression coverage, and task notes
- out of scope: sibling apps, unrelated topic queue behavior, and broader AI prompt/output redesign

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/skills/database.md`
- `docs/DATABASE_DESIGN.md`

## Repository Files Inspected

- `database/migrations/2026_06_18_130000_create_content_topics_table.php`
- `app/Models/ContentTopic.php`
- `app/Mcp/Tools/CreateTopicSuggestionTool.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/DTO/TopicSuggestionData.php`
- `app/AI/Tools/SaveTopicIdeaTool.php`
- `app/Http/Requests/Api/V1/Admin/StoreContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentTopicRequest.php`
- `app/Modules/ContentTopics/Data/CreateContentTopicData.php`
- `app/Modules/ContentTopics/Repositories/EloquentContentTopicRepository.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `tests/Feature/ContentTopicAutoAdvanceTest.php`

## Plan

1. Align `search_intent` schema and validation limits with actual topic discovery output.
2. Add a targeted regression test that persists a long AI-generated `search_intent`.
3. Run focused validation and record outcomes.

## Changed Files

- `.agent/tasks/current-task.md`
- `.agent/tasks/completed/2026-06-22-search-intent-length-mismatch.md`
- `app/Models/ContentTopic.php`
- `app/Http/Requests/Api/V1/Admin/StoreContentTopicRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentTopicRequest.php`
- `app/Mcp/Tools/CreateTopicSuggestionTool.php`
- `database/migrations/2026_06_18_130000_create_content_topics_table.php`
- `database/migrations/2026_06_22_053500_expand_search_intent_length_on_content_topics_table.php`
- `tests/Feature/ContentTopicAutoAdvanceTest.php`

## Validation

- `php artisan test tests/Feature/ContentTopicAutoAdvanceTest.php`
- result: passed, 2 tests, 8 assertions
- gap: did not run the full suite or a live `php artisan migrate` against the Laravel Cloud database from the error report

## Risks Or Follow-Ups

- existing deployed databases need the new migration applied before the production insert path is safe

## Completion Notes

- aligned `search_intent` length across model-backed validation and schema
- added a regression test covering long AI-generated `search_intent` content
