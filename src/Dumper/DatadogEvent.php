<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Dumper;

class DatadogEvent
{
    private int $datetime;

    /**
     * @param string $message Format message that will send into datadog API. Max length 4000 charsets.
     */
    public function __construct(private string $message,
                                private string $shortMessage,
                                private string $fullMessage,
                                private string $title,
                                private array  $tags = [],
                                private mixed  $cause = null,
                                ?int           $datetime = null)
    {
        $this->datetime = $datetime ?: time();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFullMessage(): string
    {
        return $this->fullMessage;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCause(): mixed
    {
        return $this->cause;
    }

    public function getDatetime(): int
    {
        return $this->datetime;
    }

    public function getShortMessage(): string
    {
        return $this->shortMessage;
    }
}
