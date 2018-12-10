<?php

namespace GSS\Models\Cms;

use Doctrine\DBAL\Connection;
use GSS\Models\AbstractRepository;

/**
 * Repository for table cms
 */
class CmsRepository extends AbstractRepository
{
    /**
     * Table name
     *
     * @var string
     */
    const TABLE = 'cms';

    /** @var Connection */
    private $connection;

    /**
     * CmsRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetches all records.
     *
     * @param int|null   $offset
     * @param int|null   $limit
     * @param array|null $where
     * @param array|null $sorters
     *
     * @return Cms[]
     */
    public function findBy(int $offset = null, int $limit = null, array $where = null, array $sorters = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE);

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($where) {
            foreach ($where as $key => $value) {
                $qb->andWhere(\sprintf('%s = %s', $key, $qb->createNamedParameter($value)));
            }
        }

        if ($sorters) {
            foreach ($sorters as $field => $sort) {
                $qb->addOrderBy($field, $sort);
            }
        }

        $result = $qb->execute()->fetchAll();

        $records = [];

        foreach ($result as $item) {
            $records[] = $this->getEntityFromDatabaseArray($item);
        }

        return $records;
    }

    /**
     * @param array $where
     *
     * @return Cms
     */
    public function findOneBy(array $where): Cms
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
           ->from(self::TABLE);

        foreach ($where as $key => $value) {
            $qb->andWhere(\sprintf('%s = %s', $key, $qb->createNamedParameter($value)));
        }

        $result = $qb->execute()->fetch();

        if (empty($result)) {
            return null;
        }

        return $this->getEntityFromDatabaseArray($result);
    }

    /**
     * @param int $id
     *
     * @return Cms
     */
    public function find(int $id): Cms
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Creates a record in the database.
     *
     * @param Cms $entity
     *
     * @return Cms
     */
    public function create(Cms $entity): Cms
    {
        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->insert(
            self::TABLE,
            $databaseArray
        );

        $entity->setId($this->connection->lastInsertId());

        return $entity;
    }

    /**
     * Update a record in the database.
     *
     * @param Cms $entity
     *
     * @return Cms
     */
    public function update(Cms $entity): Cms
    {
        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->update(
            self::TABLE,
            $databaseArray,
            ['id' => $entity->getId()]
        );

        return $entity;
    }

    /**
     * Remove a record in the database.
     *
     * @param Cms $entity
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return Cms
     */
    public function remove(Cms $entity): Cms
    {
        $this->connection->delete(
            self::TABLE,
            ['id' => $entity->getId()]
        );

        return $entity;
    }

    /**
     * Maps the given entity to the database array.
     *
     * @param Cms $entity
     *
     * @return array
     */
    public function getDatabaseArrayFromEntity(Cms $entity): array
    {
        $array = $entity->toArray();

        foreach ($array as &$item) {
            if ($item instanceof \DateTime) {
                $item = $item->format('Y-m-d H:i:s');
            } elseif (\is_array($item)) {
                $item = \json_encode($item);
            }
        }

        return $array;
    }

    /**
     * Prepares database array from properties.
     *
     * @param array $data
     *
     * @return Cms
     */
    public function getEntityFromDatabaseArray(array $data): Cms
    {
        $entity = new Cms();
        $entity->setId((int) $data['id']);
        $entity->setSlug((string) $data['slug']);
        $entity->setTitle(empty($data['title']) ? [] : \json_decode($data['title'], true));
        $entity->setContent(empty($data['content']) ? [] : \json_decode($data['content'], true));
        $entity->setMeta(empty($data['meta']) ? [] : \json_decode($data['meta'], true));

        return $entity;
    }
}
