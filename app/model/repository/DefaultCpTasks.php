<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\DefaultCpTask;

/**
 * Repository of operations performed on DefaultCpTask entities.
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
