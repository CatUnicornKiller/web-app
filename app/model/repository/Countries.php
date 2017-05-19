<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\Country;

/**
 * Repository of operations performed on Country entities.
 */
class Countries extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
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
