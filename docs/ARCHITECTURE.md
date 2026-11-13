# Architecture

## Table of contents

- [Overview](#overview)
- [Public surface](#public-surface)
- [Extension points (SPI)](#extension-points-spi)
  - [Failure reporter](#failure-reporter)
  - [Fallback template](#fallback-template)
- [Standard Symfony and DDD hosts](#standard-symfony-and-ddd-hosts)
  - [Standard Symfony host](#standard-symfony-host)
  - [DDD / hexagonal host](#ddd--hexagonal-host)
- [REQ-ARCH-001 summary](#req-arch-001-summary)

## Overview

FragmentKit is an infrastructure bundle with no entities, no Doctrine dependency and no events. It decorates Symfony's
`fragment.handler` with `ResilientFragmentHandler`. When a fragment rendered with `ignore_errors: true` fails, the
handler:

1. builds a `FragmentFailureContext` (`Service\FragmentFailureContextFactory`, from the exception and the request stack);
2. hands it to the **failure reporter** port (`Contract\FragmentFailureReporterInterface`);
3. renders the **fallback template** (`Service\FragmentFailureRenderer`) and lets the parent page continue.

The handler, the context factory and the renderer are internal (`final`) services. Hosts extend the bundle through the
reporter port and the fallback template only.

## Public surface

| Element | Kind | Framework types |
|---------|------|-----------------|
| `Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface` | Port (SPI) the host implements | None |
| `Nowo\FragmentKitBundle\Model\FragmentFailureContext` | Immutable VO passed to the port | None (`Throwable`, scalars) |
| `nowo_fragment_kit.*` config tree | Configuration (rule 8) | — |
| `@NowoFragmentKitBundle/fragment_failure.html.twig` / `fallback.template` | Presentation extension point | — |

`FragmentFailureContext` fields:

| Field | Type | Meaning |
|-------|------|---------|
| `exception` | `Throwable` | Original failure (holds the trace: never buffer it across requests) |
| `statusCode` | `int` | HTTP status of the failed fragment (500 when unknown) |
| `fragmentUri` | `?string` | URI passed to `render()`; unsigned `/_fragment` URI for `controller(...)` |
| `route` | `?string` | Sub-request route, when still known |
| `parentRoute` | `?string` | Route of the parent page |
| `parentUri` | `?string` | URI of the parent page |
| `controller` | `?string` | Controller of a `controller(...)` reference |

The port and the VO carry no Symfony, Doctrine or HTTP types (`Request`, `Response`, …), so a host adapter can live in a
framework-free layer.

## Extension points (SPI)

### Failure reporter

```php
namespace Nowo\FragmentKitBundle\Contract;

interface FragmentFailureReporterInterface
{
    public function report(FragmentFailureContext $context): void;
}
```

Contract:

- Called once per suppressed fragment failure, synchronously, before the fallback is rendered.
- Must not throw. If it does, the handler logs `Fragment failure reporter {reporter} threw {reporter_error}` (when a
  logger is available) and still renders the fallback, so a broken adapter never breaks the parent page.
- Must be worker safe (REQ-RUNTIME-002, FrankenPHP worker with `FRANKENPHP_RESET_KERNEL` unset or false): stay
  stateless or implement `ResetInterface`, and never keep `FragmentFailureContext` instances between requests.

Shipped implementations:

| Service | Selected when |
|---------|---------------|
| `Nowo\FragmentKitBundle\Sentry\SentryFragmentFailureReporter` | `sentry.enabled: true` and `sentry/sentry-symfony` is installed |
| `Nowo\FragmentKitBundle\Null\NullFragmentFailureReporter` | Otherwise (no-op) |

The bundle extension sets the alias `FragmentFailureReporterInterface` to one of them. A host replaces it with its own
alias in `config/services.yaml`. The host's alias wins over the bundle's (covered by
`ResilientFragmentKernelTest::testHostServicesAliasReplacesTheReporter`):

```yaml
# config/services.yaml
services:
    Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface: '@App\Infrastructure\Fragment\LoggingFragmentFailureReporter'
```

```php
namespace App\Infrastructure\Fragment;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Psr\Log\LoggerInterface;

final readonly class LoggingFragmentFailureReporter implements FragmentFailureReporterInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function report(FragmentFailureContext $context): void
    {
        $this->logger->warning('Fragment {uri} failed with {status}.', [
            'uri'    => $context->fragmentUri,
            'status' => $context->statusCode,
            'parent' => $context->parentRoute,
        ]);
    }
}
```

To report to several targets, write one adapter that fans out to the others; the port has a single binding.

### Fallback template

The presentation extension point (REQ-TWIG-001). Set `nowo_fragment_kit.fallback.template` to an application template,
or override `templates/bundles/NowoFragmentKitBundle/fragment_failure.html.twig`. See
[Usage](USAGE.md#fallback-template-variables) for the variables.

## Standard Symfony and DDD hosts

### Standard Symfony host

Zero config: install the bundle, optionally enable Sentry (`sentry.enabled: true` with `sentry/sentry-symfony`) and point
`fallback.template` at a template. Nothing else is needed.

### DDD / hexagonal host

FragmentKit owns no entities and dispatches no events, so there is no mapping, no ID reference and no EntityManager to
wire. A DDD host:

1. implements `FragmentFailureReporterInterface` in its infrastructure layer (the port and `FragmentFailureContext` use
   only plain PHP types), translating the context into its own logging / error-tracking port if it has one;
2. aliases the port to that adapter in `config/services.yaml` (example above);
3. keeps configuration in the `nowo_fragment_kit` tree (no InstanceSettings or ORM dependency, rule 8).

## REQ-ARCH-001 summary

| Rule | Status |
|------|--------|
| 1 Interfaces + VOs | Reporter port + `FragmentFailureContext` VO; internal services are not host contracts |
| 2 No forced base class / trait | No entities, base classes or traits |
| 3 No framework types in ports / VOs | None in the port or the VO |
| 4 Events carry IDs | No events |
| 5 SPI | Reporter port (this document) + Twig fallback template |
| 6 No host EM flush | No Doctrine usage |
| 7 Events after commit | No events |
| 8 Config through provider ports | `nowo_fragment_kit` config tree only |
