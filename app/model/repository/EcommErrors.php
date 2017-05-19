<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\EcommError;

/**
 * Repository of operations performed on EcommError entities.
 */
class EcommErrors extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, EcommError::class);
    }
}
