<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

use Graze\DogStatsD\Client;

class DatadogClient implements DogStatsInterface
{
    /**
     * @var DogStatsInterface|Client
     */
    protected $wrapped;

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $statsd = new ClientWrapper();
        $statsd->configure($options);
        $this->options = $options;
        $this->wrapped = $statsd;
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $this->wrapped->increment($metrics, $delta, $sampleRate, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $this->wrapped->decrement($metrics, $delta, $sampleRate, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function timing(string $metric, float $time, array $tags = [])
    {
        $this->wrapped->timing($metric, $time, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function time(string $metric, callable $func, array $tags = [])
    {
        $this->wrapped->time($metric, $func, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function gauge(string $metric, int $value, array $tags = [])
    {
        $this->wrapped->gauge($metric, $value, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        $this->wrapped->histogram($metric, $value, $sampleRate, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $metric, int $value, array $tags = [])
    {
        $this->wrapped->set($metric, $value, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function event(string $title, string $text, array $metadata = [], array $tags = [])
    {
        $this->wrapped->event($title, $text, $metadata, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serviceCheck(string $name, int $status, array $metadata = [], array $tags = [])
    {
        $this->wrapped->serviceCheck($name, $status, $metadata, $tags);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->wrapped->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $this->wrapped->getOption($name, $default);
    }
}
