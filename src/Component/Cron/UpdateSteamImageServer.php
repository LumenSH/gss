<?php

namespace GSS\Component\Cron;

use GSS\Component\Hosting\SSH;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateSteamImageServer implements CronInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function start(): bool
    {
        $products = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM products WHERE steamID IS NOT NULL AND steamID != ""');
        $hostIDs = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT id FROM gameroot');

        foreach ($hostIDs as $hostID) {
            $ssh = new SSH($hostID['id']);

            foreach ($products as $product) {
                $ssh->exec('cd /home/steam/steamcmd/ && screen -dmS steam_update_' . $product['internalName'] . ' ./steamcmd.sh +login anonymous +force_install_dir /imageserver/games/' . $product['internalName'] . '/ +app_update ' . $product['steamID'] . ' +quit');
            }
        }

        return true;
    }
}
