<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\Faculty;

/**
 * Repository of operations performed on Faculty entities.
 *
 * @method Faculty|null get($id)
 * @method Faculty[] findAll()
 * @method Faculty[] findBy($params, $orderBy = [])
 * @method Faculty|null findOneBy($params)
 * @method Faculty findOrThrow($id)
 */
class Faculties extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
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
