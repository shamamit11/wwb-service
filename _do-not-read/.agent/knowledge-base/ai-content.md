# AI Content Knowledge

## AI Workflow Goals

- discover valuable topics
- generate draft articles
- generate helper SEO suggestions
- support future image suggestion workflows

## Current MVP Flow

1. Knowledge Base context is gathered.
2. `TopicDiscoveryAgent` suggests scored topics within approved clusters.
3. topics below `70` are pruned automatically.
4. topics from `70` through `84.99` stay in Topic Queue for editorial review.
5. topics at `85` or higher are eligible for automatic draft routing.
5. draft posts are reviewed, edited, approved, and published manually in Admin.

## Approved Topic Clusters

- `ai_tools`
- `ai_for_blogging`
- `seo`
- `content_marketing`
- `productivity_automation`
- `developer_ai`

## Guardrails

- AI output starts as draft.
- human approval is required before publication.
- AI must not auto-publish posts.
- prompts must be database-backed and versioned.
- the main editable prompt families are `topic_standard` and `blog_standard`.
- images remain manual in MVP; AI may only suggest ideas, placement, and alt text.
- retries must not create duplicate topics or posts.
- AI work must record jobs and generation steps.

## Implementation Bias

- asynchronous processing where practical
- explicit workflow states
- admin override at every important stage
- provider-agnostic client abstractions
- structured outputs over raw free-form HTML
