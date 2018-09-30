<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Dumper;

class DatadogEvent
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $fullMessage;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var mixed|null
     */
    private $cause;

    /**
     * @var int
     */
    private $datetime;

    /**
     * @var string
     */
    private $shortMessage;

    /**
     * @param string $message Format message that will send into datadog API. Max length 4000 charsets.
     * @param string $fullMessage Full message
     * @param string $shortMessage
     * @param string $title
     * @param array $tags
     * @param int|null $datetime
     * @param mixed|null $rootCause
     */
    public function __construct(string $message, string $shortMessage, string $fullMessage, string $title, array $tags = [], $rootCause = null, ?int $datetime = null)
    {
        $this->message = $message;
        $this->fullMessage = $fullMessage;
        $this->shortMessage = $shortMessage;
        $this->title = $title;
        $this->tags = $tags;
        $this->cause = $rootCause;
        $this->datetime = $datetime ?: time();
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getFullMessage(): string
    {
        return $this->fullMessage;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return mixed|null
     */
    public function getCause()
    {
        return $this->cause;
    }

    /**
     * @return int
     */
    public function getDatetime(): int
    {
        return $this->datetime;
    }

    /**
     * @return string
     */
    public function getShortMessage(): string
    {
        return $this->shortMessage;
    }
}
