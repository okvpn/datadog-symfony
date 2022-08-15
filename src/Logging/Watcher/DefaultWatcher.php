<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging\Watcher;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Dump current context: token, request into log
 */
class DefaultWatcher implements ContextWatcherInterface
{
    private ?RequestStack $requestStack;
    private ?TokenStorageInterface $tokenStorage;

    /**
     * @param string[] $skipHttpHeaders
     */
    public function __construct(private array $skipHttpHeaders = [])
    {
        // For security skip 'Cookie:', 'X-Wsse:', 'Authorization:' headers
    }

    public function setRequestStack(RequestStack $requestStack = null): void
    {
        $this->requestStack = $requestStack;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage = null): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array<string, mixed>
     */
    public function watch(): array
    {
        $context = [];
        if ($this->tokenStorage instanceof TokenStorageInterface) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $context['token'] = $token->serialize();
            }
        }

        if ($this->requestStack instanceof RequestStack && $request = $this->requestStack->getCurrentRequest()) {
            $request = preg_replace("/\r\n/", "\n", (string) $request);
            foreach ($this->skipHttpHeaders as $filteredHeader) {
                $request = preg_replace('#'. $filteredHeader .".+\n#i", '', $request);
            }
            $context['request'] = $request;
        }

        return $context;
    }
}
