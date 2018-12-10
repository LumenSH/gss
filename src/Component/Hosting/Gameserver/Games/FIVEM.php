<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Reader\CfgReader;

/**
 * Class FIVEM
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class FIVEM extends Gameserver
{
    private $configTemplate = 'endpoint_add_tcp "%s:%s"
endpoint_add_udp "%s:%s"

start mapmanager
start chat
start spawnmanager
start sessionmanager
start fivem
start hardcap
start rconlog
start scoreboard

sv_scriptHookAllowed 1

# change this
#rcon_password yay

sv_hostname "My new FXServer!"

# nested configs!
#exec server_internal.cfg

# loading a server icon (96x96 PNG file)
#load_server_icon myLogo.png

# convars for use from script
set temp_convar "hey world!"

# disable announcing? clear out the master by uncommenting this
#sv_master1 ""

# want to only allow players authenticated with a third-party provider like Steam?
#sv_authMaxVariance 1
#sv_authMinTrust 5

# add system admins
add_ace group.admin command allow # allow all commands
add_ace group.admin command.quit deny # but don\'t allow quit
add_principal identifier.steam:110000112345678 group.admin # add the admin to the group

# hide player endpoints in external log output
#sv_endpointprivacy true

# server slots limit (default to 24)
sv_maxclients %s';

    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function install($version = 'default')
    {
        $this->getSSH()->put($this->calcServerDirectory('server.cfg'), $this->getConfig());
        $this->getSSH()->exec('cp -R /imageserver/games/' . $this->getGame() . '/server-data/* ' . $this->calcServerDirectory());
    }

    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function updateServer($version = 'default')
    {
        $this->stop();
        $this->gsData['version'] = $version;
        $this->updateStartParams();
        $this->getDaemon()->clearLogs($this->getId());
        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', ['state' => 0], ['id' => $this->getId()]);
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
                'name' => 'server.cfg',
                'path' => 'server.cfg',
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
            '/imageserver/games/fivem/' . $this->getCurrentVersion() . '/run.sh',
            '+exec',
            'server.cfg',
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
        $cfgReader = new CfgReader($configValue);
        $ip = '"' . $this->getIp() . ':' . $this->getPort() . '"';

        if ($cfgReader->get('endpoint_add_tcp') !== $ip) {
            return false;
        }

        if ($cfgReader->get('sv_maxclients') > $this->getSlot()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getConfig()
    {
        return \sprintf(
            $this->configTemplate,
            $this->getIp(),
            $this->getPort(),
            $this->getIp(),
            $this->getPort(),
            $this->getSlot()
        );
    }
}
