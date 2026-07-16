# FragmentKitBundle

Symfony bundle that makes Twig fragment sub-requests resilient when using `{ignore_errors: true}`.

## Problem

`{{ render(controller(...), {ignore_errors: true}) }}` only suppresses **exceptions** thrown during sub-requests. When the sub-request returns an HTTP error status (403, 404, …), `FragmentHandler::deliver()` throws a `RuntimeException` and the **parent page still fails with a 500**.

## Solution

This bundle decorates `fragment.handler` to:

1. Catch fragment delivery failures when `ignore_errors: true`
2. Render a configurable **Twig fallback** (or empty string)
3. Optionally **report to Sentry** without affecting the user

## Requirements

- PHP `>=8.2 <8.6`
- Symfony `^7.0 || ^8.0`
- Twig Bundle

## Quick start

```bash
composer require nowo-tech/fragment-kit-bundle
```

```yaml
# config/packages/nowo_fragment_kit.yaml
nowo_fragment_kit:
  enabled: true
  fallback:
    template: '@NowoFragmentKit/fragment_failure.html.twig'
  sentry:
    enabled: true
    level: warning
```

```twig
{{ render(controller('App\\Controller\\WidgetController::index'), {ignore_errors: true}) }}
```

## Documentation

| Doc | Description |
|-----|-------------|
| [Installation](docs/INSTALLATION.md) | Composer, Flex recipe, manual registration |
| [Configuration](docs/CONFIGURATION.md) | Options reference |
| [Usage](docs/USAGE.md) | Fallback templates, Sentry, examples |
| [Demo (FrankenPHP)](docs/DEMO-FRANKENPHP.md) | Local Symfony 8 demo |
| [Contributing](docs/CONTRIBUTING.md) | Hooks, QA, `release-check` |
| [Security](docs/SECURITY.md) | Vulnerability reporting |
| [GitHub CI](docs/GITHUB_CI.md) | REQ-GIT-001 on GitHub Actions |
| [GitLab CI](docs/GITLAB_CI.md) | REQ-GIT-001 when mirroring to GitLab |
| [Release](docs/RELEASE.md) | Release checklist |
| [Changelog](docs/CHANGELOG.md) | Version history |
| [Spec Kit](docs/SPEC-KIT.md) | Spec-driven development |

## FrankenPHP worker mode

Demos run on FrankenPHP. Prefer **worker mode** in production so Symfony boots once and handles many requests. See [DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

## Development

```bash
make setup-hooks
make up
make install
make test
make release-check
```

### Tests and coverage

```bash
make test
make test-coverage
```

Latest measured PHP line coverage: **92.91%** (see `coverage-php.txt` after `make test-coverage`).

Demo:

```bash
make -C demo/symfony8 up
# Demo started at: http://localhost:8050
```

## License

MIT — see [LICENSE](LICENSE).
