<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Logging;

use Symfony\Component\Filesystem\Filesystem;

class LocalArtifactsStorage implements ArtifactsStorageInterface
{
    private $fs;
    private $baseDir;
    protected $prefix = 'datadog-';

    public function __construct(string $baseDir)
    {
        $this->fs = new Filesystem();
        $this->baseDir = $baseDir;
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $content): string
    {
        $code = sha1(uniqid('', true));
        $this->fs->dumpFile($this->filename($code), $content);

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(string $code): ?string
    {
        if ($this->fs->exists($this->filename($code))) {
            return file_get_contents($this->filename($code));
        }

        return null;
    }

    private function filename(string $code): string
    {
        return sprintf('%s/%s%s.log', $this->baseDir, $this->prefix, $code);
    }
}
