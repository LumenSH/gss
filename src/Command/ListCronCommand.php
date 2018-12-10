<?php

namespace GSS\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ListCronCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this
            ->setName('gs:cron:list')
            ->setDescription('Displays all Cronjobs');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Table $tableHelper */
        $tableHelper = new Table($output);
        $crons = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM crontab');

        $tableHelper->setHeaders([
            'Description',
            'Action',
            'LastExecute',
            'NextExcecute',
        ]);

        foreach ($crons as $key => $cron) {
            $tableHelper->setRow($key, [
                $cron['Name'],
                $cron['Action'],
                \date('Y-m-d H-i-s', $cron['LastExecute']),
                \date('Y-m-d H-i-s', $cron['NextExecute']),
            ]);
        }

        $tableHelper->render();
    }
}
