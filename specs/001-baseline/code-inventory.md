# Code inventory — FragmentKitBundle baseline

**Spec**: [`spec.md`](spec.md)  
**Last audited**: 2026-07-16  
**Production units**: 14

## Coverage summary

| Category | Files | Mapped FR-* |
|----------|------:|-------------|
| Bundle / DI | 4 | FR-FK-001, FR-FK-007, FR-FK-008 |
| HTTP kernel | 1 | FR-FK-002, FR-FK-003 |
| Model | 1 | FR-FK-004 |
| Services | 2 | FR-FK-004, FR-FK-005 |
| Reporting | 3 | FR-FK-006 |
| Resources (config + view) | 2 | FR-FK-005, FR-FK-007 |
| Flex recipe (shipped) | 1 | FR-FK-007 |
| **Total** | **13** | — |

## Bundle / DI

| Path | Requirement IDs |
|------|-----------------|
| `src/NowoFragmentKitBundle.php` | FR-FK-001, FR-FK-007, FR-FK-008 |
| `src/DependencyInjection/Configuration.php` | FR-FK-001 |
| `src/DependencyInjection/FragmentKitExtension.php` | FR-FK-001, FR-FK-006, FR-FK-007 |
| `src/DependencyInjection/Compiler/TwigPathsPass.php` | FR-FK-008 |

## HTTP kernel

| Path | Requirement IDs |
|------|-----------------|
| `src/HttpKernel/Fragment/ResilientFragmentHandler.php` | FR-FK-002, FR-FK-003 |

## Model

| Path | Requirement IDs |
|------|-----------------|
| `src/Model/FragmentFailureContext.php` | FR-FK-004 |

## Services

| Path | Requirement IDs |
|------|-----------------|
| `src/Service/FragmentFailureContextFactory.php` | FR-FK-004 |
| `src/Service/FragmentFailureRenderer.php` | FR-FK-005 |

## Reporting

| Path | Requirement IDs |
|------|-----------------|
| `src/Contract/FragmentFailureReporterInterface.php` | FR-FK-006 |
| `src/Null/NullFragmentFailureReporter.php` | FR-FK-006 |
| `src/Sentry/SentryFragmentFailureReporter.php` | FR-FK-006 |

## Resources

| Path | Requirement IDs |
|------|-----------------|
| `src/Resources/config/services.yaml` | FR-FK-002, FR-FK-007 |
| `src/Resources/views/fragment_failure.html.twig` | FR-FK-005 |

## Flex recipe

| Path | Requirement IDs |
|------|-----------------|
| `.symfony/recipe/nowo-tech/fragment-kit-bundle/1.0.0/config/packages/nowo_fragment_kit.yaml` | FR-FK-007 |

## Tests (not counted as production)

| Path | Role |
|------|------|
| `tests/Unit/**` | Unit coverage for FR-FK-* |
| `tests/Integration/**` | Extension / DI compile |
| `demo/symfony8/` | Integrator demo (excluded from package archive) |
