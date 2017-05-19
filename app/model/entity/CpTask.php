<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CpTask
 *
 * @ORM\Entity
 */
class CpTask
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

    public function isCompleted()
    {
        return $this->completed == 1 ? true : false;
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

    public function getCpAssignedAf()
    {
        try {
            $this->cpAssignedAf->getDeleted();
            return $this->cpAssignedAf; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
