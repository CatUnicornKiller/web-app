<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EventParticipant;

/**
 * Repository of operations performed on EventParticipant entities.
 *
 * @method EventParticipant|null get($id)
 * @method EventParticipant[] findAll()
 * @method EventParticipant[] findBy($params, $orderBy = [])
 * @method EventParticipant|null findOneBy($params)
 * @method EventParticipant findOrThrow($id)
 */
class EventParticipants extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EventParticipant::class);
    }
}
