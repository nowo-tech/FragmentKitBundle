<?php

declare(strict_types=1);

namespace Nowo\FragmentKitBundle\Tests\Integration\HttpKernel\Fixtures;

use Nowo\FragmentKitBundle\Contract\FragmentFailureReporterInterface;
use Nowo\FragmentKitBundle\NowoFragmentKitBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal kernel rendering real Twig fragments through the decorated fragment.handler.
 */
final class FragmentTestKernel extends Kernel
{
    use MicroKernelTrait;

    public const PAGES = ['tolerant', 'alt', 'strict', 'default'];

    /**
     * @param bool $hostAlias When true the recording reporter is wired the way a host does it, with a plain
     *                        `services` alias (no compiler pass), to prove the host's alias wins over the bundle's
     */
    public function __construct(string $environment, bool $debug, private readonly bool $hostAlias = false)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new NowoFragmentKitBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/nowo-fragment-kit-tests/' . $this->environment . ($this->hostAlias ? '_alias' : '') . '/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/nowo-fragment-kit-tests/' . $this->environment . '/log';
    }

    protected function build(ContainerBuilder $container): void
    {
        if ($this->hostAlias) {
            return;
        }

        // Runs after the bundle extension aliased the reporter: swap in the recording one.
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $container->setAlias(FragmentFailureReporterInterface::class, RecordingFragmentFailureReporter::class);
            }
        }, PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret'                => 'test',
            'test'                  => true,
            'http_method_override'  => false,
            'handle_all_throwables' => true,
            'php_errors'            => ['log' => true],
            'router'                => ['utf8' => true],
        ]);
        $container->extension('twig', [
            'default_path' => __DIR__ . '/templates',
        ]);
        $container->extension('nowo_fragment_kit', [
            'fallback' => ['template' => 'fallback.html.twig'],
            'sentry'   => ['enabled' => false],
        ]);

        $services = $container->services();
        $services->set(FragmentTestController::class)->autowire()->public()->tag('controller.service_arguments');
        $services->set(RecordingFragmentFailureReporter::class)->public();
        $services->set('logger', NullLogger::class);

        if ($this->hostAlias) {
            // What a host writes in config/services.yaml: `Nowo\…\FragmentFailureReporterInterface: '@App\…\MyReporter'`.
            $services->alias(FragmentFailureReporterInterface::class, RecordingFragmentFailureReporter::class);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        foreach (self::PAGES as $page) {
            $routes->add('page_' . $page, '/' . $page)
                ->controller([FragmentTestController::class, 'page'])
                ->defaults(['template' => $page . '.html.twig']);
        }
    }
}
