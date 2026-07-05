# Current Task

## Task Summary

Implement a news-ingestion workflow using Currents for discovery and Firecrawl for extraction, with scoring and routing into knowledge-base/topic flows, plus scheduling and a reviewable admin API surface.

## Requested Outcome

- add DB tables and models for news discovery, extraction, scoring, and routing
- add Laravel jobs and services for Currents ingest, Firecrawl extraction, scoring, and routing
- define scoring rules and routing logic in code
- add a safe scheduler entry for discovery orchestration
- add admin API endpoints to review news items and manually trigger discover, score, extract, and route actions
- keep generated blog content draft-only

## Scope Boundaries

- in scope: backend schema, services, jobs, scheduling/config hooks, admin API endpoints, and targeted tests
- out of scope: admin UI implementation, frontend rendering, and full production credentials setup

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/ARCHITECTURE.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/skills/ai-orchestration.md`
- `.agent/skills/ai-content-engine.md`
- `.agent/skills/queue-scheduler.md`
- `.agent/skills/database.md`
- `docs/AI_JOB_LIFECYCLE.md`

## Repository Files Inspected

- `config/services.php`
- `app/Providers/AppServiceProvider.php`
- `app/Infrastructure/Ai/LaravelAiClient.php`
- `app/Models/Category.php`
- `app/Models/ContentTopic.php`
- `app/Models/KnowledgeBaseEntry.php`
- `app/Modules/Shared/Data/DataTransferObject.php`
- `app/Modules/KnowledgeBase/Data/CreateKnowledgeBaseEntryData.php`
- `app/Modules/KnowledgeBase/Data/LinkKnowledgeBaseEntryToTopicData.php`
- `app/Modules/KnowledgeBase/Repositories/EloquentKnowledgeBaseEntryRepository.php`
- `app/Modules/KnowledgeBase/Services/CreateKnowledgeBaseEntryService.php`
- `app/Modules/KnowledgeBase/Services/LinkKnowledgeBaseEntryToTopicService.php`
- `app/Modules/ContentTopics/Data/CreateContentTopicData.php`
- `app/Modules/ContentTopics/Services/CreateContentTopicService.php`
- `app/Modules/Ai/Services/ResolveTopicDiscoveryClusterService.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `database/migrations/2026_06_18_130000_create_content_topics_table.php`
- `tests/Feature/KnowledgeBaseEntryRepositoryTest.php`
- `tests/Feature/TopicDiscoveryWorkflowDailyLimitTest.php`
- `routes/api.php`
- `routes/console.php`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Http/Controllers/Api/V1/Admin/KnowledgeBaseEntryController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Requests/Api/V1/Admin/ListAiJobsRequest.php`
- `app/Http/Requests/Api/V1/Admin/ListKnowledgeBaseEntriesRequest.php`
- `app/Http/Requests/Api/V1/Admin/ListContentTopicsRequest.php`
- `app/Http/Resources/Api/ApiResource.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Http/Resources/Api/V1/KnowledgeBaseEntryResource.php`
- `app/Http/Resources/Api/V1/ContentTopicResource.php`
- `app/Modules/Ai/Services/ListAdminAiJobsService.php`
- `app/Modules/KnowledgeBase/Services/ListAdminKnowledgeBaseEntriesService.php`
- `app/Modules/ContentTopics/Services/ListAdminContentTopicsService.php`
- `app/Modules/ContentTopics/Repositories/EloquentContentTopicRepository.php`
- `tests/Feature/AdminApiAuthTest.php`
- `tests/Feature/KnowledgeBaseApiTest.php`

## Plan

1. Load AI orchestration, queue, and integration context plus the smallest existing repository slices for jobs, models, and external clients.
2. Implement schema, services, jobs, and scheduling/config for the news pipeline.
3. Add targeted tests, validate, and record completion notes.

## Changed Files

- `config/news.php`
- `config/services.php`
- `.env.example`
- `database/migrations/2026_06_21_200000_create_news_sources_table.php`
- `database/migrations/2026_06_21_200100_create_news_items_table.php`
- `database/migrations/2026_06_21_200200_create_news_item_extractions_table.php`
- `database/migrations/2026_06_21_200300_create_news_item_scores_table.php`
- `database/migrations/2026_06_21_200400_create_news_item_routes_table.php`
- `app/Models/ContentTopic.php`
- `app/Models/NewsSource.php`
- `app/Models/NewsItem.php`
- `app/Models/NewsItemExtraction.php`
- `app/Models/NewsItemScore.php`
- `app/Models/NewsItemRoute.php`
- `app/Infrastructure/News/Contracts/NewsDiscoveryClient.php`
- `app/Infrastructure/News/Contracts/NewsContentExtractionClient.php`
- `app/Infrastructure/News/Data/DiscoveredNewsArticleData.php`
- `app/Infrastructure/News/Data/ExtractedNewsContentData.php`
- `app/Infrastructure/News/CurrentsNewsClient.php`
- `app/Infrastructure/News/FirecrawlContentClient.php`
- `app/Modules/News/Data/CreateNewsSourceData.php`
- `app/Modules/News/Data/UpsertNewsItemData.php`
- `app/Modules/News/Data/CreateNewsItemExtractionData.php`
- `app/Modules/News/Data/CreateNewsItemScoreData.php`
- `app/Modules/News/Data/CreateNewsItemRouteData.php`
- `app/Modules/News/Data/NewsItemFiltersData.php`
- `app/Modules/News/Repositories/NewsSourceRepository.php`
- `app/Modules/News/Repositories/EloquentNewsSourceRepository.php`
- `app/Modules/News/Repositories/NewsItemRepository.php`
- `app/Modules/News/Repositories/EloquentNewsItemRepository.php`
- `app/Modules/News/Services/NewsDiscoveryService.php`
- `app/Modules/News/Services/NewsScoringService.php`
- `app/Modules/News/Services/ExtractNewsItemContentService.php`
- `app/Modules/News/Services/GenerateKnowledgeBaseFromNewsService.php`
- `app/Modules/News/Services/GenerateTopicFromNewsService.php`
- `app/Modules/News/Services/NewsRoutingService.php`
- `app/Modules/News/Services/ListAdminNewsItemsService.php`
- `app/Modules/News/Services/ReadNewsItemService.php`
- `app/Jobs/News/DiscoverNewsItemsJob.php`
- `app/Jobs/News/ScoreNewsItemJob.php`
- `app/Jobs/News/ExtractNewsItemContentJob.php`
- `app/Jobs/News/RouteNewsItemJob.php`
- `app/Console/Commands/DiscoverNewsCommand.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/Api/V1/Admin/NewsItemController.php`
- `app/Http/Requests/Api/V1/Admin/ListNewsItemsRequest.php`
- `app/Http/Requests/Api/V1/Admin/DiscoverNewsItemsRequest.php`
- `app/Http/Resources/Api/V1/NewsItemResource.php`
- `routes/api.php`
- `routes/console.php`
- `tests/Feature/NewsDiscoveryServiceTest.php`
- `tests/Feature/NewsScoringServiceTest.php`
- `tests/Feature/NewsRoutingServiceTest.php`
- `tests/Feature/NewsAdminApiTest.php`

## Validation

- `php artisan test --filter='NewsAdminApiTest|NewsDiscoveryServiceTest|NewsScoringServiceTest|NewsRoutingServiceTest'`
- `vendor/bin/pint --test app/Http/Controllers/Api/V1/Admin/NewsItemController.php app/Http/Requests/Api/V1/Admin/ListNewsItemsRequest.php app/Http/Requests/Api/V1/Admin/DiscoverNewsItemsRequest.php app/Http/Resources/Api/V1/NewsItemResource.php app/Modules/News routes/api.php routes/console.php app/Console/Commands/DiscoverNewsCommand.php tests/Feature/NewsAdminApiTest.php`
- `php artisan schedule:list | rg "news:discover"`

## Risks Or Follow-Ups

- external provider APIs will require environment keys and production tuning
- Firecrawl extraction currently uses deterministic content parsing, not a second AI fact-extraction pass
- the admin review surface is API-only for now; the admin app still needs to consume these endpoints

## Completion Notes

- implemented a full backend news pipeline with Currents discovery, Firecrawl extraction, multi-table persistence, scoring, routing into KB/topics, and optional draft queueing through the existing topic auto-advance flow
- added a scheduled `news:discover` orchestration entry plus admin API endpoints to review news items and manually trigger discover, score, extract, and route actions
