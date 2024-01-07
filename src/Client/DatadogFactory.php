<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

class DatadogFactory implements DatadogFactoryInterface
{
    protected static $clientFactories = [
        'null' => NullDatadogClient::class,
        'mock' => MockClient::class,
        'datadog' => DatadogClient::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function createClient(array $options): DogStatsInterface
    {
        if (isset($options['dsn'])) {
            $dsn = DatadogDns::fromString($options['dsn']);
            $options = $dsn->toArray() + $options;
        }

        $scheme = $options['scheme'] ?? 'datadog';
        if (!static::$clientFactories[$scheme]) {
            throw new \InvalidArgumentException('The datadog DSN scheme "%s" does not exists. Allowed "%s"', $scheme, implode(",", static::$clientFactories));
        }

        return new static::$clientFactories[$scheme]($options);
    }

    public static function setClientFactory(string $alias, string $className): void
    {
        static::$clientFactories[$alias] = $className;
    }
}
