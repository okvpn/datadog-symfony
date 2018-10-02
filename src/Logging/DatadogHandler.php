<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Okvpn\Bundle\DatadogBundle\Services\SkipCaptureService;

class DatadogHandler extends AbstractProcessingHandler
{
    private $errorBag;
    private $skipCaptureService;

    /**
     * @param SkipCaptureService $skipCaptureService
     * @param ErrorBag $errorBag
     */
    public function __construct(SkipCaptureService $skipCaptureService, ErrorBag $errorBag)
    {
        parent::__construct(Logger::ERROR, true);
        $this->errorBag = $errorBag;
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

            if ($exception && false === $this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)) {
                $this->errorBag->pushError($record);
            }
        }
    }
}
