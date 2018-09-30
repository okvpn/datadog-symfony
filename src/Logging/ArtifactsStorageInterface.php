<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

interface ArtifactsStorageInterface
{
    /**
     * Save artifact.
     *
     * @param string $content
     *
     * @return string Url or code to uploaded artifact
     */
    public function save(string $content): string;

    /**
     * Get artifact by code
     *
     * @param string $code
     * @return string|null
     */
    public function getContent(string $code): ?string;
}
