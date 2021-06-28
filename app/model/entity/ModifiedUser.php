<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedUser
 *
 * @ORM\Table(name="modified__user")
 * @ORM\Entity
 */
class ModifiedUser
{
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="modifications")
     */
    protected $modifiedUser;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, User $modified)
    {
        $this->user = $user;
        $this->modifiedUser = $modified;
        $this->modTime = new DateTime;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getModTime(): DateTime
    {
        return $this->modTime;
    }

    public function getUser(): ?User
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getModifiedUser(): ?User
    {
        try {
            $this->modifiedUser->getDeleted();
            return $this->modifiedUser; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
