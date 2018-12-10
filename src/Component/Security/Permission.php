<?php

namespace GSS\Component\Security;

use Doctrine\DBAL\Connection;

/**
 * Class Permission
 * Rechte Verwaltung.
 */
class Permission
{
    /**
     * Checks: Is this Database from this Gameserver?
     *
     * @param Connection $connection
     * @param $dbID
     * @param $gsID
     *
     * @return bool
     */
    public static function isDatabaseFromServer(Connection $connection, $dbID, $gsID)
    {
        return $connection->fetchColumn('SELECT 1 FROM gameserver_database WHERE id = ? AND gameserverID = ?', [$dbID, $gsID]);
    }

    public static function isFTPFromServer($ftpuser, $gsID)
    {
        \preg_match('/ftp_(\\d+)/', $ftpuser, $matches);
        if (!empty($matches['1']) && $matches['1'] == $gsID) {
            return true;
        }

        return false;
    }
}
