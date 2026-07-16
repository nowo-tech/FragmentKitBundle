<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Contract;

use Nowo\FragmentKitBundle\Model\FragmentFailureContext;

interface FragmentFailureReporterInterface
{
    public function report(FragmentFailureContext $context): void;
}
