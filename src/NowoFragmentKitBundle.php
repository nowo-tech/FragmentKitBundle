<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle;

use Nowo\FragmentKitBundle\DependencyInjection\FragmentKitExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Resilient Symfony fragment rendering for Twig sub-requests ({ignore_errors: true}).
 */
class NowoFragmentKitBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new FragmentKitExtension();
        }

        /** @var ExtensionInterface $extension */
        $extension = $this->extension;

        return $extension;
    }
}
