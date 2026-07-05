# Current Task

## Title

Implement daily AI content caps and weekly content-plan scheduling.

## Scope

- limit automatic high-score topic persistence to 2 AI-suggested topics per day
- limit automatic blog draft generation to 2 posts per day from generated topics
- align scheduled topic discovery with the requested weekly content plan
- keep manual review and publish rules unchanged

## Files Read

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/product.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `docs/AI_CONTENT_ENGINE.md`
- `docs/CONTENT_STRATEGY.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md`
- `routes/console.php`
- `app/Console/Commands/DiscoverContentTopicsCommand.php`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/AI/Tools/SaveTopicIdeaTool.php`
- `app/Models/ContentTopic.php`
- `app/Models/Post.php`
- `app/Modules/Ai/Services/AiWorkflowOrchestrator.php`
- `app/Modules/Ai/Services/TopicDiscoveryWorkflow.php`
- `app/Modules/Ai/Services/DraftGenerationWorkflow.php`
- `app/Modules/Ai/Services/ResolveAutoDraftGenerationDataService.php`
- `app/Modules/Ai/Services/ResolveTopicDiscoveryClusterService.php`
- `app/Modules/ContentTopics/Services/AutoAdvanceHighPriorityTopicService.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/ContentTopics/Services/UpdateContentTopicService.php`
- `app/Modules/ContentTopics/Repositories/ContentTopicRepository.php`
- `app/Modules/ContentTopics/Repositories/EloquentContentTopicRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Requests/Api/V1/Admin/DiscoverContentTopicsRequest.php`
- `database/seeders/CategorySeeder.php`

## Findings

- scheduled topic discovery previously ran every supported category every day with `--count=10`
- high-priority auto-advance previously used a strict `> 90.0` threshold
- automatic topic persistence now skips additional AI-suggested high-priority topics after 2 saved topics in the same day
- automatic draft queueing now stops after 2 same-day blog-writer jobs or generated posts
- weekly scheduling now routes through a dedicated command that maps weekdays to the requested content plan
- Sunday pillar content is currently represented by the existing `developer-ai` category plus `pillar` scheduler metadata because the model has no dedicated pillar taxonomy

## Files Changed

- `.agent/PROJECT-CONTEXT.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `app/AI/Agents/TopicDiscoveryAgent.php`
- `app/Console/Commands/RunWeeklyContentPlanCommand.php`
- `app/Modules/Ai/Services/AiAutomationDailyLimitService.php`
- `app/Modules/ContentTopics/Services/ApproveContentTopicService.php`
- `app/Modules/ContentTopics/Services/AutoAdvanceHighPriorityTopicService.php`
- `database/migrations/2026_06_21_190000_add_score_breakdown_to_content_topics_table.php`
- `docs/AI_CONTENT_ENGINE.md`
- `routes/console.php`
- `tests/Feature/AiAutomationDailyLimitServiceTest.php`
- `tests/Feature/ContentTopicAutoAdvanceTest.php`
- `tests/Feature/RunWeeklyContentPlanCommandTest.php`
- `tests/Feature/TopicDiscoveryWorkflowDailyLimitTest.php`

## Validation

- `php artisan test --filter=AiAutomationDailyLimitServiceTest`
- `php artisan test --filter=TopicDiscoveryWorkflowDailyLimitTest`
- `php artisan test --filter=ContentTopicAutoAdvanceTest`
- `php artisan test --filter=RunWeeklyContentPlanCommandTest`
- `php artisan test --filter='AiAutomationDailyLimitServiceTest|TopicDiscoveryWorkflowDailyLimitTest|ContentTopicAutoAdvanceTest|RunWeeklyContentPlanCommandTest'`
- `vendor/bin/pint --test app/Modules/Ai/Services/AiAutomationDailyLimitService.php app/Console/Commands/RunWeeklyContentPlanCommand.php app/AI/Agents/TopicDiscoveryAgent.php app/Modules/ContentTopics/Services/ApproveContentTopicService.php app/Modules/ContentTopics/Services/AutoAdvanceHighPriorityTopicService.php tests/Feature/AiAutomationDailyLimitServiceTest.php tests/Feature/TopicDiscoveryWorkflowDailyLimitTest.php tests/Feature/ContentTopicAutoAdvanceTest.php tests/Feature/RunWeeklyContentPlanCommandTest.php`
- `php artisan schedule:list`

## Risks

- weekly-plan mapping requires an assumption for Sunday pillar content because no dedicated pillar category exists
- auto-generated draft caps should not block manual admin-triggered generation unless explicitly intended

## Completion Notes

- implemented daily AI automation caps, scheduler remap, supporting docs updates, and a missing `content_topics.score_breakdown` migration required by the existing repository layer
