<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Null;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\Model\FragmentFailureContext;

class NullFragmentFailureReporter implements FragmentFailureReporterInterface
{
    public function report(FragmentFailureContext $context): void
    {
    }
}
