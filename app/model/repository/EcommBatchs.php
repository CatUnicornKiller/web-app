<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EcommBatch;

/**
 * Repository of operations performed on EcommBatch entities.
 */
class EcommBatchs extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EcommBatch::class);
    }
}
