<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Services\SkipCaptureService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    private LoggerInterface $logger;
    private SkipCaptureService $skipCaptureService;

    /**
     * @param LoggerInterface $logger
     * @param SkipCaptureService $skipCaptureService
     */
    public function __construct(LoggerInterface $logger, SkipCaptureService $skipCaptureService)
    {
        $this->logger = $logger;
        $this->skipCaptureService = $skipCaptureService;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)) {
            return;
        }

        $this->captureException($exception, ['error:http']);
    }

    /**
     * @param ConsoleEvent $event
     */
    public function onConsoleError(ConsoleErrorEvent $event)
    {
        $command = $event->getCommand();
        $exception = $event->getError();

        if ($this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception) || $this->skipCaptureService->shouldMessageCaptureBeSkipped($command->getName())) {
            return;
        }

        $tags = ['command:' . str_replace(':', '_', $command->getName()), 'error:console'];
        if ($event->getOutput()->isDecorated()) {
            $tags[] = 'tty';
        }

        $this->captureException($exception, $tags);
    }

    private function captureException(\Throwable $throwable, $tags)
    {
        $this->logger->error($throwable->getMessage(), ['exception' => $throwable, 'tags' => $tags]);
    }
}
