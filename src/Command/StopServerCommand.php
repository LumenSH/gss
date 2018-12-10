<?php

namespace GSS\Command;

use GSS\Component\Exception\Console\ServerNotFound;
use GSS\Component\Hosting\Gameserver\Gameserver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StopServerCommand.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class StopServerCommand extends Command implements ContainerAwareInterface
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
            ->setName('gs:server:stop')
            ->setDescription('Stops server by hostId and typ')
            ->addOption('hostId', 'hi', InputArgument::OPTIONAL, 'Host ID')
            ->addOption('typ', 't', InputArgument::OPTIONAL, 'Gameserver typ', 0)
            ->addOption('game', 'g', InputArgument::OPTIONAL, 'Game');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbal = $this->container->get('doctrine.dbal.default_connection')->createQueryBuilder();

        $dbal
            ->select('id')
            ->from('gameserver');

        if ($input->getOption('hostId')) {
            $dbal
                ->andWhere('gamerootID = :hostId')
                ->setParameter('hostId', $input->getOption('hostId'));
        }

        if ($input->getOption('typ')) {
            $dbal
                ->andWhere('Typ = :typ')
                ->setParameter('typ', $input->getOption('typ'));
        }

        if ($input->getOption('game')) {
            $dbal
                ->andWhere('Game = :game')
                ->setParameter('game', $input->getOption('game'));
        }

        $gameserver = $dbal->execute()->fetchAll();

        if (empty($gameserver)) {
            throw new ServerNotFound('Cannot find gameserver to our criteria');
        }

        $progress = new ProgressBar($output, \count($gameserver));

        foreach ($gameserver as $item) {
            $gs = Gameserver::createServer($this->container, $item['id']);

            $gs->stop();

            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln(\sprintf('%d gameservers are now offline', \count($gameserver)));
    }
}
