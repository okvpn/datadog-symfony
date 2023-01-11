<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
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
        //For BC use int not enum \Monolog\Level::Error
        parent::__construct(/*Logger::ERROR*/ 400, true);
        $this->errorBag = $errorBag;
        $this->skipCaptureService = $skipCaptureService;
    }

    /**
     * {@inheritdoc}
     */
    protected function write($record): void
    {
        if ($record instanceof LogRecord) {
            $record = $record->toArray();
        }

        $exception = false;
        if (isset($record) && \is_array($record['context'])) {
            foreach ($record['context'] as $value) {
                if ($value instanceof \Throwable) {
                    $exception = $value;
                    break;
                }
            }

            if ($exception && false === $this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception) && false === $this->skipCaptureService->shouldMessageCaptureBeSkipped($record['message'])) {
                $this->errorBag->pushError($record);
            }
        }
    }
}
