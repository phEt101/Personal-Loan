# Personal-Loan — Copilot Instructions

## Project Overview
Laravel 11 web app for Thai personal-loan consent management. UI is bilingual (TH/EN) with runtime locale switching.

## Module Structure
All features live in `app/Modules/{ModuleName}/` with this exact layout:
```
app/Modules/{Name}/
  Http/Controllers/     # One controller per module (extends Illuminate\Routing\Controller)
  Models/               # Eloquent models, namespace App\Modules\{Name}\Models\
  Resources/
    views/              # Blade files (no subdirectory nesting)
    lang/
      th/messages.php   # Return nested PHP array
      en/messages.php
  Routes/web.php        # Routes for this module only
```
Modules are registered in `app/Providers/AppServiceProvider.php` via `loadRoutesFrom`, `loadViewsFrom`, `loadTranslationsFrom`.

## Routes
- All routes: `app/Modules/{Name}/Routes/web.php`
- Middleware group: `['web', 'auth']` (except auth module which uses `['web']`)
- Named routes: `{module}.{action}` pattern (e.g. `consent.index`, `consent.save-step`)
- Current modules: `Auth` (`/login`), `Home` (`/home`), `Consent` (`/consent`)

## Views
- View namespace: `{lowercase_module}::view_name` (e.g. `consent::index`, `auth::login`)
- Layouts: `resources/views/layouts/app.blade.php` (authenticated pages)
- Partials: `resources/views/partials/{sidebar,topbar}.blade.php`
- Modal views are loaded via AJAX into the main page, not full-page views

## i18n
- Translation namespace: `{module}::messages.{nested.key}` (e.g. `consent::messages.index.page_title`)
- In Blade: `__('consent::messages.key')` or `@lang('consent::messages.key')`
- In JS (index.blade.php): strings are passed as `window.*Lang = @json(__('consent::messages.modal'))` then accessed via `window.consentLang.key`
- Lang files return a single nested PHP array — add new keys at the correct nesting level in BOTH `th/messages.php` and `en/messages.php`

## Database
- Migrations: `database/migrations/` — filename format `YYYY_MM_DD_HHMMSS_create_{table}_table.php`
- Consent module tables: `consent_applications`, `consent_applicants`, `consent_contacts`, `consent_addresses`, `consent_employments`, `consent_previous_employments`, `consent_references`, `consent_loan_requests`, `consent_disbursement_accounts`, `consent_documents_file`
- PostCodes lookup: `post_codes` table (pre-seeded via `PostCodeSeeder`)

## Code Style
- Match existing patterns exactly — no new base classes, traits, or abstractions unless already present
- Controller methods: plain public functions, return `view()` or `response()->json()`
- No repository pattern, no service classes unless already in the codebase
- Keep Blade files flat — avoid deeply nested `@if`/`@foreach` blocks
- JS lives inline in Blade files or as small `<script>` blocks — no build step, no bundler

## Implementation Discipline
- Only change what was explicitly asked for
- Do NOT add docstrings, type hints, or comments to code you did not write
- Do NOT add error handling for scenarios that cannot happen
- Do NOT refactor code adjacent to the change
- Do NOT add features "while you're at it"

## Task Decomposition
- If a task touches more than 2 files or has more than 3 distinct steps, split it into numbered sub-tasks before starting
- Present the sub-task list first and ask which part to start with — do NOT implement everything in one shot
- Each sub-task must be small enough to review in a single response
- Example split: (1) add route, (2) add controller method, (3) add view, (4) add lang keys — implement one at a time

## Repository For Reviews
- For any sizable change, create or update a review repository or branch that contains only the minimal set of changes necessary for code review (PR or disposable repo). Include a short `REPOSITORY_README.md` describing how to run and what to review.

## I18n / No Hardcoded Text
- Never hardcode Thai or English text directly in code or views. Always use the module translation files under `app/Modules/{Module}/Resources/lang/{th,en}/messages.php` or `resources/lang/` as appropriate.
- Add new translation keys to BOTH Thai and English files in the same change.

## Context Efficiency
- When asking for AI assistance or opening issues, include only the minimal set of files and code snippets required to reproduce the problem.
- Prefer pointing to specific files/line ranges instead of pasting large files. Use `grep_search` / `read_file` to load only relevant sections.

## Security & Performance
- Prioritize secure defaults: validate inputs, escape outputs, avoid unsafe deserialization, and never log secrets or PII.
- For performance-sensitive changes, include benchmarks or expected complexity and prefer incremental changes with measurable improvements.
- Add a short checklist in PR descriptions covering authentication, authorization, input validation, data encryption, and potential performance regressions.

## Context Efficiency
- Read only the files directly relevant to the current task
- Answer concisely — code blocks over prose explanations
- When referencing existing patterns, cite the file and line rather than quoting large blocks
- Prefer `grep_search` / `read_file` over semantic_search when the target file is known
