<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Unit\DependencyInjection;

use Nowo\FragmentKitBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[]]);

        $this->assertTrue($config['enabled']);
        $this->assertSame('@NowoFragmentKitBundle/fragment_failure.html.twig', $config['fallback']['template']);
        $this->assertTrue($config['sentry']['enabled']);
        $this->assertSame('warning', $config['sentry']['level']);
    }
}
