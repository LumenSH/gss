<?php

namespace GSS\Component\Hosting\Gameserver;

use GSS\Component\Commerce\GP;
use GSS\Component\Hosting\SSH;
use GSS\Component\Hosting\SSHUtil;
use GSS\Component\Structs\Gameserver as GameserverStruct;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Gameserver
 *
 * @author Soner Sayakci <***REMOVED***>
 */
abstract class Gameserver extends GameserverStruct
{
    /**
     * @var array
     */
    private $gsProperties = null;

    /**
     * @var array
     */
    private $gsPermission = [];

    /**
     * @var int
     */
    private $userID;

    /**
     * Gameserver constructor.
     *
     * @param ContainerInterface $container
     * @param array              $gsData
     */
    public function __construct(ContainerInterface $container, array $gsData)
    {
        $this->container = $container;
        if ($this->container->get('security.token_storage')->getToken()) {
            $this->userID = $this->container->get('security.token_storage')->getToken()->getUser() ? $this->container->get('security.token_storage')->getToken()->getUser()->getId() : null;
        }
        $this->gsData = $gsData;

        if ($this->hasPermission() || $this->container->get('session')->Acl()->isAllowed('admin_gameserver')) {
            $this->initGS($gsData);
        } else {
            throw new NotFoundHttpException('Access denied');
        }
    }

    /**
     * @param ContainerInterface $container
     * @param int                $gsID
     *
     * @return Gameserver
     */
    public static function createServer(ContainerInterface $container, int $gsID)
    {
        $gsData = $container->get('doctrine.dbal.default_connection')->fetchAssoc('
			SELECT
			    gameserver.*,
			    root.sshIp,
			    root.sshPort,
			    root.sshUser,
			    root.hostname,
			    rootIP.IP,
			    products.name,
			    products.consoleCommands_de,
			    products.consoleCommands_en,
			    products.steamID,
			    products.internalName AS game,
			    products.banner,
			    products_version.version,
			    CONCAT(gameserver_cloudflare.subdomain, ".", gameserver_cloudflare.domain) as cloudflaredomain
			FROM
			    gameserver
			INNER JOIN
				gameroot root ON (root.id = gameserver.gamerootID)
			INNER JOIN
				gameroot_ip rootIP ON (rootIP.id = gameserver.gamerootIpID)
            INNER JOIN
                products ON(products.id = gameserver.productID)
            LEFT JOIN 
                products_version ON(products_version.id = gameserver.versionID)
            LEFT JOIN
                gameserver_cloudflare ON(gameserver_cloudflare.gameserverID = gameserver.id)
			WHERE gameserver.ID = ?
		', [
            $gsID,
        ]);

        if (empty($gsData)) {
            throw new NotFoundHttpException('Gameserver does not exist');
        }

        $className = '\\GSS\\Component\\Hosting\\Gameserver\\Games\\' . \strtoupper($gsData['game']);

        return new $className($container, $gsData);
    }

    /**
     * Get gameserver properties
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getProperties()
    {
        if ($this->gsProperties == null) {
            $this->gsProperties = [];

            if (empty($this->gsData['properties'])) {
                $gsProperties = [];
            } else {
                $gsProperties = \json_decode($this->gsData['properties'], true);
            }

            $form = $this->getForm();

            foreach ($form as $formItem) {
                $this->gsProperties[$formItem['name']] = (!isset($gsProperties[$formItem['name']]) ? $formItem['defaultValue'] : $gsProperties[$formItem['name']]);
            }
        }

        return $this->gsProperties;
    }

    /**
     * Returns StartProperties
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartProperties()
    {
        $propertiesValues = $this->getProperties();
        $properties = $this->getForm();

        $propertyArray = [];

        foreach ($properties as $property) {
            if (isset($property['startParam']) && !empty($propertiesValues[$property['name']])) {
                $propertyArray[] = $property['startParam'];
                if ($property['type'] !== 'boolean') {
                    $propertyArray[] = $propertiesValues[$property['name']];
                }
            } elseif (!empty($property['defaultValue'])) {
                $propertyArray[] = $property['startParam'];
                $propertyArray[] = $property['defaultValue'];
            }
        }

        return $propertyArray;
    }

    /**
     * Set properties
     *
     * @param $value
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function setProperties($value)
    {
        $val = \json_encode($value);
        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', [
            'properties' => $val,
        ], ['id' => $this->gsData['id']]);

        $this->gsData['properties'] = $val;

        $this->updateStartParams();

        return true;
    }

    /**
     * Checks Permissions to server
     *
     * @param string $perm
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function hasPermission($perm = 'show')
    {
        if (PHP_SAPI === 'cli') {
            return true;
        }

        if ($this->getOwner() === $this->userID) {
            return true;
        }

        if (empty($this->gsPermission)) {
            /*
             * Admin Mode
             */
            if ($this->container->get('session')->Acl()->isAllowed('admin_gameserver')) {
                $this->gsPermission['all'] = true;
            } else {
                $permission = $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT Rights FROM users_to_gameserver WHERE userID = ? AND gameserverID = ?', [
                    $this->userID,
                    $this->getId(),
                ]);
                if (!empty($permission)) {
                    $this->gsPermission = \json_decode($permission, true);
                }
            }
        }

        if (\in_array('all', $this->gsPermission) || \in_array($perm, $this->gsPermission) || isset($this->gsPermission['all']) || isset($this->gsPermission[$perm])) {
            return true;
        }

        return false;
    }

    /**
     * Adds a Task to the Gameserver.
     *
     * @param string $taskName
     * @param array  $taskValues
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function addTask($taskName, $taskValues = [])
    {
        $channel = $this->container->get('rabbit.connection')->channel();

        $content = \json_encode([
            'name' => $taskName,
            'id' => $this->getId(),
            'args' => $taskValues,
            'date' => \date('Y-m-d H:i:s'),
        ]);

        $stateId = 0;

        switch ($taskName) {
            case 'GSDelete':
                $stateId = 4;
                break;
            case 'GSUpdate':
                $stateId = 1;
                break;
            case 'GSReinstall':
                if (isset($taskValues['step']) && $taskValues['step'] == 1) {
                    $stateId = 2;
                } else {
                    $stateId = 3;
                }
        }

        $this->container->get('doctrine.dbal.default_connection')->executeQuery('UPDATE gameserver SET state = ? WHERE id = ?', [
            $stateId,
            $this->getId(),
        ]);

        $message = new AMQPMessage($content, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $channel->basic_publish($message, '', 'server_queue');

        $channel->close();
    }

    /**
     * Get Config Path From Name
     *
     * @param $config
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getConfigPathFromName($config)
    {
        $gsConfigs = $this->getConfigFiles();
        $path = false;
        foreach ($gsConfigs as $gsConfig) {
            if ($gsConfig['name'] == $config) {
                $path = $gsConfig['path'];
            }
        }

        return $path;
    }

    /**
     * Get Server Version.
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getVersions()
    {
        $versions = $this->container->get('doctrine.dbal.default_connection')->executeQuery('SELECT id, version FROM products_version WHERE productID = (SELECT id FROM products WHERE internalName = ?) ORDER BY products_version.version DESC', [
            $this->getGame(),
        ])->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $versions;
    }

    /**
     * Get upgrades
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUpgrades()
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAll(
            'SELECT *, (SELECT img FROM products WHERE id = products_sub.productID) AS img FROM products_sub WHERE type = ? AND slots != ? AND productID = (SELECT id FROM products WHERE internalName = ?)',
            [
                $this->getTyp(),
                $this->getSlot(),
                $this->getGame(),
            ]
        );
    }

    /**
     * Get Server rights
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getRights()
    {
        return $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT users_to_gameserver.Rights, (SELECT Email FROM users WHERE ID = users_to_gameserver.userID) AS Username FROM users_to_gameserver WHERE gameserverID = ? AND userID != ?', [
            $this->getId(),
            $this->getOwner(),
        ]);
    }

    /**
     * Reinstall Server.
     *
     * @param string $version
     * @param int    $step
     * @param null   $taskId
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function reinstallServer($version = 'default', $step = 1, $taskId = null)
    {
        $this->gsData['version'] = $version;

        $data = [];
        if ($step == 1) {
            $this->stop();
            $this->getSSH()->exec('rm -rf ' . $this->calcServerDirectory('') . ' && mkdir -p ' . $this->calcServerDirectory());
            $data = ['version' => $version, 'step' => 2];
            $this->getDaemon()->clearLogs($this->getId());
        } elseif ($step == 2) {
            $this->install($version);
            $data = ['version' => $version, 'step' => 3];
        }

        $this->updateStartParams();

        if (!empty($data)) {
            $this->addTask('GSReinstall', $data);
        } else {
            $this->container->get('doctrine.dbal.default_connection')->update('gameserver', ['state' => 0], ['id' => $this->getId()]);
        }
    }

    /**
     * Update Server
     *
     * @param string $version
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function updateServer($version = 'default')
    {
        $this->stop();
        SSHUtil::installTemplate($this->getSSH(), '/imageserver/games/' . $this->getGame() . '/' . $version . '/', $this->calcServerDirectory());
        $this->getDaemon()->clearLogs($this->getId());
        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', ['state' => 0], ['id' => $this->getId()]);
        $this->updateStartParams();
    }

    /**
     * Extends a Server
     *
     * @param int $variantId
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function extendServer($variantId = 0)
    {
        $variantDetails = $this->container->get('doctrine.dbal.default_connection')->fetchAssoc(
            'SELECT * FROM products_sub WHERE id = ? AND productID = (SELECT id FROM products WHERE internalName = ?)',
            [
                $variantId,
                $this->getGame(),
            ]
        );

        if (empty($variantDetails)) {
            return false;
        }

        $factor = $this->getPrice() / $variantDetails['gp'];
        $timeDiff = \abs(\time() - $this->getDuration());

        $newDuration = ($timeDiff * $factor) + \time();

        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', [
            'duration' => $newDuration,
            'slot' => $variantDetails['slots'],
            'price' => $variantDetails['gp'],
        ], ['id' => $this->getId()]);

        return true;
    }

    /**
     * Delete Active Server
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function deleteActiveServer()
    {
        if ($this->hasPermission('all')) {
            $duration = $this->getDuration();
            $price = $this->getPrice();

            $curDate = new \DateTime();
            $gsDate = new \DateTime(\date('Y-m-d H:i:s', $duration));
            $timeInterval = $curDate->diff($gsDate);

            $residualValue = ($timeInterval->days / 30) * $price;
            $residualValueEnd = \round(($residualValue / 100) * 95);
            $this->container->get(GP::class)->addPointsToUser($this->userID, $residualValueEnd, __('Server zurückgegeben', 'Server', 'ServerBack'));
            $this->addTask('GSDelete');
        }
    }

    /**
     * Delete a gameserver
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function delete()
    {
        /*
         * Stop Server
         */
        $this->stop();

        /*
         * Delete all files
         */
        $this->getSSH()->exec('rm -rf ' . $this->calcServerDirectory(''));

        /*
         * Delete FTP Accounts
         */
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM ftpuser WHERE userid LIKE "ftp_' . $this->getId() . '_%"');

        $dbs = $this->container->get('doctrine.dbal.default_connection')->fetchAll('SELECT * FROM gameserver_database WHERE gameserverID = ?', [$this->getId()]);

        $this->container->get('app.hosting.gameserver.db.server')->setHost($this->getHost());

        /*
         * Delete Databases
         */
        foreach ($dbs as $db) {
            $this->container->get('app.hosting.gameserver.db.server')->deleteDatabase($db['databaseInternalName']);
        }

        /*
         * Delete Gameserver
         */
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM users_to_gameserver WHERE gameserverID = ?', [$this->getId()]);
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM gameserver_database WHERE gameserverID = ?', [$this->getId()]);
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM gameserver WHERE id = ?', [$this->getId()]);
        $this->container->get('doctrine.dbal.default_connection')->executeQuery('DELETE FROM gameserver_browse WHERE serverID = ?', [$this->getId()]);

        /*
         * Delete Unix User
         */
        $userId = $this->getOwner();
        $this->getSSH()->closeConnection();

        $this->getDaemon()->clearLogs($this->getId());

        if ((int) $this->container->get('doctrine.dbal.default_connection')->fetchColumn('SELECT COUNT(*) FROM gameserver WHERE gameRootID = ? AND userID = ?', [
                $this->getHost(),
                $userId,
            ]) === 0) {
            $rootSSH = new SSH($this->getHost());
            $rootSSH->exec('userdel -r user' . $userId);

            $this->container->get('doctrine.dbal.default_connection')->delete('users_to_gameroot', [
                'userID' => $userId,
                'hostID' => $this->getHost(),
            ]);
        }
    }

    protected function updateStartParams()
    {
        $params = $this->getStartArguments();

        foreach ($this->getStartProperties() as $startProperty) {
            $params[] = $startProperty;
        }

        $this->container->get('doctrine.dbal.default_connection')->update('gameserver', ['startParams' => \json_encode($params)], ['id' => $this->getId()]);
    }

    /**
     * GS Init
     *
     * @param array $gsData
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private function initGS(array $gsData)
    {
        $this->gsData = $gsData;
        $this->gsData['path'] = $this->calcServerDirectory();

        $this->ssh = new SSH($this->gsData['gameRootID'], 'user' . $this->gsData['userID']);
    }
}
