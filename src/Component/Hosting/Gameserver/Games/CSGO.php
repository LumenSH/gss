<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;

/**
 * Class CSGO.
 */
class CSGO extends Gameserver
{
    /**
     * @return array
     */
    public function getConfigFiles()
    {
        return [
            [
                'name' => 'server.cfg',
                'path' => 'csgo/cfg/server.cfg',
                'type' => 'text',
            ],
        ];
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartArguments()
    {
        return [
            '-game',
            'csgo',
            '-console',
            '-port',
            $this->getPort(),
            '-maxplayers',
            $this->getSlot(),
            '-ip',
            $this->getIp(),
        ];
    }

    /**
     * Update Server
     *
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function updateServer($version = 'default')
    {
        $this->stop();
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/', $this->calcServerDirectory());
        $this->getDaemon()->clearLogs($this->getId());
        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', ['state' => 0], ['id' => $this->getId()]);
        $this->updateStartParams();
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getForm()
    {
        return [
            [
                'name' => 'game_type',
                'startParam' => '+game_type',
                'type' => 'select',
                'options' => [
                    0 => 'Classic',
                    1 => 'Arsenal',
                ],
                'label' => 'Spiel-Typ',
                'defaultValue' => 0,
            ],
            [
                'name' => 'game_mode',
                'startParam' => '+game_mode',
                'type' => 'select',
                'options' => [
                    0 => 'Casual/Arms Race',
                    1 => 'Competitive/Demolition',
                    2 => 'Deathmatch',
                ],
                'label' => 'Gamemode',
                'defaultValue' => 0,
            ],
            [
                'name' => 'mapgroup',
                'startParam' => '+mapgroup',
                'type' => 'text',
                'label' => 'Start Map-Gruppe',
                'defaultValue' => 'mg_bomb',
            ],
            [
                'name' => 'map',
                'startParam' => '+map',
                'type' => 'text',
                'label' => 'Start-Map',
                'defaultValue' => 'de_dust',
            ],
            [
                'name' => 'steam_token',
                'startParam' => '+sv_setsteamaccount',
                'type' => 'text',
                'label' => 'Steam Gameserver Login Token',
                'defaultValue' => '',
            ],
            [
                'name' => 'insecure',
                'startParam' => '-insecure',
                'type' => 'boolean',
                'label' => '-insecure',
                'defaultValue' => 0,
            ],
        ];
    }
}
