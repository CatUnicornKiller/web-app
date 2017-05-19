<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\News;

/**
 * Repository of operations performed on News entities.
 */
class NewsRepository extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, News::class);
    }

    /**
     * Get all News entities which are max 30 days old.
     * @return array
     */
    public function getCurrentNews()
    {
        $now = new \DateTime;
        $thirtyDaysAgo = new \DateTime("-30 days");

        $qb = $this->em->createQueryBuilder();
        $qb->select("n")
                ->from(News::class, "n")
                ->add("where", $qb->expr()->between("n.date", ":from", ":to"))
                ->orderBy("n.date", "DESC")
                ->setParameters(array("from" => $thirtyDaysAgo, "to" => $now));

        return $qb->getQuery()->getResult();
    }
}
