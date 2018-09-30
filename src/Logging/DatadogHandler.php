<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Okvpn\Bundle\DatadogBundle\Services\SkipCaptureService;

class DatadogHandler extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    protected $rootError;

    /**
     * @var SkipCaptureService
     */
    protected $skipCaptureService;

    /**
     * @param SkipCaptureService $skipCaptureService
     */
    public function __construct(SkipCaptureService $skipCaptureService)
    {
        parent::__construct(Logger::ERROR, true);
        $this->skipCaptureService = $skipCaptureService;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $exception = false;
        if (isset($record) && \is_array($record['context'])) {
            foreach ($record['context'] as $value) {
                if ($value instanceof \Throwable) {
                    $exception = $value;
                    break;
                }
            }

            if ($exception && null === $this->rootError && false === $this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)) {
                $this->rootError = $record;
            }
        }
    }

    public function clear(): void
    {
        $this->rootError = null;
    }

    public function getRootError(): ?array
    {
        return $this->rootError;
    }
}
