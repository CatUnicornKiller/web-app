<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedCpTask
 *
 * @ORM\Table(name="modified__cp_task")
 * @ORM\Entity
 */
class ModifiedCpTask
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

    public function getCpTask()
    {
        try {
            $this->cpTask->getDeleted();
            return $this->cpTask; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
