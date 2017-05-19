<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedEvent
 *
 * @ORM\Table(name="modified__event")
 * @ORM\Entity
 */
class ModifiedEvent
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
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="modifications")
     */
    protected $event;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
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

    public function getEvent()
    {
        try {
            $this->event->getDeleted();
            return $this->event; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
