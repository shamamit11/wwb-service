# Phase 2 Status And Next Steps

This document summarizes the current state of the **AI-Assisted Publishing** phase for the `widewebblog/service` repository.

Important naming note:

- `docs/ROADMAP.md` calls this work **Phase 2: AI-Assisted Publishing**
- `WB_SERVICE_AI_AGENTS_TASKS.md` labels the same service backlog as **Phase 3 — AI Content Engine**

This file follows the **roadmap naming** and therefore treats the current AI content engine work as **Phase 2**.

## Scope Used For This Review

Included:

- AI-assisted publishing service work
- topic discovery, briefs, draft generation, AI job tracking, prompt management, MCP, and related contracts
- service-side APIs and workflows

Explicitly excluded from this review:

- Newsletter module
- Newsletter generation agent
- Admin UI
- frontend forms/pages
- future-phase agents such as ImageGenerator/Image workflow, EditorAgent, SeoOptimizerAgent, and PublishingAgent

## Overall Conclusion

For the service repository, **Phase 2 is mostly implemented**.

The core MVP workflow described in the docs is already present:

`Knowledge Base -> TopicDiscoveryAgent -> Topic approval -> ContentBriefAgent -> Brief approval -> BlogWriterAgent -> Draft post -> Human review and publish`

The remaining work for this phase is mostly **polish and “should-have” workflow enhancement**, not missing core foundation.

## Implemented In This Phase

### 1. AI Provider Foundation

Implemented:

- provider-agnostic AI client abstraction
- environment/config-based AI setup
- Laravel AI SDK integration

Source history:

- `WB-SVC-041`

### 2. Shared AI Contracts And DTOs

Implemented:

- base agent contract
- structured input/output DTOs
- result/status model for agent execution

Source history:

- `WB-SVC-042`

### 3. AI Job Tracking

Implemented:

- `ai_jobs`
- `ai_generation_steps`
- read APIs
- retry flow
- queue-backed job execution tracking

Source history:

- `WB-SVC-043`
- `WB-SVC-055`

### 4. AI Usage / Cost Tracking

Implemented:

- token and cost tracking schema
- service-level cost recording

Source history:

- `WB-SVC-044`

Note:

- this is already ahead of the roadmap, because cost tracking is described there as a later advanced-publishing concern.

### 5. Prompt Management

Implemented:

- prompt template schema
- prompt versions
- active version activation
- admin APIs
- runtime prompt rendering service

Source history:

- `WB-SVC-045`

### 6. Topic Queue

Implemented:

- `content_topics` schema
- repository/service layer
- admin APIs
- approval/rejection/used workflow

Source history:

- `WB-SVC-046`

### 7. TopicDiscoveryAgent

Implemented:

- topic discovery agent
- duplicate-safe save behavior
- queue/job execution path
- command/scheduler support

Source history:

- `WB-SVC-047`
- `WB-SVC-048`

### 8. Content Briefs

Implemented:

- `content_briefs` schema
- admin APIs
- approval flow
- brief generation agent

Source history:

- `WB-SVC-049`
- `WB-SVC-050`

### 9. Blog Draft Generation

Implemented:

- blog writer agent
- draft-only persistence
- admin API trigger
- queue flow
- retry-safe reuse of existing generated draft where appropriate

Source history:

- `WB-SVC-051`
- `WB-SVC-052`

### 10. Knowledge-Based Grounding

Implemented:

- Knowledge Base context retrieval
- prompt-safe formatter
- metadata filtering
- bounded context size

Source history:

- `WB-SVC-053`

### 11. Internal AI Tools

Implemented:

- `SearchExistingPostsTool`
- `CheckDuplicateTopicTool`
- `SaveTopicIdeaTool`
- `SaveContentBriefTool`
- `SavePostDraftTool`
- `FindInternalLinksTool`

Source history:

- `WB-SVC-054`

### 12. Workflow Orchestration

Implemented:

- `AiWorkflowOrchestrator`
- `TopicDiscoveryWorkflow`
- `ContentBriefWorkflow`
- `DraftGenerationWorkflow`

Source history:

- `WB-SVC-055`

### 13. MCP Content Operations

Implemented:

- safe MCP tools
- MCP resources
- MCP prompts
- production gating

Source history:

- `WB-SVC-056`

Note:

- this also goes beyond the roadmap’s “could have” line for the AI-assisted phase.

### 14. AI Documentation

Implemented:

- AI content engine documentation
- agent workflow documentation
- prompt management documentation
- AI job lifecycle documentation

Source history:

- `WB-SVC-057`

### 15. Admin Draft Review API Contract Support

Implemented:

- explicit admin post filters for:
  - `is_ai_generated`
  - `source_content_brief_id`
  - `source_content_topic_id`
  - `generated_by_ai_job_id`
- explicit post resource provenance fields:
  - `is_ai_generated`
  - `source_content_brief_id`
  - `source_content_topic_id`
  - `generated_by_ai_job_id`
  - `generated_by`

Result:

- the admin draft review page is no longer blocked by a missing service contract

### 16. Draft Rewrite And Section Regeneration

Implemented:

- post-level draft rewrite workflow
- paragraph and section-level regeneration support
- admin API contract for queued rewrites
- MCP tool and prompt for safe rewrite operations
- AI job and generation-step tracking for rewrite runs

Source history:

- `WB-SVC-058`

### 17. Explicit Metadata Suggestion Workflow

Implemented:

- review-only metadata suggestion workflow for existing posts and drafts
- title, excerpt, meta title, meta description, and focus keyword suggestions
- admin API contract for queued metadata suggestion runs
- MCP tool and prompt for metadata suggestion workflows
- AI job and generation-step tracking for metadata suggestion runs

Source history:

- `WB-SVC-059`

### 18. Editorial Generation Modes

Implemented:

- explicit `generation_mode` support for draft creation
- supported editorial modes for tutorial, comparison, opinionated analysis, and checklist drafts
- additive admin API contract for mode-aware draft queueing
- MCP tool and prompt support for selecting generation modes
- retry-safe AI job payload tracking for mode-aware draft runs
- mode-specific prompt template selection when matching templates exist

Source history:

- `WB-SVC-060`

## What Is Still Missing In This Phase

These are the main service-side items still worth doing **before declaring the AI-assisted publishing phase fully closed**.

### 1. AI-Assisted Title / Excerpt Refinement As A Separate Tool

Status:

- not exposed as a dedicated workflow

Why it matters:

- roadmap lists AI-assisted title and excerpt generation as a **Could Have**
- draft creation may produce these implicitly, but there is no dedicated refinement contract for editors

Recommended next service work:

- add post-level refinement actions for:
  - title suggestions
  - excerpt suggestions
  - alternate headline variations

Backlog task:

- `WB-SVC-061`

## Recommended “Next” Work Inside This Phase

If the goal is to finish the current AI-assisted publishing phase cleanly on the service side, the next remaining service item is:

1. Title and excerpt refinement workflow

Mapped backlog:

- `WB-SVC-061`

## Items That Should Move To The Next Phase

These should **not** be treated as remaining blockers for the current AI-assisted publishing phase.

### Future Agents

Move to next phase:

- `EditorAgent`
- `SeoOptimizerAgent`
- `PublishingAgent`
- Image generation / image workflow agents

Reason:

- the service docs and roadmap treat them as later work
- current MVP already says images remain manual

### Advanced Publishing / Media / Analytics Work

Move to next phase:

- AI image generation pipeline
- stock image sourcing workflow
- analytics layer
- SEO dashboard
- richer editorial/cluster health insights

Reason:

- these belong to `docs/ROADMAP.md` **Phase 3: Advanced Publishing**

### Newsletter

Explicitly excluded from this phase review:

- Newsletter module
- Newsletter generation agent

Reason:

- you asked to exclude them
- newsletter now has its own separate backlog file: `NEWSLETTER_BACKLOG.md`

## Practical Recommendation

If the question is:

“Can we treat the non-newsletter AI-assisted publishing backend as implemented?”

The answer is:

**Yes, for the core MVP service scope.**

If the question is:

“Is every roadmap should-have and could-have item for this phase finished?”

The answer is:

**No.**

The remaining service-side work is mainly:

- rewrite/regeneration
- metadata suggestion
- title/excerpt refinement
- optional generation variants

## Suggested Decision

Use this rule:

- treat the **core Phase 2 AI publishing engine as implemented**
- treat the remaining items above as **phase-closing polish**
- move image-generation and other advanced agents to the next phase
