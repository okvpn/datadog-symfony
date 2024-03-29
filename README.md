# Symfony datadog integration

Symfony [Datadog][1] integration to monitor and track for application errors and send notifications about them.

[![Tests](https://github.com/okvpn/datadog-symfony/actions/workflows/tests.yml/badge.svg)](https://github.com/okvpn/datadog-symfony/actions/workflows/tests.yml) [![Latest Stable Version](https://poser.pugx.org/okvpn/datadog-symfony/v/stable)](https://packagist.org/packages/okvpn/datadog-symfony) [![Latest Unstable Version](https://poser.pugx.org/okvpn/datadog-symfony/v/unstable)](https://packagist.org/packages/okvpn/datadog-symfony) [![Total Downloads](https://poser.pugx.org/okvpn/datadog-symfony/downloads)](https://packagist.org/packages/okvpn/datadog-symfony) [![License](https://poser.pugx.org/okvpn/datadog-symfony/license)](https://packagist.org/packages/okvpn/datadog-symfony)

## Benefits

Use datadog-symfony for:

* Monitor production applications in realtime.
* Application performance insights to see when performance is geting degradated.
* Access to the `okvpn_datadog.client` through the container.
* Send notification about errors in Slack, email, telegram, etc.
* Create JIRA issue when some alarm/exception triggers using this [plugin][4]

## Install
Install using [composer][2] following the official Composer [documentation][3]: 

1. Install via composer:
```
composer require okvpn/datadog-symfony
```

2. And add this bundle to your AppKernel:

For Symfony 4+ add bundle to `config/bundles.php`

```php
<?php
return [
    ... //  bundles
    Okvpn\Bundle\DatadogBundle\OkvpnDatadogBundle::class => ['all' => true], 
    ...
]
```

3. Base configuration to enable the datadog client in your `config.yml`

```yaml
okvpn_datadog:
    clients:
        default: 'datadog://127.0.0.1/namespace'
        
        ## More clients
        i2pd_client: 'datadog://10.10.1.1:8125/app?tags=tg1,tg2'
        'null': null://null
        mock: mock://mock
        dns: '%env(DD_CLIENT)%'
```

Where env var looks like:
```
DD_CLIENT=datadog://127.0.0.1:8125/app1?tags=tg1,tg2
```

Access to client via DIC:

```php
$client = $this->container->get('okvpn_datadog.client'); // Default public alias 

// okvpn_datadog.client.default - private services
// okvpn_datadog.client.i2pd_client
// okvpn_datadog.client.null

class FeedController 
{
    public function __construct(private DogStatsInterface $dogStats){} // default 
}

class FeedController 
{
    public function __construct(private DogStatsInterface $i2pdClient){} // i2pd_client
}
```

```php
class FeedController extends Controller
{
    // Inject via arg for Symfony 4+
    #[Route(path: '/', name: 'feeds')]
    public function feedsAction(DogStatsInterface $dogStats, DogStatsInterface $i2pdClient): Response
    {
        $dogStats->decrement('feed');
        
        return $this->render('feed/feeds.html.twig');
    }
}
```

## Custom metrics that provided by OkvpnDatadogBundle

Where `app` metrics namespace.

|    Name                       |    Type      |                         Description                                        |
|-------------------------------|:------------:|:--------------------------------------------------------------------------:|
| app.exception                 | counter      | Track how many exception occurred in application per second                |
| app.doctrine.median           | gauge        | Median execute time of sql query (ms.)                                     |
| app.doctrine.avg              | gauge        | Avg execute time of sql query (ms.)                                        |
| app.doctrine.count            | rate         | Count of sql queries per second                                            |
| app.doctrine.95percentile     | gauge        | 95th percentile of execute time of sql query (ms.)                         |
| app.exception                 | event        | Event then exception is happens                                            |
| app.http_request              | timing       | Measure timing how long it takes to fully render a page                    |

## Configuration

A more complex setup look like this  `config/packages/ddog.yml`:

```

okvpn_datadog:
    profiling: true       # Default false: enable exception, http request etc.
    namespace: app        # Metric namespace
    port: 8125            # datadog udp port
    host: 127.0.0.1
    tags:                 # Default tags which sent with every request
        - example.com
        - cz1
    doctrine: true        # Enable timing for sql query
    exception: all        # Send event on exception
                          #   *all*      - handle all exceptions: logger error context, console error, http error.
                          #   *uncaught* - handle uncaught exceptions: console error, http error.
                          #   *none*     - disable exceptions handler
                          
    dedup_path: null      # Path to save duplicates log records across multiple requests. 
                          # Used to prevent send multiple event on the same error
    
    dedup_keep_time: 86400 # Time in seconds during which duplicate entries should be suppressed after a given log is sent through
    artifacts_path: null   # Long events is aggregate as artifacts, because datadog event size is limited to 4000 characters.
    
    handle_exceptions:     # Skip exceptions
        skip_instanceof:
            - Symfony\Component\Console\Exception\ExceptionInterface
            - Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
        skip_command:     # Skip exception for console command
            - okvpn:message-queue:consume
```

## Usage

```php
class FeedController extends Controller
{
    // Inject via arg for Symfony 4+
    #[Route(path: '/', name: 'feeds')]
    public function feedsAction(DogStatsInterface $dogStats): Response
    {
        $dogStats->decrement('feed');
        
        return $this->render('feed/feeds.html.twig');
    }
}

// or use service directly for 3.4
$client = $this->container->get('okvpn_datadog.client');

/*
 * Increment/Decriment
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
$client->increment('page.load', 1, 0.5, ['tag1' => 'http']);
```

### Sets

```php

$consumerPid = getmypid();
$client->set('consumers', $consumerPid);
```

### Timing 

```php
$client->timing('http.response_time', 256);
```

See more metrics here [DogStatsInterface](src/Client/DogStatsInterface.php) 

## Impact on performance 

Datadog bundle use UDP protocol to send custom metrics to DogStatsD collector, that usually running on localhost (127.0.0.1).
Because it uses UDP, your application can send metrics without waiting for a response. DogStatsD aggregates multiple data
points for each unique metric into a single data point over a period of time called the flush interval and sends it to Datadog where 
it is stored and available for graphing alongside the rest of your metrics.

![1](src/Resources/docs/1.png)

## Screencasts.

What can be done using datadog.

### Datadog custom symfony dashboard

![dashboard](src/Resources/docs/dashboard.png)

### Datadog Anomaly Monitor of running consumers

![consumers](src/Resources/docs/consumers.png)

### Live exception event stream

![exception](src/Resources/docs/exception.png)

### Send notification about errors in telegram.

![telegram](src/Resources/docs/telegram.png)

### Create JIRA issue when some alarm/exception triggers

![jira](src/Resources/docs/jira.png)

License
-------
MIT License. See [LICENSE](LICENSE).

[1]:    https://docs.datadoghq.com/getting_started/
[2]:    https://getcomposer.org/
[3]:    https://getcomposer.org/download/
[4]:    https://www.datadoghq.com/blog/jira-issue-tracking/

