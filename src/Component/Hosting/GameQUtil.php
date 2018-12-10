<?php

namespace GSS\Component\Hosting;

use GameQ\GameQ;

/**
 * Class GameQUtil
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class GameQUtil
{
    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public static $gameQFilter = [
        'mc' => 'minecraft',
        'gta5mp' => 'gtmp',
        'fivem' => 'gta5m',
    ];

    /**
     * @param GameQ $gameQ
     * @param array $server
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public static function addServer(GameQ $gameQ, array $server): void
    {
        $basic = [
            'type' => (!empty(self::$gameQFilter[$server['Game']]) ? self::$gameQFilter[$server['Game']] : $server['Game']),
            'host' => $server['IP'] . ':' . $server['Port'],
            'id' => $server['id'],
        ];

        switch ($server['Game']) {
            case 'terraria':
                $basic['host'] = $server['IP'] . ':' . ($server['Port'] - 100);
                break;
        }

        $gameQ->addServer($basic);
    }
}
