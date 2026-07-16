# Code inventory — FragmentKitBundle baseline

**Spec**: [`spec.md`](spec.md)  
**Updated**: 2026-07-16

## Package layout

| Path | Role |
|------|------|
| `src/NowoFragmentKitBundle.php` | Bundle entry; exposes `FragmentKitExtension` |
| `src/DependencyInjection/Configuration.php` | Config tree |
| `src/DependencyInjection/FragmentKitExtension.php` | Loads services; toggles decorator / reporter |
| `src/HttpKernel/Fragment/ResilientFragmentHandler.php` | Decorator for `fragment.handler` |
| `src/Model/FragmentFailureContext.php` | Failure DTO |
| `src/Service/FragmentFailureContextFactory.php` | Builds context from exception + request stack |
| `src/Service/FragmentFailureRenderer.php` | Twig fallback rendering |
| `src/Contract/FragmentFailureReporterInterface.php` | Reporter contract |
| `src/Null/NullFragmentFailureReporter.php` | No-op reporter |
| `src/Sentry/SentryFragmentFailureReporter.php` | Sentry reporter |
| `src/Resources/config/services.yaml` | DI wiring |
| `src/Resources/views/fragment_failure.html.twig` | Default empty fallback |
| `.symfony/recipe/.../nowo_fragment_kit.yaml` | Flex recipe config |

## Tests

| Path | Coverage focus |
|------|----------------|
| `tests/Unit/DependencyInjection/ConfigurationTest.php` | Defaults |
| `tests/Unit/HttpKernel/Fragment/ResilientFragmentHandlerTest.php` | Catch vs rethrow |
| `tests/Unit/Service/FragmentFailureContextFactoryTest.php` | Context extraction |
| `tests/Integration/DependencyInjection/FragmentKitExtensionTest.php` | Container compile |

## Demo

| Path | Role |
|------|------|
| `demo/symfony8/` | FrankenPHP Symfony 8 app |
| `demo/symfony8/src/Controller/DemoController.php` | Home + OK/403/404 fragments |
| `demo/symfony8/templates/demo/home.html.twig` | Parent page with `render(..., {ignore_errors: true})` |
| `demo/symfony8/templates/fragment/unavailable.html.twig` | Visible fallback |
