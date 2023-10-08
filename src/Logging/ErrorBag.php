<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

class ErrorBag
{
    private $errors = [];
    private $currentIndex = 0;
    private $bufferSize;

    /**
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = 5)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @param array $record
     */
    public function pushError(array $record): void
    {
        $this->errors[$this->currentIndex] = $record;
        $this->currentIndex = ($this->currentIndex+1) % $this->bufferSize;
    }

    public function flush(): void
    {
        $this->errors = [];
        $this->currentIndex = 0;
    }

    public function rootError(): ?array
    {
        return $this->errors[0] ?? null;
    }

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
