<?php

namespace GSS\Component\Security;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * Class Acl.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Acl
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $aclConfig;

    /**
     * @var array
     */
    private $userPermissions;

    /**
     * @var string
     */
    private $userGroup;

    /**
     * @var array
     */
    private $permissions = [];

    /**
     * Acl constructor.
     *
     * @param Connection $connection
     * @param array      $aclConfig
     * @param string     $userGroup
     * @param array      $userPermissions
     */
    public function __construct(
        Connection $connection,
        array $aclConfig,
        $userGroup,
        array $userPermissions = []
    ) {
        $this->connection = $connection;
        $this->aclConfig = $aclConfig;
        $this->userGroup = $userGroup;
        $this->userPermissions = $userPermissions;

        $this->buildPermissions();
    }

    /**
     * @param string $permission
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isAllowed($permission)
    {
        return \in_array('any', $this->permissions, true) || \in_array($permission, $this->permissions, true);
    }

    /**
     * @param string $permission
     *
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUsersByPermission($permission)
    {
        // find groups
        $groups = [];

        foreach ($this->aclConfig as $roleGroup => $permissions) {
            if (\in_array($permission, $permissions, true) || \in_array('any', $permissions, true)) {
                $groups[] = $this->connection->quote($roleGroup);
            }
        }

        $qb = $this->connection->createQueryBuilder();
        $result = $qb
            ->select('id')
            ->from('users')
            ->where($qb->expr()->in('Role', $groups))
            ->orWhere($qb->expr()->like('Permissions', $this->connection->quote('%' . $permission . '%')))
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    /**
     * @author Soner Sayakci <***REMOVED***>
     */
    private function buildPermissions()
    {
        $this->permissions = ($this->aclConfig[$this->userGroup] ?? $this->aclConfig['default']) + $this->userPermissions;
    }
}
