<?php

namespace GSS\Command;

use GSS\Component\Cron\CronInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RunCronCommand extends Command implements ContainerAwareInterface
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
            ->setName('gs:cron:run')
            ->setDescription('Run Crontab')
            ->addArgument('cron', InputArgument::OPTIONAL, 'Single-Cron')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Debug-Mode');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('debug')) {
            \define('CLI_DEBUG', true);
        }

        $cron = $input->getArgument('cron');

        if ($cron === null) {
            $cronTab = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM crontab WHERE NextExecute <= ? ', [\time()]);
        } else {
            $cronTab = [
                [
                    'id' => -1,
                    'Action' => $cron,
                    'Name' => $cron,
                    'Time' => 0,
                ],
            ];
        }

        /*
         * Loop the Crons
         */
        foreach ($cronTab as $cron) {
            $cron['NextExecute'] = \time() + $cron['Time'];
            $this->container->get('doctrine.dbal.default_connection')->update('crontab', $cron, ['id' => $cron['id']]);

            if ($this->container->has('app.cron.' . $cron['Action'])) {
                /** @var CronInterface $cronObject */
                $cronObject = $this->container->get('app.cron.' . $cron['Action']);

                if ($cronObject instanceof ContainerAwareInterface) {
                    $cronObject->setContainer($this->container);
                }

                $output->writeln($cron['Name'] . ' starting to execute');

                $cronObject->start();
            }

            $cron['LastExecute'] = \time();
            $output->writeln($cron['Name'] . ' has been executed');

            $this->container->get('doctrine.dbal.default_connection')->update('crontab', $cron, ['id' => $cron['id']]);
        }
    }
}
