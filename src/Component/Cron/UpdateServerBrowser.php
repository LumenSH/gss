<?php

namespace GSS\Component\Cron;

use GameQ\GameQ;
use GSS\Component\Hosting\GameQUtil;
use GSS\Component\Structs\GameQResult;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateServerBrowser implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $gameservers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT gameserver.id, products.internalName as Game, gameserver.port as Port, gameroot_ip.IP FROM gameserver
            INNER JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gamerootIpID)
            INNER JOIN products ON(products.id = gameserver.productID)
            WHERE products.banner = 1 and gameserver.typ = 0
        ');

        $serverChunks = \array_chunk($gameservers, 10);

        foreach ($serverChunks as $gameservers) {
            $gameQClient = new GameQ();
            $gameQClient->setOption('timeout', 4);
            $gameQClient->addFilter('normalise');

            foreach ($gameservers as $gameserver) {
                GameQUtil::addServer($gameQClient, $gameserver);
            }

            $results = $gameQClient->process();

            foreach ($results as $key => $gameserver) {
                $gameserver = new GameQResult($gameserver);

                $id = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT id FROM gameserver_browse WHERE serverID = ?', [$key]);

                $updateArray = [
                    'serverID' => $key,
                    'online' => (int) $gameserver->isOnline(),
                    'name' => $gameserver->getHostname(),
                    'cur_players' => $gameserver->getCurrentPlayers(),
                    'gamemode' => $gameserver->getGame(),
                    'gametype' => $gameserver->getGameType(),
                    'gamemap' => $gameserver->getMapName(),
                ];

                if (empty($updateArray['name'])) {
                    $updateArray['name'] = 'no name';
                }

                if ((int) $gameserver->isOnline() === 0 && !empty($id)) {
                    $updateArray = [
                        'serverID' => $key,
                        'online' => 0,
                        'cur_players' => 0,
                    ];
                }

                if ($id) {
                    try {
                        $this->container->get('doctrine.dbal.default_connection')->update('gameserver_browse', $updateArray, ['serverID' => $key]);
                    } catch (\Exception $e) {
                        $this->container->get('doctrine.dbal.default_connection')->update('gameserver_browse', ['online' => 0], ['serverID' => $key]);
                    }
                } else {
                    $this->container->get('doctrine.dbal.default_connection')->insert('gameserver_browse', $updateArray);
                }
            }
        }

        $cache = $this->container->get('cache.app')->getItem('server_browse.time');
        $cache->set(\date('Y-m-d H:i:s'));
        $this->container->get('cache.app')->save($cache);

        return true;
    }
}
