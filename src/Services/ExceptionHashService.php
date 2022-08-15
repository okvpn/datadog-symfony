<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Services;

class ExceptionHashService
{
    public function __construct(private string $cacheDirPrefix)
    {
        // skip AUTO GENERATED classes
    }

    /**
     * This function returns a unique identifier for the exception.
     * This id can be used as a hash key for find duplicate exceptions
     */
    public function hash(\Throwable $exception): string
    {
        $hash = '';
        $trace = $exception->getTrace();
        $trace[] = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];

        foreach ($trace as $place) {
            if (isset($place['file'], $place['line']) && $place['file'] && $place['line'] > 0 && !str_contains($place['file'], $this->cacheDirPrefix)) {
                $hash .= $place['file'] . ':' . $place['line'] . "\n";
            }
        }

        return sha1($hash);
    }
}
