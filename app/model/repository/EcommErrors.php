<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EcommError;

/**
 * Repository of operations performed on EcommError entities.
 *
 * @method EcommError|null get($id)
 * @method EcommError[] findAll()
 * @method EcommError[] findBy($params, $orderBy = [])
 * @method EcommError|null findOneBy($params)
 * @method EcommError findOrThrow($id)
 */
class EcommErrors extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EcommError::class);
    }
}
