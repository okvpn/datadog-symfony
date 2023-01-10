<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatadogExceptionCommand extends Command
{
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:exception')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL)
            ->setDescription('Trigger error');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        switch ($input->getOption('filter')) {
            case 'skip_instanceof':
                throw new DemoDatadogException('Test datadog handle exception');
            case 'skip_capture':
                throw new \UnderflowException('Test datadog handle exception');
            case 'skip_wildcard':
                throw new \RuntimeException('Loading of entity aliases failed');
            case 'test_logger':
                $exception = new \RuntimeException('Logger exception');
                $this->logger->error('Unhatched exception', ['exception' => $exception]);
                break;
            case 'test_logger_wildcard':
                $exception = new \RuntimeException('Logger exception');
                $this->logger->error('Loading of entity aliases failed', ['exception' => $exception]);
                break;
            default:
                /** @noinspection PhpUndefinedFunctionInspection */
                \function_do_not_exists();
        }

        return 0;
    }
}
