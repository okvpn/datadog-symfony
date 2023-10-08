<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Services;

class ExceptionHashService
{
    private $cacheDirPrefix;

    public function __construct(string $cacheDirPrefix)
    {
        // skip AUTO GENERATED classes
        $this->cacheDirPrefix = $cacheDirPrefix;
    }

    /**
     * This function returns a unique identifier for the exception.
     * This id can be used as a hash key for find duplicate exceptions
     *
     * @param \Throwable $exception
     * @return string
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
            if (isset($place['file'], $place['line']) && $place['file'] && $place['line'] > 0 && strpos($place['file'], $this->cacheDirPrefix) === false) {
                $hash .= $place['file'] . ':' . $place['line'] . "\n";
            }
        }

        return sha1($hash);
    }
}
