# Task Record

## Task Summary

Fix post creation failure caused by strict post-block payload validation rejecting text-like block content when `content.markdown` is absent.

## Requested Outcome

- allow post creation to succeed for the payload shape currently reaching the service
- preserve normalized `content_markdown` persistence for structured post blocks
- cover the accepted payload shape with a focused regression test

## Scope Boundaries

- service repository only
- lightweight admin `.agent` docs and post payload code inspected to confirm the actual client payload contract
- no template or rendering behavior changes beyond payload normalization

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/TESTING.md`
- `.agent/skills/template-engine.md`
- `.agent/knowledge-base/module-map.md`

## Repository Files Inspected

- `app/Http/Requests/Api/V1/Admin/StorePostRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdatePostRequest.php`
- `app/Http/Requests/Api/V1/Admin/Concerns/InteractsWithPostData.php`
- `app/Modules/Posts/Services/PostBlockPayloadValidator.php`
- `app/Modules/Posts/Services/PostBlockPayloadMapper.php`
- `app/Modules/Posts/Services/CreatePostService.php`
- `app/Modules/Posts/Services/RewritePostDraftService.php`
- `tests/Feature/PostApiTest.php`
- `tests/Feature/PostCommandServiceTest.php`
- `docs/OPENAPI_SPEC.md`
- `docs/TEMPLATE_ENGINE.md`
- `../admin/.agent/ARCHITECTURE.md`
- `../admin/.agent/API-CONTRACT.md`
- `../admin/app/Data/Posts/PostBlockData.php`
- `../admin/app/Livewire/Admin/Posts/Editor.php`
- `../admin/tests/Integration/PostClientTest.php`

## Plan

1. Normalize text-like block content keys in post block validation and mapping.
2. Add a focused regression test for post creation with paragraph text content.
3. Run the smallest relevant test selection and record the result.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Admin/Concerns/InteractsWithPostData.php`
- `app/Modules/Posts/Services/PostBlockPayloadValidator.php`
- `app/Modules/Posts/Services/PostBlockPayloadMapper.php`
- `tests/Feature/PostApiTest.php`

## Validation

- `php artisan test --filter=PostApiTest`
- `php artisan test --filter=PostCommandServiceTest`

## Risks Or Follow-Ups

- API docs still describe the canonical structured `content` shape, while the admin currently sends ordered string arrays for blocks. The service now normalizes that legacy/editor shape, but the contract should be aligned and documented explicitly.

## Completion Notes

- Confirmed the admin post editor sends `blocks[*].content` as an ordered string array, not as a keyed object.
- Added request-layer normalization that converts admin array payloads into the structured block content expected by the posts service.
- Added a regression API test covering post creation with the admin-style paragraph array payload.
