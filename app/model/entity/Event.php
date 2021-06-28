<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Event
 *
 * @ORM\Entity
 */
class Event
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $signupDeadline;

    /**
     * @ORM\Column(type="text"))
     */
    protected $eventName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $capacity = '0';

    /**
     * @ORM\Column(type="text")
     */
    protected $eventLogo;

    /**
     * @ORM\Column(type="text")
     */
    protected $eventDescription;

    /**
     * @ORM\Column(type="text")
     */
    protected $place;

    /**
     * @ORM\Column(type="integer")
     */
    protected $price = '0';

    /**
     * @ORM\Column(type="integer")
     */
    protected $points = '0';

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\Column(type="boolean")
     */
    protected $socialProgram = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $academicQuality = false;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizedEvents")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="EventFile", mappedBy="event")
     */
    protected $files;

    /**
     * @ORM\OneToMany(targetEntity="EventCoorganizer", mappedBy="event")
     */
    protected $coorganizers;

    /**
     * @ORM\OneToMany(targetEntity="EventParticipant", mappedBy="event")
     */
    protected $participants;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedEvent", mappedBy="event", cascade={"persist"})
     */
    protected $modifications;

    /**
     * @ORM\ManyToMany(targetEntity="Faculty")
     * @ORM\JoinTable(name="event_visible_to_faculty")
     */
    protected $visibleToFaculties;


    public function __construct(
        User $user,
        $startDate,
        $endDate,
        $deadline,
        $name,
        $description,
        $logo,
        array $faculties,
        $place,
        $price,
        $capacity,
        $socialProgram = false,
        $academicQuality = false
    ) {

        $this->user = $user;
        $this->date = $startDate;
        $this->endDate = $endDate;
        $this->signupDeadline = $deadline;
        $this->eventName = $name;
        $this->eventDescription = $description;
        $this->eventLogo = $logo;
        $this->points = 5;
        $this->place = $place;
        $this->price = $price;
        $this->capacity = $capacity;
        $this->socialProgram = $socialProgram;
        $this->academicQuality = $academicQuality;
        $this->files = new ArrayCollection;
        $this->coorganizers = new ArrayCollection;
        $this->participants = new ArrayCollection;
        $this->modifications = new ArrayCollection;
        $this->visibleToFaculties = new ArrayCollection;

        foreach ($faculties as $faculty) {
            $this->visibleToFaculties->add($faculty);
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function getSignupDeadline(): ?DateTime
    {
        return $this->signupDeadline;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getCapacity(): string
    {
        return $this->capacity;
    }

    public function getEventLogo(): string
    {
        return $this->eventLogo;
    }

    public function getEventDescription(): string
    {
        return $this->eventDescription;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getSocialProgram(): bool
    {
        return $this->socialProgram;
    }

    public function getAcademicQuality(): bool
    {
        return $this->academicQuality;
    }

    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }

    public function getModifications(): ArrayCollection
    {
        return $this->modifications;
    }

    public function getVisibleToFaculties(): ArrayCollection
    {
        return $this->visibleToFaculties;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedEvent($user, $this);
        $this->modifications->add($modified);
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function isVisibleToFaculty(Faculty $faculty): bool
    {
        return $this->visibleToFaculties->contains($faculty);
    }

    public function getVisibleToFacultiesIds()
    {
        return $this->visibleToFaculties->map(function ($faculty) {
            return $faculty->id;
        })->getValues();
    }

    public function replaceVisibleToFaculties(array $faculties)
    {
        $this->visibleToFaculties->clear();
        foreach ($faculties as $faculty) {
            $this->visibleToFaculties->add($faculty);
        }
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

    public function getCoorganizers()
    {
        return $this->coorganizers->filter(function (EventCoorganizer $coorganizer) {
            if ($coorganizer->user) {
                return true;
            }
            return false;
        });
    }

    public function getParticipants()
    {
        return $this->participants->filter(function (EventParticipant $participant) {
            if ($participant->user) {
                return true;
            }
            return false;
        });
    }
}
