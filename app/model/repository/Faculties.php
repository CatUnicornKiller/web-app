<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\Faculty;

/**
 * Repository of operations performed on Faculty entities.
 */
class Faculties extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, Faculty::class);
    }

    /**
     * Find faculties by given name.
     * @param string $name
     * @return array
     */
    public function findByName(string $name)
    {
        return $this->findBy(array("facultyName" => $name));
    }
}
