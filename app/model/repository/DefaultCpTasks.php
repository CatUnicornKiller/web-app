<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\DefaultCpTask;

/**
 * Repository of operations performed on DefaultCpTask entities.
 */
class DefaultCpTasks extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, DefaultCpTask::class);
    }
}
