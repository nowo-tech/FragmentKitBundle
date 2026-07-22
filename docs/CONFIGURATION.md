# Configuration

Root node: `nowo_fragment_kit`.

```yaml
nowo_fragment_kit:
  enabled: true
  fallback:
    template: '@NowoFragmentKitBundle/fragment_failure.html.twig'  # or null for empty string
  sentry:
    enabled: true
    level: warning   # debug|info|warning|error|fatal
```

## Options

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enabled` | bool | `true` | When `false`, the `fragment.handler` decorator is not registered |
| `fallback.template` | string\|null | `@NowoFragmentKitBundle/fragment_failure.html.twig` | Twig template rendered on suppressed failure; `null` → empty string |
| `sentry.enabled` | bool | `true` | Report suppressed failures when `sentry/sentry-symfony` is installed |
| `sentry.level` | string | `warning` | Sentry event level |

## Disable resilience

```yaml
nowo_fragment_kit:
  enabled: false
```

## Custom fallback

```yaml
nowo_fragment_kit:
  fallback:
    template: 'fragment/unavailable.html.twig'
```

## Disable Sentry only

```yaml
nowo_fragment_kit:
  sentry:
    enabled: false
```

When Sentry is not installed or `sentry.enabled` is false, a null reporter is used (no-op).
