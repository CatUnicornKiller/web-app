<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedUserRole
 *
 * @ORM\Table(name="modified__user_role")
 * @ORM\Entity
 *
 * @property integer $id
 * @property User $user
 * @property User $modifiedUser
 * @property string $roleOld
 * @property string $roleNew
 * @property datetime $modTime
 */
class ModifiedUserRole
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
        $this->modTime = new \DateTime;
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

    public function getModifiedUser()
    {
        try {
            $this->modifiedUser->getDeleted();
            return $this->modifiedUser; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
