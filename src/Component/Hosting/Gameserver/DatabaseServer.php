<?php

namespace GSS\Component\Hosting\Gameserver;

use Doctrine\DBAL\Connection;
use GSS\Component\Session\Session;

class DatabaseServer
{
    /** @var $db \PDO */
    private $db = null;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * DatabaseServer constructor.
     *
     * @param Connection $connection
     * @param Session    $session
     */
    public function __construct(
        Connection $connection,
        Session $session
    ) {
        $this->config = [];
        $this->connection = $connection;
        $this->session = $session;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setHost(int $id)
    {
        $data = $this->connection->fetchAssoc('SELECT * FROM gameroot WHERE id = ?', [$id]);
        $this->config = [
            'host' => $data['sshIp'],
            'user' => 'root',
            'password' => $data['mysqlPassword'],
        ];

        return $this;
    }

    public function getDatabasesByGameserver($gameserverId)
    {
        return $this->connection->fetchAll('SELECT * FROM gameserver_database WHERE gameserverID = ?', [$gameserverId]);
    }

    public function addDatabase($gsID, $data)
    {
        $dbDataFetch = $this->connection->fetchAll('SELECT databaseInternalName FROM gameserver_database WHERE databaseInternalName LIKE ?', [
            'db_' . $gsID . '_%',
        ]);
        $dbs = \array_column($dbDataFetch, 'databaseInternalName');
        $freeName = null;

        for ($i = 1; $i <= $this->session->getUserData('MaxMySQL'); ++$i) {
            if (!\in_array('db_' . $gsID . '_' . $i, $dbs)) {
                $freeName = 'db_' . $gsID . '_' . $i;
                break;
            }
        }

        if ($freeName == null) {
            $this->session->flashMessenger()->addError('Datenbank', __('Du hast die maximale Anzahl der Datenbanken erreicht', 'Server', 'MaxDatabaseReached'));

            return false;
        }

        $this->createDatabase($freeName, $data['accountPassword']);

        $this->connection->insert('gameserver_database', [
            'gameserverID' => $gsID,
            'databaseName' => $data['accountName'],
            'databaseInternalName' => $freeName,
            'databaseDescription' => $data['accountDescription'],
        ]);

        $this->session->flashMessenger()->addSuccess('Datenbank', __('Die Datenbank wurde erfolgreich angelegt', 'Server', 'DBCreated'));

        return true;
    }

    public function updateDatabase($dbID, $data)
    {
        $this->connection->update('gameserver_database', [
            'databaseName' => $data['databaseName'],
            'databaseDescription' => $data['databaseDescription'],
        ], ['id' => $dbID]);

        if (!empty($data['databasePassword'])) {
            $databaseID = $this->connection->fetchColumn('SELECT databaseInternalName FROM gameserver_database WHERE id = ?', [
                $dbID,
            ]);

            $this->setDatabasePassword($databaseID, $data['databasePassword']);
        }

        return true;
    }

    public function removeDatabaseAccount($gsID, $username)
    {
        \preg_match('/db_(\\d+)/', $username, $matches);

        if (!empty($matches['1']) && $matches['1'] == $gsID) {
            $this->deleteDatabase($username);
        }

        return $this->connection->executeQuery('DELETE FROM gameserver_database WHERE databaseInternalName = ? AND gameserverID = ?', [
            $username,
            $gsID,
        ]);
    }

    public function createDatabase($id, $password)
    {
        $this->connect();

        try {
            $this->db->prepare('CREATE DATABASE ' . $id . ' CHARACTER SET utf8m4')->execute();
        } catch (\Throwable $e) {}

        try {
            $this->db->prepare("GRANT ALL ON $id.* TO '$id'@'%' IDENTIFIED BY '$password' WITH GRANT OPTION;")->execute();
        } catch (\Throwable $e) {}

        try {
            $this->db->prepare("GRANT ALL ON $id.* TO '$id'@'localhost' IDENTIFIED BY '$password' WITH GRANT OPTION;")->execute();
        } catch (\Throwable $e) {}

        return true;
    }

    public function setDatabasePassword($id, $password)
    {
        $this->connect();

        $this->db->query("SET PASSWORD FOR '$id'@'%' = PASSWORD('$password');");
        $this->db->query("SET PASSWORD FOR '$id'@'localhost' = PASSWORD('$password');");

        return true;
    }

    public function deleteDatabase($id)
    {
        $this->connect();

        try {
            $this->db->prepare('DROP DATABASE ' . $id)->execute();
        } catch (\Throwable $e) {}

        try {
            $this->db->prepare("DROP USER '$id'@'%';")->execute();
        } catch (\Throwable $e) {}

        try {
            $this->db->prepare("DROP USER '$id'@'localhost';")->execute();
        } catch (\Throwable $e) {}
    }

    private function connect()
    {
        if ($this->db === null) {
            $this->db = new \PDO('mysql:host=' . $this->config['host'] . ';dbname=mysql;', $this->config['user'], $this->config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        }
    }
}
