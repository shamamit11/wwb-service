# Activity Log Policy

## Purpose

Use this file for stable auditing rules with Spatie Activitylog.

## What To Log

- admin-authenticated create, update, delete, restore, and publish actions
- sensitive settings changes
- template configuration changes
- SEO metadata changes with material impact
- media deletions and attribution changes
- AI workflow approvals or rejections when user-visible

## What Not To Log

- noisy read-only requests
- high-frequency internal polling
- raw provider payloads unless explicitly needed for compliance or debugging
- secrets, access tokens, or private credentials

## Logging Principles

- Log business events, not every low-level field mutation.
- Keep descriptions readable and domain-oriented.
- Include actor, subject, event type, and key changed attributes.
- Redact or omit sensitive fields.

## Suggested Event Names

- `category.created`
- `post.updated`
- `post.published`
- `seo_metadata.updated`
- `media.deleted`
- `template.activated`
- `ai_job.approved`

## Retention And Volume

- Activity logging should remain useful for audits and debugging, not become a write-amplified dump.
- Prefer a concise, curated property set.
- Review retention expectations before logging large metadata blobs.
