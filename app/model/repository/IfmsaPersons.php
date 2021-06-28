<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\IfmsaPerson;

/**
 * Repository of operations performed on IfmsaPerson entities.
 *
 * @method IfmsaPerson|null get($id)
 * @method IfmsaPerson[] findAll()
 * @method IfmsaPerson[] findBy($params, $orderBy = [])
 * @method IfmsaPerson|null findOneBy($params)
 * @method IfmsaPerson findOrThrow($id)
 */
class IfmsaPersons extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, IfmsaPerson::class);
    }

    /**
     * Find ifmsa person entity by given af number.
     * @param int $afNumber
     * @return IfmsaPerson|NULL
     */
    public function findByAfNumber($afNumber)
    {
        return $this->findOneBy(array("afNumber" => $afNumber));
    }
}
