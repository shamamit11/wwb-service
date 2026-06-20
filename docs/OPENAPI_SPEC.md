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
- Pages
- Homepage
- Media
- Templates
- Knowledge Base
- SEO
- Topics
- AI Jobs
- Newsletter

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

---

## Newsletter

### Endpoint List

- `GET /api/v1/admin/newsletter/lists`
- `POST /api/v1/admin/newsletter/lists`
- `GET /api/v1/admin/newsletter/lists/{id}`
- `PATCH /api/v1/admin/newsletter/lists/{id}`
- `DELETE /api/v1/admin/newsletter/lists/{id}`
- `GET /api/v1/admin/newsletter/subscribers`
- `POST /api/v1/admin/newsletter/subscribers`
- `GET /api/v1/admin/newsletter/subscribers/{id}`
- `PATCH /api/v1/admin/newsletter/subscribers/{id}`
- `POST /api/v1/admin/newsletter/subscribers/{id}/unsubscribe`
- `POST /api/v1/admin/newsletter/subscribers/{id}/resubscribe`
- `GET /api/v1/admin/newsletter/campaigns`
- `POST /api/v1/admin/newsletter/campaigns`
- `GET /api/v1/admin/newsletter/campaigns/{id}`
- `PATCH /api/v1/admin/newsletter/campaigns/{id}`
- `DELETE /api/v1/admin/newsletter/campaigns/{id}`
- `GET /api/v1/admin/newsletter/campaigns/{id}/recipients`
- `POST /api/v1/admin/newsletter/campaigns/{id}/stage-recipients`
- `POST /api/v1/admin/newsletter/campaigns/{id}/send`
- `POST /api/v1/public/newsletter/subscribe`
- `GET|POST /api/v1/public/newsletter/unsubscribe`
- `GET /api/v1/public/newsletter/track/open/{recipient}`
- `GET /api/v1/public/newsletter/track/click/{recipient}/{target}`
- `POST /api/v1/public/newsletter/webhooks/events`

### Notes

- Subscribers are created active immediately. There is no verification token, verification email, or pending subscriber state.
- Recipient staging is safe: it creates recipient rows without sending email and skips already staged campaign/subscriber pairs.
- Public subscribe and unsubscribe flows are available without verification or double opt-in.
- Campaign sending is queued and routes through a provider abstraction. The default implementation uses Laravel Mail.
- Tracking and webhook ingestion update recipient and subscriber delivery state without publishing posts.

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

## Pages

### Endpoint List

- `GET /admin/api/v1/pages`
- `POST /admin/api/v1/pages`
- `GET /admin/api/v1/pages/{id}`
- `PUT /admin/api/v1/pages/{id}`
- `DELETE /admin/api/v1/pages/{id}`
- `GET /api/v1/public/pages/{slug}`

### Create Request Example

```json
{
  "title": "Privacy Policy",
  "slug": "privacy-policy",
  "type": "legal",
  "status": "draft",
  "summary": "How Wide Web Blog handles user data and editorial submissions.",
  "content_markdown": "# Privacy Policy\n\nYour privacy matters to us.",
  "visibility": "public",
  "published_at": null,
  "scheduled_for": null,
  "meta": {
    "layout": "legal"
  }
}
```

### Validation Rules

- `title`: required|string|max 255
- `slug`: nullable|string|max 190|unique:pages,slug
- `type`: required|in:legal,marketing,support,faq,standard
- `status`: required|in:draft,scheduled,published,unpublished,archived
- `summary`: nullable|string
- `content_markdown`: required|string
- `visibility`: required|in:public,private,internal
- `published_at`: nullable|date
- `scheduled_for`: nullable|date
- `meta`: nullable|array

### Response Example

```json
{
  "data": {
    "id": 21,
    "ulid": "01J00000000000000000000021",
    "title": "Privacy Policy",
    "slug": "privacy-policy",
    "type": "legal",
    "status": "published",
    "summary": "How Wide Web Blog handles user data and editorial submissions.",
    "content_markdown": "# Privacy Policy\n\nYour privacy matters to us.",
    "visibility": "public",
    "published_at": "2026-06-17T10:00:00Z",
    "scheduled_for": null,
    "canonical_url": "https://widewebblog.com/pages/privacy-policy/",
    "meta": {
      "layout": "legal"
    },
    "created_by": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "updated_by": null,
    "created_at": "2026-06-17T09:45:00Z",
    "updated_at": "2026-06-17T10:00:00Z"
  }
}
```

### Filtering And Sorting

- `search`: title, slug, summary, and markdown content
- `status`: editorial status filter
- `type`: page type filter
- `visibility`: visibility filter
- `created_by_user_id`: author filter
- `sort`: `title`, `created_at`, `updated_at`, `published_at` with optional `-` prefix

### SEO

- Pages are SEOable through the existing SEO metadata endpoints:
  - `GET /admin/api/v1/seo/page/{id}`
  - `PUT /admin/api/v1/seo/page/{id}`
- This keeps canonical URLs and metadata in the shared SEO model instead of duplicating those fields in the pages table.

### Public Page Read Rules

- `GET /api/v1/public/pages/{slug}` returns only pages where:
  - `status = published`
  - `visibility = public`
  - `published_at` is not null
- Draft, internal, private, unpublished, or archived pages return `404`.
- Public response fields include:
  - `id`
  - `title`
  - `slug`
  - `type`
  - `summary`
  - `content_markdown`
  - `published_at`
  - `updated_at`
  - `canonical_url`
  - `meta`
  - `seo`

---

## Homepage

### Endpoint List

- `GET /admin/api/v1/homepage`
- `PUT /admin/api/v1/homepage`

### Update Request Example

```json
{
  "hero": {
    "eyebrow": "Start here",
    "title": "Build better internet systems",
    "description": "Editorially curated homepage content.",
    "primary_cta_label": "Read featured stories",
    "primary_cta_url": "https://widewebblog.com/featured",
    "secondary_cta_label": "Browse resources",
    "secondary_cta_url": "https://widewebblog.com/resources",
    "media_url": "https://cdn.widewebblog.com/home/hero.png",
    "media_alt": "Homepage hero artwork"
  },
  "featured_editorial": {
    "title": "Featured editorial",
    "description": "Automatically curated featured stories.",
    "mode": "automatic",
    "post_ids": [],
    "category_ids": null,
    "limit": 2
  },
  "guide_section": {
    "title": "Recent Articles",
    "description": "Automatically selected recent stories.",
    "mode": "automatic",
    "post_ids": [],
    "category_ids": null,
    "limit": 6
  },
  "topic_section": {
    "title": "Explore Core Topics",
    "description": "Browse every active category automatically.",
    "category_ids": []
  },
  "promo_section": {
    "enabled": true,
    "eyebrow": "Resource pack",
    "title": "Download the operator kit",
    "description": "Promotional support section for a featured resource.",
    "bullet_points": ["Checklists", "Benchmarks", "Field notes"],
    "primary_cta_label": "Get the kit",
    "primary_cta_url": "https://widewebblog.com/kit",
    "stats": [
      {"label": "Templates", "value": "12"},
      {"label": "Playbooks", "value": "8"}
    ]
  },
  "newsletter_section": {
    "enabled": true,
    "title": "Get weekly dispatches",
    "description": "Editorial updates and new resources."
  },
  "seo": {
    "meta_title": "Wide Web Blog | Homepage",
    "meta_description": "Homepage metadata for discovery and click-through."
  }
}
```

### Validation Rules

- `hero`: required|array
- `hero.eyebrow`: nullable|string|max 120
- `hero.title`: nullable|string|max 255
- `hero.description`: nullable|string|max 2000
- `hero.primary_cta_label`: nullable|string|max 120
- `hero.primary_cta_url`: nullable|url|max 500
- `hero.secondary_cta_label`: nullable|string|max 120
- `hero.secondary_cta_url`: nullable|url|max 500
- `hero.media_url`: nullable|url|max 500
- `hero.media_alt`: nullable|string|max 255
- `featured_editorial.mode`: returned as `automatic`
- `featured_editorial.post_ids`: persisted as an empty array because public content is resolved automatically
- `featured_editorial.category_ids`: persisted as `null`
- `featured_editorial.limit`: present|nullable|integer|min:1|max:24
- `guide_section.mode`: returned as `automatic`
- `guide_section.post_ids`: persisted as an empty array because public content is resolved automatically
- `guide_section.category_ids`: persisted as `null`
- `guide_section.limit`: present|nullable|integer|min:1|max:24
- `topic_section.category_ids`: persisted as an empty array because public categories are resolved automatically
- `promo_section.enabled`: required|boolean
- `promo_section.bullet_points`: required|array
- `promo_section.bullet_points.*`: string|max 255
- `promo_section.primary_cta_url`: nullable|url|max 500
- `promo_section.stats`: required|array
- `promo_section.stats.*.label`: required|string|max 120
- `promo_section.stats.*.value`: required|string|max 120
- `newsletter_section.enabled`: required|boolean
- `newsletter_section.title`: nullable|string|max 255
- `newsletter_section.description`: nullable|string|max 2000
- `seo.meta_title`: nullable|string|max 255
- `seo.meta_description`: nullable|string|max 320

### Response Example

```json
{
  "data": {
    "hero": {
      "eyebrow": "Start here",
      "title": "Build better internet systems",
      "description": "Editorially curated homepage content.",
      "primary_cta_label": "Read featured stories",
      "primary_cta_url": "https://widewebblog.com/featured",
      "secondary_cta_label": "Browse resources",
      "secondary_cta_url": "https://widewebblog.com/resources",
      "media_url": "https://cdn.widewebblog.com/home/hero.png",
      "media_alt": "Homepage hero artwork"
    },
    "featured_editorial": {
      "title": "Featured editorial",
      "description": "Automatically curated featured stories.",
      "mode": "automatic",
      "post_ids": [],
      "category_ids": null,
      "limit": 2
    },
    "guide_section": {
      "title": "Recent Articles",
      "description": "Automatically selected recent stories.",
      "mode": "automatic",
      "post_ids": [],
      "category_ids": null,
      "limit": 6
    },
    "topic_section": {
      "title": "Explore Core Topics",
      "description": "Browse every active category automatically.",
      "category_ids": []
    },
    "promo_section": {
      "enabled": true,
      "eyebrow": "Resource pack",
      "title": "Download the operator kit",
      "description": "Promotional support section for a featured resource.",
      "bullet_points": ["Checklists", "Benchmarks", "Field notes"],
      "primary_cta_label": "Get the kit",
      "primary_cta_url": "https://widewebblog.com/kit",
      "stats": [
        {"label": "Templates", "value": "12"},
        {"label": "Playbooks", "value": "8"}
      ]
    },
    "newsletter_section": {
      "enabled": true,
      "title": "Get weekly dispatches",
      "description": "Editorial updates and new resources."
    },
    "seo": {
      "meta_title": "Wide Web Blog | Homepage",
      "meta_description": "Homepage metadata for discovery and click-through."
    },
    "updated_at": "2026-06-17T12:00:00Z",
    "updated_by": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    }
  }
}
```

### Singleton Behavior

- The homepage is a singleton resource, not a collection.
- `GET /admin/api/v1/homepage` auto-creates the default record if none exists yet.
- Ordered arrays such as `post_ids`, `category_ids`, `bullet_points`, and `stats` are preserved as submitted.

---

## About Page

### Endpoint List

- `GET /admin/api/v1/about-page`
- `PUT /admin/api/v1/about-page`
- `GET /api/v1/public/about`

### Update Request Example

```json
{
  "hero": {
    "eyebrow": "Our story",
    "title": "Navigating the digital frontier together.",
    "description": "Wide Web Blog was founded on the belief that technology should be accessible, insightful, and growth-oriented.",
    "media_url": "https://cdn.widewebblog.com/about/hero.png",
    "media_alt": "About page office photograph"
  },
  "mission_section": {
    "title": "Our Mission: Fueling Digital Growth",
    "description": "We provide the context needed to thrive in an era of rapid AI and technological evolution.",
    "quote": "The future is about how we leverage technology to amplify human potential."
  },
  "stats_section": {
    "items": [
      {"label": "Articles Published", "value": "500+"},
      {"label": "Monthly Readers", "value": "120K"}
    ]
  },
  "values_section": {
    "title": "The Values We Live By",
    "items": [
      {
        "icon": "clarity",
        "title": "Authentic Clarity",
        "description": "We prioritize honesty and transparency in every piece of content."
      }
    ]
  },
  "team_section": {
    "title": "Meet the Minds",
    "description": "Our multidisciplinary team combines decades of expertise.",
    "primary_cta_label": "Join the Team",
    "primary_cta_url": "https://widewebblog.com/careers",
    "members": [
      {
        "name": "Alexander Chen",
        "role": "Editor-in-Chief",
        "image_url": "https://cdn.widewebblog.com/about/alexander.png",
        "image_alt": "Alexander Chen portrait"
      }
    ]
  },
  "seo": {
    "meta_title": "About Wide Web Blog",
    "meta_description": "Learn more about Wide Web Blog, our mission, values, and editorial team."
  }
}
```

### Validation Rules

- `hero`: required|array
- `hero.eyebrow`: nullable|string|max 120
- `hero.title`: nullable|string|max 255
- `hero.description`: nullable|string|max 2000
- `hero.media_url`: nullable|url|max 500
- `hero.media_alt`: nullable|string|max 255
- `mission_section`: required|array
- `mission_section.title`: nullable|string|max 255
- `mission_section.description`: nullable|string|max 3000
- `mission_section.quote`: nullable|string|max 1000
- `stats_section`: required|array
- `stats_section.items`: required|array
- `stats_section.items.*.label`: required|string|max 120
- `stats_section.items.*.value`: required|string|max 120
- `values_section`: required|array
- `values_section.title`: nullable|string|max 255
- `values_section.items`: required|array
- `values_section.items.*.icon`: nullable|string|max 80
- `values_section.items.*.title`: required|string|max 120
- `values_section.items.*.description`: required|string|max 1000
- `team_section`: required|array
- `team_section.title`: nullable|string|max 255
- `team_section.description`: nullable|string|max 2000
- `team_section.primary_cta_label`: nullable|string|max 120
- `team_section.primary_cta_url`: nullable|url|max 500
- `team_section.members`: required|array
- `team_section.members.*.name`: required|string|max 120
- `team_section.members.*.role`: required|string|max 160
- `team_section.members.*.image_url`: nullable|url|max 500
- `team_section.members.*.image_alt`: nullable|string|max 255
- `seo.meta_title`: nullable|string|max 255
- `seo.meta_description`: nullable|string|max 320

### Response Example

```json
{
  "data": {
    "hero": {
      "eyebrow": "Our story",
      "title": "Navigating the digital frontier together.",
      "description": "Wide Web Blog was founded on the belief that technology should be accessible, insightful, and growth-oriented.",
      "media_url": "https://cdn.widewebblog.com/about/hero.png",
      "media_alt": "About page office photograph"
    },
    "mission_section": {
      "title": "Our Mission: Fueling Digital Growth",
      "description": "We provide the context needed to thrive in an era of rapid AI and technological evolution.",
      "quote": "The future is about how we leverage technology to amplify human potential."
    },
    "stats_section": {
      "items": [
        {"label": "Articles Published", "value": "500+"},
        {"label": "Monthly Readers", "value": "120K"}
      ]
    },
    "values_section": {
      "title": "The Values We Live By",
      "items": [
        {
          "icon": "clarity",
          "title": "Authentic Clarity",
          "description": "We prioritize honesty and transparency in every piece of content."
        }
      ]
    },
    "team_section": {
      "title": "Meet the Minds",
      "description": "Our multidisciplinary team combines decades of expertise.",
      "primary_cta_label": "Join the Team",
      "primary_cta_url": "https://widewebblog.com/careers",
      "members": [
        {
          "name": "Alexander Chen",
          "role": "Editor-in-Chief",
          "image_url": "https://cdn.widewebblog.com/about/alexander.png",
          "image_alt": "Alexander Chen portrait"
        }
      ]
    },
    "seo": {
      "meta_title": "About Wide Web Blog",
      "meta_description": "Learn more about Wide Web Blog, our mission, values, and editorial team."
    },
    "updated_at": "2026-06-20T12:00:00Z",
    "updated_by": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    }
  }
}
```

### Singleton Behavior

- The About page is a singleton resource, not a collection.
- `GET /admin/api/v1/about-page` auto-creates the default record if none exists yet.
- `GET /api/v1/public/about` returns the same structured content shape without admin metadata fields.
- Ordered arrays such as `stats_section.items`, `values_section.items`, and `team_section.members` are preserved as submitted.

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

- `GET /api/v1/admin/ai-jobs`
- `POST /api/v1/admin/ai-jobs/topic-discovery`
- `GET /api/v1/admin/ai-jobs/{id}`
- `POST /api/v1/admin/ai-jobs/{id}/retry`

### Topic Discovery Request Example

```json
{
  "cluster": "ai_tools",
  "count": 4,
  "audience": "Editorial leads",
  "prompt_template_key": "topic_discovery_default",
  "metadata": {
    "knowledge_context_filters": {
      "clusters": [
        "ai_tools"
      ]
    }
  }
}
```

### Validation Rules

- `cluster`: required|in:ai_tools,ai_for_blogging,seo,content_marketing,productivity_automation,developer_ai
- `count`: sometimes|integer|min 1|max 25
- `audience`: sometimes|nullable|string|max 255
- `prompt_template_key`: sometimes|nullable|string|max 190
- `metadata`: sometimes|array

### Response Example

```json
{
  "data": {
    "id": 101,
    "type": "topic_discovery",
    "status": "queued",
    "entity_type": "content_topic_batch",
    "entity_id": null,
    "provider": null,
    "model": null,
    "input_payload": {
      "cluster": "ai_tools",
      "count": 4,
      "audience": "Editorial leads",
      "prompt_template_key": "topic_discovery_default",
      "metadata": {
        "knowledge_context_filters": {
          "clusters": [
            "ai_tools"
          ]
        },
        "trigger": "admin_api"
      }
    },
    "output_payload": null,
    "usage_payload": null,
    "error_message": null,
    "attempts": 1,
    "retry_of_ai_job_id": null,
    "can_retry": false,
    "steps_count": 0,
    "cost_summary": null,
    "started_at": null,
    "completed_at": null,
    "failed_at": null,
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
