# Spec-driven development

In this repository, **spec-driven development** has three layers that stay in sync:

1. **GitHub Spec Kit baseline** — [`specs/001-baseline/`](../specs/001-baseline/) ([`spec.md`](../specs/001-baseline/spec.md), [`code-inventory.md`](../specs/001-baseline/code-inventory.md)), initialized with [GitHub Spec Kit](https://github.com/github/spec-kit) (`.specify/`, **Cursor Agent** skills in `.cursor/skills/speckit-*`). The inventory maps **100%** of production code in `src/`. **How to install, initialize, and use Spec Kit:** [`SPEC-KIT.md`](SPEC-KIT.md).
2. **Product behavior** — what **FragmentKitBundle** guarantees to applications that integrate it (see [`USAGE.md`](USAGE.md), [`CONFIGURATION.md`](CONFIGURATION.md), [`INSTALLATION.md`](INSTALLATION.md)). **PHPUnit** and **PHPStan** enforce contracts in CI.
3. **Traceability anchors** — stable **`REQ-*`** identifiers in Makefiles and demos so changes to scripts, ports, and demo workflows stay discoverable from issues and PRs.

There is no separate executable spec language (for example Gherkin); Spec Kit specs, tests, and static analysis are the mechanical proof alongside this document.

## Table of contents

- [User stories](#user-stories)
- [Bundle functional scope](#bundle-functional-scope)
- [Validating the functional spec](#validating-the-functional-spec)
- [Requirement identifiers (`REQ-*`)](#requirement-identifiers-req-)
- [Suggested workflow for contributors](#suggested-workflow-for-contributors)
- [Relationship to Engram / Spec Kit](#relationship-to-engram--spec-kit)
- [Public API (integrator-facing)](#public-api-integrator-facing)

---

## User stories

| ID | Story |
| --- | --- |
| US-01 | **As a** Symfony integrator, **I want** Twig fragment sub-requests with `{ignore_errors: true}` to survive HTTP error statuses **so that** the parent page does not return 500. |
| US-02 | **As an** integrator, **I want** a configurable Twig fallback (or empty string) **so that** failed fragments can show quiet or branded placeholders. |
| US-03 | **As an** integrator, **I want** optional Sentry reporting of suppressed failures **so that** I can observe fragment errors without breaking the page. |
| US-04 | **As a** maintainer, **I want** behavior changes covered by automated tests and Spec Kit inventory **so that** regressions are caught in CI. |
| US-05 | **As a** contributor, **I want** `REQ-*` anchors on scripted flows **so that** PRs and issues cite the same identifiers as this document. |

**Out of scope for these stories:** guarantees outside the stated public API and outside dependency limits (PHP, Symfony, Sentry).

---

## Bundle functional scope

**Goal:** Resilient Twig fragment rendering for Symfony when `{ignore_errors: true}` is used (HTTP statuses + exceptions), with optional fallback template and Sentry reporting.

**In scope**

- Documented integration (root `README.md` and `docs/`).
- Configuration and runtime behavior in [`CONFIGURATION.md`](CONFIGURATION.md) and [`USAGE.md`](USAGE.md).
- Twig namespace `NowoFragmentKitBundle` and app overrides under `templates/bundles/NowoFragmentKitBundle/` ([REQ-TWIG-001](../BUNDLES_FULL_SPECS_DETAILS.md) / USAGE).
- Consumer-facing change notes in [`CHANGELOG.md`](CHANGELOG.md) and [`UPGRADING.md`](UPGRADING.md).

**Explicit non-goals**

- Admin/CRUD UI, Doctrine entities, Messenger handlers, or frontend assets.
- **`demo/`** trees: illustrative unless a path is explicitly published as stable API.

---

## Validating the functional spec

- Run **`composer qa`** and/or **`make qa`** / **`make release-check`** as documented in [`CONTRIBUTING.md`](CONTRIBUTING.md).
- Run **PHPUnit** (100% lines) and **PHPStan** locally and in CI.
- New or changed behavior should add or adjust **tests** under `tests/`.

---

## Requirement identifiers (`REQ-*`)

| ID | Where | What it marks |
| --- | --- | --- |
| REQ-GIT-001 | `Makefile`, `.scripts/`, CI `git-hygiene` | No Cursor co-author trailers |
| REQ-MAKE-007 | `Makefile` `down-dev` | Dev-friendly container stop |
| REQ-MAKE-008 | `make update-deps` | Bundle + demo dependency refresh |
| REQ-TEST-011 | `make demo-smoke` | Demo boot + HTTP 200 |
| REQ-REL-003 | `make check-open-prs` | No unresolved open GitHub PRs before release |
| REQ-DEMO-010 | `demo/symfony8` FrankenPHP | Latest PHP image + `FRANKENPHP_MODE` |
| FR-FK-001…008 | `specs/001-baseline/spec.md` | Product functional requirements |

When you change scripted behavior, **update the existing `REQ-*` comment** if the ID still matches, or **add a new `REQ-*`** and document it here.

---

## Suggested workflow for contributors

1. **Clarify behavior** in an issue or draft PR.
2. **Implement** with tests and static analysis.
3. **Anchor scripts and demos** when dev UX changes (`REQ-*`).
4. **Ship integrator docs** when behavior or configuration changes.
5. **Keep Spec Kit artifacts in sync** when `src/` changes (`spec.md`, `code-inventory.md`, `/speckit-*` skills).

---

## Relationship to Engram / Spec Kit

- [Engram](ENGRAM.md) — persist decisions across sessions.
- [SPEC-KIT.md](SPEC-KIT.md) — Spec Kit install, skills, and maintainer checklist.
- Shared org checklist: `repositories/bundles/BUNDLES_FULL_SPECS_DETAILS.md`.

---

## Public API (integrator-facing)

| Surface | Notes |
| --- | --- |
| `nowo_fragment_kit.*` config | See [`CONFIGURATION.md`](CONFIGURATION.md) |
| `@NowoFragmentKitBundle/fragment_failure.html.twig` | Default fallback; overridable via `templates/bundles/…` |
| `FragmentFailureReporterInterface` | Null + optional Sentry implementations |
| `ResilientFragmentHandler` | Decorates `fragment.handler` when enabled |
