# Skill: Livewire Admin

## Use When

- working in the admin panel
- changing editorial workflows
- building review, approval, or publishing controls
- updating admin UI states or moderation flows

## Expected Area

Primary path is `admin/` when that repository or folder is available.

## Context To Load

- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/skills/testing.md`

## Working Rules

- Preserve strong workflow state visibility for editors and admins.
- Make approval and publishing actions explicit and auditable.
- Keep Livewire components narrowly scoped to a business flow.
- Reuse shared admin UI patterns instead of creating one-off behavior.

## Product Guardrail

Any AI-generated content shown in admin must remain draft until an authorized admin approves it.
