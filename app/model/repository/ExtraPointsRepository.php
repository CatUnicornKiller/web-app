<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\ExtraPoints;

/**
 * Repository of operations performed on ExtraPoints entities.
 *
 * @method ExtraPoints|null get($id)
 * @method ExtraPoints[] findAll()
 * @method ExtraPoints[] findBy($params, $orderBy = [])
 * @method ExtraPoints|null findOneBy($params)
 * @method ExtraPoints findOrThrow($id)
 */
class ExtraPointsRepository extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, ExtraPoints::class);
    }
}
