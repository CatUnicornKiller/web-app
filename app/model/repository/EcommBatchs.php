<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\EcommBatch;

/**
 * Repository of operations performed on EcommBatch entities.
 */
class EcommBatchs extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, EcommBatch::class);
    }
}
