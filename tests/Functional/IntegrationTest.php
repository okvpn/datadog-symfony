<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class IntegrationTest extends WebTestCase
{
    private KernelBrowser $client;

    /**
     * Manage schema and cleanup chores
     * @throws ToolsException
     */
    public static function setUpBeforeClass(): void
    {
        static::deleteTmpDir();
        $kernel = static::createClient()->getKernel();

        /** @var EntityManagerInterface $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (property_exists($this, 'booted') && self::$booted) {
            parent::tearDown();
        }

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
        self::assertEmpty($this->getClientDecorator()->getRecords());

        $this->client->request('GET', '/');
        $this->client->getResponse();

        self::assertNotEmpty($this->getClientDecorator()->getRecords());
    }

    public function testDoctrineLog()
    {
        $this->client->request('GET', '/entity');
        $this->client->getResponse();

        $records = $this->getClientDecorator()->getRecords();

        self::assertNotEmpty($records);

        $args = array_column($records, 'args');
        $metricsName = array_column($args, 0);

        self::assertContains('doctrine', $metricsName);
    }

    public function testHandleConsoleException()
    {
        self::assertEmpty($this->getClientDecorator()->getRecords());

        try {
            $this->runCommand('app:exception');
        } catch (\Throwable $exception) {}

        list($title, $desc) = $this->getClientDecorator()->getLastEvent();

        self::assertNotEmpty($this->getClientDecorator()->getRecords());
        self::assertStringContainsString('Call to undefined function function_do_not_exists', $desc);
    }

    public function testDeduplicationLogger()
    {
        self::assertEmpty($this->getClientDecorator()->getRecords());

        for ($i = 0; $i <= 2; $i++) {
            $this->getClientDecorator()->clear();
            try {
                $this->client->request('GET', '/exception');
            } catch (\Exception $exception) {}

            list($title, $desc) = $this->getClientDecorator()->getLastEvent();
            $desc = $desc ? $this->processDatadogArtifact($desc) : $desc;
            switch ($i) {
                case 0:
                    self::assertNotEmpty($this->getClientDecorator()->getRecords());
                    self::assertStringContainsString('GET /exception HTTP/1.1', $desc, 'Request details must be save in log');
                    sleep(1);
                    break;
                case 1:
                    self::assertNull($desc, 'The duplication logs must be skips');
                    sleep(5);
                    break;
                case 2:
                    self::assertNotNull($desc, 'GC must remove duplication logs after 5 sec.');
                    break;
            }
        }
    }

    /**
     * @dataProvider filterExceptionDataProvider
     */
    public function testFilterException(string $filterOption, bool $isSkip)
    {
        try {
            $this->runCommand('app:exception', ['--filter' => $filterOption]);
        } catch (\Throwable $exception) {

        }

        list($title, $desc) = $this->getClientDecorator()->getLastEvent();
        self::assertSame($isSkip, empty($desc));
    }

    public function filterExceptionDataProvider(): \Generator
    {
        yield 'Filter by instanceof' => ['skip_instanceof', true];

        yield 'Filter by capture' => ['skip_capture', true];

        yield 'Filter by wildcard' => ['skip_wildcard', true];

        yield 'Test trigger on logger context' => ['test_logger', false];

        yield 'Test filter by log message' => ['test_logger_wildcard', true];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $logger = $this->client->getContainer()->get('okvpn_datadog.logger');

        $this->getClientDecorator()->clear();
        $logger->clearDeduplicationStore();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        static::deleteTmpDir();
    }

    protected static function deleteTmpDir(): void
    {
        $fs = new Filesystem();
        try {
            $fs->remove(__DIR__ .'/App/var');
        } catch (\Throwable $exception) {}
        try {
            $fs->remove(__DIR__ .'/App/test.db');
        } catch (\Throwable $exception) {}
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/App/OkvpnKernel.php';

        return 'Okvpn\Bundle\DatadogBundle\Tests\Functional\App\OkvpnKernel';
    }

    /**
     * Builds up the environment to run the given command.
     *
     * @param bool $cleanUp strip new lines and multiple spaces, removes dependency on terminal columns
     * @throws \Exception
     */
    protected function runCommand(string $name, array $params = [], bool $cleanUp = true, bool $exceptionOnError = false): string
    {
        /** @var KernelInterface $kernel */
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

    protected function processDatadogArtifact(string $message): string
    {
        preg_match('#artifact code: (\w{40})#i', $message, $matches);

        if (isset($matches[1])) {
            $logDir = $this->client->getContainer()->getParameter('kernel.logs_dir');
            $fileName = $logDir . '/' . 'datadog-' . $matches[1] . '.log';
            if (file_exists($fileName)) {
                return file_get_contents($fileName);
            }
        }

        return $message;
    }

    protected function getClientDecorator(): App\Client\DebugDatadogClient
    {
        /** @var App\Client\DebugDatadogClient $testDecorator */
        $testDecorator = $this->client->getContainer()->get('okvpn_datadog.client_test_decorator');
        return $testDecorator;
    }
}
