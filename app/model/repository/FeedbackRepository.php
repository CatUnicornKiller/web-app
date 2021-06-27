<?php

namespace App\Model\Repository;

use App\Model\Entity\Country;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\Feedback;

/**
 * Repository of operations performed on Feedback entities.
 */
class FeedbackRepository extends BaseRepository
{
    /**
     * DI Contructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Feedback::class);
    }

    /**
     * Get list of feedback for some particular country which comply all given
     * parameters.
     * @param string $orderby
     * @param string $order
     * @param ?Country $country
     * @param ?DateTime $startDate
     * @param ?DateTime $endDate
     * @param string $exchangeType
     * @param string $hostCity
     * @param string $hostFaculty
     * @return \Doctrine\ORM\Query
     */
    public function getCountryFeedbackListQuery(
        $orderby,
        $order,
        $country,
        $startDate,
        $endDate,
        $exchangeType = "",
        $hostCity = "",
        $hostFaculty = ""
    ) {
        $ord = "id";
        $ad = "ASC";

        if (strtolower($order) == "desc") {
            $ad = "DESC";
        }
        if ($orderby == "name") {
            $ord = "name";
        } elseif ($orderby == "faculty") {
            $ord = "hostFaculty";
        } elseif ($orderby == "date") {
            $ord = "startDate";
        } elseif ($orderby == "exchangeType") {
            $ord = "exchangeType";
        } elseif ($orderby == "city") {
            $ord = "hostCity";
        } elseif ($orderby == "country") {
            $ord = "country";
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select("f")->from(Feedback::class, "f");

        if ($country) {
            $qb->andWhere($qb->expr()->eq("f.country", ":country"))
                    ->setParameter("country", $country);
        }

        if ($startDate) {
            $start = date_create_from_format("j. n. Y", $startDate)->format("Y-m-d 00:00:00");
            $qb->andWhere($qb->expr()->gte("f.endDate", ":start"))
                    ->setParameter("start", $start);
        }
        if ($endDate) {
            $end = date_create_from_format("j. n. Y", $endDate)->format("Y-m-d 23:59:59");
            $qb->andWhere($qb->expr()->lte("f.startDate", ":end"))
                    ->setParameter("end", $end);
        }

        if (!empty($exchangeType)) {
            $qb->andWhere($qb->expr()->eq("f.exchangeType", ":exchangeType"))
                    ->setParameter("exchangeType", $exchangeType);
        }
        if (!empty($hostCity)) {
            $qb->andWhere($qb->expr()->eq("f.hostCity", ":hostCity"))
                    ->setParameter("hostCity", $hostCity);
        }
        if (!empty($hostFaculty)) {
            $qb->andWhere($qb->expr()->eq("f.hostFaculty", ":hostFaculty"))
                    ->setParameter("hostFaculty", $hostFaculty);
        }

        $qb->orderBy("f." . $ord, $ad);
        return $qb->getQuery();
    }
}
