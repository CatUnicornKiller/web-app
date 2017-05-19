<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\EventCoorganizer;

/**
 * Repository of operations performed on EventCoorganizer entities.
 */
class EventCoorganizers extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, EventCoorganizer::class);
    }
}
