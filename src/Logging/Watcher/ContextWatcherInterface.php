<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging\Watcher;

interface ContextWatcherInterface
{
    /**
     * @return array
     */
    public function watch(): array;
}
