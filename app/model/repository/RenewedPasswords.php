<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\RenewedPassword;

/**
 * Repository of operations performed on RenewedPassword entities.
 *
 * @method RenewedPassword|null get($id)
 * @method RenewedPassword[] findAll()
 * @method RenewedPassword[] findBy($params, $orderBy = [])
 * @method RenewedPassword|null findOneBy($params)
 * @method RenewedPassword findOrThrow($id)
 */
class RenewedPasswords extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, RenewedPassword::class);
    }
}
