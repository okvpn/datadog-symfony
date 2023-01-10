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
    public function __construct(private LoggerInterface $logger, private SkipCaptureService $skipCaptureService)
    {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($this->skipCaptureService->shouldExceptionCaptureBeSkipped($exception)) {
            return;
        }

        $this->captureException($exception, ['error:http']);
    }

    public function onConsoleError(ConsoleEvent $event): void
    {
        $command = $event->getCommand();
        $exception = null;

        if ($event instanceof ConsoleErrorEvent) {
           $exception = $event->getError();
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

    /**
     * @param string[] $tags
     */
    private function captureException(\Throwable $throwable, array $tags): void
    {
        $this->logger->error($throwable->getMessage(), ['exception' => $throwable, 'tags' => $tags]);
    }
}
