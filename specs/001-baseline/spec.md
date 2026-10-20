# Feature Specification: FragmentKitBundle baseline

**Feature Branch**: `001-baseline`  
**Created**: 2026-07-16  
**Status**: Active  
**Input**: Baseline specification for resilient Twig fragment rendering.

**Related docs**: [`docs/SPEC-DRIVEN-DEVELOPMENT.md`](../../docs/SPEC-DRIVEN-DEVELOPMENT.md), [`docs/CONFIGURATION.md`](../../docs/CONFIGURATION.md), [`docs/USAGE.md`](../../docs/USAGE.md)  
**Code inventory**: [`code-inventory.md`](code-inventory.md)

---

## Summary

**Package**: `nowo-tech/fragment-kit-bundle`  
**Configuration root**: `nowo_fragment_kit`

Symfony bundle that decorates `fragment.handler` so Twig `render(controller(...), {ignore_errors: true})` tolerates HTTP error statuses from sub-requests (not only exceptions), optionally renders a fallback template, and can report to Sentry without failing the parent page.

---

## User Scenarios & Testing (`US-*`)

### US-01 — Parent page survives fragment HTTP errors (Priority: P1)

As an integrator, I embed a controller fragment with `ignore_errors: true`. When the sub-request returns 403/404, the parent page still returns 200 and shows the fallback (or empty content).

**Independent Test**: Demo home page at `/` embeds a 403 fragment → HTML contains fallback text and HTTP 200.

**Acceptance Scenarios**:

1. **Given** `enabled: true` and `ignore_errors: true`, **When** fragment delivery throws for an HTTP error, **Then** fallback is rendered and the exception is not rethrown.
2. **Given** `ignore_errors: false`, **When** fragment delivery fails, **Then** the exception is rethrown unchanged.

---

### US-02 — Configurable fallback (Priority: P1)

As an integrator, I set `fallback.template` to a Twig path (or `null`) so failed fragments show my UI or nothing.

**Acceptance Scenarios**:

1. **Given** a custom template, **When** a fragment fails, **Then** that template receives `status_code`, URIs, routes, controller, and `exception`.
2. **Given** `fallback.template: null`, **When** a fragment fails, **Then** an empty string is returned.

---

### US-03 — Optional Sentry reporting (Priority: P2)

As an operator, suppressed fragment failures are reported to Sentry with tags when `sentry/sentry-symfony` is present and `sentry.enabled` is true.

**Acceptance Scenarios**:

1. **Given** Sentry Hub available and enabled, **When** a failure is suppressed, **Then** `captureException` is called with `fragment.failure` / `fragment.suppressed` tags.
2. **Given** Sentry disabled or missing, **When** a failure is suppressed, **Then** a null reporter no-ops and the parent still succeeds.

---

### US-04 — Disable decorator (Priority: P2)

As an integrator, I set `enabled: false` to remove the decorator and restore stock Symfony behavior.

**Acceptance Scenarios**:

1. **Given** `enabled: false`, **When** the container is compiled, **Then** `ResilientFragmentHandler` is not decorating `fragment.handler`.

---

## Functional Requirements (`FR-FK-*`)

### Bundle, configuration and Twig namespace

| ID | Requirement |
| --- | --- |
| FR-FK-001 | Configuration tree under `nowo_fragment_kit` (`enabled`, `fallback.template`, `sentry.*`) |
| FR-FK-007 | Flex recipe ships default `nowo_fragment_kit.yaml` (outside `src/`; documented, not counted in the inventory) |
| FR-FK-008 | Twig namespace `NowoFragmentKitBundle` registered with app override precedence |

### HTTP kernel — fragment handling

| ID | Requirement |
| --- | --- |
| FR-FK-002 | `ResilientFragmentHandler` decorates `fragment.handler` |
| FR-FK-003 | Catch delivery failures only when `ignore_errors` is true |

### Failure context and fallback rendering

| ID | Requirement |
| --- | --- |
| FR-FK-004 | `FragmentFailureContextFactory` extracts status, URIs, routes, controller into `FragmentFailureContext` |
| FR-FK-005 | `FragmentFailureRenderer` renders Twig fallback or empty string |

### Reporting

| ID | Requirement |
| --- | --- |
| FR-FK-006 | `FragmentFailureReporterInterface` with Null and Sentry implementations |

### Runtime (FrankenPHP worker)

| ID | Requirement |
| --- | --- |
| FR-FK-009 | FrankenPHP worker-safe with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false): no per-request state in shared services; Sentry uses `withScope()` |

---

## Key Entities

- **FragmentFailureContext**: immutable snapshot of a failed fragment (status code, request/fragment URIs, routes, controller, exception) passed to the fallback template and reporters.
- **Fallback template**: Twig template (`fallback.template`, default `@NowoFragmentKitBundle/fragment_failure.html.twig`) or `null` for empty output.
- **Reporter**: `FragmentFailureReporterInterface` implementation (Null or Sentry).

---

## Success Criteria (`SC-*`)

- **SC-001**: **13/13** files tracked under `src/` are mapped in [`code-inventory.md`](code-inventory.md) (audit: `git ls-files src | wc -l`).
- **SC-002**: Unit tests cover Configuration, handler ignore/rethrow, and context factory; an integration test boots the Extension with sample config.
- **SC-003**: Demo healthcheck proves parent 200 + fallback for an HTTP 403 fragment.
- **SC-004**: `make release-check` passes (cs-check, PHPStan classic + worker rulesets, PHPUnit, `check-no-cursor-coauthor`, demos).
- **SC-005**: FrankenPHP worker audit (`docs/FRANKENPHP-WORKER-AUDIT.md`) documents compatibility with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false).

---

## Non-goals

- Catching fragment failures when `ignore_errors` is false (stock Symfony behavior is kept).
- Retrying failed sub-requests or caching fragment output.
- Providing an error-tracking backend other than the optional Sentry reporter (hosts can implement `FragmentFailureReporterInterface`).
- Demo applications under `demo/` are illustrative, not Packagist API.

---

## Validation commands

```bash
make qa              # composer cs-check + phpstan + PHPUnit (Docker)
make test-coverage   # PHPUnit with coverage
make release-check   # full pre-release gate (includes check-no-cursor-coauthor and demo healthchecks)
```

Behavior changes require tests under `tests/` and an update of this spec and [`code-inventory.md`](code-inventory.md).
