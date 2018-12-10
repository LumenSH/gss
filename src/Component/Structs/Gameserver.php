<?php

namespace GSS\Component\Structs;

use GSS\Component\Api\RateLimiting;
use GSS\Component\Hosting\Gameserver\Daemon;
use GSS\Component\Hosting\SSH;
use GSS\Component\Hosting\SSHUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Gameserver
{
    const ACTIVE_SERVER = 0;
    const PASSIVE_SERVER = 1;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $gsData = [];

    /**
     * @var SSH
     */
    protected $ssh;

    /**
     * @var Daemon
     */
    protected $daemon;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->gsData['id'];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->gsData;
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getTyp(): int
    {
        return $this->gsData['typ'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getOwner(): int
    {
        return $this->gsData['userID'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getSlot(): int
    {
        return $this->gsData['slot'];
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCreatedAt(): string
    {
        return $this->gsData['createdAt'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getDuration(): int
    {
        return $this->gsData['duration'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getPrice(): int
    {
        return $this->gsData['price'];
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGame(): string
    {
        return $this->gsData['game'];
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getIp(): string
    {
        return $this->gsData['IP'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getPort(): int
    {
        return $this->gsData['port'];
    }

    /**
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isActiveServer(): bool
    {
        return $this->gsData['typ'] == self::ACTIVE_SERVER;
    }

    /**
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isPassiveServer(): bool
    {
        return $this->gsData['typ'] == self::PASSIVE_SERVER;
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getState()
    {
        return $this->gsData['state'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCurrentVersion()
    {
        return $this->gsData['version'];
    }

    /**
     * Returns a active ssh session
     *
     * @return SSH
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getSSH()
    {
        return $this->ssh;
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getHost()
    {
        return $this->gsData['gameRootID'];
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getHostname()
    {
        return $this->gsData['hostname'];
    }

    /**
     * Calculates relative path to absolute path
     *
     * @param string $dir
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function calcServerDirectory($dir = ''): string
    {
        return '/home/user' . $this->getOwner() . '/' . $this->gsData['game'] . '_' . \str_replace('.', '_', $this->gsData['IP']) . '_' . $this->gsData['port'] . '/' . $dir;
    }

    /**
     * Returns the Short Servername
     *
     * @param bool $screenName
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getShort($screenName = false)
    {
        if ($screenName) {
            return \str_replace('.', '_', $this->gsData['IP']) . '_' . $this->gsData['port'];
        }

        return 'view/' . $this->getId();
    }

    public function install($version = 'default')
    {
        $sshClient = $this->getSSH();
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/', $this->calcServerDirectory());
        $sshClient->exec('echo "1" > ' . $this->calcServerDirectory('installed'));
    }

    /**
     * @return Daemon
     */
    public function getDaemon()
    {
        if ($this->daemon !== null) {
            return $this->daemon;
        }

        $this->daemon = new Daemon($this->container, $this->getHost());

        return $this->daemon;
    }

    /**
     * @return array
     */
    public function getStartArguments()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getConfigFiles()
    {
        return [];
    }

    /**
     * @param $configFile
     * @param $configValue
     *
     * @return bool
     */
    public function checkConfigFile($configFile, $configValue)
    {
        return true;
    }

    /**
     * @return array
     */
    public function getForm()
    {
        return [];
    }

    /**
     * @throws \GSS\Component\Exception\Hosting\DaemonException
     *
     * @return bool
     */
    public function stop(): bool
    {
        if ($this->isPassiveServer()) {
            $this->container->get(RateLimiting::class)->rateLimit($this->getId());
        }

        return $this->getDaemon()->stopServer($this->getId());
    }

    /**
     * @throws \GSS\Component\Exception\Hosting\DaemonException
     *
     * @return GameserverStats
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStats()
    {
        return $this->getDaemon()->getGameserverStats($this->getId());
    }
}
