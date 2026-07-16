# FrankenPHP demo

The Symfony 8 demo runs as a **single FrankenPHP container** (no separate Nginx).

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

## Development vs production Caddyfile

| Mode | Caddyfile | PHP mode |
| --- | --- | --- |
| `APP_ENV=dev` (default) | `Caddyfile.dev` | `php_server` — no workers, file changes visible immediately |
| `APP_ENV=prod` | `Caddyfile` | Can use `php_server { worker … }` for FrankenPHP workers |

The Docker entrypoint copies `Caddyfile.dev` when `APP_ENV=dev`.

## Bundle path repository

The demo mounts the bundle at `/var/fragment-kit-bundle` and uses a Composer path repository so local `src/` changes are symlinked.

## Verify

```bash
curl -s http://localhost:8050/ | grep -E 'Healthy fragment|Fragment unavailable|HTTP 403'
make -C demo release-check
```

## Worker mode note

FrankenPHP workers keep PHP state across requests. Prefer `APP_ENV=dev` without workers during local development. For production worker mode guidance, see [SERVERS.md](SERVERS.md).
