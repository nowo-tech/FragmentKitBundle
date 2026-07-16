<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Sentry;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Nowo\FragmentKitBundle\Sentry\SentryFragmentFailureReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sentry\State\HubInterface;
use Sentry\State\Scope;

#[CoversClass(SentryFragmentFailureReporter::class)]
final class SentryFragmentFailureReporterTest extends TestCase
{
    public function testReportNoopsWhenHubIsNull(): void
    {
        $reporter = new SentryFragmentFailureReporter(null, ['enabled' => true, 'level' => 'warning']);
        $reporter->report(new FragmentFailureContext(new RuntimeException('x'), 500));

        $this->addToAssertionCount(1);
    }

    public function testReportNoopsWhenDisabled(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->never())->method('withScope');

        $reporter = new SentryFragmentFailureReporter($hub, ['enabled' => false, 'level' => 'warning']);
        $reporter->report(new FragmentFailureContext(new RuntimeException('x'), 403));
    }

    public function testReportCapturesExceptionWithTags(): void
    {
        $exception = new RuntimeException('Error when rendering');
        $context   = new FragmentFailureContext(
            exception: $exception,
            statusCode: 404,
            fragmentUri: '/missing',
            route: 'child',
            parentRoute: 'home',
            parentUri: '/',
            controller: 'App\\C::m',
        );

        $scope = $this->createMock(Scope::class);
        $scope->expects($this->once())->method('setLevel');
        $scope->expects($this->exactly(3))->method('setTag');
        $scope->expects($this->atLeastOnce())->method('setExtra');

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('withScope')
            ->willReturnCallback(static function (callable $callback) use ($scope): void {
                $callback($scope);
            });
        $hub->expects($this->once())->method('captureException')->with($exception);

        $reporter = new SentryFragmentFailureReporter($hub, ['enabled' => true, 'level' => 'error']);
        $reporter->report($context);
    }

    #[DataProvider('provideSeverityLevels')]
    public function testReportResolvesAllSeverityLevels(string $level): void
    {
        $scope = $this->createMock(Scope::class);
        $scope->expects($this->once())->method('setLevel');

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('withScope')
            ->willReturnCallback(static function (callable $callback) use ($scope): void {
                $callback($scope);
            });
        $hub->expects($this->once())->method('captureException');

        $reporter = new SentryFragmentFailureReporter($hub, ['enabled' => true, 'level' => $level]);
        $reporter->report(new FragmentFailureContext(new RuntimeException('x'), 500));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideSeverityLevels(): iterable
    {
        yield 'debug' => ['debug'];
        yield 'info' => ['info'];
        yield 'warning' => ['warning'];
        yield 'fatal' => ['fatal'];
        yield 'unknown-defaults-to-warning' => ['notice'];
    }
}
