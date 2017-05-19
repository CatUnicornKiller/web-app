<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedEventCoorganizer
 *
 * @ORM\Table(name="modified__event_coorganizer")
 * @ORM\Entity
 */
class ModifiedEventCoorganizer
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
     * @ORM\ManyToOne(targetEntity="EventCoorganizer", inversedBy="modifications")
     */
    protected $eventCoorganizer;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, EventCoorganizer $eventCoorganizer)
    {
        $this->user = $user;
        $this->eventCoorganizer = $eventCoorganizer;
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

    public function getEventCoorganizer()
    {
        try {
            $this->eventCoorganizer->getDeleted();
            return $this->eventCoorganizer; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
