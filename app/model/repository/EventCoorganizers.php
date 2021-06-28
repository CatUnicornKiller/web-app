<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EventCoorganizer;

/**
 * Repository of operations performed on EventCoorganizer entities.
 *
 * @method EventCoorganizer|null get($id)
 * @method EventCoorganizer[] findAll()
 * @method EventCoorganizer[] findBy($params, $orderBy = [])
 * @method EventCoorganizer|null findOneBy($params)
 * @method EventCoorganizer findOrThrow($id)
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
