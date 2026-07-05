# Skill: Media Service

## Purpose

Use this skill for service-side upload handling, storage abstraction, image metadata, and media URL behavior.

## Use When

- changing upload or retrieval flows
- working with image processing or storage integration
- designing asset metadata or lifecycle behavior

## Context To Load

- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`

## Working Rules

- Treat Cloudflare R2 as the storage backend abstraction target.
- Keep upload, storage, and retrieval responsibilities separated.
- Keep media behavior inside service abstractions, not controllers.
- Record any signed URL, caching, or metadata assumptions in the task file.
- Prefer service boundaries that can support future AI image generation workflows.
