# Installation

## Requirements

- PHP `>= 8.2`, `< 8.6`
- Symfony `^7.4 || ^8.0`
- `symfony/twig-bundle`
- Framework fragments enabled (`framework.fragments`)

## Composer

```bash
composer require nowo-tech/fragment-kit-bundle
```

## Register the bundle

Symfony Flex registers the bundle automatically via the recipe. Manual registration:

```php
// config/bundles.php
Nowo\FragmentKitBundle\NowoFragmentKitBundle::class => ['all' => true],
```

## Flex recipe

The recipe copies:

```yaml
# config/packages/nowo_fragment_kit.yaml
nowo_fragment_kit:
  enabled: true
  fallback:
    template: '@NowoFragmentKitBundle/fragment_failure.html.twig'
  sentry:
    enabled: true
    level: warning
```

## Framework fragments

Ensure fragments are enabled (required for `render(controller(...))`):

```yaml
# config/packages/framework.yaml
framework:
  fragments:
    path: /_fragment
```

## Docker development (bundle contributors)

```bash
make up
make install
make test
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for hooks and QA targets.
