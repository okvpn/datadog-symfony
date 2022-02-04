<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

final class MockClient implements DogStatsInterface
{
    private $data = [];

    public function increment(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $this->data['increments'][] = [
            'metric' => $metrics,
            'delta' => $delta,
            'sampleRate' => $sampleRate,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getIncrements(string $metric = null): array
    {
        return $this->get('increments', $metric);
    }

    public function decrement(string $metric, int $delta = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $this->data['decrements'][] = [
            'metric' => $metric,
            'delta' => $delta,
            'sampleRate' => $sampleRate,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getDecrements(string $metric = null): array
    {
        return $this->get('decrements', $metric);
    }

    public function timing(string $metric, float $time, array $tags = [])
    {
        $this->data['timings'][] = [
            'metric' => $metric,
            'time' => $time,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getTimings(string $metric = null): array
    {
        return $this->get('timings', $metric);
    }

    public function time(string $metric, callable $func, array $tags = [])
    {
        $timerStart = microtime(true);
        $func();
        $timerEnd = microtime(true);
        $time = round(($timerEnd - $timerStart) * 1000, 4);

        return $this->timing($metric, $time, $tags);
    }

    public function gauge(string $metric, int $value, array $tags = [])
    {
        $this->data['gauges'][] = [
            'metric' => $metric,
            'value' => $value,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getGauges(string $metric = null): array
    {
        return $this->get('gauges', $metric);
    }

    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        $this->data['histograms'][] = [
            'metric' => $metric,
            'value' => $value,
            'sampleRate' => $sampleRate,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getHistograms(string $metric = null): array
    {
        return $this->get('histograms', $metric);
    }

    public function set(string $metric, int $value, array $tags = [])
    {
        $this->data['sets'][] = [
            'metric' => $metric,
            'value' => $value,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getSets(string $metric = null): array
    {
        return $this->get('sets', $metric);
    }

    public function event(string $title, string $text, array $metadata = [], array $tags = [])
    {
        $this->data['events'][] = [
            'title' => $title,
            'text' => $text,
            'metadata' => $metadata,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getEvents(string $title = null): array
    {
        return $this->get('events', $title);
    }

    public function serviceCheck(string $name, int $status, array $metadata = [], array $tags = [])
    {
        $this->data['serviceChecks'][] = [
            'name' => $name,
            'status' => $status,
            'metadata' => $metadata,
            'tags' => $tags,
        ];

        return $this;
    }

    public function getServiceChecks(string $name = null): array
    {
        return $this->get('serviceChecks', $name);
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getOption(string $name, $default = null)
    {
        return $default;
    }

    private function get(string $type, string $metric = null): array
    {
        if ($metric === null) {
            return $this->data[$type];
        }

        if ($type === 'events') {
            $key = 'title';
        } elseif ($type === 'serviceChecks') {
            $key = 'name';
        } else {
            $key = 'metric';
        }

        return array_filter($this->data[$type], static function (array $data) use ($key, $metric) {
            return $data[$key] === $metric;
        });
    }
}
