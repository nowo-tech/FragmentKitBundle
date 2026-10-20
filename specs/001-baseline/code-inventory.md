# Code inventory — FragmentKitBundle baseline

**Spec**: [`spec.md`](spec.md)  
**Last audited**: 2026-10-20  
**Production units**: 13 (every file tracked under `src/`; audit: `git ls-files src | wc -l`)

The Flex recipe under `.symfony/recipe/` ships outside `src/`; it is documented in the [Flex recipe](#flex-recipe-outside-src-not-counted) section and not counted. Tests and demos are out of scope.

## Bundle / DI

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/NowoFragmentKitBundle.php` | Bundle, configuration and Twig namespace | FR-FK-001, FR-FK-007, FR-FK-008 |
| `src/DependencyInjection/Configuration.php` | Bundle, configuration and Twig namespace | FR-FK-001 |
| `src/DependencyInjection/FragmentKitExtension.php` | Bundle, configuration and Twig namespace | FR-FK-001, FR-FK-006, FR-FK-007 |
| `src/DependencyInjection/Compiler/TwigPathsPass.php` | Bundle, configuration and Twig namespace | FR-FK-008 |

## HTTP kernel

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/HttpKernel/Fragment/ResilientFragmentHandler.php` | HTTP kernel — fragment handling | FR-FK-002, FR-FK-003, FR-FK-009 |

## Model

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/Model/FragmentFailureContext.php` | Failure context and fallback rendering | FR-FK-004 |

## Services

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/Service/FragmentFailureContextFactory.php` | Failure context and fallback rendering | FR-FK-004 |
| `src/Service/FragmentFailureRenderer.php` | Failure context and fallback rendering | FR-FK-005 |

## Reporting

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/Contract/FragmentFailureReporterInterface.php` | Reporting | FR-FK-006 |
| `src/Null/NullFragmentFailureReporter.php` | Reporting | FR-FK-006 |
| `src/Sentry/SentryFragmentFailureReporter.php` | Reporting | FR-FK-006, FR-FK-009 |

## Resources

| Source path | Spec section | Requirement IDs |
| --- | --- | --- |
| `src/Resources/config/services.yaml` | HTTP kernel — fragment handling | FR-FK-002, FR-FK-007 |
| `src/Resources/views/fragment_failure.html.twig` | Failure context and fallback rendering | FR-FK-005 |

## Coverage summary

| Category | Files | Mapped |
| --- | ---: | ---: |
| Bundle / DI | 4 | 4 |
| HTTP kernel | 1 | 1 |
| Model | 1 | 1 |
| Services | 2 | 2 |
| Reporting | 3 | 3 |
| Resources (config + view) | 2 | 2 |
| **Total production files (`git ls-files src`)** | **13** | **13** |

## Flex recipe (outside `src/`, not counted)

| Path | Spec section | Requirement IDs |
| --- | --- | --- |
| `.symfony/recipe/nowo-tech/fragment-kit-bundle/1.0.0/config/packages/nowo_fragment_kit.yaml` | Bundle, configuration and Twig namespace | FR-FK-007 |

## Tests and demo (not counted as production)

| Path | Role |
| --- | --- |
| `tests/Unit/**` | Unit coverage for FR-FK-* |
| `tests/Integration/**` | Extension / DI compile |
| `demo/symfony8/` | Integrator demo (excluded from package archive) |
