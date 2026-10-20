# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## Table of contents

- [[Unreleased]](#unreleased)
- [[1.3.1] - 2026-10-13](#131---2026-10-13)
- [[1.3.0] - 2026-10-13](#130---2026-10-13)
- [[1.2.6] - 2026-10-09](#126---2026-10-09)
- [[1.2.5] - 2026-09-27](#125---2026-09-27)
- [[1.2.4] - 2026-09-24](#124---2026-09-24)
- [[1.2.3] - 2026-08-24](#123---2026-08-24)
- [[1.2.2] - 2026-08-19](#122---2026-08-19)
- [[1.2.1] - 2026-08-18](#121---2026-08-18)
- [[1.2.0] - 2026-08-04](#120---2026-08-04)
- [[1.1.3] - 2026-07-29](#113---2026-07-29)
- [[1.1.2] - 2026-07-28](#112---2026-07-28)
- [[1.1.1] - 2026-07-28](#111---2026-07-28)
- [[1.1.0] - 2026-07-22](#110---2026-07-22)
- [[1.0.1] - 2026-07-20](#101---2026-07-20)
- [[1.0.0] - 2026-07-16](#100---2026-07-16)

## [Unreleased]

### Changed

- Dependencies refreshed (Composer and pnpm/npm lock files of the bundle and its demos, campaign 2026-10-19).
- `.php-cs-fixer.dist.php`: `new ParallelConfig(2)` (REQ-CS-001).
- Documentation: wave-1 REQ compliance — README documentation list order, GitHub stars badge image slug and canonical FrankenPHP worker claim, canonical issue templates, CONTRIBUTING Cursor Agent queue settings, TOCs for long docs, DEMO-FRANKENPHP dev/prod start and troubleshooting, Engram ↔ spec-driven cross-reference, full GitHub Spec Kit manual, baseline spec (US-*, grouped FR-*, SC-*, non-goals, validation) and inventory refresh (13/13).

## [1.3.1] - 2026-10-13

### Changed

- CI: the test matrix now includes PHP 8.5 (Symfony 7.4, 8.0, 8.1), matching the `php: >=8.2 <8.6` constraint.

[1.3.1]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.3.1

## [1.3.0] - 2026-10-13

### Fixed

- Controller exceptions in fragments rendered with `ignore_errors: true` now get the fallback template and are reported (the inline renderer used to swallow them into an empty string first). The inline renderer is now called with `ignore_errors: false` and every failure is handled by FragmentKit; `alt` fallbacks are still tried first.
- `ignore_errors` now defaults to `!kernel.debug` when not set, like Symfony's `FragmentHandler` (previously treated as `false`).
- Failure context (`fragment_uri`, `controller`, `route`, `parent_*`) no longer reports the parent page as the failing fragment: it is built from the URI / `controller()` reference passed to `render()`, with the current request as parent.

### Changed

- Repository: `.twig-cs-fixer.cache` is now git-ignored.

[1.3.0]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.3.0

## [1.2.6] - 2026-10-09

### Dependencies

- Dev tooling (Dependabot): `igor-php/igor-php` `^0.10.0` (lock 0.10.1), `nowo-tech/phpstan-frankenphp` 1.2.3, `phpstan/phpstan-phpunit` 2.1.1.
- Lockfile refresh: Symfony 7.4.20, `twig/twig` 3.30.0; dev `phpstan/phpstan` 2.3.1, `phpstan/phpstan-symfony` 2.1.0, `rector/rector` 2.7.0, `phpunit/phpunit` 11.5.57.

[1.2.6]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.6

## [1.2.5] - 2026-09-27

### Added

- **REQ-CS-008:** `igor-php/igor-php` (require-dev only), root `igor.json`, Composer/`Makefile` `igor` target, and `release-check` wiring for FrankenPHP worker-state audit.

### Changed

- **Worker safety (Igor):** justified `// @igor-ignore` annotations and/or `ResetInterface` / request-scoped fixes so `make igor` passes on package `src/`.

[1.2.5]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.5

## [1.2.4] - 2026-09-24

### Added

- **FrankenPHP worker audit** — `docs/FRANKENPHP-WORKER-AUDIT.md` for kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false); linked from README, USAGE, SERVERS, DEMO-FRANKENPHP (REQ-DEMO-008).
- Spec **FR-FK-009** / success criterion for worker + PHPStan classic/worker rulesets.

### Changed

- Document kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/`0`) across README and FrankenPHP docs; custom reporter contract notes worker safety.
- `NullFragmentFailureReporter` is `final readonly`; exclude `Contract/` and `Model/` from the service resource loader.
- PHPDoc on `ResilientFragmentHandler` / Sentry reporter clarifying no per-request state and `withScope()` isolation.

### Notes

- **No API or configuration changes** for integrators; runtime behaviour unchanged.

[1.2.4]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.4

## [1.2.3] - 2026-08-24

### Changed

- **QA:** add `phpstan-frankenphp` extension (REQ-CS-005).
- **Docs:** PHP-FIG PSR evaluation (REQ-CS-007).
- **Dependencies:** routine Composer/npm bumps (Dependabot).

### Notes

- **No API or configuration changes** for integrators unless noted above.

[1.2.3]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.3

## [1.2.2] - 2026-08-19

### Security

- **CI:** run `composer audit --locked` after dependency install (REQ-SEC / P3).

## [1.2.1] - 2026-08-18

### Changed

- **Demos:** pin `nowo-tech/hot-reload-bundle` to `^1.4` with FrankenPHP Mercure/`hot_reload` (`dev`/`test` only).

[1.2.1]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.1

## [1.2.0] - 2026-08-04

### Added
- **REQ-TWIG-004:** require `twig/extra-bundle` + `twig/string-extra`; `make check-twig-extra` in `release-check`; demos register `TwigExtraBundle`.
- **Twig-CS-Fixer:** `vincentlanglet/twig-cs-fixer`, `.twig-cs-fixer.php`, `composer twig:lint` / `twig:fix`.

[1.2.0]: https://github.com/nowo-tech/FragmentKitBundle/releases/tag/v1.2.0

## [1.1.3] - 2026-07-29

### Changed

- Mark concrete `src/` classes as `final` / `final readonly` (REQ-PHP-001); drop redundant `readonly` on promoted props in readonly classes.
- Makefiles: prefer Compose V2 (`docker compose`) with V1 fallback (REQ-MAKE-010); optional `-include` for monorepo `update-deps` helpers (REQ-MAKE-009).
- Add TOC to long docs (CHANGELOG, UPGRADING, USAGE, INSTALLATION, CONTRIBUTING); GitHub topic `frankenphp`; PHPStan `ignoreErrors: []`; formalize REQ-SEC-004 Pass (good) in `docs/SECURITY.md`.

## [1.1.2] - 2026-07-28

### Added

- Flex recipe `manifest.json` (bundle registration + config copy) (REQ-RECIPE-001).
- `make check-open-prs` / `.scripts/check-open-prs.sh` (REQ-REL-003).
- `make coverage-check` / `test-coverage-100` (REQ-TEST-006); CI coverage gate **100%** + PHPStan job.
- Cursor rule `.cursor/rules/20-twig-and-public-assets.mdc` (REQ-IDE-003).

### Changed

- Demo FrankenPHP: PHP **8.5** image, real `worker` in Caddyfile, entrypoint paths aligned (REQ-DEMO-010).
- README: canonical Documentation order, GitHub stars badge, `FRANKENPHP_MODE` docs (REQ-DOCS-002/004).
- USAGE/CONFIGURATION: Twig override via `templates/bundles/NowoFragmentKitBundle/` (REQ-TWIG-001).
- Expanded `SPEC-DRIVEN-DEVELOPMENT.md`; `.github/SECURITY.md` supports **1.1.x**.
- Demo `up` messages + DNS comment (REQ-DEMO-005/009); `setup-hooks` uses `core.hooksPath` (REQ-MAKE-006).
- Dev deps bump: phpstan 2.2.6, rector 2.5.8, php-cs-fixer 3.95.17.

## [1.1.1] - 2026-07-28

### Added

- **REQ compliance** — demo Twig Inspector (`REQ-DEMO-001`), FrankenPHP Friendly banner (`REQ-DOCS-017`), Spec Kit skills + `.specify` scaffold (`REQ-SPECKIT-001`), Dependabot / PR-lint / stale workflows (`REQ-GH-002/004/005`), `make down-dev` / `demo-smoke` (`REQ-MAKE-007`, `REQ-TEST-011`), and `nowo-tech/phpstan-frankenphp` (`REQ-CS-005`).
- **SECURITY 12.4.1** — release security checklist in `docs/SECURITY.md` (`REQ-SEC-002`).
- Demo `FRANKENPHP_MODE` + `docker/entrypoint.sh` (`REQ-DEMO-010`).

### Changed

- **`sentry.level`** — validated as enum (`debug|info|warning|error|fatal`); invalid values are rejected (`REQ-SF-006`).
- PHPUnit: `SYMFONY_DEPRECATIONS_HELPER=max[direct]=0` (`REQ-SF-005`).
- Demo `.env.example` / `.gitignore` aligned with REQ-DEMO-003 / REQ-ENV-001; TOC in long docs (`REQ-DOCS-005`).
- Root `release-check` demos no longer swallows demo failures.

## [1.1.0] - 2026-07-22

### Added

- **`TwigPathsPass`** — registers Twig namespace `NowoFragmentKitBundle` and prepends app overrides from `templates/bundles/NowoFragmentKitBundle/` (FR-FK-008).

### Changed

- **Twig fallback namespace** — default template is now `@NowoFragmentKitBundle/fragment_failure.html.twig` (was `@NowoFragmentKit/...`). Flex recipe and docs updated accordingly.

### Migration

See [UPGRADING.md](UPGRADING.md) — update explicit `@NowoFragmentKit/...` config references and move template overrides under `templates/bundles/NowoFragmentKitBundle/`.

## [1.0.1] - 2026-07-20

### Changed

- **REQ-GIT-001** — `check-no-cursor-coauthor.sh` uses `git --no-replace-objects` so local `git replace` refs cannot hide dirty history from CI.
- **REQ-GIT-001** — `strip-cursor-coauthor-from-history.sh` refuses a dirty working tree before rewriting history.
- **docs/GITHUB_CI.md** — expanded canonical operator/CI guide (adoption checklist, pitfalls, multi-bundle rollout).
- Removed obsolete `docs/GITLAB_CI.md`; README and CONTRIBUTING now point only to GitHub CI hygiene docs.

### Changed (dev)

- `composer.lock` — `guzzlehttp/psr7` 2.12.5 → 2.13.0 (dev transitive).

## [1.0.0] - 2026-07-16

First stable release.

### Added

- Resilient Twig fragment rendering: `{ignore_errors: true}` also covers HTTP error statuses (403, 404, …), not only exceptions
- Configurable Twig fallback template (`nowo_fragment_kit.fallback.template`; default `@NowoFragmentKit/fragment_failure.html.twig` → empty output)
- Optional Sentry reporting via `SentryFragmentFailureReporter` (`nowo_fragment_kit.sentry.*`; requires `sentry/sentry-symfony`)
- Symfony Flex recipe (`nowo_fragment_kit` config under `.symfony/recipe`)
- Symfony 8 FrankenPHP demo (`demo/symfony8`)
- Unit and integration tests with 100% line coverage; QA toolchain (CS Fixer, PHPStan, Rector)

### Compatibility

- PHP `>= 8.2`, `< 8.6`
- Symfony `^7.4 || ^8.0` (CI matrix: 7.4, 8.0, 8.1)
