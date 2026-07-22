<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle;

use Nowo\FragmentKitBundle\DependencyInjection\Compiler\TwigPathsPass;
use Nowo\FragmentKitBundle\DependencyInjection\FragmentKitExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Resilient Symfony fragment rendering for Twig sub-requests ({ignore_errors: true}).
 */
class NowoFragmentKitBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new TwigPathsPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new FragmentKitExtension();
        }

        /** @var ExtensionInterface $extension */
        $extension = $this->extension;

        return $extension;
    }
}
