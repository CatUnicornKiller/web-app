<?php

namespace App\Model\Repository;

use App\Exceptions\NotFoundException;
use Nette;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Doctrine\Common\Collections\Criteria;

/**
 * Base repository for all others. Contains predefined handy operations.
 */
class BaseRepository extends Nette\Object
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Constructor.
     * @param EntityManager $em
     * @param string $entityType unique class name
     */
    public function __construct(EntityManager $em, $entityType)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($entityType);
    }

    /**
     * Get entity with given identification.
     * @param int $id entity identifier
     * @return Entity|NULL
     */
    public function get($id)
    {
        return $this->repository->findOneById($id);
    }

    /**
     * Get all entities.
     * @return array
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * Get all entities which matches given parameters.
     * @param array $params
     * @param array $orderBy
     * @return array
     */
    public function findBy($params, $orderBy = [])
    {
        return $this->repository->findBy($params, $orderBy);
    }

    /**
     * Find one entity by given parameters.
     * @param array $params
     * @return Entity|NULL
     */
    public function findOneBy($params)
    {
        return $this->repository->findOneBy($params);
    }

    /**
     * Find one entity with given identification or throw exception.
     * @param int $id
     * @return Entity
     * @throws NotFoundException
     */
    public function findOrThrow($id)
    {
        $entity = $this->get($id);
        if (!$entity) {
            throw new NotFoundException("Cannot find '$id'");
        }
        return $entity;
    }

    /**
     * Count all entities.
     * @return int
     */
    public function countAll()
    {
        return $this->repository->countBy();
    }

    /**
     * Persist given entity to database.
     * @param Entity $entity
     * @param bool $autoFlush
     */
    public function persist($entity, $autoFlush = true)
    {
        $this->em->persist($entity);
        if ($autoFlush === true) {
            $this->flush();
        }
    }

    /**
     * Remove given entity from database.
     * @param Entity $entity
     * @param bool $autoFlush
     */
    public function remove($entity, $autoFlush = true)
    {
        $this->em->remove($entity);
        if ($autoFlush === true) {
            $this->flush();
        }
    }

    /**
     * Flush all changes made to appropriate entities.
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Find all matching entities based on given criteria.
     * @param Criteria $params
     * @return array
     */
    public function matching(Criteria $params)
    {
        return $this->repository->matching($params);
    }
}
