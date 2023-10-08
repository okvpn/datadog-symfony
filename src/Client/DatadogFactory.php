<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

class DatadogFactory implements DatadogFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $options): DogStatsInterface
    {
    }
}
