<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SqlLoggerPass implements CompilerPassInterface
{
    public function __construct(private array $connections)
    {}

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('okvpn_datadog.logger.sql')) {
            return;
        }

        foreach ($this->connections as $name) {
            $configuration = sprintf('doctrine.dbal.%s_connection.configuration', $name);
            if (false === $container->hasDefinition($configuration)) {
                continue;
            }
            $loggerId = 'okvpn_datadog.sql_logger.' . $name;
            $container->setDefinition($loggerId, new ChildDefinition('okvpn_datadog.logger.sql'));
            $configuration = $container->getDefinition($configuration);
            if ($configuration->hasMethodCall('setSQLLogger')) {
                $chainLoggerId = 'doctrine.dbal.logger.chain.' . $name;
                if ($container->hasDefinition($chainLoggerId)) {
                    $chainLogger = $container->getDefinition($chainLoggerId);
                    $chainLogger->addMethodCall('addLogger', [new Reference($loggerId)]);
                } else {
                    $configuration->addMethodCall('setSQLLogger', [new Reference($loggerId)]);
                }
            } else  {
                $configuration->addMethodCall('setSQLLogger', [new Reference($loggerId)]);
            }
        }
    }
}
