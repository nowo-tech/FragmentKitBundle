# Upgrading

This package has no published releases yet. When upgrading between versions:

1. Read [CHANGELOG.md](CHANGELOG.md) for breaking changes.
2. Compare `config/packages/nowo_fragment_kit.yaml` with the Flex recipe defaults.
3. Run `make release-check` (or `composer qa` + demo healthcheck) after upgrading.
