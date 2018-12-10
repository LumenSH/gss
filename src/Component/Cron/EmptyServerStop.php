<?php

namespace GSS\Component\Cron;

use GameQ\GameQ;
use GSS\Component\Hosting\GameQUtil;
use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Structs\GameQResult;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class EmptyServerStop
 */
class EmptyServerStop implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function start(): bool
    {
        $gameservers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('
            SELECT gameserver.id, products.internalName as Game, gameserver.port as Port, gameroot_ip.IP FROM gameserver
            INNER JOIN gameroot_ip ON(gameroot_ip.id = gameserver.gamerootIpID)
            INNER JOIN products ON(products.id = gameserver.productID)
            WHERE products.banner = 1 and gameserver.typ = 1
        ');

        $gameserverChunk = \array_chunk($gameservers, 10);

        foreach ($gameserverChunk as $gameservers) {
            $gameQClient = new GameQ();
            $gameQClient->setOption('timeout', 4);
            $gameQClient->addFilter('normalise');

            foreach ($gameservers as $gameserver) {
                GameQUtil::addServer($gameQClient, $gameserver);
            }

            $results = $gameQClient->process();

            foreach ($results as $key => $gameserver) {
                $gameserver = new GameQResult($gameserver);

                if ($gameserver->getCurrentPlayers() === 0 && $gameserver->isOnline()) {
                    $this->addServerStop($key);
                }
            }
        }

        return true;
    }

    /**
     * @param int $id
     */
    private function addServerStop(int $id)
    {
        $gs = Gameserver::createServer($this->container, $id);
        $gs->stop();
    }
}
