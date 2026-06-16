# Knowledge Base Specification: Wide Web Blog

## Document Purpose

This document defines the knowledge base for Wide Web Blog. The knowledge base stores reusable, internally valuable information that improves article quality, editorial consistency, and future AI-assisted publishing.

## Strategic Purpose

The knowledge base is one of the main ways Wide Web Blog can avoid generic AI content.

It creates original value by capturing:

- internal experience
- architectural reasoning
- reusable technical notes
- research summaries
- implementation-specific context
- editorial standards and terminology

Instead of generating articles from thin prompts, the system can generate and review content using accumulated knowledge that is unique to the publication.

## Knowledge Item Types

- `note`
- `research`
- `experience`
- `architecture`
- `code`
- `reference`
- `idea`

## Type Definitions

### Note

- short reusable editorial or technical note

### Research

- summarized findings from documentation, standards, or market observation

### Experience

- practical lessons, opinions, or implementation insights from real work

### Architecture

- design rationale, tradeoffs, diagrams, and system decisions

### Code

- reusable technical patterns, snippets, or implementation notes

### Reference

- canonical definitions, glossary entries, or stable source material

### Idea

- early topic or angle concept not yet matured into a topic or article

## CRUD Behavior

### Create

- admin creates a knowledge item with title, type, status, tags, and content

### Read

- searchable index
- detail view with linked posts and topics

### Update

- full metadata and content editing
- status changes
- relationship management

### Delete / Archive

- prefer archive over hard delete
- retain auditability where useful

## Status Model

Recommended statuses:

- `draft`
- `active`
- `archived`

## Tagging

Knowledge items should support flexible tagging for:

- technologies
- concepts
- workflows
- editorial functions

Examples:

- `laravel`
- `queues`
- `mcp`
- `ai-agents`
- `architecture`
- `seo`

## Categorization

Knowledge items should also support broader categorization aligned to the publication’s editorial clusters.

Suggested categories:

- AI Agents
- MCP
- Laravel + AI
- AWS + Automation
- Software Architecture
- Developer Productivity

Tags are granular. Categories are navigational and strategic.

## Search

Knowledge base search should support:

- title
- summary
- body content
- tags
- type
- category

Filters should support:

- status
- type
- category
- linked posts
- linked topics

## Linking Knowledge Items To Posts

Knowledge items should support explicit many-to-many linkage to posts.

Use cases:

- post sourced from a knowledge item
- post references a technical note
- article refresh should reuse earlier research

Recommended behavior:

- allow linking from post editor and knowledge base detail page
- show related knowledge items in post editing context

## Linking Knowledge Items To Topics

Knowledge items should support linking to topics to improve topic discovery and blueprint generation.

Use cases:

- topic has supporting research already
- topic should inherit canonical definitions
- AI blueprint should pull approved context from linked knowledge items

## Future AI Usage

The knowledge base should become a grounding layer for future AI workflows.

### AI Use Cases

- supply context to topic discovery
- enrich content blueprints
- support article draft generation
- improve SEO suggestion quality
- reduce hallucinated generic statements

### AI Rules

- only `active` knowledge items should be eligible by default
- system should be able to choose context by tags, categories, or linked topic
- knowledge usage should be traceable in AI job metadata where feasible

## Quality Rules

Every knowledge item should meet minimum quality rules.

### Required Standards

- clear title
- correct type
- meaningful summary or opening context
- technically useful body content
- tags or category assigned

### Recommended Standards

- cite source URL when based on external material
- identify whether content is firsthand experience or external synthesis
- avoid vague notes with no future retrieval value

## Admin UX Expectations

Knowledge base admin should support:

- list view
- create/edit form
- markdown or structured rich text entry
- links to related posts
- links to related topics
- filters by type and category

## Data Model Expectations

Core fields:

- title
- slug
- entry_type
- status
- summary
- content_markdown
- source_url
- featured_media_id
- metadata

Future relationship tables may include:

- `knowledge_base_entry_post`
- `knowledge_base_entry_topic`
- `knowledge_base_entry_tag`

## Example Use Flow

1. Admin creates a knowledge entry about Laravel queue retry behavior.
2. Entry is tagged with `laravel`, `queues`, `ai`.
3. Entry is linked to a topic about AI job reliability.
4. Topic blueprint engine pulls the entry as grounding context.
5. Draft article references the same technical reasoning, reducing generic output.

## Summary

The knowledge base should function as Wide Web Blog’s internal intelligence layer. It captures original insight, stabilizes terminology, and gives both humans and future AI systems access to reusable high-value context. This is a core mechanism for producing more differentiated content than generic AI publishing systems can generate on their own.
