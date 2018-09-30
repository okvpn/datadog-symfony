<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Okvpn\Bundle\DatadogBundle\Client\DogStatsInterface;
use Okvpn\Bundle\DatadogBundle\Dumper\ContextDumperInterface;
use Okvpn\Bundle\DatadogBundle\Dumper\DatadogEvent;
use Okvpn\Bundle\DatadogBundle\Logging\Watcher\ContextWatcherInterface;
use Okvpn\Bundle\DatadogBundle\Services\ExceptionHashService;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

class DeduplicationDatadogLogger extends AbstractLogger
{
    private $statsd;
    private $deduplicationKeepTime;
    private $watcher;
    private $artifactStorage;
    private $exceptionHashService;
    private $contextDumper;
    private $fs;

    /**
     * @var bool 
     */
    protected $gc = false;

    /**
     * @var string 
     */
    protected $deduplicationStore;

    /**
     * @var array
     */
    protected $formatLevelMap = [
        LogLevel::EMERGENCY => DogStatsInterface::ALERT_ERROR,
        LogLevel::ALERT => DogStatsInterface::ALERT_ERROR,
        LogLevel::CRITICAL => DogStatsInterface::ALERT_ERROR,
        LogLevel::ERROR => DogStatsInterface::ALERT_ERROR,
        LogLevel::WARNING => DogStatsInterface::ALERT_WARNING,
        LogLevel::NOTICE => DogStatsInterface::ALERT_INFO,
        LogLevel::INFO => DogStatsInterface::ALERT_INFO,
        LogLevel::DEBUG => DogStatsInterface::ALERT_INFO,
    ];
    
    public function __construct(DogStatsInterface $statsd, ArtifactsStorageInterface $artifactStorage, ContextWatcherInterface $watcher, ContextDumperInterface $contextDumper, ExceptionHashService $exceptionHashService, string $deduplicationStore = null, int $deduplicationKeepTime = 86400 * 7)
    {
        $this->statsd = $statsd;
        $this->artifactStorage = $artifactStorage;
        $this->watcher = $watcher;
        $this->deduplicationKeepTime = $deduplicationKeepTime;
        $this->contextDumper = $contextDumper;
        $this->exceptionHashService = $exceptionHashService;
        $deduplicationFileName = '/datadog-dedup-' . substr(md5(__FILE__), 0, 8) .'.log';
        $this->deduplicationStore = $deduplicationStore === null ? sys_get_temp_dir() . '/' . $deduplicationFileName : $deduplicationStore . '/' . $deduplicationFileName;
        $this->fs = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        if ($this->statsd->getOption('enable', false) === true) {
            return;
        }

        $this->touch();
        $context = array_merge(
            $context,
            $this->watcher->watch()
        );
        $datadogEvent = $this->contextDumper->dumpContext($message, $context);
        if (null !== $datadogEvent->getCause()) {
            $this->statsd->increment('exception', 1, 1, $datadogEvent->getTags());
        }
        $cause = $datadogEvent->getCause();
        $causeCode = $cause instanceof \Throwable ? $this->exceptionHashService->hash($cause) : sha1($message);
        
        if (!$this->isDuplicate($causeCode)) {
            $this->appendRecord($datadogEvent, $causeCode);
            $message = $datadogEvent->getMessage();
            try {
                $artifactUrl = $this->artifactStorage->save($datadogEvent->getFullMessage());
                $message = sprintf("Artifact code: %s \n%s", $artifactUrl, $message);
            } catch (\Throwable $exception) {}

            $this->statsd->event(
                $datadogEvent->getTitle(),
                $message,
                [
                    'time'  => $datadogEvent->getDatetime(),
                    'alert' => $this->formatLevelMap[$level],
                ],
                $datadogEvent->getTags()
            );
        }

        if ($this->gc) {
            $this->collectLogs();
        }
    }

    /**
     * @return array
     */
    public function clearDeduplicationStore(): array
    {
        $logs = $this->deduplicationLogs();
        $this->touch();

        return $logs;
    }

    /**
     * @return array
     */
    public function deduplicationLogs(): array
    {
        if (!file_exists($this->deduplicationStore)) {
            return [];
        }

        $store = file($this->deduplicationStore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $store ?: [];
    }

    protected function isDuplicate(string $causeCode): bool
    {
        if (!file_exists($this->deduplicationStore)) {
            return false;
        }

        $store = file($this->deduplicationStore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($store)) {
            return false;
        }

        $timestampValidity = time() - $this->deduplicationKeepTime;
        for ($i = count($store) - 1; $i >= 0; $i--) {
            if ($store[$i] && strpos($store[$i], ':') !== 10) {
                $this->gc = true;
                continue;
            }
            list($timestamp, $message) = explode(':', $store[$i], 3);

            if ($message === $causeCode && $timestamp > $timestampValidity) {
                return true;
            }

            if ($timestamp < $timestampValidity) {
                $this->gc = true;
            }
        }

        return false;
    }

    protected function collectLogs(): void
    {
        if (!file_exists($this->deduplicationStore)) {
            return;
        }

        $handle = fopen($this->deduplicationStore, 'rw+');
        flock($handle, LOCK_EX);
        $validLogs = array();

        $timestampValidity = time() - $this->deduplicationKeepTime;

        while (!feof($handle)) {
            $log = fgets($handle);
            if ($log && substr($log, 0, 10) >= $timestampValidity) {
                $validLogs[] = $log;
            }
        }

        ftruncate($handle, 0);
        rewind($handle);
        foreach ($validLogs as $log) {
            fwrite($handle, $log);
        }

        flock($handle, LOCK_UN);
        fclose($handle);
        $this->gc = false;
    }

    protected function appendRecord(DatadogEvent $event, string $hash): void
    {
        file_put_contents($this->deduplicationStore, $event->getDatetime() . ':' . $hash . ':' . preg_replace('{[\r\n].*}', '', $event->getShortMessage()) . "\n", FILE_APPEND);
    }

    protected function touch()
    {
        $path = basename($this->deduplicationStore);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($this->deduplicationStore)) {
            file_put_contents($this->deduplicationStore, '');
            @chmod($this->deduplicationStore, 0777);
        }
    }
}
