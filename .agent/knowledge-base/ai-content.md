# AI Content Knowledge

## AI Workflow Goals

- help discover topics
- generate draft articles
- generate SEO metadata
- support future image generation

## Phase 3 MVP Flow

1. Knowledge Base context is gathered.
2. `TopicDiscoveryAgent` suggests topics within approved clusters.
3. approved topics feed `ContentBriefAgent`.
4. approved briefs feed `BlogWriterAgent`.
5. draft posts are reviewed, edited, approved, and published manually in Admin.

## Approved Topic Clusters

- `ai_tools`
- `ai_for_blogging`
- `seo`
- `content_marketing`
- `productivity_automation`
- `developer_ai`

## Guardrails

- AI output starts as draft
- human approval is required before publication
- moderation and auditability should be first-class design concerns
- AI must not auto-approve topics or briefs
- AI must not auto-publish posts
- prompts must be database-backed and versioned
- images remain manual in MVP; AI may only suggest ideas, placement, and alt text
- retries must not create duplicate topics, briefs, or posts
- AI work must record jobs and generation steps

## Implementation Bias

- asynchronous processing where practical
- explicit workflow states
- admin override at every important stage
- provider-agnostic client abstractions
- structured outputs over raw free-form HTML
