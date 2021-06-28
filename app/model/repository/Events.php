<?php

namespace App\Model\Repository;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\Event;
use App\Model\Entity\Faculty;
use Doctrine\ORM\Query;

/**
 * Repository of operations performed on Event entities.
 *
 * @method Event|null get($id)
 * @method Event[] findAll()
 * @method Event[] findBy($params, $orderBy = [])
 * @method Event|null findOneBy($params)
 * @method Event findOrThrow($id)
 */
class Events extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Event::class);
    }

    /**
     * Count events based on given faculty.
     * @param Faculty $faculty
     * @return int
     */
    public function countEvents(Faculty $faculty = null)
    {
        if ($faculty) {
            $qb = $this->repository->createQueryBuilder("e");
            $qb->select("count(e.id)");
            $qb->leftJoin("e.visibleToFaculties", "f");
            $qb->andWhere($qb->expr()->in('f.id', $faculty->getId()));
            return $qb->getQuery()->getSingleScalarResult();
        }

        return $this->countAll();
    }

    /**
     * Get list of faculty events which comply all given parameters.
     * @note Returns only constructed query.
     * @param Faculty $faculty
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $socialProgram
     * @param bool $academicQuality
     * @param ?Faculty $organizerFaculty
     * @return Query
     */
    public function getEventsListQuery(
        Faculty $faculty,
        $startDate,
        $endDate,
        $socialProgram,
        $academicQuality,
        $organizerFaculty
    ) {
        $qb = $this->repository->createQueryBuilder("e");
        $qb->innerJoin("e.user", "u");
        $qb->andWhere("e.date <= :endDate")
                ->setParameter("endDate", $endDate);
        $qb->andWhere("e.endDate >= :startDate")
                ->setParameter("startDate", $startDate);

        if (!empty($socialProgram)) {
            $qb->andWhere("e.socialProgram = :socialProgram")
                    ->setParameter("socialProgram", 1);
        }

        if (!empty($academicQuality)) {
            $qb->andWhere("e.academicQuality = :academicQuality")
                    ->setParameter("academicQuality", 1);
        }

        if ($organizerFaculty) {
            $qb->andWhere("u.faculty = :faculty")
                    ->setParameter("faculty", $organizerFaculty);
        }

        // find events visible to given faculty
        $qb->leftJoin("e.visibleToFaculties", "vf");
        $qb->andWhere($qb->expr()->in('vf.id', $faculty->getId()));

        return $qb->getQuery();
    }

    /**
     * Get list of all events which comply all given parameters.
     * @note Returns only constructed query.
     * @param string $orderby
     * @param string $order
     * @param ?Faculty $faculty
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return Query
     */
    public function getAllEventsListQuery(
        $orderby,
        $order,
        $faculty,
        $startDate,
        $endDate
    ) {

        $ord = "e.date";
        $ad = "ASC";

        if (strtolower($order) == "desc") {
            $ad = "DESC";
        }
        if ($orderby == "organizer") {
            $ord = "u.firstname";
        } elseif ($orderby == "endDate") {
            $ord = "e.endDate";
        } elseif ($orderby == "eventName") {
            $ord = "e.eventName";
        } elseif ($orderby == "eventDescription") {
            $ord = "e.eventDescription";
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select("e")->from(Event::class, "e");
        $qb->innerJoin("e.user", "u");
        $qb->andWhere("e.date <= :endDate")
                ->setParameter("endDate", $endDate);
        $qb->andWhere("e.endDate >= :startDate")
                ->setParameter("startDate", $startDate);

        if ($faculty) {
            $qb->andWhere("u.faculty = :faculty")
                    ->setParameter("faculty", $faculty);
        }

        $qb->orderBy($ord, $ad);
        return $qb->getQuery();
    }
}
