<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CpAssignedAf
 *
 * @ORM\Entity
 */
class CpAssignedAf
{
    use MagicGetters;

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
        $this->afArrival = new DateTime($afArrival);
        $this->tasks = new ArrayCollection;
        $this->modifications = new ArrayCollection;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getAfNumber(): int
    {
        return $this->afNumber;
    }

    public function getAfName(): string
    {
        return $this->afName;
    }

    public function getAfArrival(): DateTime
    {
        return $this->afArrival;
    }

    public function setAfArrival(DateTime $afArrival): void
    {
        $this->afArrival = $afArrival;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function getModifications(): Collection
    {
        return $this->modifications;
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

    public function getUser(): ?User
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getCompletedTasks()
    {
        return $this->tasks->filter(function (CpTask $task) {
            return $task->isCompleted();
        });
    }
}
