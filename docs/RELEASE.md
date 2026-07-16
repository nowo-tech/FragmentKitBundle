# Release process

## Pre-release checklist

```bash
make release-check
```

`release-check` runs (in order):

1. `check-no-cursor-coauthor`
2. `composer-sync`
3. `cs-fix`, `cs-check`
4. `rector-dry`
5. `phpstan`
6. `test-coverage`
7. Demo healthcheck (if `demo/Makefile` defines `release-check`)

## Version bump

1. Update `docs/CHANGELOG.md` with a dated `## [X.Y.Z]` section.
2. Update `docs/UPGRADING.md` if integrators must act.
3. Commit on `main`.

## Tag and GitHub release

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

GitHub Actions `release.yml` creates a release from the tag message and changelog section.

## Packagist

After the GitHub release, verify Packagist auto-update (webhook) or trigger manual update.

## Sync missing releases

## After tagging / before push

After creating the release commit (and tag), run again:

```bash
make check-no-cursor-coauthor
```

`release-check` alone is insufficient when the release commit is created **after** `release-check` finishes. See [GITHUB_CI.md](GITHUB_CI.md) and [GITLAB_CI.md](GITLAB_CI.md).
