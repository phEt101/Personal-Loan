---
name: New Module Feature
description: Add a new feature (route + controller method + view + i18n keys) to an existing module following project conventions.
argument-hint: "Module name + feature description (e.g. 'Consent — export PDF button')"
tools: [read, search, edit]
model: ['Claude Haiku 3.5 (copilot)', 'GPT-4o mini (copilot)']
---

## Task
Add the feature described by the user to the specified module. Follow the steps below exactly.

---

## Step 1 — Read First
Before writing anything, read these files for the target module:

- `app/Modules/{Module}/Http/Controllers/{Module}Controller.php` — existing method patterns
- `app/Modules/{Module}/Routes/web.php` — existing route patterns
- `app/Modules/{Module}/Resources/views/` — list existing Blade files
- `app/Modules/{Module}/Resources/lang/th/messages.php` — existing key structure
- `app/Modules/{Module}/Resources/lang/en/messages.php` — existing key structure

---

## Step 2 — Implement (in order)

### Route
- Add to `app/Modules/{Module}/Routes/web.php` inside the existing middleware group
- Named route: `{module}.{action}` pattern (e.g. `consent.export`)
- HTTP verb must match the action (GET for pages/modals, POST for mutations, DELETE for removal)

### Controller Method
- Add a plain `public function` to the existing controller — no new classes
- Return `view('{module}::{view_name}')` for pages/modals, or `response()->json(...)` for AJAX
- Match the style of the method immediately above it

### View (if needed)
- Create `app/Modules/{Module}/Resources/views/{view_name}.blade.php`
- Reference the layout: `@extends('layouts.app')` for full pages, no extends for modal partials

### i18n Keys
- Add keys to BOTH `th/messages.php` and `en/messages.php` under the correct nested array path
- Never add top-level keys — nest under an existing or new logical group
- In Blade use: `__('consent::messages.group.key')` or `@lang('consent::messages.group.key')`

---

## Constraints
- Do NOT add docstrings, type hints, or comments to code you did not write
- Do NOT refactor adjacent code
- Do NOT add validation or error handling beyond what already exists in the controller
- Do NOT create new base classes, traits, or service classes
