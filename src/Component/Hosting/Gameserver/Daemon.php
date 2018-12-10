<?php

namespace GSS\Component\Hosting\Gameserver;

use Doctrine\DBAL\Connection;
use GSS\Component\Exception\Hosting\DaemonException;
use GSS\Component\Structs\GameserverStats;
use GSS\Component\Structs\ServerStats;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Daemon
 */
class Daemon
{
    /**
     * @var string
     */
    const API_VERSION = 'v1';
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var string
     */
    private $daemonUsername;

    /**
     * @var string
     */
    private $daemonPassword;

    /**
     * Daemon constructor.
     *
     * @param ContainerInterface $container
     * @param int                $hostId
     */
    public function __construct(ContainerInterface $container, int $hostId)
    {
        $this->container = $container;
        $this->connection = $container->get('doctrine.dbal.default_connection');
        $hostDetails = $this->connection->fetchAssoc('SELECT hostname, daemonUsername, daemonPassword FROM gameroot WHERE id = ?', [
            $hostId,
        ]);

        $this->hostname = $hostDetails['hostname'];
        $this->daemonUsername = $hostDetails['daemonUsername'];
        $this->daemonPassword = $hostDetails['daemonPassword'];
    }

    /**
     * @param $id
     *
     * @throws DaemonException
     *
     * @return bool
     */
    public function startServer(int $id): bool
    {
        return $this->doRequest('POST', 'gameserver/start', [
            'id' => $id,
        ])['success'];
    }

    /**
     * @param $id
     *
     * @throws DaemonException
     *
     * @return bool
     */
    public function stopServer($id): bool
    {
        return $this->doRequest('POST', 'gameserver/stop', [
            'id' => $id,
        ])['success'];
    }

    /**
     * @param $id
     *
     * @throws DaemonException
     */
    public function clearLogs($id): void
    {
        $this->doRequest('POST', 'gameserver/clear', [
            'id' => $id,
        ]);
    }

    /**
     * @param int $id
     *
     * @throws DaemonException
     *
     * @return string
     */
    public function getToken(int $id): string
    {
        return $this->doRequest('POST', 'token/create', [
            'id' => $id,
        ])['payload'];
    }

    /**
     * @param $id
     *
     * @throws DaemonException
     *
     * @return GameserverStats
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGameserverStats($id): GameserverStats
    {
        $request = $this->doRequest('GET', 'gameserver/' . $id);

        return new GameserverStats($request['payload']);
    }

    /**
     * @throws DaemonException
     *
     * @return ServerStats
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getServerStats(): ServerStats
    {
        $request = $this->doRequest('GET', 'monitoring');

        if (empty($request)) {
            throw new DaemonException('Failed to request Server stats for ' . $this->hostname);
        }

        return new ServerStats($request['payload']);
    }

    /**
     * @return string
     */
    public function getSocketUrl(): string
    {
        if (\strpos($this->hostname, '.local') !== false) {
            return 'http://' . $_SERVER['HTTP_HOST'];
        }

        return \sprintf('https://%s', $this->hostname);
    }

    /**
     * @param $method
     * @param $uri
     * @param array $data
     *
     * @throws DaemonException
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function doRequest($method, $uri, array $data = [])
    {
        $scheme = 'https';

        if (\strpos($this->hostname, '.local') !== false) {
            $scheme = 'http';
        }

        $curlHandle = \curl_init($scheme . '://' . $this->hostname . '/api/' . self::API_VERSION . '/' . $uri);
        \curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            \curl_setopt($curlHandle, CURLOPT_POSTFIELDS, \json_encode($data));
        }

        \curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        \curl_setopt($curlHandle, CURLOPT_USERPWD, $this->daemonUsername . ':' . $this->daemonPassword);
        \curl_setopt($curlHandle, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = \curl_exec($curlHandle);
        \curl_close($curlHandle);

        $result = \json_decode($response, true);
        if (\json_last_error()) {
            throw new DaemonException(\sprintf('JSON Decoding error on host "%s", got %s', $this->hostname, $response));
        }

        return $result;
    }
}
