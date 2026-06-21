# AI Agents

## Purpose

This document explains the current AI agent roles in the service repository and how they fit into the active editorial workflow.

## Main Agent Set

The main editorial pipeline includes:

- `TopicDiscoveryAgent`
- `BlogWriterAgent`

Helper AI agents also exist for metadata and title/excerpt suggestions, but they are not separate editorial stages in the publish pipeline.

## Shared Rules

- agents never publish content directly
- agents work through workflow services and internal tools
- agents should rely on Knowledge Base context where available
- agents should produce structured, reviewable output
- images remain manual in this phase

## TopicDiscoveryAgent

### Role

Generate scored editorial topics inside approved content clusters.

### Inputs

- approved cluster
- optional audience
- existing topic titles
- Knowledge Base context

### Outputs

- suggested topics
- score breakdown:
  - `trend_score`
  - `knowledge_base_fit`
  - `business_value`
  - `originality_gap`
  - `execution_confidence`
- aggregate `priority_score`

### Guardrails

- cluster must be approved
- duplicates should be skipped safely
- topics below `90` should not remain in the queue

## BlogWriterAgent

### Role

Generate one full article draft from a topic that has cleared the score threshold.

### Inputs

- content topic
- category and optional author/media selections
- versioned `blog_standard` prompt template
- Knowledge Base context
- existing post and internal link context

### Outputs

- draft article content
- short description
- full article markdown
- optional HTML body
- FAQ suggestions
- SEO-oriented metadata suggestions

### Guardrails

- resulting content stays in draft
- no auto-publish path exists
- retries should reuse existing generated post state when appropriate
- the agent should write one article, not a block collection

## Internal Tooling Used By Agents

- `CheckDuplicateTopicTool`
- `SaveTopicIdeaTool`
- `SavePostDraftTool`
- `SearchExistingPostsTool`
- `FindInternalLinksTool`

## Workflow Ownership

Agents are not triggered directly from controllers.

Controllers and commands call orchestration services such as:

- `TopicDiscoveryWorkflow`
- `DraftGenerationWorkflow`
- `AiWorkflowOrchestrator`

This preserves:

- thin controllers
- explicit workflow boundaries
- persistent AI job tracking
- retry-safe behavior

## Admin Relationship

The main admin-facing operational surfaces are:

- Topic Queue
- Posts
- AI Jobs
- Prompt Management

## Summary

The current editorial agents are specialized workers:

- topic discovery scores and routes ideas
- blog writing produces one draft article

Publishing remains human-owned.
