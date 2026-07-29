# Usage

## Table of contents

- [Basic usage](#basic-usage)
- [Fallback template variables](#fallback-template-variables)
  - [Example custom fallback](#example-custom-fallback)
- [Overriding the default Twig template (REQ-TWIG-001)](#overriding-the-default-twig-template-req-twig-001)
- [Sentry](#sentry)
- [Demo](#demo)

## Basic usage

Wrap embedded controllers with `ignore_errors: true`:

```twig
{{ render(controller('App\\Controller\\WidgetController::index'), {ignore_errors: true}) }}
```

If the sub-request throws **or** returns an HTTP error status (403, 404, 500, …), FragmentKit:

1. Reports the failure (optional Sentry)
2. Renders the configured fallback template (or empty string)
3. Lets the **parent response continue** with HTTP 200

Without this bundle, HTTP error statuses from fragments still surface as a parent 500 via `FragmentHandler::deliver()`.

## Fallback template variables

| Variable | Description |
|----------|-------------|
| `status_code` | HTTP status of the failed fragment (when known) |
| `fragment_uri` | URI of the failed sub-request |
| `route` | Sub-request route name |
| `parent_route` | Parent page route name |
| `parent_uri` | Parent page URI |
| `controller` | Sub-request controller |
| `exception` | Original exception |

### Example custom fallback

```twig
{# templates/fragment/unavailable.html.twig #}
<div class="alert alert-warning" role="alert">
  Fragment unavailable (HTTP {{ status_code|default('?') }}).
</div>
```

The default bundle template (`@NowoFragmentKitBundle/fragment_failure.html.twig`) renders an empty string so production pages stay quiet until you override it.

## Overriding the default Twig template (REQ-TWIG-001)

The bundle registers Twig namespace **`NowoFragmentKitBundle`** via `TwigPathsPass`. Application overrides take precedence:

1. Create `templates/bundles/NowoFragmentKitBundle/fragment_failure.html.twig` in your app (same relative path as under `src/Resources/views/`).
2. Keep using `@NowoFragmentKitBundle/fragment_failure.html.twig` as `nowo_fragment_kit.fallback.template`, **or** point `fallback.template` at your own app template (e.g. `fragment/unavailable.html.twig`).

Symfony will load the app file under `templates/bundles/NowoFragmentKitBundle/` before the bundle’s shipped view.

## Sentry

Requires `sentry/sentry-symfony`. Events are tagged with:

- `fragment.failure=true`
- `fragment.suppressed=true`

```bash
composer require sentry/sentry-symfony
```

## Demo

```bash
make -C demo/symfony8 up
```

Open `http://localhost:8050` — the home page embeds healthy, 403, and 404 fragments. Failed ones show the custom fallback while the parent stays OK.

See [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md).
