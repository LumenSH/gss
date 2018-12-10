<?php

namespace GSS\Component\Cron;

use GameQ\GameQ;
use GSS\Component\Commerce\GP;
use GSS\Component\Hosting\GameQUtil;
use GSS\Component\Structs\GameQResult;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ServerPlayer implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $gameservers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT gameserver.id, gameserver.userID as Owner, products.internalName as Game, gameserver.Port, gameroot_ip.IP FROM gameserver
            INNER JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gamerootIpID)
            INNER JOIN products ON(products.id = gameserver.productID)
            WHERE Typ = 0 and products.banner = 1
        ');
        $idGameservers = [];

        $gameserverChunk = \array_chunk($gameservers, 10);

        foreach ($gameserverChunk as $gameservers) {
            $gameQClient = new GameQ();
            $gameQClient->setOption('timeout', 4);
            $gameQClient->addFilter('normalise');

            foreach ($gameservers as $gameserver) {
                GameQUtil::addServer($gameQClient, $gameserver);
                $idGameservers[$gameserver['id']] = $gameserver;
            }

            $results = $gameQClient->process();
            $gpPointsPerUser = $this->container->getParameter('gppoints.serverplayer');

            foreach ($results as $key => $gameserver) {
                $gameserver = new GameQResult($gameserver);

                if ($gameserver->isOnline()) {
                    if ($gameserver->getCurrentPlayers() > 0) {
                        $points = $gameserver->getCurrentPlayers() * $gpPointsPerUser;
                        $this->container->get(GP::class)->addPointsToUser($idGameservers[$key]['Owner'], $points, 'Player');
                    }
                }
            }
        }

        return true;
    }
}
