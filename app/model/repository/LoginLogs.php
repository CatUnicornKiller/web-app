<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\LoginLog;

/**
 * Repository of operations performed on LoginLog entities.
 */
class LoginLogs extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, LoginLog::class);
    }

    /**
     * Get last LoginLog entities for officers based on given count.
     * @param int $count
     * @return array
     */
    public function getLastOfficersLogins($count = 50)
    {
        $qb = $this->repository->createQueryBuilder("l");
        $qb->innerJoin("l.user", "u");
        $qb->andWhere("u.userType = :type")
                ->setParameter("type", "officer");
        $qb->orderBy("l.id", "desc")->setMaxResults($count);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get last LoginLog entities for incomings based on given count.
     * @param int $count
     * @return array
     */
    public function getLastIncomingsLogins($count = 50)
    {
        $qb = $this->repository->createQueryBuilder("l");
        $qb->innerJoin("l.user", "u");
        $qb->andWhere("u.userType = :type")
                ->setParameter("type", "incoming");
        $qb->orderBy("l.id", "desc")->setMaxResults($count);

        return $qb->getQuery()->getResult();
    }
}
