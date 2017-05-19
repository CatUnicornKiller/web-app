<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\ExtraPoints;

/**
 * Repository of operations performed on ExtraPoints entities.
 */
class ExtraPointsRepository extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, ExtraPoints::class);
    }
}
