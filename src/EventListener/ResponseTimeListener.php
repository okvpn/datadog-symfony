<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ResponseTimeListener
{
    private $kernel;
    private $dogStats;

    public function __construct(KernelInterface $kernel = null, DogStatsInterface $dogStats)
    {
        $this->kernel = $kernel;
        $this->dogStats = $dogStats;
    }

    public function onKernelTerminate()
    {
        if (null !== $this->kernel) {
            $responseTime = round(1000 * (microtime(true) - $this->kernel->getStartTime()));
            $this->dogStats->timing('http_request', $responseTime);
        }
    }
}
