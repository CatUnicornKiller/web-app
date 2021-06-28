<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedCpAssignedAf
 *
 * @ORM\Table(name="modified__cp_assigned_af")
 * @ORM\Entity
 */
class ModifiedCpAssignedAf
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

    public function getCpAssignedAf(): ?CpAssignedAf
    {
        try {
            $this->cpAssignedAf->getDeleted();
            return $this->cpAssignedAf; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
