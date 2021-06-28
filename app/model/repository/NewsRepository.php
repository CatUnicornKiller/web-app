<?php

namespace App\Model\Repository;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\News;

/**
 * Repository of operations performed on News entities.
 *
 * @method News|null get($id)
 * @method News[] findAll()
 * @method News[] findBy($params, $orderBy = [])
 * @method News|null findOneBy($params)
 * @method News findOrThrow($id)
 */
class NewsRepository extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, News::class);
    }

    /**
     * Get all News entities which are max 30 days old.
     * @return array
     */
    public function getCurrentNews()
    {
        $now = new DateTime;
        $thirtyDaysAgo = new DateTime("-30 days");

        $qb = $this->em->createQueryBuilder();
        $qb->select("n")
                ->from(News::class, "n")
                ->add("where", $qb->expr()->between("n.date", ":from", ":to"))
                ->orderBy("n.date", "DESC")
                ->setParameters(array("from" => $thirtyDaysAgo, "to" => $now));

        return $qb->getQuery()->getResult();
    }
}
