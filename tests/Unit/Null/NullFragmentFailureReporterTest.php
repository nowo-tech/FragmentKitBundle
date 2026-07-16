<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\Null;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;
use Nowo\FragmentKitBundle\Null\NullFragmentFailureReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(NullFragmentFailureReporter::class)]
final class NullFragmentFailureReporterTest extends TestCase
{
    public function testReportIsNoOp(): void
    {
        $reporter = new NullFragmentFailureReporter();
        $reporter->report(new FragmentFailureContext(new RuntimeException('x'), 500));

        $this->addToAssertionCount(1);
    }
}
