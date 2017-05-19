<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\RenewedPassword;

/**
 * Repository of operations performed on RenewedPassword entities.
 */
class RenewedPasswords extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, RenewedPassword::class);
    }
}
