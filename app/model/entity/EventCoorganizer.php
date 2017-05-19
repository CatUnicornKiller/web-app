<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventCoorganizer
 *
 * @ORM\Entity
 */
class EventCoorganizer
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points = '0';

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="coorganizedEvents")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="coorganizers")
     */
    protected $event;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedEventCoorganizer", mappedBy="eventCoorganizer", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
        $this->points = 3;
        $this->modifications = new ArrayCollection;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedEventCoorganizer($user, $this);
        $this->modifications->add($modified);
    }

    public function delete()
    {
        $this->deleted = true;
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
