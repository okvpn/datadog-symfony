<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle;

use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\PushDatadogHandlerPass;
use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\SqlLoggerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkvpnDatadogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SqlLoggerPass(['default']));
        $container->addCompilerPass(new PushDatadogHandlerPass());
    }
}
