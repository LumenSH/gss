<?php

namespace GSS\Component\Hosting\Gameserver;

use Doctrine\DBAL\Connection;
use GSS\Component\Security\Permission;
use GSS\Component\Util;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FTPManager
{
    private $dbalConnection;
    private $container;

    public function __construct(ContainerInterface $container, Connection $connection)
    {
        $this->container = $container;
        $this->dbalConnection = $connection;
    }

    /**
     * @param $gsID
     * @return array
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getFTPAccounts($gsID) : array
    {
        return $this->dbalConnection->fetchAll('SELECT * FROM ftpuser WHERE userid REGEXP ?', [
            'ftp_' . $gsID . '_',
        ]);
    }

    /**
     * Adds a FTP Account.
     *
     * @param Gameserver $gameserver
     * @param $data
     *
     * @return bool
     */
    public function addFTPAccount(Gameserver $gameserver, $data) : bool
    {
        $ftpFetch = $this->getFTPAccounts($gameserver->getId());
        $ftps = \array_column($ftpFetch, 'userid');

        $freeName = null;

        for ($i = 1; $i <= $this->container->get('session')->getUserData('MaxFTP'); ++$i) {
            if (!\in_array('ftp_' . $gameserver->getId() . '_' . $i, $ftps)) {
                $freeName = 'ftp_' . $gameserver->getId() . '_' . $i;
                break;
            }
        }

        if ($freeName == null) {
            $this->container->get('flash.messenger')->addError('FTP-Account', __('Du hast die Maximale Anzahl der FTP Benutzer erreicht', 'Server', 'MaxFTPUser'));

            return false;
        }

        if (!empty($data['accountDescription'])) {
            $data['accountDescription'] = \substr($data['accountDescription'], 0, 10);
        }

        $this->dbalConnection->insert('ftpuser', [
            'name' => $data['accountName'],
            'userid' => $freeName,
            'passwd' => Util::getMySQLPassword($data['accountPassword']),
            'uid' => 10000 + $gameserver->getOwner(),
            'gid' => 10000 + $gameserver->getOwner(),
            'homedir' => $gameserver->calcServerDirectory(empty($data['accountPath']) ? '' : $data['accountPath']),
            'description' => $data['accountDescription'],
        ]);

        $this->container->get('flash.messenger')->addSuccess('FTP-Account', __('Der FTP-Benutzer wurde erfolgreich angelegt', 'Server', 'FTPCreated'));

        return true;
    }

    /**
     * Update FTP Account.
     *
     * @param Gameserver $gameserver
     * @param $ftpUser
     * @param $data
     *
     * @return void
     */
    public function updateFTPAccount(Gameserver $gameserver, $ftpUser, $data) : void
    {
        $upData = [
            'name' => $data['name'],
            'homedir' => $gameserver->calcServerDirectory($data['homedir']),
            'description' => $data['description'],
        ];

        if (!empty($data['passwd'])) {
            $upData['passwd'] = Util::getMySQLPassword($data['passwd']);
        }

        $this->dbalConnection->update('ftpuser', $upData, ['userid' => $ftpUser]);
    }

    /**
     * @param $gsID
     * @param $username
     * @return bool
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function removeFTPAccount($gsID, $username) : bool
    {
        if (Permission::isFTPFromServer($username, $gsID)) {
            $this->dbalConnection->delete('ftpuser', ['userid' => $username]);

            return true;
        }

        return false;
    }
}
