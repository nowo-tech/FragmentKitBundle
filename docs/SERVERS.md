# Server cookbook — Fragment Kit

FragmentKitBundle does **not** expose public HTTP endpoints. It decorates Symfony’s internal `fragment.handler` used by Twig `render(controller(...))`.

## FrankenPHP / Caddy

Ensure all application requests reach `public/index.php` (standard Symfony front controller). No special Caddy routes are required for this bundle.

Worker mode boots Symfony once and reuses the kernel. Fragment decoration is registered at compile time and is compatible with workers.

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
