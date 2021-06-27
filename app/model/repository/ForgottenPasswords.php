<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\ForgottenPassword;

/**
 * Repository of operations performed on ForgottenPassword entities.
 */
class ForgottenPasswords extends BaseRepository
{
    /**
     * DI Contructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, ForgottenPassword::class);
    }

    /**
     * Find one forgotten password entry by the given token.
     * @param string $token
     * @return ForgottenPassword|NULL
     */
    public function findOneActiveByToken($token)
    {
        $now = new \DateTime;
        $tenMinutesAgo = new \DateTime("-10 minutes");

        $qb = $this->em->createQueryBuilder();
        $qb->select("f")
                ->from(ForgottenPassword::class, "f")
                ->where($qb->expr()->eq("f.token", $token))
                ->add("where", $qb->expr()->between("f.time", ":from", ":to"))
                ->setParameters(array("from" => $tenMinutesAgo, "to" => $now));

        return $qb->getQuery()->getOneOrNullResult();
    }
}
