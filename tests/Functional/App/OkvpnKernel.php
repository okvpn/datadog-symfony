<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

class OkvpnKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
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
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', true);
            $container->setParameter('container.dumper.inline_class_loader', true);
            $container->addObjectResource($this);

            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => 'kernel:loadRoutes',
                    'type' => 'service',
                ],
            ]);
        });



        $loader->load(__DIR__.'/config.yml');
    }

    /**
     * @param LoaderInterface $loader
     * @return RouteCollection
     */
    public function loadRoutes(LoaderInterface $loader)
    {
        $routes = new RouteCollectionBuilder($loader);

        $routes->add('/', "app.controller.base_controller:index");
        $routes->add('/exception', "app.controller.base_controller:exception");

        return $routes->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return __DIR__ . '/var';
    }
}
