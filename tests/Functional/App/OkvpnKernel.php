<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

if (Kernel::VERSION_ID >= 50000) {
    trait OkvpnKernelTrait
    {
        public function loadRoutes()
        {
            $routes = new RouteCollection();

            $routes->add('index', $this->createRoute("/", "app.controller.base_controller::index"));
            $routes->add("exception", $this->createRoute('/exception', "app.controller.base_controller::exception"));
            $routes->add("entity", $this->createRoute('/entity', "app.controller.base_controller::entity"));
            return $routes;
        }

        private function createRoute(string $path, string $controller): Route
        {
            return new Route($path, ['_controller' => $controller]);
        }
    }
} else {
    trait OkvpnKernelTrait
    {
        public function loadRoutes(LoaderInterface $loader)
        {
            $routes = new RouteCollectionBuilder($loader);

            $routes->add('/', "app.controller.base_controller:index");
            $routes->add('/exception', "app.controller.base_controller:exception");
            $routes->add('/entity', "app.controller.base_controller:entity");

            return $routes->build();
        }
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
                    'type' => 'service',
                ],
            ]);
        });

        if (Kernel::VERSION_ID >= 60000) {
            $loader->load(__DIR__.'/config6.yml');
        } else {
            $loader->load(__DIR__.'/config.yml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
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
