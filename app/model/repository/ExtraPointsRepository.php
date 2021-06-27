<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\ExtraPoints;

/**
 * Repository of operations performed on ExtraPoints entities.
 */
class ExtraPointsRepository extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, ExtraPoints::class);
    }
}
