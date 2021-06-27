<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EcommTransaction;

/**
 * Repository of operations performed on EcommTransaction entities.
 */
class EcommTransactions extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EcommTransaction::class);
    }

    /**
     * Find one transaction by given identification.
     * @param string $transId
     * @return EcommTransaction
     */
    public function findOneByTransactionId($transId)
    {
        return $this->findOneBy(array("transId" => $transId));
    }

    /**
     * Get list of transactions which comply all given parameters.
     * @note Returns only constructed query.
     * @param int $year
     * @param int $month
     * @param bool $paid
     * @return \Doctrine\ORM\Query
     */
    public function getTransactionListQuery($year, $month, $paid)
    {
        if ($month == "" || ctype_digit($month) == false) {
            $startDate = date_create_from_format("Y/m/d", $year . "/1/01");
            $endDate = date_create_from_format("Y/m/d", ($year + 1) . "/1/01");
        } else {
            $nextMonth = $month + 1;
            if ($nextMonth == 13) {
                $nextMonth = 1;
            }
            $startDate = date_create_from_format("Y/m/d", $year . "/" . $month . "/01");
            $endDate = date_create_from_format("Y/m/d", $year . "/" . $nextMonth . "/01");
        }

        if ($startDate == false || $endDate == false) {
            $startDate = date_create_from_format("Y/m/d", date("Y") . "/1/01");
            $endDate = date_create_from_format("Y/m/d", (date("Y") + 1) . "/1/01");
        }

        $qb = $this->repository->createQueryBuilder("t");
        $qb->add("where", $qb->expr()->between("t.tDate", ":from", ":to"))
                ->setParameters(array("from" => $startDate, "to" => $endDate));

        if ($paid) {
            $qb->andWhere("t.result = :ok")
                    ->setParameter("ok", "OK");
        }

        return $qb->orderBy("t.tDate", "desc")->getQuery();
    }
}
