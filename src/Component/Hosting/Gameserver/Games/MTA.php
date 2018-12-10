<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;

/**
 * Class MTA.
 */
class MTA extends Gameserver
{
    public function install($version = 'default')
    {
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
        $this->getSSH()->exec('cp -R /imageserver/games/' . $this->getGame() . '/resources ' . $this->calcServerDirectory('mods/deathmatch'));
    }

    /**
     * @return array
     */
    public function getStartArguments()
    {
        return [
            '--ip',
            $this->getIp(),
            '--port',
            $this->getPort(),
            '--httpport',
            $this->getPort(),
            '--maxplayers',
            $this->getSlot(),
            '-n',
            '-u',
        ];
    }

    public function getConfigFiles()
    {
        return [
            [
                'name' => 'acl.xml',
                'path' => 'mods/deathmatch/acl.xml',
                'type' => 'xml',
            ],
            [
                'name' => 'mtaserver.conf',
                'path' => 'mods/deathmatch/mtaserver.conf',
                'type' => 'xml',
            ],
        ];
    }
}
