<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PushDatadogHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ('all' !== $container->getParameter('okvpn_datadog.logging') || false === $container->getParameter('okvpn_datadog.profiling')) {
            return;
        }

        if ($loggerByChanel = $container->getParameter('okvpn_datadog.monolog_channels')) {
            $loggerByChanel = [];
        }

        foreach ($container->findTaggedServiceIds('monolog.logger') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['channel'])) {
                    continue;
                }

                $resolvedChannel = $container->getParameterBag()->resolveValue($tag['channel']);
                $loggerByChanel[] = sprintf('monolog.logger.%s', $resolvedChannel);
            }
        }

        $loggerByChanel = array_unique($loggerByChanel);
        foreach ($loggerByChanel as $loggerId) {
            if ($container->hasDefinition($loggerId)) {
                $definition = $container->getDefinition($loggerId);
                $definition->addMethodCall('pushHandler', [new Reference('okvpn_datadog.monolog.log_handler')]);
            }
        }
    }
}
