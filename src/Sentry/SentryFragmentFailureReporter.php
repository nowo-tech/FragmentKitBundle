<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Sentry;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Sentry\Severity;
use Sentry\State\HubInterface;

/**
 * Reports suppressed fragment failures to Sentry when a Hub is available.
 */
final readonly class SentryFragmentFailureReporter implements FragmentFailureReporterInterface
{
    /**
     * @param array{enabled: bool, level: string} $config
     */
    public function __construct(
        private ?HubInterface $hub,
        private array $config,
    ) {
    }

    public function report(FragmentFailureContext $context): void
    {
        if (!$this->hub instanceof HubInterface || !$this->config['enabled']) {
            return;
        }

        $severity = $this->resolveSeverity($this->config['level']);

        $this->hub->withScope(function ($scope) use ($context, $severity): void {
            $scope->setLevel($severity);
            $scope->setTag('fragment.failure', 'true');
            $scope->setTag('fragment.status_code', (string) $context->statusCode);
            $scope->setTag('fragment.suppressed', 'true');

            if ($context->route !== null) {
                $scope->setExtra('fragment.route', $context->route);
            }

            if ($context->parentRoute !== null) {
                $scope->setExtra('fragment.parent_route', $context->parentRoute);
            }

            if ($context->parentUri !== null) {
                $scope->setExtra('fragment.parent_uri', $context->parentUri);
            }

            if ($context->fragmentUri !== null) {
                $scope->setExtra('fragment.uri', $context->fragmentUri);
            }

            if ($context->controller !== null) {
                $scope->setExtra('fragment.controller', $context->controller);
            }

            $this->hub->captureException($context->exception);
        });
    }

    private function resolveSeverity(string $level): Severity
    {
        return match (strtolower($level)) {
            'debug' => Severity::debug(),
            'info'  => Severity::info(),
            'error' => Severity::error(),
            'fatal' => Severity::fatal(),
            default => Severity::warning(),
        };
    }
}
