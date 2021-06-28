<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventParticipant
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_participation", columns={"event_id", "user_id"})})
 * @ORM\Entity
 */
class EventParticipant
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
    protected $paid = '0';

    /**
     * @ORM\OneToMany(targetEntity="EcommTransaction", mappedBy="eventParticipant")
     */
    protected $ecommTransactions;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="participants")
     */
    protected $event;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="participatedEvents")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedEventParticipant", mappedBy="eventParticipant", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
        $this->modifications = new ArrayCollection;
        $this->ecommTransactions = new ArrayCollection;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getPaid(): int
    {
        return $this->paid;
    }

    public function setPaid(int $paid): void
    {
        $this->paid = $paid;
    }

    public function getEcommTransactions(): Collection
    {
        return $this->ecommTransactions;
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
        $modified = new ModifiedEventParticipant($user, $this);
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

    public function getSuccessfulTransaction()
    {
        return $this->ecommTransactions->filter(function (EcommTransaction $transaction) {
            if ($transaction->isOk()) {
                return true;
            } else {
                return false;
            }
        })->first();
    }
}
