<?php

namespace App\Model\Repository;

use App\Model\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository of operations performed on Country entities.
 */
class Countries extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Country::class);
    }

    /**
     * Get countries which are marked as belonging to ifmsa program.
     * @return array
     */
    public function getIfmsaCountries()
    {
        return $this->findBy(array("isIfmsa" => 1));
    }
}
