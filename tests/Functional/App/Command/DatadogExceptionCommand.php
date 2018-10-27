<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatadogExceptionCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:exception')
            ->setDescription('Trigger error');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \function_do_not_exists();
    }
}
