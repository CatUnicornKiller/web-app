<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventCoorganizer
 *
 * @ORM\Entity
 */
class EventCoorganizer
{
    use MagicGetters;

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

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getDeleted(): string
    {
        return $this->deleted;
    }

    public function getModifications(): Collection
    {
        return $this->modifications;
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

    public function getUser(): ?User
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getEvent(): ?Event
    {
        try {
            $this->event->getDeleted();
            return $this->event; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
