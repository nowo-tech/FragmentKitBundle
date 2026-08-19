# Upgrading

## Table of contents

- [Unreleased](#unreleased)
- [To 1.2.2](#to-122)
- [To 1.2.1](#to-121)
- [To 1.2.0](#to-120)
- [To 1.1.3](#to-113)
  - [Breaking changes](#breaking-changes)
  - [Integrator notes](#integrator-notes)
  - [Contributors / demos](#contributors--demos)
- [To 1.1.2](#to-112)
  - [Breaking changes](#breaking-changes-1)
  - [Integrator notes](#integrator-notes-1)
  - [Contributors / demos](#contributors--demos-1)
- [To 1.1.1](#to-111)
  - [Breaking changes](#breaking-changes-2)
  - [Integrator notes](#integrator-notes-2)
  - [Contributors / demos](#contributors--demos-2)
- [To 1.1.0](#to-110)
  - [Breaking / migration](#breaking--migration)
  - [What is new](#what-is-new)
  - [After upgrading](#after-upgrading)
- [To 1.0.1](#to-101)
  - [Breaking changes](#breaking-changes-3)
  - [Integrator notes](#integrator-notes-3)
  - [Contributors](#contributors)
- [To 1.0.0 (initial release)](#to-100-initial-release)
  - [Requirements](#requirements)
  - [Enable and configure](#enable-and-configure)
  - [Integrator notes](#integrator-notes-4)
  - [Breaking changes](#breaking-changes-4)
  - [After upgrading](#after-upgrading-1)


## Unreleased

## To 1.2.2

From **1.2.1** — No application upgrade steps.

```bash
composer update nowo-tech/fragment-kit-bundle
```

## To 1.2.1

From **1.2.0** — No application upgrade steps. **Demos only:** Hot Reload Bundle `^1.4` (FrankenPHP Mercure/`hot_reload`, `dev`/`test`).

```bash
composer update nowo-tech/fragment-kit-bundle
```

## To 1.2.0

From **1.1.3** — Adds required Twig Extra (REQ-TWIG-004) and Twig-CS-Fixer. Register TwigExtraBundle if Flex did not.

```bash
composer update nowo-tech/fragment-kit-bundle
php bin/console cache:clear
```

### Twig Extra Bundle (REQ-TWIG-004)

Hosts that render this bundle's Twig templates must install:

```bash
composer require twig/extra-bundle twig/string-extra
```

and enable `Twig\Extra\TwigExtraBundle\TwigExtraBundle`. Flex recipes usually register it automatically.

### Twig-CS-Fixer (maintainers)

Package maintainers: `composer twig:lint` / `composer twig:fix` use `.twig-cs-fixer.php` over `src/` (and `templates/` when present).


## To 1.1.3

```bash
composer update nowo-tech/fragment-kit-bundle
```

Or require explicitly:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.1.3
```

### Breaking changes

- Concrete classes under `src/` are now **`final`** (and `FragmentFailureContext` is **`final readonly`**). Extending them is no longer possible; compose via configuration and public interfaces instead.

### Integrator notes

- Configuration keys, Twig namespace, and public interfaces are unchanged since 1.1.2.
- REQ-SEC-004 Pass (good) / Low risk is recorded in `docs/SECURITY.md`.

### Contributors / demos

- Root and demo Makefiles prefer `docker compose` (V2) and tolerate missing monorepo `../.scripts` includes on standalone clones.

---

## To 1.1.2

```bash
composer update nowo-tech/fragment-kit-bundle
```

Or require explicitly:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.1.2
```

### Breaking changes

None.

### Integrator notes

- **No application migration required.** Public API, Twig namespace (`@NowoFragmentKitBundle/...`), and `sentry.level` enum rules are unchanged since 1.1.1.
- Optional: override the default fallback via `templates/bundles/NowoFragmentKitBundle/fragment_failure.html.twig` (documented in USAGE).

### Contributors / demos

- Symfony 8 demo image is now FrankenPHP **PHP 8.5**; recreate with `make -C demo/symfony8 build && make -C demo/symfony8 up`.
- Prefer `FRANKENPHP_MODE=classic|worker` (default `worker`); recreate containers after changing `.env` (no image rebuild).
- `make release-check` now runs `check-open-prs` and `coverage-check`.

---

## To 1.1.1

```bash
composer update nowo-tech/fragment-kit-bundle
```

Or require explicitly:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.1.1
```

### Breaking changes

None for valid configurations.

### Integrator notes

- **`sentry.level`** must be one of `debug`, `info`, `warning`, `error`, `fatal`. Invalid values now fail container compilation.
- Public API and Twig namespace (`@NowoFragmentKitBundle/...`) are unchanged since 1.1.0.

### Contributors / demos

- Demo stack now includes Twig Inspector; run `make -C demo/symfony8 up` (or `make demo-smoke`) after pulling.
- Prefer `make down-dev` / `make demo-smoke` as documented in the Makefile help.

---

## To 1.1.0

```bash
composer update nowo-tech/fragment-kit-bundle
```

Or require explicitly:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.1
```

### Breaking / migration

- **Twig namespace** — Update fallback template references from `@NowoFragmentKit/...` to `@NowoFragmentKitBundle/...`.
- **Application overrides** — Move bundle template overrides to `templates/bundles/NowoFragmentKitBundle/...`.

If you still use the default template path from the Flex recipe / docs:

```yaml
nowo_fragment_kit:
  fallback:
    template: '@NowoFragmentKitBundle/fragment_failure.html.twig'
```

Custom app templates (e.g. `fragment/unavailable.html.twig`) need no change unless they referenced `@NowoFragmentKit/...`.

### What is new

- `TwigPathsPass` registers the `NowoFragmentKitBundle` Twig namespace and gives app overrides precedence via `prependPath`.

### After upgrading

Clear the Symfony cache and smoke-test pages that embed fragments with `ignore_errors: true`.

---

## To 1.0.1

```bash
composer update nowo-tech/fragment-kit-bundle
```

Or require explicitly:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.0.1
```

### Breaking changes

None.

### Integrator notes

- **No application migration required.** Public API and `nowo_fragment_kit` configuration are unchanged since 1.0.0.
- This release hardens contributor/CI git hygiene (REQ-GIT-001) and documentation only.

### Contributors

- Re-run `make setup-hooks` on existing clones if needed.
- Prefer `make check-no-cursor-coauthor` / `make strip-cursor-coauthor-from-history` as documented in [GITHUB_CI.md](GITHUB_CI.md).

---

## To 1.0.0 (initial release)

This is the first stable release. Install or require the package:

```bash
composer require nowo-tech/fragment-kit-bundle:^1.0
```

### Requirements

- PHP `>= 8.2`, `< 8.6`
- Symfony `^7.4 || ^8.0` (CI covers 7.4, 8.0, 8.1)
- `symfony/twig-bundle`
- Framework fragments enabled (`framework.fragments`)

### Enable and configure

Symfony Flex registers the bundle and copies `config/packages/nowo_fragment_kit.yaml`. Manual setup:

```php
// config/bundles.php
Nowo\FragmentKitBundle\NowoFragmentKitBundle::class => ['all' => true],
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

> **Note:** From **1.1.0** the default Twig namespace is `@NowoFragmentKitBundle/...`. See [To 1.1.0](#to-110).

### Integrator notes

- Use `{{ render(controller(...), {ignore_errors: true}) }}` for fragments that must not break the parent page.
- Override `fallback.template` (or set `null`) if you want custom markup instead of the quiet default.
- Sentry reporting is optional; without `sentry/sentry-symfony` a no-op reporter is used.
- Set `nowo_fragment_kit.enabled: false` to restore stock Symfony fragment failure behavior.

### Breaking changes

None (initial release).

### After upgrading

```bash
make release-check
```

Or in a consuming app: clear cache and smoke-test pages that embed fragments with `ignore_errors: true`.
