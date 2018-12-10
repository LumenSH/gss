<?php

namespace GSS\Command;

use GSS\Component\Commerce\GP;
use GSS\Component\Hosting\Gameserver\Gameserver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GiveGPCommand.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class GiveGPCommand extends Command implements ContainerAwareInterface
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
            ->setName('gs:give:gp')
            ->setDescription('Give GP to all users or limited on events <3')
            ->addOption('gameserverTyp', 't', InputArgument::OPTIONAL, 'Gameserver typ', null)
            ->addArgument('gp', InputArgument::REQUIRED, 'GP Amount')
            ->addArgument('reason', InputArgument::REQUIRED, 'Reason');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = [];
        if ($input->getOption('gameserverTyp') != null) {
            $dbal = $this->container->get('doctrine.dbal.default_connection')->createQueryBuilder();

            $dbal
                ->select('Owner')
                ->from('gameserver');

            $dbal
                ->andWhere('Typ = :gameserverTyp')
                ->setParameter('gameserverTyp', $input->getOption('gameserverTyp'));

            $gameserver = $dbal->execute()->fetchAll();
            $users = \array_column($gameserver, 'Owner');
        } else {
            $users = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id FROM users');
            $users = \array_column($users, 'id');
        }

        $progress = new ProgressBar($output, \count($users));

        foreach ($users as $user) {
            $this->container->get(GP::class)->addPointsToUser($user, $input->getArgument('gp'), $input->getArgument('reason'));
            $progress->advance();
        }

        $progress->finish();

        $output->writeln("\n");
        $output->writeln('Finished');
        $output->writeln('Happy GP Day <3');
    }
}
