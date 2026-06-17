# Wide Web Blog Service — AI Agents Integration Tasks

## Purpose

This document defines the service-side implementation tasks for integrating AI Agents into the **Wide Web Blog Service API**.

The current Admin application already has:

- Knowledge Base CRUD implemented
- Topic Queue placeholder
- AI Jobs placeholder

The next step is to implement the real backend services, APIs, jobs, schemas, and AI workflows that these Admin modules can later consume.

---

## Core MVP Flow

```txt
Knowledge Base
    ↓
TopicDiscoveryAgent
    ↓
ContentBriefAgent
    ↓
BlogWriterAgent
    ↓
Draft Post
    ↓
Human review in Admin
```

---

## MVP Rules

- This phase is service/API only.
- Do not implement Admin UI or Livewire in the service repository.
- Do not auto-publish AI-generated content.
- AI-generated posts must always be saved as `draft`.
- Admin user must review, edit, approve, and publish manually.
- Images are manual in this MVP phase.
- AI may suggest image ideas, image placement notes, and alt text only.
- Topic discovery creates suggested topics only.
- Only approved topics can generate content briefs.
- Only approved content briefs can generate draft posts.
- All AI workflows must create `ai_jobs` records.
- All agent executions must create `ai_generation_steps` records.
- Retry logic must not duplicate topics, briefs, or posts.
- Use explicit queue name: `ai`.

---

## Recommended AI Agents

### MVP Agents

1. `TopicDiscoveryAgent`
2. `ContentBriefAgent`
3. `BlogWriterAgent`

### Later Agents

4. `EditorAgent`
5. `SeoOptimizerAgent`
6. `PublishingAgent`

---

## Recommended Service Structure

```txt
service/
├── app/AI/Agents/
│   ├── TopicDiscoveryAgent.php
│   ├── ContentBriefAgent.php
│   ├── BlogWriterAgent.php
│   ├── EditorAgent.php
│   └── SeoOptimizerAgent.php
│
├── app/AI/Contracts/
│   └── ContentAgentInterface.php
│
├── app/AI/DTO/
│   ├── AgentInput.php
│   ├── AgentResult.php
│   ├── TopicDiscoveryInput.php
│   ├── TopicDiscoveryResult.php
│   ├── ContentBriefInput.php
│   ├── ContentBriefResult.php
│   ├── BlogDraftInput.php
│   └── BlogDraftResult.php
│
├── app/AI/Enums/
│   └── AiRunStatus.php
│
├── app/AI/Tools/
│   ├── SearchExistingPostsTool.php
│   ├── SaveTopicIdeaTool.php
│   ├── SaveContentBriefTool.php
│   ├── SavePostDraftTool.php
│   ├── FindInternalLinksTool.php
│   └── CheckDuplicateTopicTool.php
│
├── app/Infrastructure/Ai/
│   ├── Contracts/
│   └── LaravelAiClient.php
│
└── app/Modules/Ai/
    ├── Services/
    └── Repositories/
```

---

## Content Clusters

Topic discovery must stay inside the selected Wide Web Blog niche clusters.

```txt
ai_tools
ai_for_blogging
seo
content_marketing
productivity_automation
developer_ai
```

---

# Phase 3 — AI Content Engine Tasks

---

## WB-SVC-041 — Install and configure Laravel AI SDK

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Install and configure Laravel AI SDK in the service application. This creates the foundation for all AI-powered workflows.

### Deliverables

- Install Laravel AI SDK package
- Configure AI providers through environment variables
- Add default provider and model configuration
- Add AI config file
- Add safe timeout and retry configuration
- Add service-level abstraction over Laravel AI SDK

### Suggested Files / Areas

```txt
config/ai.php
app/Infrastructure/Ai/
app/Infrastructure/Ai/Contracts/
app/Infrastructure/Ai/LaravelAiClient.php
```

### Acceptance Criteria

- Service can call AI through one internal abstraction.
- Provider configuration is environment-based.
- No domain service directly depends on vendor-specific AI classes.
- OpenAI, Anthropic, and Gemini can be supported later through config.
- Tests can fake/mock AI responses.

### Validation

```bash
composer test
php artisan test
```

---

## WB-SVC-042 — Create AI agent base contracts and shared DTOs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create shared contracts for all content agents so each agent follows the same input/output style.

### Deliverables

- Base `ContentAgentInterface`
- Agent input DTOs
- Agent output DTOs
- Structured result objects
- Agent execution result status enum

### Suggested Files / Areas

```txt
app/AI/Contracts/ContentAgentInterface.php
app/AI/DTO/
app/AI/Enums/AiRunStatus.php
app/AI/Agents/
```

### Acceptance Criteria

- Every AI agent returns structured output.
- Agent result includes status, raw response, parsed response, usage metadata, and error info.
- Agents are testable without calling real AI providers.
- Failed AI responses do not crash the workflow silently.

### Validation

```bash
php artisan test
```

---

## WB-SVC-043 — Implement AI job tracking schema and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistent tracking for AI jobs so the Admin placeholder can later become a real AI Jobs screen.

### Deliverables

- `ai_jobs` migration
- `ai_generation_steps` migration
- AI job model
- AI generation step model
- Repository/service layer
- Read API for AI jobs
- Retry endpoint for failed jobs

### Suggested Database Tables

```txt
ai_jobs
- id
- type
- status
- entity_type
- entity_id
- provider
- model
- input_payload
- output_payload
- error_message
- started_at
- completed_at
- failed_at
- timestamps

ai_generation_steps
- id
- ai_job_id
- agent_name
- status
- input_payload
- output_payload
- error_message
- started_at
- completed_at
- timestamps
```

### Suggested API Endpoints

```txt
GET    /api/admin/ai-jobs
GET    /api/admin/ai-jobs/{id}
POST   /api/admin/ai-jobs/{id}/retry
```

### Acceptance Criteria

- Every AI workflow creates an `ai_jobs` record.
- Every agent execution creates an `ai_generation_steps` record.
- Admin can query job status.
- Failed jobs can be retried safely.
- Retry does not duplicate posts or topics.

### Validation

```bash
php artisan migrate
php artisan test
```

---

## WB-SVC-044 — Implement AI token and cost tracking

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Track token usage and estimated cost for each AI call.

### Deliverables

- `ai_job_costs` migration
- Usage recording service
- Provider/model usage fields
- Cost estimation support

### Suggested Database Table

```txt
ai_job_costs
- id
- ai_job_id
- ai_generation_step_id
- provider
- model
- input_tokens
- output_tokens
- total_tokens
- estimated_cost
- actual_cost
- currency
- metadata
- timestamps
```

### Acceptance Criteria

- AI usage is recorded per job and per step.
- Usage can be queried by provider, model, and job.
- Cost can be estimated even when provider does not return exact cost.
- Missing usage metadata does not fail the workflow.

### Validation

```bash
php artisan migrate
php artisan test
```

---

## WB-SVC-045 — Implement prompt template schema and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Store AI prompts in the database instead of hardcoding them in PHP classes.

### Deliverables

- Prompt template migration
- Prompt versioning
- Prompt category/type support
- CRUD/read APIs
- Prompt rendering service

### Suggested Database Tables

```txt
ai_prompt_templates
- id
- name
- key
- type
- description
- status
- active_version_id
- timestamps

ai_prompt_template_versions
- id
- prompt_template_id
- version
- system_prompt
- user_prompt
- output_schema
- variables
- status
- timestamps
```

### Suggested Prompt Types

```txt
topic_discovery
content_brief
blog_writer
editor
seo_optimizer
publishing
```

### Suggested API Endpoints

```txt
GET    /api/admin/ai-prompts
POST   /api/admin/ai-prompts
GET    /api/admin/ai-prompts/{id}
PATCH  /api/admin/ai-prompts/{id}
POST   /api/admin/ai-prompts/{id}/versions
POST   /api/admin/ai-prompts/{id}/activate-version/{versionId}
```

### Acceptance Criteria

- Prompts are stored, categorized, versioned, and retrievable.
- Only one active prompt version is used by each agent.
- Prompt variables can be rendered safely.
- Prompt output schema can be stored for structured output validation.

### Validation

```bash
php artisan migrate
php artisan test
```

---

## WB-SVC-046 — Implement Topic Queue schema, repository, and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the real backend support for the Admin Topic Queue placeholder.

### Deliverables

- `content_topics` migration
- Topic model
- Topic repository
- Topic service
- CRUD APIs
- Status transition APIs

### Suggested Database Table

```txt
content_topics
- id
- title
- slug
- cluster
- primary_keyword
- secondary_keywords
- search_intent
- priority_score
- difficulty_note
- source
- status
- notes
- approved_at
- rejected_at
- used_at
- timestamps
```

### Statuses

```txt
suggested
approved
rejected
used
```

### Suggested API Endpoints

```txt
GET    /api/admin/content-topics
POST   /api/admin/content-topics
GET    /api/admin/content-topics/{id}
PATCH  /api/admin/content-topics/{id}
DELETE /api/admin/content-topics/{id}

POST   /api/admin/content-topics/{id}/approve
POST   /api/admin/content-topics/{id}/reject
POST   /api/admin/content-topics/{id}/mark-used
```

### Acceptance Criteria

- Topic Queue placeholder can later consume real service data.
- Topics can be suggested, approved, rejected, and marked as used.
- Only approved topics can move to content brief generation.
- Duplicate topic checks are supported by service logic.

### Validation

```bash
php artisan migrate
php artisan test
```

---

## WB-SVC-047 — Implement TopicDiscoveryAgent

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the first real AI agent: `TopicDiscoveryAgent`.

This agent generates topic ideas only inside the approved Wide Web Blog clusters.

### Deliverables

- `TopicDiscoveryAgent`
- Topic discovery input DTO
- Topic discovery output DTO
- Prompt template integration
- Knowledge Base context support
- Duplicate topic check
- Save topic suggestions

### Suggested Files / Areas

```txt
app/AI/Agents/TopicDiscoveryAgent.php
app/AI/Tools/CheckDuplicateTopicTool.php
app/AI/Tools/SaveTopicIdeaTool.php
app/Modules/ContentTopics/
```

### Agent Input

```txt
cluster
target_count
audience
existing_topics
knowledge_context
```

### Agent Output

```txt
title
slug
cluster
primary_keyword
secondary_keywords
search_intent
priority_score
difficulty_note
summary
```

### Acceptance Criteria

- Agent creates topic suggestions only.
- Agent does not approve topics automatically.
- Agent checks existing posts/topics before saving suggestions.
- Agent output is stored in `content_topics`.
- Agent execution is tracked in `ai_jobs` and `ai_generation_steps`.

### Validation

```bash
php artisan test
```

---

## WB-SVC-048 — Implement topic discovery job and scheduler

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow topic discovery to run asynchronously through Laravel queues and scheduler.

### Deliverables

- `DiscoverContentTopicsJob`
- Explicit `ai` queue usage
- Console command for manual topic discovery
- Scheduler registration
- Retry-safe topic creation

### Suggested Files / Areas

```txt
app/Jobs/AI/DiscoverContentTopicsJob.php
app/Console/Commands/DiscoverContentTopicsCommand.php
routes/console.php
```

### Suggested Command

```bash
php artisan ai:discover-topics --cluster=ai_tools --count=10
```

### Acceptance Criteria

- Topic discovery can run from command line.
- Topic discovery can run from queue.
- Scheduler can list the task.
- Retries do not create duplicate topics.
- No topic is auto-approved.

### Validation

```bash
php artisan schedule:list
php artisan test
```

---

## WB-SVC-049 — Implement Content Brief schema and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create backend support for structured content briefs generated from approved topics.

### Deliverables

- `content_briefs` migration
- Content brief model
- Repository/service layer
- APIs for brief generation and review
- Link content brief to topic

### Suggested Database Table

```txt
content_briefs
- id
- content_topic_id
- title
- slug
- meta_title
- meta_description
- primary_keyword
- secondary_keywords
- search_intent
- outline
- headings
- faq_suggestions
- internal_link_suggestions
- image_suggestions
- status
- approved_at
- timestamps
```

### Statuses

```txt
draft
approved
rejected
used
```

### Suggested API Endpoints

```txt
GET    /api/admin/content-briefs
GET    /api/admin/content-briefs/{id}
PATCH  /api/admin/content-briefs/{id}
POST   /api/admin/content-briefs/{id}/approve
POST   /api/admin/content-topics/{id}/generate-brief
```

### Acceptance Criteria

- Briefs are generated only from approved topics.
- Brief output is structured data, not raw HTML.
- Briefs can be reviewed and approved before draft generation.
- Brief includes image ideas and alt text suggestions, but does not generate images.

### Validation

```bash
php artisan migrate
php artisan test
```

---

## WB-SVC-050 — Implement ContentBriefAgent

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create `ContentBriefAgent`, which turns approved topic ideas into structured article plans.

### Deliverables

- `ContentBriefAgent`
- Brief generation DTOs
- Knowledge Base context injection
- Existing post/internal link context
- Structured output validation

### Suggested Files / Areas

```txt
app/AI/Agents/ContentBriefAgent.php
app/AI/Tools/SearchExistingPostsTool.php
app/AI/Tools/FindInternalLinksTool.php
app/AI/Tools/SaveContentBriefTool.php
```

### Agent Output

```txt
recommended_title
slug
meta_title
meta_description
intro_angle
target_audience
outline
heading_structure
faq_suggestions
internal_link_suggestions
image_ideas
alt_text_suggestions
```

### Acceptance Criteria

- Agent uses approved topic as input.
- Agent can use Knowledge Base entries as context.
- Agent returns structured output.
- Agent saves a draft content brief.
- Agent does not create a post directly.

### Validation

```bash
php artisan test
```

---

## WB-SVC-051 — Implement BlogWriterAgent

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `13`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create `BlogWriterAgent`, which turns an approved content brief into a draft blog post.

### Deliverables

- `BlogWriterAgent`
- Draft generation DTOs
- Draft post creation service
- Post block mapping
- SEO metadata draft support
- AI job tracking

### Suggested Files / Areas

```txt
app/AI/Agents/BlogWriterAgent.php
app/AI/Tools/SavePostDraftTool.php
app/Modules/Posts/
app/Modules/PostBlocks/
app/Modules/Seo/
```

### Agent Output

```txt
title
slug
excerpt
markdown_body
content_blocks
seo_title
meta_description
faq_suggestions
suggested_tags
image_placement_notes
alt_text_suggestions
```

### Acceptance Criteria

- Agent only creates draft posts.
- Agent cannot publish posts.
- Generated content maps to post blocks or markdown source.
- Raw generated HTML is not the primary content source.
- SEO fields are saved as editable draft metadata.
- Image notes are saved as suggestions only.

### Validation

```bash
php artisan test
```

---

## WB-SVC-052 — Implement draft generation API and queue flow

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose draft generation through API and run it asynchronously.

### Deliverables

- Generate draft endpoint
- `GenerateBlogDraftJob`
- Retry-safe draft creation
- AI job status updates
- Link generated draft to source brief/topic

### Suggested API Endpoint

```txt
POST /api/admin/content-briefs/{id}/generate-draft
```

### Suggested Files / Areas

```txt
app/Jobs/AI/GenerateBlogDraftJob.php
app/Http/Controllers/Admin/AI/
app/Modules/Ai/Services/
```

### Acceptance Criteria

- Admin can request draft generation from an approved brief.
- Job status is visible through AI Jobs API.
- Failed generation can be retried.
- Retry does not create duplicate posts.
- Post remains in draft state.

### Validation

```bash
php artisan test
```

---

## WB-SVC-053 — Implement Knowledge Base context retrieval for agents

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Allow AI agents to retrieve relevant Knowledge Base entries and use them as grounded context.

### Deliverables

- Knowledge context query service
- Context formatter for prompts
- Optional metadata filtering
- Agent-safe context size limits

### Suggested Files / Areas

```txt
app/Modules/KnowledgeBase/Services/KnowledgeContextService.php
app/AI/Context/
```

### Acceptance Criteria

- Agents can request relevant Knowledge Base context.
- Context is limited to avoid oversized prompts.
- Draft generation can include editorial references.
- Knowledge Base content is used as context, not overwritten by AI.

### Validation

```bash
php artisan test
```

---

## WB-SVC-054 — Implement internal AI tools

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create internal tools that agents can use during content workflows.

### Deliverables

- `SearchExistingPostsTool`
- `CheckDuplicateTopicTool`
- `SaveTopicIdeaTool`
- `SaveContentBriefTool`
- `SavePostDraftTool`
- `FindInternalLinksTool`

### Suggested Files / Areas

```txt
app/AI/Tools/
```

### Acceptance Criteria

- Tools are small, focused, and testable.
- Tools call application services, not raw database queries where avoidable.
- Duplicate topic detection is available before saving generated ideas.
- Internal link suggestions can search existing published posts.
- Tools do not publish posts.

### Validation

```bash
php artisan test
```

---

## WB-SVC-055 — Implement AI workflow orchestration service

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create orchestration services that coordinate agents, jobs, and persistence.

### Deliverables

- Topic discovery orchestration
- Brief generation orchestration
- Draft generation orchestration
- AI job lifecycle management
- Retry-safe execution rules

### Suggested Files / Areas

```txt
app/Modules/Ai/Services/AiWorkflowOrchestrator.php
app/Modules/Ai/Services/TopicDiscoveryWorkflow.php
app/Modules/Ai/Services/ContentBriefWorkflow.php
app/Modules/Ai/Services/DraftGenerationWorkflow.php
```

### Acceptance Criteria

- Workflow logic is not inside controllers.
- Controllers only validate request and dispatch service/job.
- Each workflow creates and updates AI job records.
- Each workflow records generation steps.
- Workflows are safe to retry.

### Validation

```bash
php artisan test
```

---

## WB-SVC-056 — Implement Laravel MCP server for content operations

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Add Laravel MCP support so external AI clients can interact with selected service capabilities through controlled MCP tools.

### Deliverables

- MCP server registration
- MCP tools for safe content operations
- MCP resources for readable context
- MCP prompts for reusable content workflows

### Suggested MCP Tools

```txt
searchKnowledgeBase
listContentTopics
createTopicSuggestion
generateContentBrief
generateBlogDraft
getAiJobStatus
```

### Suggested MCP Resources

```txt
knowledge-base://entries
topics://suggested
topics://approved
ai-jobs://recent
```

### Suggested MCP Prompts

```txt
topic-discovery
content-brief
blog-draft
seo-review
```

### Acceptance Criteria

- MCP exposes only safe service actions.
- MCP tools do not publish posts.
- MCP tools respect the same validation and service-layer rules as APIs.
- MCP is disabled or protected in production unless explicitly configured.

### Validation

```bash
php artisan test
```

---

## WB-SVC-057 — Add AI-specific service documentation

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Update project documentation so future agents understand how the AI content engine works.

### Deliverables

- Update AI content engine documentation
- Add agent workflow documentation
- Add prompt management documentation
- Add AI job lifecycle documentation

### Suggested Files

```txt
docs/AI_CONTENT_ENGINE.md
docs/AI_AGENTS.md
docs/PROMPT_MANAGEMENT.md
docs/AI_JOB_LIFECYCLE.md
.agent/MEMORY.md
.agent/skills/ai-content-engine.md
.agent/tasks/current-task.md
```

### Acceptance Criteria

- Documentation explains MVP agent flow.
- Documentation clearly states human approval before publishing.
- Documentation states images are manual in this phase.
- Documentation explains Topic Queue and AI Jobs admin placeholders.

### Validation

```bash
php artisan test
```

---

# MVP Build Order

Use this order for the coding agent:

```txt
1. WB-SVC-041 — Install and configure Laravel AI SDK
2. WB-SVC-042 — Create AI agent base contracts and shared DTOs
3. WB-SVC-043 — Implement AI job tracking schema and APIs
4. WB-SVC-044 — Implement AI token and cost tracking
5. WB-SVC-045 — Implement prompt template schema and APIs
6. WB-SVC-046 — Implement Topic Queue schema, repository, and APIs
7. WB-SVC-047 — Implement TopicDiscoveryAgent
8. WB-SVC-048 — Implement topic discovery job and scheduler
9. WB-SVC-049 — Implement Content Brief schema and APIs
10. WB-SVC-050 — Implement ContentBriefAgent
11. WB-SVC-051 — Implement BlogWriterAgent
12. WB-SVC-052 — Implement draft generation API and queue flow
13. WB-SVC-053 — Implement Knowledge Base context retrieval for agents
14. WB-SVC-054 — Implement internal AI tools
15. WB-SVC-055 — Implement AI workflow orchestration service
16. WB-SVC-056 — Implement Laravel MCP server for content operations
17. WB-SVC-057 — Add AI-specific service documentation
```

---

# Coding Agent Guardrails

```md
## AI Content Engine Rules

- This is the Laravel 13 service application only.
- Do not implement Admin UI or Livewire here.
- Use Laravel AI SDK for AI provider interactions.
- Keep provider-specific logic isolated behind service abstractions.
- Use application services, repositories, DTOs, jobs, and resources consistently.
- Do not hardcode prompts inside agent classes.
- Prompts must be database-backed and versioned.
- All AI workflows must create ai_jobs records.
- All agent steps must create ai_generation_steps records.
- AI-generated posts must always be saved as draft.
- Never publish automatically.
- Images are manual in this MVP phase.
- AI may suggest image ideas, placement notes, and alt text only.
- Topic discovery may create suggested topics only.
- Only approved topics can generate content briefs.
- Only approved content briefs can generate draft posts.
- Retry logic must not duplicate topics, briefs, or posts.
- Use explicit queue name: ai.
- Add tests for services, jobs, status transitions, and retry safety.
```

---

# First Coding Agent Prompt

```md
# Task: Implement Laravel AI SDK foundation for Wide Web Blog Service

Act as a senior Laravel 13 backend engineer and AI content workflow architect.

You are working inside the `service` repository of Wide Web Blog.

The admin application already has:
- Knowledge Base CRUD implemented
- Topic Queue placeholder
- AI Jobs placeholder

Now implement the service-side foundation for AI Agents.

## Task Scope

Implement:

1. Laravel AI SDK installation/configuration
2. Internal AI provider abstraction
3. Base AI agent contracts
4. Shared DTOs/result objects
5. AI job tracking schema and APIs
6. AI generation step tracking
7. Token/cost tracking schema if practical in this task
8. Tests for the foundation layer

## Important References

Review the official Laravel 13 documentation for:
- Laravel AI SDK
- Laravel MCP
- Laravel Boost

Use Boost only as development-agent support. Do not build product behavior around Boost.

## Architecture Rules

- This is service-only.
- Do not implement Admin UI.
- Do not implement Livewire.
- Do not implement frontend code.
- Do not publish AI-generated content automatically.
- All generated posts in later tasks must remain drafts.
- Keep human review and approval mandatory.
- Images are manual in MVP.
- AI may only suggest image ideas, image placement notes, and alt text.

## Suggested Structure

Create or align with:

app/AI/
app/AI/Agents/
app/AI/Contracts/
app/AI/DTO/
app/AI/Enums/
app/AI/Tools/
app/Infrastructure/Ai/
app/Modules/Ai/
app/Modules/Ai/Services/
app/Modules/Ai/Repositories/

## Expected Deliverables

- config/ai.php
- AI provider/client abstraction
- ContentAgentInterface
- Agent input/output DTOs
- AI job migration
- AI generation steps migration
- AI job model/repository/service
- Admin read APIs for AI jobs
- Retry endpoint skeleton if safe
- Tests

## Validation

Run:

php artisan migrate
php artisan test

## Completion Notes

After implementation, update the task file with:
- changed files
- migrations added
- tests added
- commands run
- risks/TBC items
```
