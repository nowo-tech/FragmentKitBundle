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
final class NowoFragmentKitBundle extends Bundle
{
    /**
     * Registers the bundle compiler passes.
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new TwigPathsPass());
    }

    /**
     * Returns the bundle extension (alias nowo_fragment_kit).
     */
    public function getContainerExtension(): ExtensionInterface
    {
        // @igor-ignore - Symfony bundle extension lazy-init at boot; not request state
        $this->extension ??= new FragmentKitExtension();

        /** @var ExtensionInterface $extension */
        $extension = $this->extension;

        return $extension;
    }
}
