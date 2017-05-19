<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\IfmsaPerson;

/**
 * Repository of operations performed on IfmsaPerson entities.
 */
class IfmsaPersons extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, IfmsaPerson::class);
    }

    /**
     * Find ifmsa person entity by given af number.
     * @param string $afNumber
     * @return IfmsaPerson|NULL
     */
    public function findByAfNumber($afNumber)
    {
        return $this->findOneBy(array("afNumber" => $afNumber));
    }
}
