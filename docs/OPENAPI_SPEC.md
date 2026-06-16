# OpenAPI Spec Reference: Wide Web Blog

## Document Purpose

This document defines API-style contracts for the Wide Web Blog Laravel monolith. Even though the system is primarily Laravel + Livewire, these contracts provide a stable service boundary for:

- Livewire actions and internal HTTP endpoints
- future external API exposure
- admin-to-service interaction consistency
- testing, validation, and documentation

This is a reference specification, not a generated OpenAPI JSON file.

## API Design Principles

- JSON over HTTP for service endpoints
- predictable resource-oriented URLs
- server-side validation with structured error responses
- publish-safe filtering on public resources
- draft-safe admin endpoints behind authentication
- forward-compatible fields and pagination shapes

## Base Conventions

### Base Paths

- Admin/private endpoints: `/admin/api/v1/...`
- Public/read endpoints: `/api/v1/...`

### Authentication Expectations

- Admin/private endpoints require authenticated admin session or token
- Public content endpoints are unauthenticated
- Future API tokens should support scoped access

### Standard Headers

- `Accept: application/json`
- `Content-Type: application/json`
- authenticated endpoints may also rely on session auth and CSRF when called from Livewire

## Error Format

```json
{
  "message": "The given data was invalid.",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "title": [
      "The title field is required."
    ]
  },
  "meta": {
    "request_id": "req_01hxyz..."
  }
}
```

### Common Error Codes

- `VALIDATION_ERROR`
- `UNAUTHORIZED`
- `FORBIDDEN`
- `NOT_FOUND`
- `CONFLICT`
- `UNPROCESSABLE_STATE`
- `RATE_LIMITED`
- `INTERNAL_ERROR`
- `PROVIDER_ERROR`

## Pagination Format

```json
{
  "data": [],
  "links": {
    "first": "https://widewebblog.com/admin/api/v1/posts?page=1",
    "last": "https://widewebblog.com/admin/api/v1/posts?page=8",
    "prev": null,
    "next": "https://widewebblog.com/admin/api/v1/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 8,
    "path": "https://widewebblog.com/admin/api/v1/posts",
    "per_page": 15,
    "to": 15,
    "total": 112
  }
}
```

## Filtering And Sorting Conventions

### Filtering

- use `filter[...]` syntax
- examples:
  - `filter[status]=published`
  - `filter[category_id]=3`
  - `filter[search]=laravel ai`

### Sorting

- use `sort=field` for ascending
- use `sort=-field` for descending
- examples:
  - `sort=title`
  - `sort=-published_at`

### Include Conventions

- use `include=relation1,relation2`
- examples:
  - `include=category,featuredMedia`
  - `include=seo,tags,author`

## Resource Summary

- Categories
- Posts
- Media
- Templates
- Knowledge Base
- SEO
- Topics
- AI Jobs

---

## Categories

### Endpoint List

- `GET /admin/api/v1/categories`
- `POST /admin/api/v1/categories`
- `GET /admin/api/v1/categories/{id}`
- `PUT /admin/api/v1/categories/{id}`
- `DELETE /admin/api/v1/categories/{id}`
- `GET /api/v1/categories`
- `GET /api/v1/categories/{slug}`

### Create Request Example

```json
{
  "name": "AI Agents",
  "slug": "ai-agents",
  "description": "Technical content about agents, memory, tools, and orchestration.",
  "parent_id": null,
  "is_active": true,
  "sort_order": 10
}
```

### Validation Rules

- `name`: required, string, max 120
- `slug`: nullable|string|max 160|unique:categories,slug
- `description`: nullable|string
- `parent_id`: nullable|exists:categories,id
- `is_active`: boolean
- `sort_order`: nullable|integer|min:0

### Response Example

```json
{
  "data": {
    "id": 1,
    "ulid": "01J00000000000000000000001",
    "name": "AI Agents",
    "slug": "ai-agents",
    "description": "Technical content about agents, memory, tools, and orchestration.",
    "is_active": true,
    "sort_order": 10,
    "seo": null,
    "created_at": "2026-06-16T12:00:00Z",
    "updated_at": "2026-06-16T12:00:00Z"
  }
}
```

---

## Posts

### Endpoint List

- `GET /admin/api/v1/posts`
- `POST /admin/api/v1/posts`
- `GET /admin/api/v1/posts/{id}`
- `PUT /admin/api/v1/posts/{id}`
- `DELETE /admin/api/v1/posts/{id}`
- `POST /admin/api/v1/posts/{id}/publish`
- `POST /admin/api/v1/posts/{id}/schedule`
- `POST /admin/api/v1/posts/{id}/unpublish`
- `GET /api/v1/posts`
- `GET /api/v1/posts/{slug}`

### Create Request Example

```json
{
  "title": "How AI Agent Memory Works",
  "slug": "how-ai-agent-memory-works",
  "excerpt": "A practical look at short-term, long-term, and retrieval-based memory patterns.",
  "category_id": 1,
  "template_id": 2,
  "featured_media_id": 14,
  "status": "draft",
  "visibility": "public",
  "tag_ids": [4, 9],
  "blocks": [
    {
      "block_type": "heading",
      "sort_order": 1,
      "content": {
        "text": "How AI Agent Memory Works",
        "level": 1
      }
    },
    {
      "block_type": "paragraph",
      "sort_order": 2,
      "content": {
        "markdown": "Memory is one of the most misunderstood parts of agent design..."
      }
    }
  ]
}
```

### Validation Rules

- `title`: required|string|max 255
- `slug`: nullable|string|max 190|unique:posts,slug
- `excerpt`: nullable|string
- `category_id`: required|exists:categories,id
- `template_id`: nullable|exists:templates,id
- `featured_media_id`: nullable|exists:media,id
- `status`: in:draft,scheduled,published,unpublished,archived
- `visibility`: in:public,private,internal
- `tag_ids`: array
- `tag_ids.*`: exists:tags,id
- `blocks`: required|array|min:1
- `blocks.*.block_type`: required|in:heading,paragraph,image,quote,list,code,faq,callout
- `blocks.*.sort_order`: required|integer|min:1
- `blocks.*.content`: required|array

### Response Example

```json
{
  "data": {
    "id": 12,
    "ulid": "01J00000000000000000000012",
    "title": "How AI Agent Memory Works",
    "slug": "how-ai-agent-memory-works",
    "status": "draft",
    "visibility": "public",
    "published_at": null,
    "category": {
      "id": 1,
      "name": "AI Agents",
      "slug": "ai-agents"
    },
    "author": {
      "id": 1,
      "name": "Amit Kumar Sharma"
    },
    "featured_media": {
      "id": 14,
      "url": "https://cdn.widewebblog.com/media/..."
    },
    "seo": null,
    "tags": [
      {"id": 4, "name": "memory"},
      {"id": 9, "name": "architecture"}
    ],
    "created_at": "2026-06-16T12:00:00Z",
    "updated_at": "2026-06-16T12:05:00Z"
  }
}
```

---

## Media

### Endpoint List

- `GET /admin/api/v1/media`
- `POST /admin/api/v1/media`
- `POST /admin/api/v1/media/batch`
- `GET /admin/api/v1/media/{id}`
- `PUT /admin/api/v1/media/{id}`
- `DELETE /admin/api/v1/media/{id}`
- `GET /api/v1/media/{ulid}` only if public asset metadata endpoint is needed later

### Upload Request Example

Multipart upload is preferred for binary data. Metadata may be included as fields.

```json
{
  "alt_text": "Architecture diagram showing AI agent memory flows",
  "caption": "AI agent memory architecture",
  "source_type": "uploaded",
  "source_url": null,
  "attribution_text": null
}
```

### Validation Rules

- `file`: required|file|max:10240|mimes:jpg,jpeg,png,webp,gif,svg,pdf
- `alt_text`: nullable|string|max 255
- `caption`: nullable|string
- `source_type`: required|in:uploaded,ai_generated,stock
- `source_url`: nullable|url|max 500
- `attribution_text`: nullable|string|max 255

### Response Example

```json
{
  "data": {
    "id": 14,
    "ulid": "01J00000000000000000000014",
    "source_type": "uploaded",
    "mime_type": "image/webp",
    "file_size_bytes": 184233,
    "width": 1600,
    "height": 900,
    "alt_text": "Architecture diagram showing AI agent memory flows",
    "caption": "AI agent memory architecture",
    "url": "https://cdn.widewebblog.com/media/2026/06/diagram.webp",
    "status": "ready",
    "usage_count": 3
  }
}
```

---

## Templates

### Endpoint List

- `GET /admin/api/v1/templates`
- `POST /admin/api/v1/templates`
- `GET /admin/api/v1/templates/{id}`
- `PUT /admin/api/v1/templates/{id}`
- `DELETE /admin/api/v1/templates/{id}`
- `POST /admin/api/v1/templates/{id}/preview`
- `POST /admin/api/v1/templates/{id}/seed-post`

### Create Request Example

```json
{
  "name": "Tutorial",
  "slug": "tutorial",
  "template_type": "tutorial",
  "description": "Structured step-by-step tutorial layout",
  "status": "active",
  "default_meta": {
    "recommended_sections": ["intro", "prerequisites", "steps", "faq"]
  },
  "blocks": [
    {
      "block_type": "heading",
      "sort_order": 1,
      "label": "Title",
      "default_content": {"text": "{{title}}", "level": 1}
    }
  ]
}
```

### Validation Rules

- `name`: required|string|max 160
- `slug`: required|string|max 180|unique:templates,slug
- `template_type`: required|in:standard,tutorial,listicle,comparison,news
- `status`: required|in:draft,active,archived
- `blocks`: array

---

## Knowledge Base

### Endpoint List

- `GET /admin/api/v1/knowledge-base`
- `POST /admin/api/v1/knowledge-base`
- `GET /admin/api/v1/knowledge-base/{id}`
- `PUT /admin/api/v1/knowledge-base/{id}`
- `DELETE /admin/api/v1/knowledge-base/{id}`
- `POST /admin/api/v1/knowledge-base/{id}/link-post`
- `POST /admin/api/v1/knowledge-base/{id}/link-topic`

### Create Request Example

```json
{
  "title": "Laravel Queue Retry Patterns",
  "slug": "laravel-queue-retry-patterns",
  "entry_type": "architecture",
  "status": "active",
  "summary": "Notes on retries, timeouts, and dead-letter patterns.",
  "content_markdown": "When using queues for AI jobs, idempotency matters...",
  "tag_ids": [2, 7],
  "category_ids": [3]
}
```

### Validation Rules

- `title`: required|string|max 255
- `slug`: nullable|string|max 190|unique:knowledge_base_entries,slug
- `entry_type`: required|in:note,research,experience,architecture,code,reference,idea
- `status`: required|in:draft,active,archived
- `summary`: nullable|string
- `content_markdown`: required|string

---

## SEO

### Endpoint List

- `GET /admin/api/v1/seo/{seoableType}/{seoableId}`
- `PUT /admin/api/v1/seo/{seoableType}/{seoableId}`
- `POST /admin/api/v1/seo/score/{postId}`

### Update Request Example

```json
{
  "meta_title": "How AI Agent Memory Works",
  "meta_description": "A practical explanation of short-term, long-term, and retrieval-based memory for AI agents.",
  "canonical_url": "https://widewebblog.com/how-ai-agent-memory-works/",
  "robots_index": true,
  "robots_follow": true,
  "og_title": "How AI Agent Memory Works",
  "og_description": "A practical explanation of memory design for AI agents.",
  "og_image_media_id": 14,
  "schema_type": "Article"
}
```

### Validation Rules

- `meta_title`: nullable|string|max 255
- `meta_description`: nullable|string|max 320
- `canonical_url`: nullable|url|max 500
- `robots_index`: boolean
- `robots_follow`: boolean
- `og_image_media_id`: nullable|exists:media,id

---

## Topics

### Endpoint List

- `GET /admin/api/v1/topics`
- `POST /admin/api/v1/topics`
- `GET /admin/api/v1/topics/{id}`
- `PUT /admin/api/v1/topics/{id}`
- `POST /admin/api/v1/topics/{id}/approve`
- `POST /admin/api/v1/topics/{id}/reject`
- `POST /admin/api/v1/topics/{id}/mark-used`

### Validation Rules

- `name`: required|string|max 180
- `slug`: nullable|string|max 190|unique:topics,slug
- `status`: in:suggested,approved,rejected,used
- `category_id`: nullable|exists:categories,id
- `description`: nullable|string

---

## AI Jobs

### Endpoint List

- `GET /admin/api/v1/ai-jobs`
- `POST /admin/api/v1/ai-jobs/topic-discovery`
- `POST /admin/api/v1/ai-jobs/content-generation`
- `POST /admin/api/v1/ai-jobs/seo-generation`
- `GET /admin/api/v1/ai-jobs/{id}`
- `POST /admin/api/v1/ai-jobs/{id}/retry`
- `POST /admin/api/v1/ai-jobs/{id}/cancel`

### Content Generation Request Example

```json
{
  "topic_id": 5,
  "template_id": 2,
  "provider": "openai",
  "model": "gpt-5",
  "prompt_template_id": 3,
  "target_post_id": null,
  "options": {
    "generate_faq": true,
    "generate_tags": true,
    "generate_seo": true
  }
}
```

### Validation Rules

- `topic_id`: required|exists:topics,id
- `template_id`: nullable|exists:templates,id
- `provider`: required|in:openai,anthropic,gemini
- `model`: required|string|max 120
- `prompt_template_id`: nullable|exists:prompt_templates,id
- `target_post_id`: nullable|exists:posts,id

### Response Example

```json
{
  "data": {
    "id": 101,
    "ulid": "01J00000000000000000000101",
    "job_type": "content_generation",
    "status": "queued",
    "provider": "openai",
    "model": "gpt-5",
    "queue_name": "ai",
    "target_type": "topic",
    "target_id": 5,
    "created_at": "2026-06-16T12:00:00Z"
  }
}
```

## Public API Filtering Examples

### List published posts by category

`GET /api/v1/posts?filter[category_slug]=ai-agents&sort=-published_at`

### Search public posts

`GET /api/v1/posts?filter[search]=laravel%20ai`

### Admin list draft posts

`GET /admin/api/v1/posts?filter[status]=draft&sort=-updated_at`

## Extensibility Notes

- keep `data`, `links`, and `meta` response conventions stable
- prefer additive changes over breaking shape changes
- use `include=` for optional relations
- reserve `/api/v2/` only for genuinely breaking changes
- support future token-based external consumers without changing core resource semantics
