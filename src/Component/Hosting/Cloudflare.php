<?php

namespace GSS\Component\Hosting;

use Cloudflare\Zone\Dns;
use Doctrine\DBAL\Connection;
use GSS\Component\Exception\CloudflareException;
use GSS\Component\Hosting\Gameserver\Gameserver;

/**
 * Class Cloudflare.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Cloudflare
{
    /**
     * @var Dns
     */
    private $api;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Cloudflare constructor.
     *
     * @param string $apiEmail
     * @param string $apiPassword
     */
    public function __construct(string $apiEmail, string $apiPassword, Connection $connection)
    {
        $this->api = new Dns($apiEmail, $apiPassword);
        $this->connection = $connection;
    }

    /**
     * @param string $domain
     * @param string $subdomain
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isSubdomainUsed(string $domain, string $subdomain)
    {
        return (bool) $this->connection->fetchColumn('SELECT 1 FROM gameserver_cloudflare WHERE domain = ? AND subdomain = ?', [
            $domain,
            $subdomain,
        ]);
    }

    /**
     * @param Gameserver $gameserver
     * @param string     $domain
     * @param string     $subdomain
     *
     * @throws CloudflareException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function registerSubdomain(Gameserver $gameserver, string $domain, string $subdomain)
    {
        $zoneId = $this->getZoneIdByDomain($domain);

        $this->removeSubdomain($gameserver);

        $hostname = $gameserver->getHostname();
        $re = '/\w+/';
        \preg_match_all($re, $hostname, $matches);
        $subdomainHostname = $matches[0][0];

        $result = $this->api->create(
            $zoneId,
            'SRV',
            'gs_' . $gameserver->getId(),
            $gameserver->getIp(),
            1,
            false,
            5,
            [
                'name' => $subdomain,
                'weight' => 1,
                'priority' => 1,
                'port' => $gameserver->getPort(),
                'target' => $subdomainHostname . '.' . $domain,
                'service' => '_minecraft',
                'proto' => '_tcp',
            ]
        );

        if ($result->success) {
            $this->connection->insert('gameserver_cloudflare', [
                'gameserverID' => $gameserver->getId(),
                'domain' => $domain,
                'subdomain' => $subdomain,
                'recordId' => $result->result->id,
            ]);
        }

        return $result->success;
    }

    /**
     * @param Gameserver $gameserver
     *
     * @throws CloudflareException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function removeSubdomain(Gameserver $gameserver)
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM gameserver_cloudflare WHERE gameserverID = ?', [
            $gameserver->getId(),
        ]);

        if (!empty($data)) {
            $zone = $this->getZoneIdByDomain($data['domain']);

            $this->api->delete_record($zone, $data['recordId']);

            $this->connection->executeQuery('DELETE FROM gameserver_cloudflare WHERE gameserverID = ?', [
                $gameserver->getId(),
            ]);
        }
    }

    /**
     * @param string $domain
     *
     * @throws CloudflareException
     *
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getZoneIdByDomain(string $domain)
    {
        $zones = $this->api->get('zones');

        foreach ($zones->result as $item) {
            if ($item->name == $domain) {
                return $item->id;
            }
        }

        throw new CloudflareException('Cannot find zone with domain ' . $domain);
    }
}
