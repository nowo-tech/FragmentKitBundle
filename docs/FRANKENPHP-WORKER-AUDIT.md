# FrankenPHP worker mode audit (`FRANKENPHP_RESET_KERNEL` unset / false)

| Field | Value |
|-------|-------|
| Package | `nowo-tech/fragment-kit-bundle` (`symfony-bundle`) |
| Audited revision | release **1.3.4** (first audit 1.2.4) |
| Audit date | 2026-09-24, refreshed 2026-11-12 (REQ-RUNTIME-002) |
| Worker test | [`ResilientFragmentKernelTest::testManyRequestsWithAThrowingReporterOnOneKernelKeepMemoryFlat`](#worker-test-req-runtime-002): 1,100 requests through `http_kernel` (no services resetter) |
| Method | Manual review of every file under `src/` (fragment handler decorator, services, Sentry reporter, DI extension, compiler pass, `Resources/config/services.yaml`, fallback template) + PHPStan FrankenPHP classic/worker rulesets |
| **Verdict** | ✅ **Compatible** with FrankenPHP worker when the kernel is **not** reset between requests (`FRANKENPHP_RESET_KERNEL` unset or `0`). Every shared service is `final` / `final readonly` or holds only injected collaborators; request data is read from `RequestStack` per call and never stored. |

## Table of contents

- [Execution model assumed](#execution-model-assumed)
- [Summary](#summary)
- [Services reviewed](#services-reviewed)
- [Findings](#findings)
  - [W-01 — The decorator inherits `FragmentHandler` state that it never uses (Info)](#w-01--the-decorator-inherits-fragmenthandler-state-that-it-never-uses-info)
  - [W-02 — Sentry tags and extras are set inside an isolated scope (Info)](#w-02--sentry-tags-and-extras-are-set-inside-an-isolated-scope-info)
- [Worker test (REQ-RUNTIME-002)](#worker-test-req-runtime-002)
- [Usage recommendations in worker mode](#usage-recommendations-in-worker-mode)
- [Re-audit triggers](#re-audit-triggers)

## Execution model assumed

FrankenPHP worker mode boots the Symfony kernel once per worker and serves many requests with the same container. Symfony Runtime default is **kernel reused** (`FRANKENPHP_RESET_KERNEL` unset/false). Setting `FRANKENPHP_RESET_KERNEL=1` clones the application after each request (escape hatch; lower throughput). This audit targets the **strict** default:

- **A — kernel not reset, `services_resetter` still runs:** services tagged `kernel.reset` (or implementing `ResetInterface`) are reset between requests.
- **B — kernel not reset, no service reset relied upon:** nothing in this bundle needs `kernel.reset`; shared services only hold injected deps / compiled config.

A bundle that is safe under **B** is safe under **A**, under `FRANKENPHP_RESET_KERNEL=1`, and under classic mode / PHP-FPM.

Precondition, precisely: with the stock Symfony kernel, `FRANKENPHP_RESET_KERNEL=false` only stops the kernel from being
cloned; `Kernel::handle()` still calls `services_resetter->reset()` before every request after the first, so that alone
is scenario A. Scenario B (no reset at all) happens when a host removes, replaces or bypasses `services_resetter` (or
drives `http_kernel` directly), in Messenger consumers started with `--no-reset`, and in long-running console commands.
This bundle keeps no state, so it is correct in both.

## Summary

| Area | Status | Notes |
|------|--------|-------|
| Mutable state in shared services | ✅ | All bundle services are `final` / `final readonly` or have no properties; the inherited `FragmentHandler::$renderers` of the decorator stays empty (W-01) |
| Static properties / `static` locals | ✅ | None |
| `ResetInterface` / `kernel.reset` coverage | ✅ N/A | Nothing to reset |
| Request / user / locale captured in services | ✅ | `FragmentFailureContextFactory` keeps only `RequestStack` and reads the current/parent request inside `fromException()` |
| Superglobals, `$_ENV`, `putenv`, `ini_set`, `setlocale`, timezone | ✅ | None used; config is compiled into container parameters |
| Doctrine / EntityManager | ✅ N/A | No persistence |
| Output, headers, `exit`, shutdown functions | ✅ | None; fallback HTML is returned as a string |
| Resources (files, sockets, cURL) held open | ✅ | None (`TwigPathsPass` only calls `is_dir()` at compile time) |
| Memory growth across requests | ✅ | No caches or accumulating arrays; failure contexts are created per call and not stored |
| Blocking I/O and timeouts | ✅ N/A | No I/O of its own; Sentry sending is done by the Sentry SDK transport |
| Third-party static state | ✅ | Sentry scope changes are isolated with `Hub::withScope()` (W-02) |
| PHPStan FrankenPHP rulesets | ✅ | `ruleset-classic.neon` + `ruleset-worker.neon` included in `phpstan.neon.dist` |

## Services reviewed

| Service | Shared | Mutable state | Scenario A | Scenario B |
|---------|--------|---------------|------------|------------|
| `Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler` (decorates `fragment.handler`) | yes | none of its own (`readonly` collaborators); inherited parent state unused (W-01) | ✅ | ✅ |
| `Nowo\FragmentKitBundle\Service\FragmentFailureContextFactory` | yes | none (`final readonly`, holds `RequestStack` only) | ✅ | ✅ |
| `Nowo\FragmentKitBundle\Service\FragmentFailureRenderer` | yes | none (`final readonly`, Twig env + template name) | ✅ | ✅ |
| `Nowo\FragmentKitBundle\Sentry\SentryFragmentFailureReporter` | yes | none (`final readonly`, nullable Hub + config array) | ✅ | ✅ |
| `Nowo\FragmentKitBundle\Null\NullFragmentFailureReporter` | yes | none (`final readonly`) | ✅ | ✅ |

`FragmentFailureContext` (`src/Model/FragmentFailureContext.php`) is a `final readonly` value object created per failure. It is excluded from the service resource loader (not a container service).

## Findings

### W-01 — The decorator inherits `FragmentHandler` state that it never uses (Info)

- **Where:** `src/HttpKernel/Fragment/ResilientFragmentHandler.php`. The class extends `Symfony\Component\HttpKernel\Fragment\FragmentHandler` and calls `parent::__construct($requestStack)`, which gives it a private `$renderers` array and a `RequestStack` reference. `addRenderer()` is overridden to delegate to `$inner`, and `render()` delegates to `$inner->render()`.
- **Worker impact:** the inherited `$renderers` array stays empty for the life of the worker, and the `RequestStack` reference is the shared service, not a captured request. Renderer registration and lazy loading remain the responsibility of the decorated Symfony handler, whose state is bounded by the number of renderer names, exactly as without this bundle.
- **Recommendation:** none. If the class is refactored, keep delegating instead of storing renderers or the current request in the decorator. The class cannot be `readonly` because it extends a non-readonly Symfony base class.

### W-02 — Sentry tags and extras are set inside an isolated scope (Info)

- **Where:** `src/Sentry/SentryFragmentFailureReporter.php` uses `$this->hub->withScope(...)`.
- **Worker impact:** the Sentry Hub is a long-lived shared object when the kernel is reused. `withScope()` pushes a temporary scope and pops it in a `finally` block, so the `fragment.*` tags, the parent URI and the route never stick to later events or later requests. This is the correct pattern.
- **Recommendation:** keep using `withScope()`; do not switch to `configureScope()`, which would make the tags persist on the shared Hub.

No other findings. The fallback path builds a new context and renders a template per failure; nothing survives the request.

## Worker test (REQ-RUNTIME-002)

`tests/Integration/HttpKernel/ResilientFragmentKernelTest.php::testManyRequestsWithAThrowingReporterOnOneKernelKeepMemoryFlat`
(runs in `make test` / CI):

- **Kernel:** one `FragmentTestKernel` booted once; every request goes through the `http_kernel` service and
  `terminate()`, never `Kernel::handle()`, so the services resetter does not run between requests (scenario B).
- **N:** 100 warm-up requests, then **1,000** measured requests, alternating two pages (`/tolerant` and `/alt`) whose
  fragments fail while the host reporter throws.
- **Assertions:** every page renders its own fallback (`|page_tolerant|` / `|page_alt|`), the last report of each
  request carries that request's URI (never a previous one), and memory grows by less than **512 KiB** over the
  1,000 measured requests.

The bundle has no `ResetInterface` service, no boundary listener and no Doctrine. A host that disables or bypasses the
services resetter must clear its own entity managers at every boundary (main request, Messenger message, console
command); otherwise the identity map serves stale rows across requests (REQ-RUNTIME-002 rule 7).

## Usage recommendations in worker mode

- No special configuration or reset hook is needed for `FRANKENPHP_RESET_KERNEL` unset/`0`.
- A custom fallback template (`nowo_fragment_kit.fallback.template`) receives the current exception, route and parent URI. It must only render them; Twig extensions or globals used by that template must not store them.
- A custom `FragmentFailureReporterInterface` implementation must stay stateless, or implement `ResetInterface`, and must not buffer contexts (they hold the exception and its trace). Prefer `Hub::withScope()` over `configureScope()` if integrating Sentry yourself.
- The demo in `demo/symfony8/` runs FrankenPHP in worker mode (`docker/frankenphp/Caddyfile` has a `worker` block) with kernel reuse (Runtime default).

## Re-audit triggers

Re-run this audit when a change adds: properties to `ResilientFragmentHandler`, the context factory, the renderer or the reporters; a failure counter, buffer or de-duplication cache; use of Sentry `configureScope()`; an event listener or subscriber; or any use of `$_SERVER` / `$_ENV` at runtime.
