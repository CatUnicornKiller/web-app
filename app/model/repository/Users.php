<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Exceptions\NotFoundException;
use App\Model\Entity\User;
use App\Model\Entity\Faculty;

/**
 * Repository of operations performed on User entities.
 */
class Users extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, User::class);
    }

    /**
     * Find one user by given username.
     * @param string $username
     * @return User|NULL
     */
    public function findByUsername(string $username)
    {
        return $this->findOneBy([ "username" => $username ]);
    }

    /**
     * Find one user by given username and email.
     * @param string $username
     * @param string $email
     * @return User|NULL
     */
    public function findByUsernameAndEmail(string $username, string $email)
    {
        return $this->findOneBy(array("username" => $username, "email" => $email));
    }

    /**
     * Find users by given role.
     * @param string $role
     * @return array
     */
    public function findByRole($role)
    {
        return $this->findBy(array("role" => $role));
    }

    /**
     * Find officer by given identification or throw exception
     * @param int $id user identification
     * @return User
     * @throws NotFoundException if officer was not found
     */
    public function findOfficerOrThrow($id)
    {
        $user = $this->get($id);
        if (!$user || !$user->isOfficer()) {
            throw new NotFoundException("Cannot find '$id'");
        }
        return $user;
    }

    /**
     * Find incoming by given identification or throw exception
     * @param int $id user identification
     * @return User
     * @throws NotFoundException if incoming was not found
     */
    public function findIncomingOrThrow($id)
    {
        $user = $this->get($id);
        if (!$user || !$user->isIncoming()) {
            throw new NotFoundException("Cannot find '$id'");
        }
        return $user;
    }

    /**
     * Count users based on given type, faculty and privileges.
     * @param string $type
     * @param ?Faculty $faculty
     * @param string $privileges
     * @return int
     */
    private function countUsers($type, $faculty, $privileges)
    {
        $criteria = array("userType" => $type);
        if ($faculty && !empty($faculty)) {
            $criteria["faculty"] = $faculty;
        }
        if ($privileges && !empty($privileges)) {
            $criteria["role"] = $privileges;
        }

        return $this->repository->countBy($criteria);
    }

    /**
     * Count officers in database based on given faculty and privileges.
     * @param Faculty $faculty
     * @param string $privileges
     * @return int
     */
    public function countOfficers($faculty = null, $privileges = "")
    {
        return $this->countUsers("officer", $faculty, $privileges);
    }

    /**
     * Count incomings based on given faculty and privileges.
     * @param Faculty $faculty
     * @param string $privileges
     * @return int
     */
    public function countIncomings($faculty = null, $privileges = "")
    {
        return $this->countUsers("incoming", $faculty, $privileges);
    }

    /**
     * Get all officers.
     * @return array
     */
    public function getOfficers()
    {
        return $this->findBy(array("userType" => "officer"));
    }

    /**
     * Get all officers from given faculty.
     * @param Faculty $faculty
     * @return array
     */
    public function getFacultyOfficers(Faculty $faculty)
    {
        return $this->findBy(array("userType" => "officer", "faculty" => $faculty));
    }

    /**
     * Get list of officers which comply all given parameters.
     * @note Returns only constructed query.
     * @param string $orderby
     * @param string $order
     * @param Faculty $faculty
     * @param string $privileges
     * @return \Doctrine\ORM\Query
     */
    public function getOfficersListQuery(
        $orderby,
        $order,
        $faculty = null,
        $privileges = ""
    ) {
        $ord = "id";
        $ad = "ASC";

        if (strtolower($order) == "desc") {
            $ad = "DESC";
        }

        if ($orderby == "username") {
            $ord = "username";
        } elseif ($orderby == "name") {
            $ord = "firstname";
        } elseif ($orderby == "privileges") {
            $ord = "role";
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select("u")->from(User::class, "u");
        $qb->andWhere("u.userType = :userType")
                ->setParameter("userType", "officer");

        if ($faculty) {
            $qb->andWhere("u.faculty = :faculty")
                    ->setParameter("faculty", $faculty);
        }

        if ($privileges && !empty($privileges)) {
            $qb->andWhere("u.role = :role")
                    ->setParameter("role", $privileges);
        }

        $qb->orderBy("u." . $ord, $ad);
        return $qb->getQuery();
    }

    /**
     * Get list of incomings which comply all given parameters.
     * @note Returns only constructed query.
     * @param string $orderby
     * @param string $order
     * @param Faculty $faculty
     * @param string $privileges
     * @return \Doctrine\ORM\Query
     */
    public function getIncomingsListQuery(
        $orderby,
        $order,
        $faculty = null,
        $privileges = ""
    ) {

        $ord = "id";
        $ad = "ASC";

        if (strtolower($order) == "desc") {
            $ad = "DESC";
        }

        if ($orderby == "username") {
            $ord = "username";
        } elseif ($orderby == "name") {
            $ord = "firstname";
        } elseif ($orderby == "privileges") {
            $ord = "role";
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select("u")->from(User::class, "u");
        $qb->andWhere("u.userType = :userType")
                ->setParameter("userType", "incoming");

        if ($faculty) {
            $qb->andWhere("u.faculty = :faculty")
                    ->setParameter("faculty", $faculty);
        }

        if ($privileges && !empty($privileges)) {
            $qb->andWhere("u.role = :role")
                    ->setParameter("role", $privileges);
        }

        $qb->orderBy("u." . $ord, $ad);
        return $qb->getQuery();
    }
}
