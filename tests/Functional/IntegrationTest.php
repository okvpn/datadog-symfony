<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

class IntegrationTest extends WebTestCase
{
    /** @var Client */
    private $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testServices()
    {
        $client = $this->client->getContainer()->get('okvpn_datadog.client');
        $decorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        $logger = $this->client->getContainer()->get('okvpn_datadog.logger');

        self::assertNotNull($client);
        self::assertNotNull($decorator);
        self::assertNotNull($logger);
    }

    public function testLogRequest()
    {
        $decorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        self::assertEmpty($decorator->getRecords());

        $this->client->request('GET', '/');
        $this->client->getResponse();

        self::assertNotEmpty($decorator->getRecords());
    }

    public function testHandleConsoleException()
    {
        $decorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        self::assertEmpty($decorator->getRecords());

        try {
            $this->runCommand('app:exception');
        } catch (\Throwable $exception) {}

        list($title, $desc) = $decorator->getLastEvent();

        self::assertNotEmpty($decorator->getRecords());
        self::assertContains('Call to undefined function function_do_not_exists', $desc);
    }

    public function testHandleHttpException()
    {
        $decorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        self::assertEmpty($decorator->getRecords());

        try {
            $this->client->request('GET', '/exception');
        } catch (\Exception $exception) {}

        list($title, $desc) = $decorator->getLastEvent();
        self::assertNotEmpty($decorator->getRecords());
        self::assertContains('GET /exception HTTP/1.1', $desc, 'Request details must be save in log');

    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $decorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        $logger = $this->client->getContainer()->get('okvpn_datadog.logger');

        $decorator->clear();
        $logger->clearDeduplicationStore();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        static::deleteTmpDir();
    }

    /**
     * {@inheritdoc}
     */
    protected static function deleteTmpDir()
    {
        if (!file_exists($dir = __DIR__ .'/App/var')) {
            return;
        }
        $fs = new Filesystem();
        $fs->remove($dir);
    }

    /**
     * @return string
     */
    protected static function getKernelClass()
    {
        require_once __DIR__.'/App/OkvpnKernel.php';

        return 'Okvpn\Bundle\DatadogBundle\Tests\Functional\App\OkvpnKernel';
    }

    /**
     * Builds up the environment to run the given command.
     *
     * @param string $name
     * @param array $params
     * @param bool $cleanUp strip new lines and multiple spaces, removes dependency on terminal columns
     * @param bool $exceptionOnError
     *
     * @return string
     */
    protected function runCommand($name, array $params = [], $cleanUp = true, $exceptionOnError = false): string
    {
        $kernel = $this->client->getContainer()->get('kernel');

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $args = ['application', $name];
        foreach ($params as $k => $v) {
            if (is_bool($v)) {
                if ($v) {
                    $args[] = $k;
                }
            } else {
                if (!is_int($k)) {
                    $args[] = $k;
                }
                $args[] = $v;
            }
        }
        $input = new ArgvInput($args);
        $input->setInteractive(false);

        $fp = fopen('php://temp/maxmemory:' . (1024 * 1024 * 1), 'br+');
        $output = new StreamOutput($fp);

        $exitCode = $application->run($input, $output);

        rewind($fp);

        $content = stream_get_contents($fp);

        if ($exceptionOnError && $exitCode !== 0) {
            throw new \RuntimeException($content);
        }

        if ($cleanUp) {
            $content = preg_replace(['/\s{2,}\n\s{2,}/', '/(\n|\s{2,})+/'], ['', ' '], $content);
        }

        return trim($content);
    }
}
