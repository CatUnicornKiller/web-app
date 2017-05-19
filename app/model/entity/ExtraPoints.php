<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ExtraPoints
 *
 * @ORM\Entity
 */
class ExtraPoints
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points = 0;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="extraPointsList")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $assignedByUser;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedExtraPoints", mappedBy="extraPoints", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(
        User $user,
        User $assignedByUser,
        $points,
        $description
    ) {

        $this->user = $user;
        $this->assignedByUser = $assignedByUser;
        $this->points = $points;
        $this->description = $description;
        $this->modifications = new ArrayCollection;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedExtraPoints($user, $this);
        $this->modifications->add($modified);
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

    public function getAssignedByUser()
    {
        try {
            $this->assignedByUser->getDeleted();
            return $this->assignedByUser; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
