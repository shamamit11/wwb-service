# Prompt Management

## Purpose

This document explains how prompt management works in the current AI content engine.

## Core Principle

Workflow prompts are database-backed and versioned.

Prompt text should not be hardcoded in agent classes when the workflow depends on an editable prompt template.

## Prompt Entities

Prompt management is built around:

- `ai_prompt_templates`
- `ai_prompt_template_versions`

The template describes the workflow-level prompt identity.
The version stores the active prompt content, schema, and declared variables.

## Supported Prompt Categories

The current backend supports prompt template types for:

- topic discovery
- content brief
- blog writer
- editor
- seo optimizer
- publishing

Not every type is fully exercised by the Phase 3 MVP workflow today, but the schema is prepared for those categories.

## Active Version Model

Each prompt template can have multiple versions.

One version is activated as the current live version for the workflow. Agents and workflows should resolve the active version instead of selecting arbitrary prompt text.

## Rendering Flow

Prompt rendering is handled by `RenderAiPromptTemplateService`.

That service:

- resolves the active version
- replaces `{{ variable }}` placeholders
- returns rendered system and user prompts
- records declared variables
- reports missing variables instead of failing silently

## Current Workflow Usage

Prompt templates currently matter most for:

- topic discovery
- content brief generation
- blog draft generation

Each workflow may accept an optional `prompt_template_key` override, but the workflow still stays inside the same business guardrails.

## Admin Placeholder

The backend already exposes prompt management APIs for future admin UI work:

- list templates
- create templates
- read templates
- update templates
- create versions
- activate versions

This is the current Prompt Management placeholder referenced by the AI documentation.

## What Prompt Templates Should Contain

Prompt templates should define:

- stable workflow instructions
- variable placeholders for domain context
- output schema expectations where applicable
- workflow-specific framing rather than raw provider-specific hacks

## What Prompt Templates Should Not Do

- bypass business rules
- encode publish authorization
- replace service-layer validation
- assume AI may create live content

## Operational Notes

- Prompt changes should be auditable through versions.
- Missing variables should be visible during development and debugging.
- Prompt keys should remain stable so workflows and admin consumers can reference them safely.

## Summary

Prompt management is a backend subsystem, not just a text field. Its job is to keep workflow prompting:

- editable
- versioned
- reviewable
- compatible with provider-agnostic orchestration
