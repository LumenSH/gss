<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;

/**
 * Class GMOD
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class GMOD extends Gameserver
{
    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getConfigFiles()
    {
        return [
            [
                'name' => 'server.cfg',
                'path' => 'garrysmod/cfg/server.cfg',
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
            '-console',
            '-game',
            'garrysmod',
            '+maxplayers',
            $this->getSlot(),
            '-port',
            $this->getPort(),
            '-ip',
            $this->getIp(),
        ];
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
                'name' => 'map',
                'type' => 'text',
                'label' => 'Start-Map',
                'startParam' => '+map',
                'defaultValue' => 'gm_flatgrass',
            ],
            [
                'name' => 'authkey',
                'type' => 'text',
                'label' => 'Steam Auth Key',
                'startParam' => '-authkey',
                'defaultValue' => '',
            ],
            [
                'name' => 'host_workshop_collection',
                'type' => 'text',
                'label' => 'Workshop Collection',
                'startParam' => '+host_workshop_collection',
                'defaultValue' => '',
            ],
            [
                'name' => 'steam_token',
                'type' => 'text',
                'label' => 'Steam Gameserver Login Token',
                'startParam' => '+sv_setsteamaccount',
                'defaultValue' => '',
            ],
        ];
    }
}
