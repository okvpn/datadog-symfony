<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Stream;

use Graze\DogStatsD\Stream\StreamWriter;

class UdpStreamWriter extends StreamWriter
{
    /**
     * Maximum UDP payload size is actually around 65k, but Dogstatsd used buffer size 8k.
     * @see https://github.com/DataDog/dd-agent/issues/2638
     */
    const MAX_SEND_LENGTH = 8192;
}
