<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Logging\DatadogHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class DatadogFlushBufferListener
{
    /**
     * @var DatadogHandler
     */
    protected $errorHandler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param DatadogHandler $errorHandler
     */
    public function __construct(LoggerInterface $logger, DatadogHandler $errorHandler)
    {
        $this->logger = $logger;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event): void
    {
        if ($record = $this->errorHandler->getRootError()) {
            $this->errorHandler->clear();
            try {
                $context = $record['context'];
                $context['tags'] = ['error:http', 'channel:' . $record['channel']];
                $this->logger->warning($record['message'], $context);
            } catch (\Exception $exception) {}
        }
    }

    /**
     * @param ConsoleTerminateEvent $event
     */
    public function onCliTerminate(ConsoleTerminateEvent $event): void
    {
        if ($record = $this->errorHandler->getRootError()) {
            $this->errorHandler->clear();
            try {
                $context = $record['context'];
                $context['tags'] = ['error:console', 'channel:' . $record['channel']];
                if ($event->getOutput()->isDecorated()) {
                    $context['tags'][] = 'tty';
                }

                $this->logger->warning($record['message'], $context);
            } catch (\Exception $exception) {}
        }
    }
}
