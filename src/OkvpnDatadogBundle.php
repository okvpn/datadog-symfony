<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle;

use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\PushDatadogHandlerPass;
use Okvpn\Bundle\DatadogBundle\DependencyInjection\CompilerPass\SqlLoggerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkvpnDatadogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SqlLoggerPass(['default']));
        $container->addCompilerPass(new PushDatadogHandlerPass());

        $client = $this->container->get('okvpn_datadog.client');


        /*
         * Increment
         *
         * Counters track how many times something happens per second, such as page views.
         * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#counters
         *
         * @param string          $metrics    Metric(s) to increment
         * @param int             $delta      Value to decrement the metric by
         * @param float           $sampleRate Sample rate of metric
         * @param string[]        $tags       List of tags for this metric
         *
         * @return DogStatsInterface
         */
        $client->increment('page.views', 1);

        /*
         * Decrement
         *
         * Counters track how many times something happens per second, such as page views.
         * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#counters
         *
         * @param string          $metrics    Metric(s) to increment
         * @param int             $delta      Value to decrement the metric by
         * @param float           $sampleRate Sample rate of metric
         * @param string[]        $tags       List of tags for this metric
         *
         * @return DogStatsInterface
         */
        $client->decrement('page.views', 1);
    }
}
