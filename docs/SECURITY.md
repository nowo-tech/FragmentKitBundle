# Security

## Table of contents

- [Scope](#scope)
- [Attack surface](#attack-surface)
- [Threats and mitigations](#threats-and-mitigations)
- [Integrator guidance](#integrator-guidance)
- [Logging and observability](#logging-and-observability)
- [Dependencies](#dependencies)
- [Reporting a vulnerability](#reporting-a-vulnerability)
- [Supported versions](#supported-versions)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)
- [AI security audit](#ai-security-audit)

## Scope

Fragment Kit makes Twig fragment sub-requests resilient when `{ignore_errors: true}` is used. Review fallback templates and optional Sentry reporting in your application.

## Attack surface

- **Fragment sub-requests** decorated via `fragment.handler` when `nowo_fragment_kit.enabled` is true.
- **Twig fallback template** (`nowo_fragment_kit.fallback.template`) and context variables (`exception`, URIs, routes).
- **Optional Sentry reporting** (`nowo_fragment_kit.sentry.*`) when `sentry/sentry-symfony` is installed.
- No admin UI, no outbound HTTP client of its own, and no subprocesses.

## Threats and mitigations

| Threat | Mitigation |
|--------|------------|
| XSS via fallback markup | Prefer minimal fallbacks; Twig auto-escapes by default. Do not dump stack traces in production templates. |
| Information disclosure in Sentry | Tag only fragment metadata; align DSN and PII policies with your organization. |
| Untrusted URI reflection | Escape `fragment_uri` / `parent_uri` in custom fallbacks (Twig escaping is enough for normal use). |
| Intentional stock Symfony failure behavior | Set `nowo_fragment_kit.enabled: false` when you do not want suppression. |

## Integrator guidance

- Failed fragments may include exception objects in Twig context (`exception`). Do not dump stack traces in production fallback templates.
- Prefer minimal fallback markup; avoid reflecting untrusted URIs without escaping.
- When enabling Sentry reporting, ensure your DSN and PII policies match your organization standards.
- Keep `nowo_fragment_kit.enabled: false` in environments where you intentionally want stock Symfony fragment failure behavior.

## Logging and observability

This bundle does **not** inject a Monolog logger into shipped `src/` services for routine fragment handling (REQ-OBS-001). Optional Sentry reporting is the observability path when enabled. Integrators **must not** log secrets, session identifiers, or full personal data in custom fallbacks or wrappers.

## Dependencies

Run `composer audit` in consuming applications and keep Symfony/Twig updated per your support policy.

## Reporting a vulnerability

Report security issues privately to **hectorfranco@nowo.tech**. Do not open public issues for sensitive reports.

See [.github/SECURITY.md](../.github/SECURITY.md) for supported versions.

## Supported versions

Security fixes are applied to the current major version. Upgrade to the latest release to receive fixes.

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|--------|
| **SECURITY.md** | This document is current and linked from the README where applicable. |
| **`.gitignore` and `.env`** | `.env` and local env files are ignored; no committed secrets. |
| **No secrets in repo** | No API keys, passwords, or tokens in tracked files. |
| **Recipe / Flex** | Default recipe or installer templates do not ship production secrets. |
| **Input / output** | Fallback context is escaped in Twig; do not dump exceptions in production templates. |
| **Dependencies** | `composer audit` run; issues triaged. |
| **Logging / Sentry** | Events do not print secrets, tokens, or session identifiers unnecessarily. |
| **Permissions / exposure** | No admin routes; fragment suppression is opt-in via config and Twig options. |
| **Limits / DoS** | Fragment failures stay local to the sub-request; parent page continues. |
| **AI security audit (REQ-SEC-004)** | Grade **Pass (good)** / risk **Low** (2026-07-28). Recorded in the Nowo monorepo `BUNDLES_SECURITY_ANALYSIS.md`. |

Record confirmation in the release PR or tag notes.

## AI security audit

| Field | Value |
| ----- | ----- |
| Date | 2026-07-28 |
| Grade | Pass (good) |
| Risk | Low |
| Method | Cursor security-review / campaign static pass (`src/`, Flex recipe, demo, SECURITY docs) |
| Open residuals | None Critical/High. App-owned: avoid dumping exceptions/URIs in production fallbacks; align Sentry DSN/PII policy when reporting is enabled. |
