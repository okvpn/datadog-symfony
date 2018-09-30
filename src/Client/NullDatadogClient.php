<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

class NullDatadogClient implements DogStatsInterface
{
    /**
     * {@inheritdoc}
     */
    public function increment(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function timing(string $metric, float $time, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function time(string $metric, callable $func, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function gauge(string $metric, int $value, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $metric, int $value, array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function event(string $title, string $text, array $metadata = [], array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serviceCheck(string $name, int $status, array $metadata = [], array $tags = [])
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $default;
    }
}
