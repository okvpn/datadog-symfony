<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle;

use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\PushDatadogHandlerPass;
use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\SqlLoggerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkvpnDatadogBundle extends Bundle
{
    private ?float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (null === $this->startTime) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        $this->startTime = null;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SqlLoggerPass(['default']));
        $container->addCompilerPass(new PushDatadogHandlerPass());
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }
}
