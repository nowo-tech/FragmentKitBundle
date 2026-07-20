# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
