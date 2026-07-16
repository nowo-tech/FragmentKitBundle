# Copilot / AI assistant instructions (FragmentKitBundle)

## Project

- PHP `>=8.2 <8.6`, Symfony `^7.4 || ^8.0` (CI: 7.4, 8.0, 8.1).
- Strict types, PHPDoc in English, PSR-12 + Symfony CS Fixer.
- No `doctrine/annotations`; use PHP 8 attributes.

## Domain

Resilient Twig fragments: decorate `fragment.handler` so `{ignore_errors: true}` also tolerates HTTP error statuses from sub-requests, with optional Twig fallback and Sentry reporting.

## Git

Never add Cursor co-author trailers to commits:

```text
Co-authored-by: Cursor <cursoragent@cursor.com>
```

See `docs/GITHUB_CI.md` and `.cursor/rules/01-git-commits.mdc`.

## Docs

All Markdown is English. Prefer updating `docs/` and `specs/001-baseline/` with code changes.
