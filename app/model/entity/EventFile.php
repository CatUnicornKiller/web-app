<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EventFile
 *
 * @ORM\Entity
 */
class EventFile
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $oldName;

    /**
     * @ORM\Column(type="text")
     */
    protected $newName;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="files")
     */
    protected $event;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedEventFile", mappedBy="eventFile", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(User $user, Event $event, $oldName, $newName)
    {
        $this->user = $user;
        $this->event = $event;
        $this->oldName = $oldName;
        $this->newName = $newName;
        $this->modifications = new ArrayCollection;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedEventFile($user, $this);
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
