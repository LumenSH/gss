<?php

namespace GSS\Component\Cron;

use GSS\Component\Hosting\Gameserver\Gameserver;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DeleteExpiratedServers implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $gameservers = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id FROM gameserver WHERE Duration < ? AND Typ = 0', [\time()]);

        foreach ($gameservers as $gameserver) {
            $gs = Gameserver::createServer($this->container, $gameserver['id']);
            $gs->delete();
        }

        return true;
    }
}
