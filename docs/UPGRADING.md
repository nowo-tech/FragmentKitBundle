# Upgrading

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
