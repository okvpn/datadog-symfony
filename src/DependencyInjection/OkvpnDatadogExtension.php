<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection;

use Okvpn\Bundle\DatadogBundle\Client\DatadogFactoryInterface;
use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OkvpnDatadogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->getDefinition('okvpn_datadog.services.skip_capture')
            ->replaceArgument(1, $this->defaultHandlerExceptions($config));
        $container->getDefinition('okvpn_datadog.logger')
            ->replaceArgument(5, $config['dedup_path'])
            ->replaceArgument(6, $config['dedup_keep_time']);
        $container->getDefinition('okvpn_datadog.logger.artifact_storage')
            ->replaceArgument(0, $config['artifacts_path'] ?: $container->getParameter('kernel.logs_dir'));

        $container->getDefinition('okvpn_datadog.client')
            ->replaceArgument(0, $config);

        if (isset($config['clients'])) {
            foreach ($config['clients'] as $client) {
                $this->loadClient($container, $client);
            }

            if (isset($config['clients']['default'])) {
                $container->removeDefinition('okvpn_datadog.client');
                $container->setAlias('okvpn_datadog.client', 'okvpn_datadog.client.default')->setPublic(true);
                $container->setAlias(DogStatsInterface::class, 'okvpn_datadog.client.default')->setPublic(true);
            }
        }

        if (true === $config['profiling']) {
            if (true === $config['doctrine'] && class_exists('Doctrine\DBAL\Logging\SQLLogger')) {
                $loader->load('sqllogger.yml');
            }
            $loader->load('listener.yml');
        }

        $container->setParameter('okvpn_datadog.logging', $config['exception']);
        $container->setParameter('okvpn_datadog.profiling', $config['profiling']);
    }

    protected function loadClient(ContainerBuilder $container, array $client)
    {
        $ddDef = new Definition(DogStatsInterface::class, [$client]);
        $ddDef->setFactory([new Reference(DatadogFactoryInterface::class), 'createClient']);

        $container->setDefinition($id = sprintf('okvpn_datadog.client.%s', $client['alias']), $ddDef);
        $container->registerAliasForArgument($id, DogStatsInterface::class, $client['alias']);
    }

    protected function defaultHandlerExceptions(array $config): array
    {
        $config = $config['handle_exceptions'] ?? [];
        $config['skip_instanceof'] = array_merge(
            $config['skip_instanceof'] ?? [],
            [
                'Symfony\Component\Console\Exception\ExceptionInterface', //command argument invalid
                'Symfony\Component\HttpKernel\Exception\HttpExceptionInterface', //Http exceptions
            ]
        );

        return $config;
    }
}
