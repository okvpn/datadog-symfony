<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

trait OkvpnKernelTrait
{
    protected function loadRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('/', "app.controller.base_controller:index");
        $routes->add('/exception', "app.controller.base_controller:exception");
        $routes->add('/entity', "app.controller.base_controller:entity");
    }
}

class OkvpnKernel extends Kernel
{
    use OkvpnKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle,
            new \Okvpn\Bundle\DatadogBundle\OkvpnDatadogBundle(),
        ];
    }





    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', true);
            $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => AppKernelRouting::class . '::loadRoutes',
                    #'resource' => AppKernelRouting::class . '::configureRoutes',
                    'type' => 'service',
                ],
            ]);
        });

        $loader->load(__DIR__.'/config.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getRootDir(): string
    {
        return __DIR__ . '/var';
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/var/cache/'.$this->getEnvironment();
    }
}
