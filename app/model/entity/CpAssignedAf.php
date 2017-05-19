<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CpAssignedAf
 *
 * @ORM\Entity
 */
class CpAssignedAf
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $afNumber;

    /**
     * @ORM\Column(type="text")
     */
    protected $afName;

    /**
     * @ORM\Column(type="date")
     */
    protected $afArrival;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="assignedIncomings")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="CpTask", mappedBy="cpAssignedAf")
     */
    protected $tasks;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedCpAssignedAf", mappedBy="cpAssignedAf", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(User $user, $afNumber, $afName, $afArrival)
    {
        $this->user = $user;
        $this->afNumber = $afNumber;
        $this->afName = $afName;
        $this->afArrival = new \DateTime($afArrival);
        $this->tasks = new ArrayCollection;
        $this->modifications = new ArrayCollection;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedCpAssignedAf($user, $this);
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

    public function getCompletedTasks()
    {
        return $this->tasks->filter(function (CpTask $task) {
            return $task->isCompleted() == true ? true : false;
        });
    }
}
