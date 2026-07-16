# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- Initial release: resilient Twig fragment rendering (`ignore_errors: true` + HTTP error statuses)
- Configurable Twig fallback template
- Optional Sentry reporting (`SentryFragmentFailureReporter`)
- Symfony Flex recipe (`nowo_fragment_kit` config)
- Symfony 8 FrankenPHP demo (`demo/symfony8`)
- Unit and integration tests, QA toolchain (CS Fixer, PHPStan, Rector, coverage)
