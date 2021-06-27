<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EventCoorganizer;

/**
 * Repository of operations performed on EventCoorganizer entities.
 */
class EventCoorganizers extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EventCoorganizer::class);
    }
}
