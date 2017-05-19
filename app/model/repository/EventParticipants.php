<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\EventParticipant;

/**
 * Repository of operations performed on EventParticipant entities.
 */
class EventParticipants extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, EventParticipant::class);
    }
}
