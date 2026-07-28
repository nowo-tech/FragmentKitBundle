# Upgrading

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
