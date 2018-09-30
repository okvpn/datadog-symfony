<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection;

use Doctrine\DBAL\Logging\SQLLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OkvpnDatadogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->getDefinition('okvpn_datadog.services.skip_capture')
            ->replaceArgument(1, $this->defaultHandlerExceptions($config));

        if ($config['doctrine'] && class_exists(SQLLogger::class)) {
            $loader->load('sqllogger.yml');
        }
        if ('none' !== $config['exception'] && true === $config['enable']) {
            $loader->load('listener.yml');
        }

        $container->setParameter('okvpn_datadog.logging', $config['exception']);
        $container->setParameter('okvpn_datadog.enable', $config['enable']);
    }

    protected function defaultHandlerExceptions(array $config): array
    {
        $config = $config['handle_exceptions'];
        $config['skip_instanceof'] = array_merge(
            $config['skip_instanceof'],
            [
                'Symfony\Component\Console\Exception\ExceptionInterface', //command argument invalid
                'Symfony\Component\HttpKernel\Exception\HttpExceptionInterface', //Http exceptions
            ]
        );

        return $config;
    }
}
