<?php

namespace GSS\Component\Commerce;

use Doctrine\DBAL\Connection;

/**
 * Class GP.
 */
class GP
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * GP constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Add Points to User.
     *
     * @param int    $customerId
     * @param int    $points
     * @param string $reason
     *
     * @return bool
     */
    public function addPointsToUser(int $customerId, int $points, string $reason): bool
    {
        $oldPoints = $this->connection->fetchColumn('SELECT GP FROM users WHERE id = ?', [$customerId]);
        $newPoints = $oldPoints + $points;

        $this->connection->insert('gp_stats', [
            'name' => $reason,
            'value' => $points,
            'userID' => $customerId,
            'status' => 'in',
        ]);

        $this->connection->executeQuery('UPDATE users SET GP = ? WHERE id = ?', [
            $newPoints,
            $customerId,
        ]);

        return true;
    }

    /**
     * Remove Points to User.
     *
     * @param int    $customerId
     * @param int    $points
     * @param string $reason
     *
     * @return bool
     */
    public function removePointsFromUser(int $customerId, int $points, string $reason): bool
    {
        $oldPoints = $this->connection->fetchColumn('SELECT GP FROM users WHERE id = ?', [$customerId]);
        $newPoints = $oldPoints - $points;

        $this->connection->insert('gp_stats', [
            'name' => $reason,
            'value' => $points,
            'userID' => $customerId,
            'status' => 'out',
        ]);

        $this->connection->executeQuery('UPDATE users SET GP = ? WHERE id = ?', [
            $newPoints,
            $customerId,
        ]);

        return true;
    }

    /**
     * Gets the GP stats.
     *
     * @param int $userID
     *
     * @return array
     */
    public function getGPStats(int $userID): array
    {
        $data = [];

        list($in, $out) = $this->connection->fetchAssoc('SELECT (SELECT SUM(value) FROM gp_stats WHERE `userID` = :userId AND status = "in") AS "0", (SELECT SUM(value) FROM gp_stats WHERE `userID` = :userId AND status = "out") AS "1"', [
            'userId' => $userID,
        ]);

        $data['in'] = $this->connection->fetchAll('SELECT * FROM gp_stats WHERE userID = ? AND status = "in" ORDER BY id DESC LIMIT 10', [$userID]);
        $data['out'] = $this->connection->fetchAll('SELECT * FROM gp_stats WHERE userID = ? AND status = "out" ORDER BY id DESC LIMIT 10', [$userID]);
        $data['all'] = $this->connection->fetchAll('SELECT * FROM gp_stats WHERE userID = ? ORDER BY id DESC LIMIT 10', [$userID]);

        if (!empty($data['all'])) {
            $data['Graph'] = [['label' => 'Eingenommen', 'data' => $in], ['label' => 'Ausgegeben', 'data' => $out]];
        } else {
            $data['Graph'] = [['label' => 'Eingenommen', 'data' => 0], ['label' => 'Ausgegeben', 'data' => 0]];
        }

        return $data;
    }

    public function getGPCount()
    {
        return $this->connection->fetchColumn('SELECT SUM(GP) FROM users');
    }
}
