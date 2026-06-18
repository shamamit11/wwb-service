# AI Agents

## Purpose

This document explains the current AI agent roles in the service repository and how they fit into the MVP workflow.

## Agent Set

The shipped Phase 3 backend includes three agents:

- `TopicDiscoveryAgent`
- `ContentBriefAgent`
- `BlogWriterAgent`

These are workflow-specific agents, not general autonomous assistants.

## Shared Rules

- Agents never publish content directly.
- Agents work through workflow services and internal tools.
- Agents should rely on Knowledge Base context where available.
- Agents should produce structured, reviewable output instead of unbounded raw HTML.
- Images remain manual in this phase.

## TopicDiscoveryAgent

### Role

Generate suggested editorial topics inside approved content clusters.

### Inputs

- approved cluster
- optional audience
- existing topic titles
- Knowledge Base context

### Outputs

- suggested topic ideas
- metadata for duplicate handling and saved topic IDs

### Persistence Path

Topic discovery ultimately writes suggested topics into the Topic Queue through internal tools and service rules.

### Guardrails

- cluster must be one of the approved clusters
- output becomes `suggested` topics only
- duplicates should be skipped safely

## ContentBriefAgent

### Role

Turn an approved topic into a structured content brief.

### Inputs

- approved content topic
- Knowledge Base context
- existing internal content context
- optional prompt template override

### Outputs

- brief title and slug
- outline and headings
- FAQ suggestions
- internal link suggestions
- image ideas and alt text suggestions

### Guardrails

- only approved topics may generate briefs
- brief output is an intermediate planning asset
- image suggestions are advisory only

## BlogWriterAgent

### Role

Generate a draft blog post from an approved content brief.

### Inputs

- approved content brief
- category and optional author/template/media selections
- prompt template override when provided

### Outputs

- draft post content
- draft-oriented metadata and related persistence side effects

### Guardrails

- only approved briefs may generate drafts
- resulting content stays in draft
- no auto-publish path exists
- existing generated post state should be reused on retry when appropriate

## Internal Tooling Used By Agents

The current internal tool set includes:

- `CheckDuplicateTopicTool`
- `SaveTopicIdeaTool`
- `SaveContentBriefTool`
- `SavePostDraftTool`
- `SearchExistingPostsTool`
- `FindInternalLinksTool`

These tools let agents:

- check for duplicates before saving
- persist through service-layer rules
- look up related published content
- suggest internal links without bypassing domain boundaries

## Workflow Ownership

Agents are not triggered directly from controllers in the current architecture.

Instead, controllers and commands call orchestration services such as:

- `TopicDiscoveryWorkflow`
- `ContentBriefWorkflow`
- `DraftGenerationWorkflow`
- `AiWorkflowOrchestrator`

This separation matters because future agents should preserve:

- thin controllers
- explicit workflow boundaries
- persistent AI job tracking
- retry-safe behavior

## Admin Placeholder Relationship

The current admin-facing placeholders for agent-backed work are:

- Topic Queue
- Content Briefs
- AI Jobs
- Prompt Management

These are the operational surfaces that future admin UI work is expected to consume.

## Summary

The current agents are specialized editorial workers:

- topic discovery suggests
- brief generation plans
- blog writing drafts

All final publishing decisions remain human-owned.
