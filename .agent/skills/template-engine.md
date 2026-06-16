# Skill: Template Engine

## Purpose

Use this skill for post templates, template configs, structured block rendering, and template-driven content rules.

## Context To Load

- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `docs/TEMPLATE_ENGINE.md`
- `.agent/ARCHITECTURE.md`

## Use This When

- adding or changing predefined templates
- changing template JSON config
- changing block compatibility rules
- reviewing how templates affect SEO or rendering

## Core Rules

- templates define structure and presentation constraints
- structured post blocks remain the article source of truth
- do not use raw AI-generated HTML as the main content source
- template logic should remain separable from publish lifecycle logic

## Implementation Checklist

- template config schema is explicit
- block compatibility is validated
- preview and public rendering expectations are documented
- SEO defaults are template-aware when useful
