<?php

namespace GSS\Component\Cron;

use GSS\Component\Hosting\Gameserver\Daemon;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BuildStatsDashboard implements CronInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function start(): bool
    {
        $servers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id, sshIp,description, cpus FROM gameroot');

        $hosts = [];

        foreach ($servers as $server) {
            if (@\fsockopen($server['sshIp'], 22, $errno, $err, 2)) {
                $daemon = new Daemon($this->container, $server['id']);
                $stats = $daemon->getServerStats();
                $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM gameroot_avg WHERE `date` < DATE_ADD(NOW(), INTERVAL -1 DAY)');

                $this->container->get('doctrine.dbal.default_connection')->insert('gameroot_avg', [
                    'hostID' => $server['id'],
                    'loadavg' => $stats->getLoadAvg(1),
                    'date' => \date('Y-m-d H:i:s'),
                ]);

                $this->container->get('doctrine.dbal.default_connection')->update('gameroot', [
                    'curRam' => $stats->getUsedMemory(),
                    'freeRam' => $stats->getAvailableMemory(),
                    'maxRam' => $stats->getTotalMemory(),
                    'curCpu' => $stats->getLoadAvg(1),
                    'cpus' => $stats->getCpuCount(),
                ], [
                    'id' => $server['id'],
                ]);

                $avg = (float) $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT AVG(loadavg) FROM gameroot_avg WHERE hostID = ?', [$server['id']]);
                $maxAvg = $stats->getCpuCount() * 1.5;
                $avgPercent = $avg / $maxAvg;

                if ($avgPercent >= 1) {
                    $avgPercent = 1;
                }

                $hosts[] = [
                    'id' => $server['id'],
                    'ip' => $server['sshIp'],
                    'description' => $server['description'],
                    'avg' => \round($avgPercent, 2),
                    'memory' => \round($stats->getUsedMemory() / $stats->getTotalMemory(), 2),
                ];
            }
        }

        $this->container->get('cache')->set('indexServer', ['hosts' => $hosts]);
        $this->container->get('cache')->set('indexStats', [
            'userCount' => $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) AS userCount FROM users'),
            'activeCount' => $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) AS activeCount FROM gameserver WHERE Typ = 0'),
            'passiveCount' => $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) AS passiveCount FROM gameserver WHERE Typ = 1'),
            'onlineServer' => $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) AS online FROM gameserver WHERE BannerOn = 1'),
        ]);

        $status = $this->doRequest('https://status.gameserver-sponsor.me/api/v1/incidents?status=1');
        $statusA = $this->doRequest('https://status.gameserver-sponsor.me/api/v1/incidents?status=2');
        $statusB = $this->doRequest('https://status.gameserver-sponsor.me/api/v1/incidents?status=3');

        if (\is_array($status)) {
            $indicents = \array_merge($status['data'], $statusA['data'], $statusB['data']);
            $this->container->get('cache')->set('incidents', $indicents);
        }

        return true;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function doRequest($url)
    {
        $ch = \curl_init($url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Cachet-Token: dN5s50sDWn1EauvGVUQ3',
        ]);

        $response = \json_decode(\curl_exec($ch), true);

        \curl_close($ch);

        return $response;
    }
}
