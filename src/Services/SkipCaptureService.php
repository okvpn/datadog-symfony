<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Services;

class SkipCaptureService
{
    /** @var ExceptionHashService */
    private $hashService;

    /** @var array */
    private $skipCapture;

    /** @var array */
    private $skipCommand;

    /** @var array */
    private $skipInstanceof;

    /** @var array */
    private $skipHash;

    /** @var array */
    private $skipWildcard;

    /**
     * @param ExceptionHashService $hashService
     * @param array $skipConfig
     */
    public function __construct(ExceptionHashService $hashService, array $skipConfig)
    {
        $this->hashService = $hashService;
        $this->skipCapture = $skipConfig['skip_capture'] ?? [];
        $this->skipCommand = $skipConfig['skip_command'] ?? [];
        $this->skipInstanceof = $skipConfig['skip_instanceof'] ?? [];
        $this->skipHash = $skipConfig['skip_hash'] ?? [];
        $this->skipWildcard = $skipConfig['skip_wildcard'] ?? [];
    }

    /**
     * Check that exception should be skip
     *
     * @param \Throwable $exception
     * @return bool
     */
    public function shouldExceptionCaptureBeSkipped(\Throwable $exception): bool
    {
        if (in_array(get_class($exception), $this->skipCapture, true)) {
            return true;
        }
        if ($this->skipHash && in_array($this->hashService->hash($exception), $this->skipHash, true)) {
            return true;
        }
        foreach ($this->skipInstanceof as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        if (function_exists('fnmatch')) {
            $message = $exception->getMessage();
            foreach ($this->skipWildcard as $wildcard) {
                if (fnmatch($wildcard, $message)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check that message or command should be skip
     *
     * @param string $message
     * @return bool
     */
    public function shouldMessageCaptureBeSkipped(string $message): bool
    {
        if (function_exists('fnmatch')) {
            foreach ($this->skipWildcard as $wildcard) {
                if (fnmatch($wildcard, $message)) {
                    return true;
                }
            }
        }
        if (in_array($message, $this->skipCapture, true)) {
            return true;
        }

        if (in_array($message, $this->skipCommand, true)) {
            return true;
        }
        return false;
    }
}
