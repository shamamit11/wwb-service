# Newsletter Backlog

This file tracks the remaining newsletter-related service work after the current Newsletter module foundation, admin API, public subscribe/unsubscribe flow, queued sending flow, tracking, and webhook/event ingestion implementation.

## Current State

Implemented already:

- subscriber, list, campaign, and recipient data model
- newsletter enums, repositories, services, and jobs
- admin newsletter API
- public subscribe and unsubscribe endpoints
- queued campaign sending
- mail-backed delivery provider abstraction
- open/click tracking
- webhook-style bounce, complaint, and unsubscribe event processing

Not implemented here:

- admin UI
- frontend forms and user-facing pages

## Remaining Service Work

### 1. Newsletter Generation Agent

We need to build a Newsletter generation agent.

Suggested scope:

- generate newsletter campaign drafts from recent posts, knowledge base entries, and editorial context
- support prompt-driven generation for subject, preview text, markdown body, and HTML-ready structure
- keep generated newsletters as draft only until human approval
- track generation in AI Jobs and generation steps
- support reusable prompt templates for newsletter generation

Suggested deliverables:

- newsletter generation agent
- newsletter campaign generation workflow/orchestrator
- prompt template support for newsletter campaigns
- draft campaign persistence from agent output
- tests for generation workflow and guardrails

### 2. ESP-Specific Delivery Adapters

The module currently uses a generic Laravel Mail-backed provider. It still needs first-class provider adapters.

Suggested deliverables:

- provider adapters for one or more ESPs such as Postmark, Resend, SES, or Mailgun
- provider-specific payload mapping
- provider message ID persistence
- provider metadata storage on recipients/campaigns
- provider-specific failure normalization

### 3. Webhook Hardening

Webhook ingestion exists, but production-grade provider verification still needs to be implemented.

Suggested deliverables:

- signature verification per provider
- timestamp/replay protection where supported
- provider-specific webhook parser layer
- normalized webhook event mapping
- tests for invalid signature and replay rejection

### 4. Campaign Delivery Controls

Campaign sending is queued, but operational controls are still limited.

Suggested deliverables:

- pause campaign sending
- cancel campaign sending
- chunked recipient dispatch for large campaigns
- send throttling / rate limiting
- retry failed recipients
- resend selected recipients

### 5. Scheduling Execution

Campaigns can store `scheduled_at`, but scheduled execution orchestration is still incomplete.

Suggested deliverables:

- scheduler or command to discover due campaigns
- idempotent dispatch of scheduled sends
- scheduled-to-sending transition rules
- tests for due, future, and already-sent campaign handling

### 6. List Preference Management

Unsubscribe is currently subscriber-wide. List-level preference handling is still missing.

Suggested deliverables:

- unsubscribe from a single list
- keep subscriber active on remaining lists
- list preference update endpoints/services
- recipient targeting that respects list-level opt-out state
- preference-aware tests

### 7. Audience Segmentation

Recipient staging currently supports explicit subscribers, explicit lists, or all active subscribers. Segmentation is still basic.

Suggested deliverables:

- filter by subscriber status subsets where appropriate
- filter by source
- metadata-driven segment selection
- saved segment definitions if needed
- segment preview/count service

### 8. Analytics and Reporting

Tracking fields exist, but reporting services and API summaries are still missing.

Suggested deliverables:

- campaign delivery summary service
- sent/open/click/bounce/complaint/unsubscribe aggregates
- recipient status breakdowns
- time-series reporting if needed
- API resources for reporting payloads

### 9. Content Rendering Improvements

Current rendering is functional but minimal.

Suggested deliverables:

- stronger markdown-to-HTML newsletter rendering
- safer HTML sanitization policy if required
- improved plain-text rendering
- reusable newsletter layout wrappers
- link rewriting coverage beyond simple absolute HTML links

### 10. Delivery Observability

Operational visibility should improve before high-volume use.

Suggested deliverables:

- structured logging for campaign dispatch and recipient failures
- richer delivery metadata persistence
- queue failure diagnostics
- command or endpoint to inspect delivery progress

### 11. Testing Expansion

The current suite covers core flows, but more depth is still useful.

Suggested deliverables:

- provider adapter contract tests
- webhook signature tests
- scheduled send tests
- retry and partial-failure tests
- larger audience staging/send tests

## Suggested Next Order

1. Newsletter generation agent
2. Scheduling execution
3. ESP-specific delivery adapter
4. Webhook hardening
5. Campaign delivery controls
6. Analytics and reporting
7. List preference management

## Product Rules To Preserve

- no email verification
- no double opt-in
- subscribers become active immediately
- newsletters remain draft until human approval
- newsletter tools and services must not publish posts
