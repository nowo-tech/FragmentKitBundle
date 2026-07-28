# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
- Root `release-check-demos` no longer swallows demo failures.

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
