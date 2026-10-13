<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;

/**
 * Collects reported failures so kernel tests can assert on them.
 */
final class RecordingFragmentFailureReporter implements FragmentFailureReporterInterface
{
    /** @var list<FragmentFailureContext> */
    public array $reports = [];

    public function report(FragmentFailureContext $context): void
    {
        $this->reports[] = $context;
    }
}
