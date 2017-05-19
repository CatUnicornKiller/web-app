<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * UserInfoRequest
 *
 * @ORM\Entity
 */
class UserInfoRequest
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="infoRequests")
     */
    protected $requestedUser;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $time;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $requestDesc;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $deletedByUser;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedTime;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $completed = '0';


    public function __construct(User $user, User $requestedUser, $requestDesc)
    {
        $this->user = $user;
        $this->requestedUser = $requestedUser;
        $this->time = new \DateTime;
        $this->requestDesc = $requestDesc;
    }

    public static function requestAdditionalInfo(User $user, User $requestedUser)
    {
        return new self($user, $requestedUser, "additional_information");
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function getUser()
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getRequestedUser()
    {
        try {
            $this->requestedUser->getDeleted();
            return $this->requestedUser; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getDeletedByUser()
    {
        try {
            $this->deletedByUser->getDeleted();
            return $this->deletedByUser; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
