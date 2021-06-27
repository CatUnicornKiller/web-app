<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\CpAssignedAf;

/**
 * Repository of operations performed on CpAssignedAf entities.
 */
class CpAssignedAfs extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, CpAssignedAf::class);
    }

    /**
     * Find assigned incoming entry by given af number.
     * @param string $afNumber
     * @return CpAssignedAf|NULL
     */
    public function findOneByAfNumber($afNumber)
    {
        return $this->findOneBy(array("afNumber" => $afNumber));
    }
}
