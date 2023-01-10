<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging\Watcher;

interface ContextWatcherInterface
{
    public function watch(): array;
}
