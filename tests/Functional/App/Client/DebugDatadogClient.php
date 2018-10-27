<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Client;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;

class DebugDatadogClient implements DogStatsInterface
{
    private $wrapper;
    private $debug = [];
    private $lastEvent = [null, null];

    public function __construct(DogStatsInterface $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $metric, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function timing(string $metric, float $time, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function time(string $metric, callable $func, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function gauge(string $metric, int $value, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $metric, int $value, array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function event(string $title, string $text, array $metadata = [], array $tags = [])
    {
        $this->lastEvent = [$title, $text];
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function serviceCheck(string $name, int $status, array $metadata = [], array $tags = [])
    {
        return $this->collect(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->wrapper->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $this->wrapper->getOption($name, $default);
    }

    private function collect(string $method, array $args)
    {
        $this->debug[] = [
            'method' => $method,
            'args' => $args
        ];

        return $this->wrapper->{$method}(...$args);
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->debug;
    }

    /**
     * @return array
     */
    public function getLastEvent(): array
    {
        return $this->lastEvent;
    }

    /**
     * Removes all log records.
     */
    public function clear(): void
    {
        $this->debug = [];
        $this->lastEvent = [null, null];
    }
}
