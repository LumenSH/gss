<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;
use GSS\Component\Reader\McReader;

/**
 * Class MC.
 */
class MC extends Gameserver
{
    private $configTemplate = 'generator-settings=
level-name=world
enable-query=true
allow-flight=false
server-port=%s
query.port=%s
level-type=DEFAULT
enable-rcon=false
level-seed=
server-ip=%s
max-build-height=256
spawn-npcs=true
white-list=false
spawn-animals=true
hardcore=false
texture-pack=
online-mode=true
pvp=true
difficulty=1
gamemode=0
max-players=%s
spawn-monsters=true
generate-structures=true
view-distance=10
motd=Gesponsort von Gameserver-Sponsor';

    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function install($version = 'default')
    {
        $this->getSSH()->put($this->calcServerDirectory('server.properties'), $this->getConfig());
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartArguments()
    {
        $properties = \json_decode($this->getData()['properties'], true);

        return [
            '-Xincgc',
            '-Xms' . $properties['ram'] . 'M',
            '-Xmx' . $properties['ram'] . 'M',
            '-jar',
            'minecraft.jar',
        ];
    }

    public function getConfigFiles()
    {
        return [
            [
                'name' => 'server.properties',
                'path' => 'server.properties',
                'type' => 'text',
            ],
            [
                'name' => 'eula.txt',
                'path' => 'eula.txt',
                'type' => 'text',
            ],
        ];
    }

    public function checkConfigFile($configFile, $configValue)
    {
        if ($configFile === 'server.properties') {
            $cfg = new McReader($configValue);

            if (
                $cfg->get('server-ip') != $this->getIp() ||
                $cfg->get('max-players') > $this->getSlot() ||
                $cfg->get('server-port') != $this->getPort() ||
                $cfg->get('query.port') != $this->getPort() ||
                $cfg->get('enable-query') != 'true'
            ) {
                return false;
            }

            return true;
        }

        return true;
    }

    private function getConfig()
    {
        return \sprintf(
            $this->configTemplate,
            $this->getPort(),
            $this->getPort(),
            $this->getIp(),
            $this->getSlot()
        );
    }
}
