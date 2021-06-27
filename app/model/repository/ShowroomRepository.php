<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\Showroom;

/**
 * Repository of operations performed on Showroom entities.
 */
class ShowroomRepository extends BaseRepository
{
    /**
     * DI Contructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Showroom::class);
    }

    /**
     * Find showroom entries by the given role.
     * @param string $role
     * @return array
     */
    public function findByRole($role)
    {
        return $this->findBy(array("role" => $role));
    }
}
