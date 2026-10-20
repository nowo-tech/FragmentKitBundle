# FrankenPHP demo

**REQ-DEMO-001:** FrankenPHP demos must install **Nowo Twig Inspector** and **Nowo Hot Reload** together (`nowo-tech/twig-inspector-bundle` + `nowo-tech/hot-reload-bundle` in `require-dev`). Caddyfile: Mercure + `hot_reload` (and `worker { file …; watch }` in worker mode). Do not enable Hot Reload in production.

The Symfony 8 demo runs as a **single FrankenPHP container** (no separate Nginx).

## Table of contents

- [Quick start](#quick-start)
- [What the demo shows](#what-the-demo-shows)
- [PHP version (REQ-DEMO-010)](#php-version-req-demo-010)
- [FRANKENPHP_MODE (classic vs worker)](#frankenphp_mode-classic-vs-worker)
- [Kernel reuse (`FRANKENPHP_RESET_KERNEL`)](#kernel-reuse-frankenphp_reset_kernel)
- [Bundle path repository](#bundle-path-repository)
- [Start the demo in development](#start-the-demo-in-development)
- [Start the demo in production mode](#start-the-demo-in-production-mode)
- [Troubleshooting](#troubleshooting)
- [Verify](#verify)

## Quick start

```bash
make -C demo up-symfony8
```

Default URL: `http://localhost:8050` (see `demo/symfony8/.env.example`).

## What the demo shows

The home page embeds three Twig fragments with `{ignore_errors: true}`:

1. Healthy fragment (control)
2. HTTP 403 fragment → custom fallback
3. HTTP 404 fragment → custom fallback

The parent response stays HTTP 200.

## PHP version (REQ-DEMO-010)

The Symfony 8 demo image uses the newest FrankenPHP PHP tag allowed by Composer (`dunglas/frankenphp:1-php8.5-alpine` as of this release). Older Symfony major demos may keep an older PHP that matches that major’s constraints.

## FRANKENPHP_MODE (classic vs worker)

| Mode | Value | Caddyfile | PHP behaviour |
| --- | --- | --- | --- |
| Worker (default) | `FRANKENPHP_MODE=worker` | `docker/frankenphp/Caddyfile` | `php_server { worker … }` — long-lived workers |
| Classic | `FRANKENPHP_MODE=classic` | `docker/frankenphp/Caddyfile.dev` | `php_server` without workers — easier hot-reload |

Set `FRANKENPHP_MODE` in `demo/symfony8/.env` (from `.env.example`). Compose passes it into the container; it is **not** baked into the Dockerfile `ENV`.

Switch modes without rebuilding the image:

```bash
# edit demo/symfony8/.env → FRANKENPHP_MODE=classic|worker
make -C demo/symfony8 down
make -C demo/symfony8 up
```

A plain `docker compose restart` does **not** reload env; recreate with `up -d` after editing `.env`.

The Docker entrypoint copies the matching Caddyfile into `/etc/frankenphp/Caddyfile` before starting FrankenPHP.

## Kernel reuse (`FRANKENPHP_RESET_KERNEL`)

With Symfony Runtime, the worker **reuses the kernel** by default (`FRANKENPHP_RESET_KERNEL` unset or `0`). Set `FRANKENPHP_RESET_KERNEL=1` only if you need a fresh kernel each request (lower throughput). Fragment Kit is audited for the default (no reset): see [FRANKENPHP-WORKER-AUDIT.md](FRANKENPHP-WORKER-AUDIT.md).

## Bundle path repository

The demo mounts the bundle at `/var/fragment-kit-bundle` and uses a Composer path repository so local `src/` changes are symlinked.

## Start the demo in development

```bash
cd demo/symfony8
cp -n .env.example .env        # first run only; set FRANKENPHP_MODE=classic for request mode + hot reload
make up                        # docker compose up -d (entrypoint selects Caddyfile / Caddyfile.dev from FRANKENPHP_MODE)
make install                   # composer install inside the container (if the Makefile does not run it in up)
```

Open http://localhost:8050. In development `APP_ENV=dev`; `Caddyfile.dev` (classic mode) serves PHP without workers, and `docker/php-dev.ini` (when present) sets `opcache.revalidate_freq=0` so code changes are picked up immediately.

## Start the demo in production mode

The production pattern is the worker Caddyfile (`php_server { worker { file /app/public/index.php; watch } }`), which is the default (`FRANKENPHP_MODE=worker`).

```bash
cd demo/symfony8
# .env: FRANKENPHP_MODE=worker, APP_ENV=prod, APP_DEBUG=0, a real APP_SECRET
make down && make up           # recreate containers so the new env is applied
docker compose exec php php bin/console cache:clear --env=prod
```

In a real deployment, bake the application into the image (no bind mount), keep `FRANKENPHP_MODE=worker`, and warm the cache at build time. Worker-mode state rules are documented in the bundle worker audit (`docs/FRANKENPHP-WORKER-AUDIT.md`, when present).

## Troubleshooting

| Symptom | Cause | Fix |
| --- | --- | --- |
| Changes in PHP code are not visible | Worker mode keeps the kernel in memory | Use `FRANKENPHP_MODE=classic` in development, or restart the container (`make down && make up`) |
| `FRANKENPHP_MODE` change has no effect | `docker compose restart` does not reload `.env` | Recreate the containers: `make down && make up` (or `docker compose up -d`) |
| Port already in use on `make up` | Another stack uses the demo port | Change `PORT` in `.env` and recreate the containers |
| Bundle changes are not picked up | Composer path repository / autoload cache out of date | Run `make update-bundle` (or `composer dump-autoload` + `cache:clear` in the container) |
| HTTP 502 / container restarts | PHP fatal error at boot (missing `vendor/`, bad config) | Check `docker compose logs php`; run `make install` and `bin/console cache:clear` |
| Stale Twig output in development | Twig cache enabled | Ensure `config/packages/dev/twig.yaml` sets `twig.cache: false` (when the demo ships it) and clear the cache |

## Verify

```bash
curl -s http://localhost:8050/ | grep -E 'Healthy fragment|Fragment unavailable|HTTP 403'
make -C demo release-check
make -C demo demo-smoke
```

For production worker guidance beyond the demo, see [SERVERS.md](SERVERS.md).
