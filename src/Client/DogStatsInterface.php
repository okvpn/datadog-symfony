<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

interface DogStatsInterface
{
    public const STATUS_OK       = 0;
    public const STATUS_WARNING  = 1;
    public const STATUS_CRITICAL = 2;
    public const STATUS_UNKNOWN  = 3;

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_NORMAL = 'normal';

    public const ALERT_ERROR   = 'error';
    public const ALERT_WARNING = 'warning';
    public const ALERT_INFO    = 'info';
    public const ALERT_SUCCESS = 'success';

    /**
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
    public function increment(string $metrics, int $delta = 1, float $sampleRate = 1.0, array $tags = []);

    /**
     * Counters track how many times something happens per second, such as page views.
     * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#counters
     *
     * @param string          $metric    Metric(s) to decrement
     * @param int             $delta      Value to increment the metric by
     * @param float           $sampleRate Sample rate of metric
     * @param string[]        $tags       List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function decrement(string $metric, int $delta = 1, float $sampleRate = 1.0, array $tags = []);

    /**
     * Timers in DogStatsD are an implementation of Histograms (not to be confused with timers in the standard StatsD).
     * They measure timing data only, for example, the amount of time a section of code takes to execute,
     * or how long it takes to fully render a page.
     * @see https://docs.datadoghq.com/developers/dogstatsd/data_types/#timers
     *
     * @param string   $metric Metric to track
     * @param float    $time   Time in milliseconds
     * @param string[] $tags   List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function timing(string $metric, float $time, array $tags = []);

    /**
     * Time a function
     * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#timers
     *
     * @param string   $metric Metric to time
     * @param callable $func   Function to record
     * @param string[] $tags   List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function time(string $metric, callable $func, array $tags = []);

    /**
     * Gauges measure the value of a particular thing over time.
     * For example, in order to track the amount of free memory on a machine,
     * periodically sample that value as the metric system.mem.free:
     * @see https://docs.datadoghq.com/developers/dogstatsd/data_types/#gauges
     *
     * @param string   $metric Metric to gauge
     * @param int      $value  Set the value of the gauge
     * @param string[] $tags   List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function gauge(string $metric, int $value, array $tags = []);

    /**
     * Histograms are specific to DogStatsD. They calculate the statistical distribution of any kind of value,
     * such as the size of files uploaded to your site:
     * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#histograms
     *
     * @param string   $metric     Metric to send
     * @param float    $value      Value to send
     * @param float    $sampleRate Sample rate of metric
     * @param string[] $tags       List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = []);

    /**
     * Sets are used to count the number of unique elements in a group,
     * for example, the number of unique visitors to your site:
     * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#sets
     *
     * @param string   $metric
     * @param int      $value
     * @param string[] $tags List of tags for this metric
     *
     * @return DogStatsInterface
     */
    public function set(string $metric, int $value, array $tags = []);

    /**
     * Send a event notification
     *
     * @link http://docs.datadoghq.com/guides/dogstatsd/#events
     *
     * @param string   $title     Event Title
     * @param string   $text      Event Text
     * @param array    $metadata  Set of metadata for this event:
     *                            - time - Assign a timestamp to the event.
     *                            - hostname - Assign a hostname to the event
     *                            - key - Assign an aggregation key to th event, to group it with some others
     *                            - priority - Can be 'normal' or 'low'
     *                            - source - Assign a source type to the event
     *                            - alert - Can be 'error', 'warning', 'info' or 'success'
     * @param string[] $tags      List of tags for this event
     * @return DogStatsInterface
     */
    public function event(string $title, string $text, array $metadata = [], array $tags = []);

    /**
     * DogStatsD can send service checks to Datadog.
     * Use checks to track the status of services your application depends on:
     * @link https://docs.datadoghq.com/developers/dogstatsd/data_types/#service-checks
     *
     * @param string   $name     Name of the service
     * @param int      $status   digit corresponding to the status you’re reporting (OK = 0, WARNING = 1, CRITICAL = 2,
     *                           UNKNOWN = 3)
     * @param array    $metadata - time - Assign a timestamp to the service check
     *                           - hostname - Assign a hostname to the service check
     * @param string[] $tags     List of tags for this event
     * @return DogStatsInterface
     */
    public function serviceCheck(string $name, int $status, array $metadata = [], array $tags = []);

    /**
     * Get Stat agent transport options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Get Stat agent transport options
     *
     * @param string $name        Option name
     * @param mixed $default      Default value
     * @return mixed
     */
    public function getOption(string $name, $default = null);
}
