<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedEventFile
 *
 * @ORM\Table(name="modified__event_file")
 * @ORM\Entity
 */
class ModifiedEventFile
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

    public function getEventFile()
    {
        try {
            $this->eventFile->getDeleted();
            return $this->eventFile; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
