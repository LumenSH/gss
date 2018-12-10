<?php

namespace GSS\Command;

use GSS\Component\Hosting\SSH;
use GSS\Component\Hosting\SSHUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CreateLinuxUserCommand.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class CreateLinuxUserCommand extends Command implements ContainerAwareInterface
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
            ->setName('gs:add:user')
            ->setDescription('Creates linux user on host')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('userId', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ssh = new SSH($input->getArgument('host'));

        SSHUtil::createLinuxUser($ssh, $input->getArgument('userId'));

        if (!$this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM users_to_gameroot WHERE hostID = ?', [
            $input->getArgument('host'),
        ])) {
            $this->container->get('doctrine.dbal.default_connection')->insert('users_to_gameroot', [
                'userID' => $input->getArgument('userId'),
                'hostID' => $input->getArgument('host'),
            ]);
        }

        $output
            ->writeln('Create linux user' . $input->getArgument('userId'));
    }
}
