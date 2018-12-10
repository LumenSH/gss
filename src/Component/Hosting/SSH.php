<?php

namespace GSS\Component\Hosting;

use GSS\Component\Exception\Hosting\ServerConnectionException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;

/**
 * Class SSH
 */
class SSH
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $user;

    /**
     * @var array
     */
    private $hostData;

    /**
     * @var SSH2
     */
    private $ssh;
    /**
     * @var SFTP
     */
    private $sftp;

    /**
     * @var bool
     */
    private $isConnectedSSH = false;

    /**
     * @var bool
     */
    private $isConnectedSFTP = false;

    /**
     * @var RSA
     */
    private $sshKey;

    public function __construct($hostID = null, $username = null)
    {
        global $kernel;
        $hostDetails = $kernel->getContainer()->get('doctrine.dbal.default_connection')->fetchAssoc('SELECT * FROM gameroot WHERE id = ?', [$hostID]);
        $this->ip = $hostDetails['sshIp'];
        $this->port = $hostDetails['sshPort'];
        $this->user = ($username ?? $hostDetails['sshUser']);
        $this->hostData = $hostDetails;

        $this->sshKey = new RSA();
        $this->sshKey->loadKey(\file_get_contents($kernel->getContainer()->getParameter('kernel.root_dir') . '/Component/Hosting/Resources/ssh'));
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    public function connectSSH()
    {
        $this->ssh = new SSH2($this->ip, $this->port);

        if (!$this->ssh->login($this->user, $this->sshKey)) {
            throw new ServerConnectionException('The Gameserver is currently unreachable (IP: ' . $this->ip . ', ' . $this->user . ')');
        }
        $this->isConnectedSSH = true;
    }

    public function connectSFTP()
    {
        $this->sftp = new SFTP($this->ip, $this->port);
        if (!$this->sftp->login($this->user, $this->sshKey)) {
            throw new ServerConnectionException('The Gameserver is currently unreachable (IP: ' . $this->ip . ', ' . $this->user . ')');
        }
        $this->isConnectedSFTP = true;
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getHostname()
    {
        return $this->hostData['hostname'];
    }

    public function exec($cmd)
    {
        if (!$this->isConnectedSSH) {
            $this->connectSSH();
        }

        $result = $this->ssh->exec($cmd);

        if (\defined('CLI_DEBUG')) {
            \var_dump($result);
        }

        return $result;
    }

    public function get($filename)
    {
        if (!$this->isConnectedSFTP) {
            $this->connectSFTP();
        }

        return $this->sftp->get($filename);
    }

    public function put($filename, $data)
    {
        if (!$this->isConnectedSFTP) {
            $this->connectSFTP();
        }

        return $this->sftp->put($filename, $data);
    }

    /**
     * @param $folder
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function listContents($folder)
    {
        if (!$this->isConnectedSFTP) {
            $this->connectSFTP();
        }

        return $this->sftp->nlist($folder);
    }

    public function closeConnection()
    {
        if ($this->isConnectedSSH) {
            $this->ssh->disconnect();
        }
    }

    public function canPassiveServerStarted()
    {
        if ($this->hostData['freeRam'] < 1000 || $this->hostData['curRam'] > ($this->hostData['cpus'] * 1.5)) {
            return false;
        }

        return true;
    }
}
