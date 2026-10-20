<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\HttpKernel;

use LogicException;
use Nowo\FragmentKitBundle\HttpKernel\Fragment\ResilientFragmentHandler;
use Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures\FragmentTestController;
use Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures\FragmentTestKernel;
use Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures\RecordingFragmentFailureReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

/**
 * Renders real Twig fragments whose controllers throw or return HTTP errors.
 */
#[CoversClass(ResilientFragmentHandler::class)]
final class ResilientFragmentKernelTest extends TestCase
{
    private const BOOM = FragmentTestController::class . '::boom';

    private ?FragmentTestKernel $kernel = null;

    protected function tearDown(): void
    {
        $this->kernel?->shutdown();
        $this->kernel = null;
        (new Filesystem())->remove(sys_get_temp_dir() . '/nowo-fragment-kit-tests');
        parent::tearDown();
    }

    public function testThrowingFragmentControllerGetsFallbackAndIsReported(): void
    {
        $content = $this->request('/tolerant');

        self::assertStringContainsString('[FALLBACK(500|' . self::BOOM . '|page_tolerant|no-route)', $content);
        self::assertStringContainsString('[FALLBACK(403|' . FragmentTestController::class . '::forbidden|page_tolerant|no-route)', $content);
        self::assertStringContainsString('[ALT-OK]', $content);

        $reports = $this->reporter()->reports;
        self::assertCount(2, $reports);
        self::assertInstanceOf(LogicException::class, $reports[0]->exception);
        self::assertSame('/tolerant', $reports[0]->parentUri);
    }

    public function testAltIsTriedBeforeFallback(): void
    {
        $content = $this->request('/alt');

        self::assertStringContainsString('[ALT-OK]', $content);
        self::assertStringContainsString('[FALLBACK(403|' . self::BOOM . '|page_alt|no-route)', $content);
        self::assertCount(1, $this->reporter()->reports);
    }

    public function testIgnoreErrorsFalseStillFailsTheParentPage(): void
    {
        try {
            $this->request('/strict');
            self::fail('Expected the fragment exception to propagate.');
        } catch (Throwable $exception) {
            self::assertTrue($this->chainContains($exception, LogicException::class));
        }

        self::assertSame([], $this->reporter()->reports);
    }

    public function testIgnoreErrorsDefaultsToTrueWhenKernelIsNotInDebug(): void
    {
        self::assertStringContainsString('[FALLBACK(500|' . self::BOOM, $this->request('/default'));
    }

    public function testIgnoreErrorsDefaultsToFalseWhenKernelIsInDebug(): void
    {
        try {
            $this->request('/default', debug: true);
            self::fail('Expected the fragment exception to propagate in debug.');
        } catch (Throwable $exception) {
            self::assertTrue($this->chainContains($exception, LogicException::class));
        }
    }

    private function request(string $path, bool $debug = false): string
    {
        $this->kernel = new FragmentTestKernel($debug ? 'test_debug' : 'test', $debug);
        $this->kernel->boot();

        $response = $this->kernel->handle(Request::create($path), HttpKernelInterface::MAIN_REQUEST, false);
        self::assertSame(200, $response->getStatusCode());

        return (string) $response->getContent();
    }

    private function reporter(): RecordingFragmentFailureReporter
    {
        self::assertNotNull($this->kernel);
        $reporter = $this->kernel->getContainer()->get(RecordingFragmentFailureReporter::class);
        self::assertInstanceOf(RecordingFragmentFailureReporter::class, $reporter);

        return $reporter;
    }

    /**
     * @param class-string<Throwable> $class
     */
    private function chainContains(Throwable $exception, string $class): bool
    {
        for ($current = $exception; $current instanceof Throwable; $current = $current->getPrevious()) {
            if ($current instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
