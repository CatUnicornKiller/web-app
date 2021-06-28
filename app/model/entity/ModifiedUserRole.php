<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedUserRole
 *
 * @ORM\Table(name="modified__user_role")
 * @ORM\Entity
 */
class ModifiedUserRole
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="roleModifications")
     */
    protected $modifiedUser;

    /**
     * @ORM\Column(type="string")
     */
    protected $roleOld;

    /**
     * @ORM\Column(type="string")
     */
    protected $roleNew;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, User $modUser, $roleOld, $roleNew)
    {
        $this->user = $user;
        $this->modifiedUser = $modUser;
        $this->roleOld = $roleOld;
        $this->roleNew = $roleNew;
        $this->modTime = new DateTime;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoleOld(): string
    {
        return $this->roleOld;
    }

    public function getRoleNew(): string
    {
        return $this->roleNew;
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
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getModifiedUser(): ?User
    {
        try {
            $this->modifiedUser->getDeleted();
            return $this->modifiedUser; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
