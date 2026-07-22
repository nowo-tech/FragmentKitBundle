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

## User Scenarios & Testing

### User Story 1 — Parent page survives fragment HTTP errors (Priority: P1)

As an integrator, I embed a controller fragment with `ignore_errors: true`. When the sub-request returns 403/404, the parent page still returns 200 and shows the fallback (or empty content).

**Independent Test**: Demo home page at `/` embeds a 403 fragment → HTML contains fallback text and HTTP 200.

**Acceptance Scenarios**:

1. **Given** `enabled: true` and `ignore_errors: true`, **When** fragment delivery throws for an HTTP error, **Then** fallback is rendered and the exception is not rethrown.
2. **Given** `ignore_errors: false`, **When** fragment delivery fails, **Then** the exception is rethrown unchanged.

---

### User Story 2 — Configurable fallback (Priority: P1)

As an integrator, I set `fallback.template` to a Twig path (or `null`) so failed fragments show my UI or nothing.

**Acceptance Scenarios**:

1. **Given** a custom template, **When** a fragment fails, **Then** that template receives `status_code`, URIs, routes, controller, and `exception`.
2. **Given** `fallback.template: null`, **When** a fragment fails, **Then** an empty string is returned.

---

### User Story 3 — Optional Sentry reporting (Priority: P2)

As an operator, suppressed fragment failures are reported to Sentry with tags when `sentry/sentry-symfony` is present and `sentry.enabled` is true.

**Acceptance Scenarios**:

1. **Given** Sentry Hub available and enabled, **When** a failure is suppressed, **Then** `captureException` is called with `fragment.failure` / `fragment.suppressed` tags.
2. **Given** Sentry disabled or missing, **When** a failure is suppressed, **Then** a null reporter no-ops and the parent still succeeds.

---

### User Story 4 — Disable decorator (Priority: P2)

As an integrator, I set `enabled: false` to remove the decorator and restore stock Symfony behavior.

**Acceptance Scenarios**:

1. **Given** `enabled: false`, **When** the container is compiled, **Then** `ResilientFragmentHandler` is not decorating `fragment.handler`.

---

## Functional Requirements

| ID | Requirement |
| --- | --- |
| FR-FK-001 | Configuration tree under `nowo_fragment_kit` (`enabled`, `fallback.template`, `sentry.*`) |
| FR-FK-002 | `ResilientFragmentHandler` decorates `fragment.handler` |
| FR-FK-003 | Catch delivery failures only when `ignore_errors` is true |
| FR-FK-004 | `FragmentFailureContextFactory` extracts status, URIs, routes, controller |
| FR-FK-005 | `FragmentFailureRenderer` renders Twig fallback or empty string |
| FR-FK-006 | `FragmentFailureReporterInterface` with Null and Sentry implementations |
| FR-FK-007 | Flex recipe ships default `nowo_fragment_kit.yaml` |
| FR-FK-008 | Twig namespace `NowoFragmentKitBundle` registered with app override precedence |

---

## Success Criteria

- Unit tests cover Configuration, handler ignore/rethrow, and context factory.
- Integration test boots Extension with sample config.
- Demo healthcheck proves parent 200 + fallback for HTTP 403 fragment.
- `make release-check` passes.
