<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\EventListener;

use Okvpn\Bundle\DatadogBundle\Logging\ErrorBag;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class DatadogFlushBufferListener
{
    /**
     * @var ErrorBag
     */
    private $errorBag;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param ErrorBag $errorBag
     */
    public function __construct(LoggerInterface $logger, ErrorBag $errorBag)
    {
        $this->logger = $logger;
        $this->errorBag = $errorBag;
    }

    public function onKernelTerminate(): void
    {
        if ($record = $this->errorBag->rootError()) {
            $this->errorBag->flush();
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
        if ($record = $this->errorBag->rootError()) {
            $this->errorBag->flush();
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
