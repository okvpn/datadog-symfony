<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Okvpn\Bundle\DatadogBundle\OkvpnDatadogBundle;
use Symfony\Component\HttpKernel\KernelInterface;

class ResponseTimeListener
{
    public function __construct(private DogStatsInterface $dogStats, private ?KernelInterface $kernel = null)
    {}

    public function onKernelTerminate(): void
    {
        if (null !== $this->kernel) {
            if ($this->kernel->getStartTime() > 0) {
                $responseTime = round(microtime(true) - $this->kernel->getStartTime(), 4);
            } else {
                /** @var OkvpnDatadogBundle $datadogBundle */
                $datadogBundle = $this->kernel->getBundle('OkvpnDatadogBundle');
                $responseTime = round(microtime(true) - $datadogBundle->getStartTime(), 4);
            }

            $this->dogStats->timing('http_request', $responseTime);
        }
    }
}
