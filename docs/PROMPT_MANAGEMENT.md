# Prompt Management

## Purpose

This document explains prompt ownership in the current AI content engine.

## Core Principle

Main-flow prompts are database-backed and versioned.

Prompt text for the topic and blog generation workflows should not be duplicated in `site_settings` or hardcoded in agent classes.

## Prompt Entities

Prompt management is built around:

- `ai_prompt_templates`
- `ai_prompt_template_versions`

The template stores the stable workflow identity.
The version stores the active prompt content, schema, and declared variables.

## Supported Standard Families

The current editable main-flow prompt families are:

- `topic_standard`
- `blog_standard`

Helper AI passes such as metadata suggestions and title/excerpt refinement use fixed internal prompts and are not part of the versioned main-flow prompt family surface.

## Active Version Model

Each template can have multiple versions.

One version is active at a time and should be resolved by workflows rather than selecting arbitrary prompt text.

## Rendering Flow

Prompt rendering is handled by `RenderAiPromptTemplateService`.

That service:

- resolves the active version
- replaces `{{ variable }}` placeholders
- returns rendered system and user prompts
- records declared variables
- reports missing variables explicitly

## Workflow Usage

Versioned prompt templates are the source of truth for:

- topic discovery
- blog draft generation

The backend still supports prompt template version management through admin APIs for these standard families.

## What Prompt Templates Should Contain

- stable workflow instructions
- variable placeholders for domain context
- output-shape expectations where applicable
- workflow-specific framing rather than provider-specific hacks

## What Prompt Templates Should Not Do

- bypass business rules
- encode publish authorization
- replace service-layer validation
- create a second source of truth outside prompt template versions

## Operational Notes

- prompt changes should be auditable through versions
- prompt keys should remain stable
- prompt ownership for the main flow must remain centralized in prompt templates
