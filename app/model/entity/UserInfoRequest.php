<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserInfoRequest
 *
 * @ORM\Entity
 */
class UserInfoRequest
{
    use MagicGetters;

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
        $this->time = new DateTime;
        $this->requestDesc = $requestDesc;
    }

    public static function requestAdditionalInfo(User $user, User $requestedUser)
    {
        return new self($user, $requestedUser, "additional_information");
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function getRequestDesc(): string
    {
        return $this->requestDesc;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getDeletedTime(): DateTime
    {
        return $this->deletedTime;
    }

    public function setDeletedTime(DateTime $deletedTime): void
    {
        $this->deletedTime = $deletedTime;
    }

    public function getCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
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
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getRequestedUser()
    {
        try {
            $this->requestedUser->getDeleted();
            return $this->requestedUser; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getDeletedByUser(): ?User
    {
        try {
            $this->deletedByUser->getDeleted();
            return $this->deletedByUser; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function setDeletedByUser(?User $deletedByUser): void
    {
        $this->deletedByUser = $deletedByUser;
    }
}
