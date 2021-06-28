<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\DefaultCpTask;

/**
 * Repository of operations performed on DefaultCpTask entities.
 *
 * @method DefaultCpTask|null get($id)
 * @method DefaultCpTask[] findAll()
 * @method DefaultCpTask[] findBy($params, $orderBy = [])
 * @method DefaultCpTask|null findOneBy($params)
 * @method DefaultCpTask findOrThrow($id)
 */
class DefaultCpTasks extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, DefaultCpTask::class);
    }
}
