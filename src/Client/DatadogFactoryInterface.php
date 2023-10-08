<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

interface DatadogFactoryInterface
{
    public function createClient(array $options): DogStatsInterface;
}
