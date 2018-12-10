<?php

namespace GSS\Component\Hosting\Gameserver\Games;

use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSHUtil;

/**
 * Class TERRARIA
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class TERRARIA extends Gameserver
{
    private $config = '{
  "InvasionMultiplier": 1,
  "DefaultMaximumSpawns": 5,
  "DefaultSpawnRate": 600,
  "ServerPort": %s,
  "EnableWhitelist": false,
  "InfiniteInvasion": false,
  "PvPMode": "normal",
  "SpawnProtection": true,
  "SpawnProtectionRadius": 10,
  "MaxSlots": %s,
  "RangeChecks": true,
  "DisableBuild": false,
  "SuperAdminChatRGB": [
    255,
    0,
    0
  ],
  "SuperAdminChatPrefix": "(Admin) ",
  "SuperAdminChatSuffix": "",
  "BackupInterval": 0,
  "BackupKeepFor": 60,
  "RememberLeavePos": false,
  "HardcoreOnly": false,
  "MediumcoreOnly": false,
  "KickOnMediumcoreDeath": false,
  "BanOnMediumcoreDeath": false,
  "AutoSave": true,
  "AnnounceSave": true,
  "MaximumLoginAttempts": 3,
  "ServerName": "",
  "UseServerName": false,
  "StorageType": "sqlite",
  "MySqlHost": "localhost:3306",
  "MySqlDbName": "",
  "MySqlUsername": "",
  "MySqlPassword": "",
  "MediumcoreBanReason": "Death results in a ban",
  "MediumcoreKickReason": "Death results in a kick",
  "EnableIPBans": true,
  "EnableUUIDBans": true,
  "EnableBanOnUsernames": false,
  "DefaultRegistrationGroupName": "default",
  "DefaultGuestGroupName": "guest",
  "DisableSpewLogs": true,
  "DisableSecondUpdateLogs": false,
  "HashAlgorithm": "sha512",
  "ServerFullReason": "Server is full",
  "WhitelistKickReason": "You are not on the whitelist.",
  "ServerFullNoReservedReason": "Server is full. No reserved slots open.",
  "SaveWorldOnCrash": true,
  "EnableGeoIP": false,
  "EnableTokenEndpointAuthentication": false,
  "RestApiEnabled": true,
  "RestApiPort": %s,
  "DisableTombstones": true,
  "DisplayIPToAdmins": false,
  "KickProxyUsers": true,
  "DisableHardmode": false,
  "DisableDungeonGuardian": false,
  "DisableClownBombs": false,
  "DisableSnowBalls": false,
  "ChatFormat": "{1}{2}{3}: {4}",
  "ChatAboveHeadsFormat": "{2}",
  "ForceTime": "normal",
  "TileKillThreshold": 60,
  "TilePlaceThreshold": 20,
  "TileLiquidThreshold": 15,
  "ProjectileThreshold": 50,
  "HealOtherThreshold": 50,
  "ProjIgnoreShrapnel": true,
  "RequireLogin": false,
  "DisableInvisPvP": false,
  "MaxRangeForDisabled": 10,
  "ServerPassword": "",
  "RegionProtectChests": false,
  "RegionProtectGemLocks": true,
  "DisableLoginBeforeJoin": false,
  "DisableUUIDLogin": false,
  "KickEmptyUUID": false,
  "AllowRegisterAnyUsername": false,
  "AllowLoginAnyUsername": true,
  "MaxDamage": 1175,
  "MaxProjDamage": 1175,
  "KickOnDamageThresholdBroken": false,
  "IgnoreProjUpdate": false,
  "IgnoreProjKill": false,
  "IgnoreNoClip": false,
  "AllowIce": false,
  "AllowCrimsonCreep": true,
  "AllowCorruptionCreep": true,
  "AllowHallowCreep": true,
  "StatueSpawn200": 3,
  "StatueSpawn600": 6,
  "StatueSpawnWorld": 10,
  "PreventBannedItemSpawn": false,
  "PreventDeadModification": true,
  "EnableChatAboveHeads": false,
  "ForceXmas": false,
  "AllowAllowedGroupsToSpawnBannedItems": false,
  "IgnoreChestStacksOnLoad": false,
  "LogPath": "tshock",
  "UseSqlLogs": false,
  "RevertToTextLogsOnSqlFailures": 10,
  "PreventInvalidPlaceStyle": true,
  "BroadcastRGB": [
    127,
    255,
    212
  ],
  "ApplicationRestTokens": {},
  "ReservedSlots": 20,
  "LogRest": false,
  "RespawnSeconds": 5,
  "RespawnBossSeconds": 10,
  "TilePaintThreshold": 15,
  "ForceHalloween": false,
  "AllowCutTilesAndBreakables": false,
  "CommandSpecifier": "/",
  "CommandSilentSpecifier": ".",
  "KickOnHardcoreDeath": false,
  "BanOnHardcoreDeath": false,
  "HardcoreBanReason": "Death results in a ban",
  "HardcoreKickReason": "Death results in a kick",
  "AnonymousBossInvasions": true,
  "MaxHP": 500,
  "MaxMP": 200,
  "SaveWorldOnLastPlayerExit": true,
  "BCryptWorkFactor": 7,
  "MinimumPasswordLength": 4,
  "RESTMaximumRequestsPerInterval": 5,
  "RESTRequestBucketDecreaseIntervalMinutes": 1,
  "RESTLimitOnlyFailedLoginRequests": true,
  "ShowBackupAutosaveMessages": true
}';

    /**
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function install($version = 'default')
    {
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
        $this->getSSH()->exec('mkdir ' . $this->calcServerDirectory('tshock'));
        $this->getSSH()->put($this->calcServerDirectory('tshock/config.json'), \sprintf($this->config, $this->getPort(), $this->getSlot(), $this->getPort() + 1));
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
                'name' => 'config.json',
                'path' => 'tshock/config.json',
                'type' => 'javascript',
            ],
        ];
    }

    public function checkConfigFile($configFile, $configValue)
    {
        $data = \json_decode($configValue, true);

        if ($data['ServerPort'] == $this->getPort() &&
            $data['MaxSlots'] == $this->getSlot() &&
            $data['RestApiEnabled'] == true &&
            $data['RestApiPort'] == ($this->getPort() + 1)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartArguments()
    {
        return [
            'TerrariaServer.exe',
            '-ip',
            $this->getIp(),
            '-port',
            $this->getPort(),
            '-maxplayers',
            $this->getSlot(),
            '-world',
            $this->calcServerDirectory('Terraria/Worlds/World.wld'),
            '-autocreate',
            '2',
        ];
    }
}
