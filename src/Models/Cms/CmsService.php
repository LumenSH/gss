<?php

namespace GSS\Models\Cms;

use GSS\Component\Routing\RewriteManager;
use GSS\Models\AbstractService;

/**
 * Service for table cms
 */
class CmsService extends AbstractService
{
    /**
     * @var CmsRepository
     */
    private $repository;

    /**
     * @var RewriteManager
     */
    private $rewriteManager;

    /**
     * CmsService constructor.
     *
     * @param CmsRepository  $repository
     * @param RewriteManager $rewriteManager
     */
    public function __construct(CmsRepository $repository, RewriteManager $rewriteManager)
    {
        $this->repository = $repository;
        $this->rewriteManager = $rewriteManager;
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
        return $this->repository->findBy($offset, $limit, $where, $sorters);
    }

    /**
     * @param array $where
     *
     * @return Cms
     */
    public function findOneBy(array $where): Cms
    {
        return $this->repository->findOneBy($where);
    }

    /**
     * @param int $id
     *
     * @return Cms
     */
    public function find(int $id): Cms
    {
        return $this->repository->find($id);
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
        $entity = $this->repository->create($entity);

        $this->rewriteManager->addRewrite($entity->getSlug(), 'cms', 'detail', ['cmsID' => $entity->getId()]);

        return $entity;
    }

    /**
     * Update a record in the database.
     *
     * @param Cms $entity
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return Cms
     */
    public function update(Cms $entity): Cms
    {
        $oldEntity = $this->repository->find($entity->getId());

        if ($oldEntity->getSlug() !== $entity->getSlug()) {
            $this->rewriteManager->removeRewrite($oldEntity->getSlug());
            $this->rewriteManager->addRewrite($entity->getSlug(), 'cms', 'detail', ['cmsID' => $entity->getId()]);
        }

        return $this->repository->update($entity);
    }

    /**
     * Remove a record in the database.
     *
     * @param Cms $entity
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return Cms
     */
    public function remove(Cms $entity): Cms
    {
        $this->rewriteManager->removeRewrite($entity->getSlug());

        return $this->repository->remove($entity);
    }
}
