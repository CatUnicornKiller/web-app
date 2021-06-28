<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedEventFile
 *
 * @ORM\Table(name="modified__event_file")
 * @ORM\Entity
 */
class ModifiedEventFile
{
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
     * @ORM\ManyToOne(targetEntity="EventFile", inversedBy="modifications")
     */
    protected $eventFile;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, EventFile $eventFile)
    {
        $this->user = $user;
        $this->eventFile = $eventFile;
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
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getEventFile(): ?EventFile
    {
        try {
            $this->eventFile->getDeleted();
            return $this->eventFile; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
