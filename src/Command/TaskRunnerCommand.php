<?php

namespace GSS\Command;

use GSS\Component\Hosting\Gameserver\Gameserver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Throwable;

/**
 * Class TaskRunnerCommand
 */
class TaskRunnerCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this->setName('gs:task:runner')
            ->setDescription('Start a specific task on a gameserver')
            ->addArgument('args', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = \json_decode($input->getArgument('args'), true);

        if ($this->isServerExists($data['id'])) {
            $gs = Gameserver::createServer($this->container, $data['id']);

            try {
                switch ($data['name']) {
                    case 'GSUpdate':
                        $data['args']['version'] = $this->updateVersion($gs, $data['args']['version']);
                        $gs->updateServer($data['args']['version']);
                        break;
                    case 'GSReinstall':
                        if (!isset($data['args']['version'])) {
                            break;
                        }

                        if (isset($data['args']['step']) && $data['args']['step'] == 1) {
                            $data['args']['version'] = $this->updateVersion($gs, $data['args']['version']);
                        }

                        $gs->reinstallServer($data['args']['version'], $data['args']['step']);
                        break;
                    case 'GSDelete':
                        $gs->delete();
                        break;
                }
            } catch (Throwable $e) {
                $this->container->get('logger')->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $output->writeln('Error ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            }
        }
    }

    /**
     * @param int $serverId
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function isServerExists(int $serverId)
    {
        try {
            return $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT 1 FROM gameserver WHERE id = ?', [$serverId]);
        } catch (Throwable $e) {
            exit(-1);
        }
    }

    /**
     * @param Gameserver $gs
     * @param $version
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function updateVersion(Gameserver $gs, $version)
    {
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE gameserver SET gameserver.versionID = ? WHERE id = ?', [
            $version,
            $gs->getId(),
        ]);

        return $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT version FROM products_version WHERE id = ?', [
            $version,
        ]);
    }
}
