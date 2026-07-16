# GitLab CI — REQ-GIT-001 for mirrored repos

Teams that mirror this repository to an internal GitLab instance should enforce the same Cursor co-author hygiene as GitHub Actions ([GITHUB_CI.md](GITHUB_CI.md)).

## Verification commands

```bash
make setup-hooks
make check-no-cursor-coauthor
```

Or directly:

```bash
chmod +x .scripts/check-no-cursor-coauthor.sh
./.scripts/check-no-cursor-coauthor.sh HEAD
```

## Strip dirty history

`git replace` only hides commits **locally** and does **not** fix the remote. Use the per-repo strip script:

```bash
make strip-cursor-coauthor-from-history
make check-no-cursor-coauthor
git push --force-with-lease origin main
# Recreate and force-push tags if needed
```

## Example `.gitlab-ci.yml` job

Shallow clones hide dirty history — set `GIT_DEPTH: "0"`.

```yaml
git-hygiene:
  stage: test
  variables:
    GIT_DEPTH: "0"
  script:
    - chmod +x .scripts/check-no-cursor-coauthor.sh
    - ./.scripts/check-no-cursor-coauthor.sh HEAD
  rules:
    - if: $CI_PIPELINE_SOURCE == "merge_request_event"
    - if: $CI_COMMIT_BRANCH == $CI_DEFAULT_BRANCH
```

## References

- [GITHUB_CI.md](GITHUB_CI.md) — GitHub Actions `git-hygiene` job
- [CONTRIBUTING.md](CONTRIBUTING.md) — local hooks
- [RELEASE.md](RELEASE.md) — check after release commit, before push
