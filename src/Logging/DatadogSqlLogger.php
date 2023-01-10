<?php

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Doctrine\DBAL\Logging\SQLLogger;

class DatadogSqlLogger implements SQLLogger
{
    /**
     * Start time of currently executed query
     */
    private ?float $queryStartTime = null;

    public function __construct(private DogStatsInterface $statsd)
    {}

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queryStartTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $mtime = round(microtime(true) - $this->queryStartTime, 5) * 1000;
        $this->statsd->histogram('doctrine', $mtime);
    }
}
