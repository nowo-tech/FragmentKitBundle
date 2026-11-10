<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use RuntimeException;

/**
 * Collects reported failures so kernel tests can assert on them.
 */
final class RecordingFragmentFailureReporter implements FragmentFailureReporterInterface
{
    /** @var list<FragmentFailureContext> */
    public array $reports = [];

    /** When true, report() records then throws, like a broken host adapter. */
    public bool $throw = false;

    public function report(FragmentFailureContext $context): void
    {
        $this->reports[] = $context;

        if ($this->throw) {
            throw new RuntimeException('Host reporter is down.');
        }
    }
}
