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
    private $skipHeaders;
    private $requestStack;
    private $tokenStorage;

    /**
     * @param array $skipHttpHeaders
     */
    public function __construct(array $skipHttpHeaders = [])
    {
        // For security skip 'Cookie:', 'X-Wsse:', 'Authorization:' headers
        $this->skipHeaders = $skipHttpHeaders;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public function watch(): array
    {
        $context = [];
        if ($this->tokenStorage instanceof TokenStorageInterface) {
            $token = $this->tokenStorage->getToken();
            if (null  !== $token) {
                $context['token'] = $token->serialize();
            }
        }

        if ($this->requestStack instanceof RequestStack && $request = $this->requestStack->getCurrentRequest()) {
            $request = preg_replace("/\r\n/", "\n", (string) $request);
            foreach ($this->skipHeaders as $filteredHeader) {
                $request = preg_replace('#'. $filteredHeader .".+\n#i", '', $request);
            }
            $context['request'] = $request;
        }

        return $context;
    }
}
