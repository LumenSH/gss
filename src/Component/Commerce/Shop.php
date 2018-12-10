<?php

namespace GSS\Component\Commerce;

use Doctrine\DBAL\Connection;
use GSS\Component\Hosting\Gameserver\Gameserver;
use GSS\Component\Hosting\SSH;
use GSS\Component\Hosting\SSHUtil;
use GSS\Component\Session\Session;
use GSS\Component\Util;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Shop.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Shop
{
    /**
     * @var array
     */
    private $userData = [];

    /**
     * @var int
     */
    private $userID = null;

    /**
     * @var int
     */
    private $rootServerId = null;

    /**
     * @var array
     */
    private $packageInfo = [];

    /**
     * @var array
     */
    private $serverPorts = [
        'mta' => [[22003, 22125], [35000, 35122], [35250, 35372], [35500, 35625], [40000, 40125], [40254, 40374]],
        'samp' => [7777, 8000],
        'csgo' => [21000, 22000],
        'gta5mp' => [23000, 24000],
        'factorio' => [24000, 25000],
        'gmod' => [25000, 26000],
        'mc' => [26001, 27000],
        'terraria' => [27001, 28000, 2],
        'l4d2' => [27005, 28000],
        'rust' => [28001, 29000, 2],
        'openttd' => [29001, 30000],
        'fivem' => [31000, 32000],
    ];

    /**
     * @var Util
     */
    private $util;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var GP
     */
    private $gpService;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Shop constructor.
     *
     * @param Session            $session
     * @param Util               $util
     * @param Connection         $connection
     * @param GP                 $gpService
     * @param ContainerInterface $container
     */
    public function __construct(
        Session $session,
        Util $util,
        Connection $connection,
        GP $gpService,
        ContainerInterface $container
    ) {
        $this->userData = (array) $session->get('user');
        $this->userID = $session->getUserID();
        $this->session = $session;
        $this->util = $util;
        $this->connection = $connection;
        $this->gpService = $gpService;
        $this->container = $container;
    }

    /**
     * Action to buy a server.
     *
     * @param null $packageId
     *
     * @return bool
     */
    public function buyNewServer($packageId = null)
    {
        $this->loadPackageInfo($packageId);

        if ($this->hasCustomerHasRequirements()) {
            $this->rootServerId = $this->getFreeRootServer();

            if ($this->rootServerId == null) {
                $this->session->flashMessenger()->addError('Shop', __('All servers all in use', 'Shop', 'AllServerInUse'));

                return false;
            }

            $serverDetails = $this->getFreeServerDetails();

            if (\is_array($serverDetails)) {
                if (!empty($this->packageInfo['gp'])) {
                    /*
                     * Remove Points from User
                     */
                    $this->gpService->removePointsFromUser($this->userID, $this->packageInfo['gp'], __('Server Kauf', 'Shop', 'BuyServerGPStatus'));
                }

                $lastVersion = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT id FROM products_version WHERE productID = ? ORDER BY version DESC', [
                    $this->packageInfo['productID'],
                ]);

                $ip = $this->connection->fetchColumn('SELECT ip FROM gameroot_ip WHERE id = ?', [$serverDetails['ip']]);

                $this->connection->insert('gameserver', [
                    // Server Details
                    'gameRootID' => $this->rootServerId,
                    'gameRootIpID' => $serverDetails['ip'],
                    'productID' => $this->packageInfo['productID'],
                    'versionID' => $lastVersion,
                    'port' => $serverDetails['port'],
                    'userID' => $this->userID,

                    'name' => \sprintf('(%s) %s:%s', $this->packageInfo['internalName'], $ip, $serverDetails['port']),

                    // Package Details
                    'slot' => ($this->packageInfo['type'] == 0 ? $this->packageInfo['slots'] : $this->session->getUserData('MaxSlots')),
                    'price' => $this->packageInfo['gp'],
                    'typ' => $this->packageInfo['type'],

                    // Time
                    'duration' => \strtotime('+30days'),
                    'createdAt' => \date('Y-m-d'),

                    'properties' => $this->packageInfo['internalName'] === 'mc' ? \json_encode(['ram' => $this->packageInfo['ram']]) : null,
                ]);

                $lastInsertId = $this->connection->lastInsertId();
                /*
                 * Give User rights to Server
                 */
                $this->connection->insert('users_to_gameserver', ['userID' => $this->userID, 'gameserverID' => $lastInsertId, 'Rights' => \json_encode(['all' => true])]);

                if (!$this->util->hasUnixAccount($this->rootServerId)) {
                    $rootSSH = new SSH($this->rootServerId);

                    SSHUtil::createLinuxUser($rootSSH, $this->userID);

                    $rootSSH->closeConnection();

                    $this->connection->insert('users_to_gameroot', [
                        'userID' => $this->userID,
                        'hostID' => $this->rootServerId,
                    ]);
                }

                /*
                 * Reinstall Server
                 */
                $gs = Gameserver::createServer($this->container, $lastInsertId);

                // Create Server Directory
                $gs->getSSH()->exec('mkdir -p ' . $gs->calcServerDirectory() . ' && echo "1" > ' . $gs->calcServerDirectory('removed'));

                // Add Install Task
                $gs->addTask('GSReinstall', ['version' => $lastVersion, 'step' => '1']);

                $this->session->flashMessenger()->addSuccess('Shop', __('Gameserver wurde erfolgreich erstellt', 'Shop', 'ServerCreated'));

                \header('Location: /server/' . $gs->getShort());
                die();
            }

            $this->session->flashMessenger()->addError('Shop', __('Es sind zurzeit leider alle Server belegt', 'Shop', 'AllServerInUse'));
        }
    }

    /**
     * Load Package Info.
     *
     * @param $packageId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function loadPackageInfo($packageId)
    {
        $this->packageInfo = $this->connection->fetchAssoc('
            SELECT * FROM products_sub
			LEFT JOIN products ON(products.id = products_sub.productID)
			WHERE products_sub.id = ?', [$packageId]);
    }

    /**
     * Check user requirements.
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function hasCustomerHasRequirements()
    {
        /*
         * Check for active gameserver
         */
        if ($this->packageInfo['type'] == 0) {
            if (empty($this->userData['sms'])) {
                $this->session->flashMessenger()->addError('Shop', __('You need to activate your account with sms at first.', 'Shop', 'AccountNotActivated'));

                return false;
            }

            if ($this->userData['GP'] >= $this->packageInfo['gp']) {
                return true;
            }

            $this->session->flashMessenger()->addError('Shop', __('Du hast nicht genügend Gameserver-Punkte', 'Shop', 'NotEnoughtGameserverPoints'));

            return false;
        }

        $currentServer = $this->connection->fetchColumn('SELECT COUNT(*) FROM gameserver WHERE userID = ? AND typ = 1', [$this->userID]);

        if ($currentServer >= $this->userData['MaxServer']) {
            $this->session->flashMessenger()->addError('Shop', __('Du hast zu viele Passive Server, bitte upgrade deine Maximale Server Anzahl', 'Shop', 'TooMuchPassiveServers'));

            return false;
        }

        return true;
    }

    /**
     * Calculates with loadavg the server, which have at lowest loadaverage.
     *
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getFreeRootServer()
    {
        return 2;
        $hosts = $this->connection->fetchAll('
            SELECT
            gameroot.id,
            (SELECT AVG(loadavg) FROM gameroot_avg WHERE hostID = gameroot.id) as averageLoadAvg,
            ((gameroot.cpus * 1.5) / (SELECT AVG(loadavg) FROM gameroot_avg WHERE hostID = gameroot.id)) as used,
            (gameroot.cpus * 1.5) as max
            FROM gameroot
            ORDER BY ((gameroot.cpus * 1.5) / (SELECT AVG(loadavg) FROM gameroot_avg WHERE hostID = gameroot.id)) DESC
        ');

        // is higher than max
        if ($hosts[0]['averageLoadAvg'] > $hosts[0]['max']) {
            return false;
        }

        return $hosts[0]['id'];
    }

    /**
     * Search for a free ip and port.
     *
     * @return array|bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function getFreeServerDetails()
    {
        $serverIps = $this->connection->executeQuery('SELECT id FROM gameroot_ip WHERE gamerootID = ?', [$this->rootServerId])->fetchAll(PDO::FETCH_COLUMN);
        $usedServer = $this->connection->executeQuery('SELECT CONCAT(gamerootIpID, "_", Port) FROM gameserver WHERE gamerootID = ?', [$this->rootServerId])->fetchAll(PDO::FETCH_COLUMN);

        if (empty($this->serverPorts[$this->packageInfo['internalName']])) {
            return false;
        }

        $serverPorts = [];
        if (\is_int($this->serverPorts[$this->packageInfo['internalName']][0])) {
            $serverPorts[] = $this->serverPorts[$this->packageInfo['internalName']];
        } else {
            $serverPorts = $this->serverPorts[$this->packageInfo['internalName']];
        }

        foreach ($serverPorts as $serverPort) {
            $step = empty($serverPort[2]) ? 1 : $serverPort[2];
            for ($port = $serverPort[0]; $port <= $serverPort[1]; $port += $step) {
                foreach ($serverIps as $ip) {
                    if (!\in_array($ip . '_' . $port, $usedServer)) {
                        return ['ip' => $ip, 'port' => $port];
                    }
                }
            }
        }

        return false;
    }
}
