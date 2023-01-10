<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Monolog\LogRecord;

class ErrorBag
{
    /**
     * @var LogRecord[]
     */
    private array $errors = [];
    private int $currentIndex = 0;

    public function __construct(private int $bufferSize = 5)
    {}

    public function pushError(LogRecord $record): void
    {
        $this->errors[$this->currentIndex] = $record;
        $this->currentIndex = ($this->currentIndex+1) % $this->bufferSize;
    }

    public function flush(): void
    {
        $this->errors = [];
        $this->currentIndex = 0;
    }

    public function rootError(): ?LogRecord
    {
        return $this->errors[0] ?? null;
    }

    /**
     * @return LogRecord[]
     */
    public function getErrors(): array
    {
        $sortErrors = [];
        for ($i = $this->currentIndex + $this->bufferSize - 1; $i >= 0; $i--) {
            $index = ($i % $this->bufferSize);
            if (!isset($this->errors[$index]) || count($sortErrors) >= $this->bufferSize) {
                break;
            }

            $sortErrors[] = $this->errors[$index];
        }

        return $sortErrors;
    }
}
