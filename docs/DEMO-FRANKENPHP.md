# FrankenPHP demo

The Symfony 8 demo runs as a **single FrankenPHP container** (no separate Nginx).

## Table of contents

- [Quick start](#quick-start)
- [What the demo shows](#what-the-demo-shows)
- [PHP version (REQ-DEMO-010)](#php-version-req-demo-010)
- [FRANKENPHP_MODE (classic vs worker)](#frankenphp_mode-classic-vs-worker)
- [Bundle path repository](#bundle-path-repository)
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

## Bundle path repository

The demo mounts the bundle at `/var/fragment-kit-bundle` and uses a Composer path repository so local `src/` changes are symlinked.

## Verify

```bash
curl -s http://localhost:8050/ | grep -E 'Healthy fragment|Fragment unavailable|HTTP 403'
make -C demo release-check
make -C demo demo-smoke
```

For production worker guidance beyond the demo, see [SERVERS.md](SERVERS.md).
