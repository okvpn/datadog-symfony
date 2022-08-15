<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Okvpn\Bundle\DatadogBundle\Services\SkipCaptureService;

class DatadogHandler extends AbstractProcessingHandler
{
    public function __construct(private SkipCaptureService $skipCaptureService, private ErrorBag $errorBag)
    {
        parent::__construct(Level::Error, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        $exception = false;
        if (isset($record) && \is_array($record['context'])) {
            foreach ($record['context'] as $value) {
                if ($value instanceof \Throwable) {
                    $exception = $value;
                    break;
                }
            }

            if ($exception
                && false === $this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)
                && false === $this->skipCaptureService->shouldMessageCaptureBeSkipped($record['message'])
            ) {
                $this->errorBag->pushError($record);
            }
        }
    }
}
