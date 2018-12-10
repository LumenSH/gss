<?php

namespace GSS\Command;

use GSS\Component\Hosting\Gameserver\Gameserver;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Throwable;

class RabbitConsumerCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var OutputInterface
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $output;

    /**
     * @param AMQPMessage $msg
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function processMessage(AMQPMessage $msg)
    {
        $data = \json_decode($msg->body, true);

        $this->output->writeln('[*] Consuming task: ' . $data['name'] . ', for: ' . $data['id']);

        if ($this->isServerExists($data['id'])) {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            $gs = Gameserver::createServer($this->container, $data['id']);

            try {
                switch ($data['name']) {
                    case 'GSUpdate':
                        $data['args']['version'] = $this->updateVersion($gs, $data['args']['version']);
                        $gs->updateServer($data['args']['version']);
                        break;
                    case 'GSReinstall':
                        if ($data['args']['step'] == 1) {
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
                $this->output->writeln('Error ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            }

            unset($gs);
        } else {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        }

        $this->output->writeln('[*] Consumed task: ' . $data['name'] . ', for: ' . $data['id']);
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function configure()
    {
        $this->setName('gs:rabbit:worker');
        $this->setDescription('Start rabbitmq worker');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $connection = $this->container->get('rabbit.connection');

        $channel = $connection->channel();
        $channel->queue_declare('server_queue', false, true, false, false);

        $output->writeln('[*] Waiting for tasks. To exit press CTRL+C');

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('server_queue', '', false, false, false, false, [$this, 'processMessage']);

        while (\count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
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
