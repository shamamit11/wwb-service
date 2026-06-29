# Task Summary

Add a service-side originality/plagiarism review gate so AI-generated articles can be flagged before they move forward in the editorial workflow.

## Requested Outcome

- create a new branch from `main`
- inspect the current AI article pipeline
- implement a practical originality check or review-flag mechanism for AI-generated articles

## Scope Boundaries

- service repository only
- no sibling app changes
- preserve the draft-first and manual-publish rules

## Assumptions

- `main` was the correct requested branch base
- a first pass internal originality gate is acceptable without introducing a third-party plagiarism API in this change
- flagged drafts should remain drafts and become easier for admin to identify, not auto-publish or auto-reject

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/skills/ai-content-engine.md`

## Repository Files Inspected

- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/Tools/SavePostDraftTool.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Models/ContentTopic.php`
- `app/Models/Post.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/ContentTopics/Services/MarkContentTopicUsedService.php`
- `app/Modules/Posts/Data/CreatePostCommandData.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Data/UpdatePostCommandData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromTopicService.php`
- `app/Modules/Posts/Services/ListAdminPostsService.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`

## Plan

1. Add an internal originality assessment service for generated drafts.
2. Run the assessment automatically when AI drafts are saved.
3. Expose and filter the resulting review flag in post API payloads.
4. Validate with targeted tests.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/AI/Tools/SavePostDraftTool.php`
- `app/Http/Requests/Api/V1/Admin/ListPostsRequest.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Posts/Data/PostFiltersData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Services/AssessPostOriginalityService.php`
- `tests/Feature/PostOriginalityReviewTest.php`

## Validation

- branch created from updated `main`
- `php artisan test --filter=PostOriginalityReviewTest`
- `vendor/bin/phpstan analyse app/AI/Tools/SavePostDraftTool.php app/Http/Requests/Api/V1/Admin/ListPostsRequest.php app/Http/Resources/Api/V1/PostResource.php app/Modules/Posts/Data/PostFiltersData.php app/Modules/Posts/Repositories/EloquentPostRepository.php app/Modules/Posts/Repositories/PostRepository.php app/Modules/Posts/Services/AssessPostOriginalityService.php tests/Feature/PostOriginalityReviewTest.php`

## Risks Or Follow-Ups

- this is an internal overlap detector, not a substitute for a full external plagiarism provider
- admin UI changes may still be needed later to make the flag prominent in the workflow

## Completion Notes

- added an automatic originality assessment step to the AI draft save flow
- stored `needs_originality_review` and `originality_review` in post metadata
- exposed the originality review signal in `PostResource`
- added admin post filtering support for `needs_originality_review`
- validated with targeted tests and focused static analysis
