---
name: Add i18n Keys
description: Migrate hardcoded Thai/English strings in a Blade file to i18n translation keys following the consent::messages.* pattern.
argument-hint: "Path to the Blade file to migrate (e.g. app/Modules/Consent/Resources/views/consent_form_modal.blade.php)"
tools: [read, search, edit]
model: ['Claude Haiku 3.5 (copilot)', 'GPT-4o mini (copilot)']
---

## Task
Migrate hardcoded strings in the specified Blade file to i18n translation keys.

---

## Step 1 — Read First
1. Read the target Blade file in full
2. Read `app/Modules/Consent/Resources/lang/th/messages.php` — understand existing key structure and nesting
3. Read `app/Modules/Consent/Resources/lang/en/messages.php` — find equivalent English strings

---

## Step 2 — Identify Strings
List all hardcoded Thai (or English) strings in the Blade file:
- Static text inside HTML elements
- Blade `placeholder`, `title`, `aria-label`, `value` attributes
- Option text inside `<select>` dropdowns
- Button labels and link text

Skip strings that are:
- Already using `__()` or `@lang()`
- Dynamic PHP variables
- HTML entity references (`&nbsp;`, etc.)

---

## Step 3 — Add Translation Keys
For each string found:
1. Determine the correct nesting path based on the file's section (e.g. `modal.form.step1.*` for step 1 fields)
2. Add the key to `th/messages.php` with the Thai value
3. Add the same key to `en/messages.php` with the English translation
4. Follow the existing array nesting structure exactly — do not create new top-level keys

---

## Step 4 — Update the Blade File
Replace each hardcoded string with the translation helper:
- Static text: `{{ __('consent::messages.group.key') }}`
- HTML attributes: `placeholder="{{ __('consent::messages.group.key') }}"`
- Long text blocks: `@lang('consent::messages.group.key')`

---

## Step 5 — JS Strings (index.blade.php only)
If the target file is `index.blade.php` and strings are used in JavaScript:
- Add the keys to the lang files as above
- Pass them via the existing `window.*Lang = @json(__('consent::messages.section'))` pattern
- Access in JS via `window.consentLang.key` — do not hardcode strings inside JS

---

## Constraints
- Do NOT change any HTML structure, CSS classes, or element attributes other than string content
- Do NOT change JS logic — only the string values
- Do NOT refactor Blade syntax or layout
- Add keys to BOTH `th/messages.php` AND `en/messages.php` — never only one
