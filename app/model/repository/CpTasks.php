<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\CpTask;

/**
 * Repository of operations performed on CpTask entities.
 *
 * @method CpTask|null get($id)
 * @method CpTask[] findAll()
 * @method CpTask[] findBy($params, $orderBy = [])
 * @method CpTask|null findOneBy($params)
 * @method CpTask findOrThrow($id)
 */
class CpTasks extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, CpTask::class);
    }
}
