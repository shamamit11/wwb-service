# Task Summary

Implement `WB-SVC-051 — BlogWriterAgent` so approved content briefs can generate retry-safe draft posts with structured post blocks, editable SEO draft metadata, and AI job tracking.

## Requested Outcome

- add `BlogWriterAgent`
- update blog draft DTOs
- add `SavePostDraftTool`
- add draft post generation service from approved briefs
- preserve draft-only behavior with structured block persistence
- validate with focused tests and `php artisan test`

## Scope Boundaries

- in scope: service-side AI draft generation, post persistence, block mapping, SEO draft persistence, job and step tracking, retry-safe reuse of existing draft post for the same brief
- out of scope: admin UI, public frontend work, queue command wiring for draft generation, publishing flow, media generation

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/seo.md`
- `docs/AI_CONTENT_ENGINE.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`

## Repository Files Inspected

- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Agents/ContentBriefAgent.php`
- `app/AI/Contracts/ContentAgentInterface.php`
- `app/AI/DTO/AgentInput.php`
- `app/AI/DTO/AgentOutput.php`
- `app/AI/DTO/AgentResult.php`
- `app/AI/DTO/BlogDraftInput.php`
- `app/AI/DTO/BlogDraftResult.php`
- `app/AI/Tools/SaveTopicIdeaTool.php`
- `app/AI/Tools/SaveContentBriefTool.php`
- `app/AI/Tools/SearchExistingPostsTool.php`
- `app/AI/Tools/FindInternalLinksTool.php`
- `app/Models/AiJob.php`
- `app/Models/AiPromptTemplate.php`
- `app/Models/Category.php`
- `app/Models/ContentBrief.php`
- `app/Models/ContentTopic.php`
- `app/Models/Post.php`
- `app/Models/Tag.php`
- `app/Models/User.php`
- `app/Modules/Ai/Services/TrackAiJobService.php`
- `app/Modules/ContentBriefs/Data/UpdateContentBriefData.php`
- `app/Modules/ContentBriefs/Repositories/ContentBriefRepository.php`
- `app/Modules/ContentBriefs/Repositories/EloquentContentBriefRepository.php`
- `app/Modules/ContentBriefs/Services/GenerateContentBriefFromTopicService.php`
- `app/Modules/ContentBriefs/Services/UpdateContentBriefService.php`
- `app/Modules/ContentTopics/Services/MarkContentTopicUsedService.php`
- `app/Modules/Categories/Repositories/CategoryRepository.php`
- `app/Modules/Posts/Data/CreatePostData.php`
- `app/Modules/Posts/Data/CreatePostCommandData.php`
- `app/Modules/Posts/Data/PostBlockPayloadData.php`
- `app/Modules/Posts/Data/UpdatePostData.php`
- `app/Modules/Posts/Data/UpdatePostCommandData.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/PostBlockPayloadMapper.php`
- `app/Modules/Posts/Services/PostBlockPayloadValidator.php`
- `app/Modules/Posts/Services/UpdatePostService.php`
- `app/Modules/Seo/Data/UpdateSeoMetadataData.php`
- `app/Modules/Seo/Services/FindRelatedContentService.php`
- `app/Modules/Seo/Services/UpsertSeoMetadataService.php`
- `app/Modules/Tags/Repositories/TagRepository.php`
- `tests/Feature/AiContentAgentContractsTest.php`
- `tests/Feature/ContentBriefAgentTest.php`
- `tests/Feature/PostCommandServiceTest.php`
- `tests/Feature/TopicDiscoveryAgentTest.php`

## Plan

1. Update the blog draft DTO contract to match the requested structured output and explicit persistence inputs.
2. Implement `BlogWriterAgent` with prompt rendering, context hydration, structured response parsing, and AI job or step tracking.
3. Add `SavePostDraftTool` plus a brief-to-draft orchestration service with retry-safe post reuse and draft-only persistence.
4. Add focused feature and contract tests for successful generation, persistence, and approval gating.
5. Run `php artisan test` and record any remaining risk.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/AI/DTO/BlogDraftInput.php`
- `app/AI/DTO/BlogDraftResult.php`
- `app/AI/Tools/SavePostDraftTool.php`
- `app/Modules/Posts/Data/GeneratedBlogDraftData.php`
- `app/Modules/Posts/Exceptions/BlogDraftGenerationNotAllowedException.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `tests/Feature/AiContentAgentContractsTest.php`
- `tests/Feature/BlogWriterAgentTest.php`

## Validation

- `php -l app/AI/Agents/BlogWriterAgent.php`
- `php -l app/AI/Tools/SavePostDraftTool.php`
- `php -l app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `php -l tests/Feature/BlogWriterAgentTest.php`
- `php -l tests/Feature/AiContentAgentContractsTest.php`
- `php artisan test tests/Feature/AiContentAgentContractsTest.php`
- `php artisan test tests/Feature/BlogWriterAgentTest.php`
- `php artisan test tests/Feature/TopicDiscoveryAgentTest.php`
- `php artisan test tests/Feature/ContentBriefAgentTest.php`
- `php artisan test` failed in `Tests\Feature\ActivityLogTest::test_editorial_mutations_are_recorded_with_curated_audit_payloads` because storage credentials could not be retrieved from the instance metadata service for an external write path

## Risks Or Follow-Ups

- draft generation now defaults `author_user_id` to `1` when omitted, but still needs explicit `category_id` because the current schema has no safe implicit category mapping for AI-created drafts
- full-suite validation is currently blocked by an environment storage credential issue outside the new blog-writer path

## Completion Notes

- completed `WB-SVC-051` implementation
- `BlogWriterAgent` now generates draft-only posts from approved briefs, persists structured post blocks and editable SEO draft metadata, tracks `ai_jobs` and `ai_generation_steps`, and reuses the same draft post on retry via `source_content_brief_id`
