<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Services\SkipCaptureService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Kernel;

class ExceptionListener
{
    private $logger;
    private $skipCaptureService;

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
     * @param GetResponseForExceptionEvent|ExceptionEvent $event
     */
    public function onKernelException($event): void
    {
        $exception = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();
        if ($this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)) {
            return;
        }

        $this->captureException($exception, ['error:http']);
    }

    /**
     * @param ConsoleEvent|ConsoleExceptionEvent $event
     */
    public function onConsoleError(ConsoleEvent $event)
    {
        $command = $event->getCommand();
        $exception = null;
        if (Kernel::VERSION_ID > 30300) {
            if ($event instanceof ConsoleErrorEvent) {
                $exception = $event->getError();
            }
        } else {
            if ($event instanceof ConsoleExceptionEvent) {
                $exception = $event->getException();
            }
        }

        if (!$exception || $this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception) || $this->skipCaptureService->shouldMessageCaptureBeSkipped($command->getName())) {
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
