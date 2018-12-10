<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;

class GTA5MP extends Gameserver
{
    private $configTemplate = '<?xml version="1.0"?>
<config xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <servername>Simple Grand Theft Multiplayer Server | Sponsored by gameserver-sponsor.me</servername>
  <serverport>%s</serverport>
  <maxplayers>%s</maxplayers>
  <local_address>%s</local_address>
  <minclientversion>0.0.0.0</minclientversion>
  <minclientversion_auto_update>true</minclientversion_auto_update>
  <announce>true</announce>
  <password></password>
  <masterserver>http://master.mta-v.net/api/</masterserver>
  <acl_enabled>true</acl_enabled>
  <loglevel>0</loglevel>
  <log>true</log>
  <global_streaming_range>175</global_streaming_range>
  <player_streaming_range>500</player_streaming_range>
  <vehicle_streaming_range>250</vehicle_streaming_range>
  <vehicle_lagcomp>true</vehicle_lagcomp>
  <onfoot_lagcomp>true</onfoot_lagcomp>
  <refresh_rate>120</refresh_rate>
  <resource src="admin" />
  <resource src="freeroam" />
  <resource src="speedometer" />
  <announce_lan>true</announce_lan>
  <upnp>false</upnp>
  <fqdn></fqdn>
  <conntimeout>false</conntimeout>
  <allow_client_devtools>false</allow_client_devtools>
</config>';

    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function install($version = 'default')
    {
        $this->getSSH()->put($this->calcServerDirectory('settings.xml'), $this->getConfig());
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
        $this->getSSH()->exec('cp -R /imageserver/games/' . $this->getGame() . '/resources ' . $this->calcServerDirectory());
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
                'name' => 'settings.xml',
                'path' => 'settings.xml',
                'type' => 'xml',
            ],
        ];
    }

    public function getStartArguments()
    {
        return [
            'GrandTheftMultiplayer.Server.exe',
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
        $xml = \simplexml_load_string($configValue);

        if (
            $xml->xpath('maxplayers')[0]->__toString() != $this->getSlot() ||
            $xml->xpath('serverport')[0]->__toString() != $this->getPort() ||
            $xml->xpath('local_address')[0]->__toString() != $this->getIp()
        ) {
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
            $this->getPort(),
            $this->getSlot(),
            $this->getIp()
        );
    }
}
