<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventParticipant
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_participation", columns={"event_id", "user_id"})})
 * @ORM\Entity
 */
class EventParticipant
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

    public function modified(User $user)
    {
        $modified = new ModifiedEventParticipant($user, $this);
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
