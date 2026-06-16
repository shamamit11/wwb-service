# Admin UI/UX Specification: Wide Web Blog

## Document Purpose

This document defines the admin experience for the single Laravel + Livewire monolith. The admin should feel clean, structured, and efficient for a technical publishing workflow.

## UI Principles

- publishing-focused, not generic CMS-heavy
- low visual noise
- fast navigation between content operations
- table-first for management, editor-first for creation
- explicit draft and review states
- strong error feedback and safe destructive actions

## Shared UX Rules

### Layout

- left sidebar navigation on desktop
- compact top bar with search, user menu, and status indicators
- responsive stacked navigation on smaller screens

### Common Table Behavior

- search field in header
- filters in toolbar
- sort by clickable headers
- row actions on the right
- bulk actions only where truly useful

### Validation Behavior

- inline field validation on blur for obvious issues
- full form validation on submit
- global error banner for submit failures

### Confirmation Modals

Use confirmation modals for:

- deletes
- publish actions
- unpublish actions
- retrying AI jobs that may incur cost
- replacing featured media if destructive

## Screen: Login

### Purpose

Authenticate admins securely into the CMS.

### Layout

- centered card
- brand mark and short product line
- email and password form
- optional password reset link

### Main Actions

- sign in
- request password reset

### Form Fields

- email
- password
- remember me

### Empty States

- none

### Validation Behavior

- invalid credentials return generic error
- email field validates email shape

### UX Notes

- keep the login screen minimal and professional

## Screen: Dashboard

### Purpose

Provide a quick operational overview of publishing, SEO, and AI activity.

### Layout

- top summary cards
- recent drafts and recent published posts
- topic queue and AI job status widgets
- SEO completeness widget

### Main Actions

- create post
- review drafts
- open topic queue
- open AI jobs

### Table Columns

Recent drafts:

- title
- status
- updated at
- author
- actions

### Empty States

- “No posts yet. Create the first post.”

### UX Notes

- dashboard should emphasize actions over vanity metrics in MVP

## Screen: Category Management

### Purpose

Manage the primary taxonomy for the publication.

### Layout

- list view with create button
- create/edit drawer or dedicated page

### Main Actions

- create category
- edit
- archive/delete

### Table Columns

- name
- slug
- parent
- active
- sort order
- updated at
- actions

### Form Fields

- name
- slug
- description
- parent category
- active toggle
- sort order
- SEO title
- SEO description

### Empty States

- “No categories yet. Create the first content category.”

### Confirmation Modals

- delete or archive category

### UX Notes

- show post count to help prevent careless deletes later

## Screen: Post Management

### Purpose

Create, edit, organize, schedule, and publish posts.

### Layout

- index screen with filters and search
- editor screen with metadata side panel
- block editor center column

### Main Actions

- create post
- edit draft
- duplicate
- publish
- schedule
- unpublish
- delete

### Table Columns

- title
- status
- category
- author
- SEO score
- published at
- updated at
- actions

### Form Fields

- title
- slug
- excerpt
- category
- tags
- template
- featured image
- visibility
- scheduled publish date
- block editor
- SEO fields

### Empty States

- “No posts found.”
- “No drafts yet.”
- “No published posts yet.”

### Validation Behavior

- title required
- category required
- block set must contain valid content
- schedule date required when scheduling

### Confirmation Modals

- publish
- unpublish
- delete

### UX Notes

- use sticky side panel for status, SEO, and featured image
- preserve draft autosave where practical later

## Screen: Media Library

### Purpose

Upload, search, manage, and reuse media assets.

### Layout

- upload area
- searchable media grid or list
- detail drawer for selected asset

### Main Actions

- upload
- multi-upload
- search
- edit metadata
- copy URL
- delete

### Table Columns

- thumbnail
- filename
- source type
- mime type
- dimensions
- usage count
- created at
- actions

### Form Fields

- file
- alt text
- caption
- source type
- source URL
- attribution text

### Empty States

- “No media yet. Upload the first asset.”

### Confirmation Modals

- delete media

### UX Notes

- surface usage count before delete
- expose “used in posts” references later

## Screen: Template Management

### Purpose

Manage predefined post templates and preview their structure.

### Layout

- template list
- editor with template metadata and block configuration
- preview panel

### Main Actions

- create template
- edit
- activate/archive
- preview
- create draft from template

### Table Columns

- name
- type
- status
- blocks count
- updated at
- actions

### Form Fields

- name
- slug
- template type
- description
- status
- default meta JSON or structured config
- ordered block configuration

### Empty States

- “No templates yet. Create the first editorial template.”

### UX Notes

- use explicit block ordering UI, not a visual page builder

## Screen: Knowledge Base

### Purpose

Store reusable internal knowledge that improves content quality and future AI workflows.

### Layout

- searchable list
- edit view with content and metadata
- related posts and topics side panel

### Main Actions

- create entry
- edit
- archive
- link to post
- link to topic

### Table Columns

- title
- type
- status
- tags
- linked posts
- updated at
- actions

### Form Fields

- title
- slug
- type
- status
- summary
- markdown content
- tags
- categories
- source URL

### Empty States

- “No knowledge entries yet. Add reusable editorial context.”

### UX Notes

- markdown editor is acceptable; focus on usability over richness

## Screen: SEO Settings

### Purpose

Manage sitewide SEO defaults, metadata rules, and quality signals.

### Layout

- sitewide defaults tab
- scoring or recommendations tab
- structured data config summary

### Main Actions

- update defaults
- review low-score posts
- view missing metadata

### Table Columns

Low-score content:

- title
- score
- missing items
- updated at
- action

### Form Fields

- site title
- default meta description
- organization name
- social image defaults
- robots defaults
- RSS toggle

### Empty States

- “No SEO issues detected.”

### UX Notes

- keep this screen operational, not overly technical

## Screen: Topic Queue

### Purpose

Review, approve, reject, and manage suggested topics.

### Layout

- filterable queue
- topic detail panel
- action buttons for approval flow

### Main Actions

- approve
- reject
- mark used
- generate blueprint

### Table Columns

- topic name
- status
- suggested category
- source
- discovery score
- created at
- actions

### Form Fields

- topic name
- slug
- description
- suggested category
- status
- notes

### Empty States

- “No topics in queue.”

### Confirmation Modals

- reject topic
- mark topic used

### UX Notes

- make approval quick; this should support editorial flow, not become a bottleneck

## Screen: AI Jobs

### Purpose

Monitor AI-assisted operations, statuses, retries, and costs.

### Layout

- filterable jobs table
- detail drawer with job inputs, outputs, and usage

### Main Actions

- retry
- cancel
- inspect output

### Table Columns

- job ID
- job type
- target
- provider
- model
- status
- cost
- created at
- actions

### Empty States

- “No AI jobs yet.”

### Confirmation Modals

- retry job
- cancel job

### UX Notes

- show clear separation between successful jobs and draft approval state

## Screen: Settings

### Purpose

Manage site-wide operational settings beyond SEO-only concerns.

### Layout

- tabs for general, publishing, storage, AI, and integrations

### Main Actions

- save settings
- test integrations where supported later

### Form Fields

- site name
- base URL
- publishing defaults
- storage settings summary
- AI provider defaults
- feature toggles

### UX Notes

- avoid exposing low-level secrets directly if a better config path exists
