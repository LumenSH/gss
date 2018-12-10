<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;

class FACTORIO extends Gameserver
{
    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function install($version = 'default')
    {
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());

        // Config
        $config = \json_decode($this->getSSH()->get($this->calcServerDirectory('data/server-settings.example.json')), true);
        $config['max_players'] = $this->getSlot();
        $this->getSSH()->put($this->calcServerDirectory('data/server-settings.json'), \json_encode($config, JSON_PRETTY_PRINT));

        // Generate default map required
        $this->getSSH()->exec('cd ' . $this->calcServerDirectory() . ' && ./bin/x64/factorio --create saves/default');

        // Default config files
        $this->getSSH()->exec('cp ' . $this->calcServerDirectory('data/server-whitelist.example.json') . ' ' . $this->calcServerDirectory('data/server-whitelist.json'));
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartArguments()
    {
        return [
            '--bind',
            $this->getIp(),
            '--port',
            $this->getPort(),
            '--rcon-port',
            $this->getPort() + 1,
            '--rcon-password',
            \md5($this->getId()),
            '--server-settings',
            $this->calcServerDirectory('data/server-settings.json'),
            '--server-whitelist',
            $this->calcServerDirectory('data/server-whitelist.json'),
            '--start-server',
            $this->calcServerDirectory('saves/default'),
        ];
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getConfigFiles()
    {
        return [
            [
                'name' => 'server-settings.json',
                'path' => 'data/server-settings.json',
                'type' => 'javascript',
            ],
            [
                'name' => 'server-whitelist.json',
                'path' => 'data/server-whitelist.json',
                'type' => 'javascript',
            ],
        ];
    }

    /**
     * @param $configFile
     * @param $configValue
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function checkConfigFile($configFile, $configValue)
    {
        if ($configFile === 'server-settings.json') {
            $config = \json_decode($configValue, true);

            if ($config['max_players'] != $this->getSlot()) {
                return false;
            }
        }

        return true;
    }
}
