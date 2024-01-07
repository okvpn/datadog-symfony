<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Client;

final class DatadogDns
{
    private string $originalDsn;
    private string $scheme;
    private string $host;
    private int $port;
    private string $namespace;
    private array $tags = [];

    public function __construct(string $dsn)
    {
        $this->parser($dsn);
    }

    public static function fromString(string $dsn): self
    {
        return new DatadogDns($dsn);
    }

    private function parser(string $dsn): void
    {
        $this->originalDsn = $dsn;

        if (false === $dsn = parse_url($dsn)) {
            throw new \InvalidArgumentException('The datadog DSN is invalid.');
        }

        if (!isset($dsn['scheme'])) {
            throw new \InvalidArgumentException('The datadog DSN must contain a scheme.');
        }

        $this->scheme = $dsn['scheme'];
        $this->host = $dsn['host'] ?? '127.0.0.1';
        $this->port = $dsn['port'] ?? 8125;
        $this->namespace = str_replace('/', '', $dsn['path'] ?? 'app');

        if (isset($dsn['query'])) {
            parse_str($dsn['query'], $query);
            if (isset($query['tags'])) {
                $this->tags = explode(',', $query['tags']);
            }
        }
    }

    /**
     * @return string
     */
    public function getOriginalDsn(): string
    {
        return $this->originalDsn;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function toArray(): array
    {
        return [
            'scheme' => $this->getScheme(),
            'namespace' => $this->getNamespace(),
            'tags' => $this->getTags(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
        ];
    }
}
