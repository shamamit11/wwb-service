# Template Engine Specification: Wide Web Blog

## Document Purpose

This document defines the post template engine for Wide Web Blog. The initial system supports predefined editorial templates, not a visual page designer.

## Core Rules

- templates seed structured content blocks
- templates do not store page-builder layout instructions
- raw AI-generated HTML must not be stored as the primary article source
- public rendering comes from validated block structures

## Initial Templates

- Standard
- Tutorial
- Listicle
- Comparison
- News

## Template Data Structure

Templates are composed of:

- template metadata
- template configuration
- ordered template blocks

### Core Fields

- `name`
- `slug`
- `template_type`
- `description`
- `status`
- `default_meta`

## Template Config JSON

Example:

```json
{
  "version": 1,
  "recommended_sections": [
    "introduction",
    "main-content",
    "faq",
    "conclusion"
  ],
  "seo_rules": {
    "faq_recommended": true,
    "min_internal_links": 4,
    "preferred_schema": "Article"
  },
  "editor_hints": {
    "tone": "clear, technical, implementation-aware",
    "examples_required": true
  }
}
```

## Supported Block Types

- Heading
- Paragraph
- Image
- Quote
- List
- Code
- FAQ
- Callout

## Block Content Shape Examples

### Heading

```json
{
  "block_type": "heading",
  "content": {
    "text": "How AI Agent Memory Works",
    "level": 2
  }
}
```

### Paragraph

```json
{
  "block_type": "paragraph",
  "content": {
    "markdown": "Memory is one of the core design variables in agent systems..."
  }
}
```

### FAQ

```json
{
  "block_type": "faq",
  "content": {
    "items": [
      {
        "question": "What is short-term memory in an AI agent?",
        "answer_markdown": "It is the working context used during the current run."
      }
    ]
  }
}
```

## Template Definitions

## Standard

Purpose:

- default long-form technical article

Recommended flow:

- title
- introduction
- key sections
- summary

## Tutorial

Purpose:

- step-by-step instructional content

Recommended flow:

- title
- introduction
- prerequisites
- steps
- troubleshooting
- FAQ

## Listicle

Purpose:

- structured multi-point content

Recommended flow:

- title
- intro
- numbered sections
- wrap-up

## Comparison

Purpose:

- tradeoff and decision-oriented content

Recommended flow:

- title
- problem framing
- option A
- option B
- comparison table or summary
- recommendation

## News

Purpose:

- timely but still structured coverage

Recommended flow:

- title
- what happened
- why it matters
- implications

## Template Selection Flow

1. Admin clicks “Create Post”.
2. Admin chooses template type.
3. System shows short template description and preview.
4. Admin confirms selection.
5. System creates a draft post seeded with template blocks.

## Preview Flow

- templates can be previewed before creating a draft
- preview uses representative placeholder data
- preview should show both editor structure and approximate public rendering

## Content Block Rendering

### Editorial Rendering

- render blocks into structured editor components
- preserve order and block-specific validation

### Public Rendering

- convert each block to Blade partials or rendering classes
- sanitize and normalize content before final output
- avoid trusting arbitrary raw HTML as canonical source

## SEO Behavior Per Template

### Standard

- `Article` schema
- FAQ optional

### Tutorial

- `Article` schema
- FAQ recommended
- stronger internal linking expectation

### Listicle

- `Article` schema
- emphasize scanability and headings

### Comparison

- `Article` schema
- internal links to compared concepts required

### News

- `Article` schema
- shorter freshness-oriented metadata acceptable

## Public Rendering Behavior

- public output is derived from stored blocks
- template choice may influence wrapper layout or helper components
- template choice must not lock rendering into unmaintainable branching logic

## Validation Rules

- templates must have at least one block
- block order must be unique per template
- unsupported block types are rejected
- template type must be one of the allowed initial templates

## Future Visual Designer Support

The current system should preserve a future path to richer template editing by:

- storing structured block definitions
- versioning template config
- separating template metadata from rendering code

Do not add visual-designer complexity in the MVP. The current engine should remain editorial-template-first.
