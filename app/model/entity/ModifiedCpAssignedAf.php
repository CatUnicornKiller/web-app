<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedCpAssignedAf
 *
 * @ORM\Table(name="modified__cp_assigned_af")
 * @ORM\Entity
 */
class ModifiedCpAssignedAf
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
     * @ORM\ManyToOne(targetEntity="CpAssignedAf", inversedBy="modifications")
     */
    protected $cpAssignedAf;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, CpAssignedAf $cpAssignedAf)
    {
        $this->user = $user;
        $this->cpAssignedAf = $cpAssignedAf;
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
