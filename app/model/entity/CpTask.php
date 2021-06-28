<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CpTask
 *
 * @ORM\Entity
 */
class CpTask
{
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
     * @ORM\Column(type="integer")
     */
    protected $sortOrder = '0';

    /**
     * @ORM\Column(type="text"))
     */
    protected $cpTasksDescription;

    /**
     * @ORM\Column(type="text")
     */
    protected $cpTasksNote;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\Column(type="integer")
     */
    protected $completed = '0';

    /**
     * @ORM\ManyToOne(targetEntity="CpAssignedAf", inversedBy="tasks")
     */
    protected $cpAssignedAf;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedCpTask", mappedBy="cpTask", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(
        CpAssignedAf $cpAssignedAf,
        $afNumber,
        $description,
        $note
    ) {
        $this->cpAssignedAf = $cpAssignedAf;
        $this->afNumber = $afNumber;
        $this->cpTasksDescription = $description;
        $this->cpTasksNote = $note;
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

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    public function getCpTasksDescription(): string
    {
        return $this->cpTasksDescription;
    }

    public function getCpTasksNote(): string
    {
        return $this->cpTasksNote;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getCompleted(): string
    {
        return $this->completed;
    }

    public function getModifications(): ArrayCollection
    {
        return $this->modifications;
    }

    public function isCompleted(): bool
    {
        return $this->completed ? true : false;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedCpTask($user, $this);
        $this->modifications->add($modified);
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function getCpAssignedAf(): ?CpAssignedAf
    {
        try {
            $this->cpAssignedAf->getDeleted();
            return $this->cpAssignedAf; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
