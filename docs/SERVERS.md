# Server cookbook — Fragment Kit

FragmentKitBundle does **not** expose public HTTP endpoints. It decorates Symfony’s internal `fragment.handler` used by Twig `render(controller(...))`.

## FrankenPHP / Caddy

Ensure all application requests reach `public/index.php` (standard Symfony front controller). No special Caddy routes are required for this bundle.

Worker mode boots Symfony once and **reuses the kernel** by default (`FRANKENPHP_RESET_KERNEL` unset or `0`). Fragment decoration is registered at compile time; bundle services hold no per-request state and need no `kernel.reset`. See [FRANKENPHP-WORKER-AUDIT.md](FRANKENPHP-WORKER-AUDIT.md).

Set `FRANKENPHP_RESET_KERNEL=1` only if you need a fresh kernel each request (escape hatch; lower throughput). The bundle remains compatible either way.

## php-fpm + Nginx

Proxy PHP to the front controller as usual. No extra `location` blocks are needed for FragmentKit.

## Checklist

- [ ] Bundle registered in `config/bundles.php`
- [ ] `config/packages/nowo_fragment_kit.yaml` present
- [ ] `framework.fragments` enabled
- [ ] Fallback template appropriate for production (no debug dumps)
- [ ] Optional: Sentry DSN configured when `sentry.enabled: true`

## Related

- [DEMO-FRANKENPHP.md](DEMO-FRANKENPHP.md)
- [CONFIGURATION.md](CONFIGURATION.md)
- [USAGE.md](USAGE.md)
