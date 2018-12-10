<?php

namespace GSS\Command;

use GSS\Component\Hosting\Gameserver\Daemon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class StartServerCommand
 */
class StartServerCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this->setName('gs:server:start')
            ->setDescription('Starts server by hostID and serverIds')
            ->addArgument('hostId', InputArgument::REQUIRED)
            ->addArgument('serverIds', InputArgument::REQUIRED, 'Server ids seperated with comma');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $daemon = new Daemon($this->container, $input->getArgument('hostId'));
        $io = new SymfonyStyle($input, $output);

        foreach (\explode(',', $input->getArgument('serverIds')) as $id) {
            $daemon->startServer($id);
            $io->success('Started server ' . $id);
        }
    }
}
