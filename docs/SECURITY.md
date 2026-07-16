# Security

## Reporting vulnerabilities

Report security issues privately to **hectorfranco@nowo.tech**. Do not open public issues for sensitive reports.

See [.github/SECURITY.md](../.github/SECURITY.md) for supported versions.

## Integrator guidance

- Failed fragments may include exception objects in Twig context (`exception`). Do not dump stack traces in production fallback templates.
- Prefer minimal fallback markup; avoid reflecting untrusted URIs without escaping (Twig auto-escapes by default).
- When enabling Sentry reporting, ensure your DSN and PII policies match your organization standards.
- Keep `nowo_fragment_kit.enabled: false` in environments where you intentionally want stock Symfony fragment failure behavior.

## Dependencies

Run `composer audit` in consuming applications and keep Symfony/Twig updated per your support policy.
