# FragmentKitBundle

[![CI](https://github.com/nowo-tech/FragmentKitBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/FragmentKitBundle/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/fragment-kit-bundle.svg?style=flat)](https://packagist.org/packages/nowo-tech/fragment-kit-bundle) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/fragment-kit-bundle.svg)](https://packagist.org/packages/nowo-tech/fragment-kit-bundle) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net) [![Symfony](https://img.shields.io/badge/Symfony-7.4%20%7C%208.0%20%7C%208.1%2B-000000?logo=symfony)](https://symfony.com) [![Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen)](#tests-and-coverage)

> ⭐ **Found this useful?** Give it a star on GitHub! It helps us maintain and improve the project.

![FrankenPHP Friendly Worker Mode](docs/images/frankenphp-friendly.png)

This bundle is **FrankenPHP worker mode friendly**.

Symfony bundle that makes Twig fragment sub-requests resilient when using `{ignore_errors: true}`.

## Features

- ✅ **HTTP error tolerance** — `{ignore_errors: true}` also covers 403/404 (not only exceptions)
- ✅ **Twig fallback** — configurable template (or empty string)
- ✅ **Optional Sentry** — report suppressed failures without breaking the parent page
- ✅ **Flex recipe** — ships `nowo_fragment_kit` config
- ✅ **FrankenPHP demo** — Symfony 8 single-container demo

**FrankenPHP:** Demos use a **single PHP service** (FrankenPHP, no nginx). With **`APP_ENV=dev`** (default), the Docker **entrypoint swaps in `Caddyfile.dev`** — **`php_server` without workers** for local development. The production `Caddyfile` can use **`php_server { worker … }`**; see [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

## Problem

`{{ render(controller(...), {ignore_errors: true}) }}` only suppresses **exceptions** thrown during sub-requests. When the sub-request returns an HTTP error status (403, 404, …), `FragmentHandler::deliver()` throws a `RuntimeException` and the **parent page still fails with a 500**.

## Installation

```bash
composer require nowo-tech/fragment-kit-bundle
```

Register the bundle (Flex does this automatically):

```php
Nowo\FragmentKitBundle\NowoFragmentKitBundle::class => ['all' => true],
```

```twig
{{ render(controller('App\\Controller\\WidgetController::index'), {ignore_errors: true}) }}
```

## Requirements

- PHP `>= 8.2`, `< 8.6`
- Symfony `^7.4 || ^8.0` (CI covers **7.4**, **8.0**, **8.1**)
- Twig Bundle

## Documentation

- [GitHub Actions CI requirements](docs/GITHUB_CI.md)
- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [Usage](docs/USAGE.md)
- [Contributing](docs/CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Changelog](docs/CHANGELOG.md)
- [Upgrading](docs/UPGRADING.md)
- [Release](docs/RELEASE.md)
- [Security](docs/SECURITY.md)
- [Engram](docs/ENGRAM.md)
- [Spec-driven development](docs/SPEC-DRIVEN-DEVELOPMENT.md)
- [GitHub Spec Kit](docs/SPEC-KIT.md)

### Additional documentation

- [Demo with FrankenPHP](docs/DEMO-FRANKENPHP.md) (includes worker mode)
- [Server cookbook (Nginx, php-fpm, FrankenPHP)](docs/SERVERS.md)

## Version information

| Version | PHP | Symfony | Status |
|---------|-----|---------|--------|
| 1.0.x | >= 8.2 | 7.4 – 8.1+ | Stable |

## Demos

```bash
make -C demo up-symfony8   # http://localhost:8050 (default PORT)
```

## Tests and coverage

```bash
make test
make test-coverage
```

- Tests: PHPUnit (unit + integration)
- PHP: **100%** lines (`make test-coverage`)

## Development

```bash
make setup-hooks
make up
make install
make release-check
```

## License

MIT — see [LICENSE](LICENSE).
