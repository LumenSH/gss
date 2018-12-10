<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;
use GSS\Component\Reader\CfgReader;

class SAMP extends Gameserver
{
    private $configTemplate = 'echo Executing Server Config...
lanmode 0
rcon_password iamgsuser
maxplayers %s
port %s
hostname Default Gameserver-Sponsor SA-MP Server
gamemode0 grandlarc 1
filterscripts base gl_actions gl_property gl_realtime
announce 0
query 1
weburl www.gameserver-sponsor.me
maxnpc 0
onfoot_rate 40
incar_rate 40
weapon_rate 40
stream_distance 300.0
stream_rate 1000
bind %s';

    public function install($version = 'default')
    {
        $this->getSSH()->put($this->calcServerDirectory('server.cfg'), $this->getConfig());
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
    }

    public function getConfigFiles()
    {
        return [
            [
                'name' => 'server.cfg',
                'path' => 'server.cfg',
                'type' => 'text',
            ],
        ];
    }

    public function checkConfigFile($configFile, $configValue)
    {
        $cfg = new CfgReader($configValue);

        if (
            $cfg->get('bind') != $this->getIp() ||
            $cfg->get('maxplayers') != $this->getSlot() ||
            $cfg->get('port') != $this->getPort()
        ) {
            return false;
        }

        return true;
    }

    private function getConfig()
    {
        return \sprintf(
            $this->configTemplate,
            $this->getSlot(),
            $this->getPort(),
            $this->getIp()
        );
    }
}
