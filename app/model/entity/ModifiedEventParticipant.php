<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedEventParticipant
 *
 * @ORM\Table(name="modified__event_participant")
 * @ORM\Entity
 */
class ModifiedEventParticipant
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="mod_time", type="datetime")
     */
    protected $modTime;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="EventParticipant", inversedBy="modifications")
     */
    protected $eventParticipant;


    public function __construct(User $user, EventParticipant $eventParticipant)
    {
        $this->user = $user;
        $this->eventParticipant = $eventParticipant;
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

    public function getEventParticipant()
    {
        try {
            $this->eventParticipant->getDeleted();
            return $this->eventParticipant; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
