<?php

namespace GSS\Component\Api;

use Doctrine\DBAL\Connection;

/**
 * Class RateLimiting
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class RateLimiting
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * RateLimiting constructor.
     *
     * @param Connection $connection
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $serverId
     *
     * @return bool
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function isRateLimited(int $serverId): bool
    {
        return $this->connection->fetchColumn('SELECT 1 FROM blocked_tasks WHERE Email = "api" AND Method = ? AND TTL > ?', [
            $serverId,
            \time(),
        ]);
    }

    /**
     * @param int $serverId
     * @param int $ttl
     *
     * @return bool
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function rateLimit(int $serverId, int $ttl = 3600): bool
    {
        $this->connection->insert('blocked_tasks', [
            'Email' => 'api',
            'Method' => $serverId,
            'Value' => 'lul',
            'TTL' => \time() + $ttl,
        ]);

        return true;
    }
}
