<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Dumper;

interface ContextDumperInterface
{
    /**
     * Dump logs context into format that can send as datadog events
     *
     * @param string $message
     * @param array $context
     *
     * @return DatadogEvent
     */
public function dumpContext(string $message, array $context): DatadogEvent;
}
