<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedCpTask
 *
 * @ORM\Table(name="modified__cp_task")
 * @ORM\Entity
 */
class ModifiedCpTask
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
     * @ORM\ManyToOne(targetEntity="CpTask", inversedBy="modifications")
     */
    protected $cpTask;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, CpTask $cpTask)
    {
        $this->user = $user;
        $this->cpTask = $cpTask;
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
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getCpTask(): ?CpTask
    {
        try {
            $this->cpTask->getDeleted();
            return $this->cpTask; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
