<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\LoginLog;

/**
 * Repository of operations performed on LoginLog entities.
 *
 * @method LoginLog|null get($id)
 * @method LoginLog[] findAll()
 * @method LoginLog[] findBy($params, $orderBy = [])
 * @method LoginLog|null findOneBy($params)
 * @method LoginLog findOrThrow($id)
 */
class LoginLogs extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
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
