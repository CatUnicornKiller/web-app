<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Nette\Security\IIdentity;

/**
 * User
 *
 * @ORM\Entity
 *
 * @property integer $id
 * @property string $username
 * @property string $firstname
 * @property string $surname
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $userType
 * @property string $profileImg
 * @property boolean $deleted
 * @property Faculty $faculty
 * @property Country $country
 * @property OfficersProfile $officersProfile
 * @property ArrayCollection $infoRequests
 * @property ArrayCollection $organizedEvents
 * @property ArrayCollection $coorganizedEvents
 * @property ArrayCollection $assignedIncomings
 * @property ArrayCollection $participatedEvents
 */
class User implements IIdentity
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $surname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="text")
     */
    protected $passwordHash;

    /**
     * @ORM\Column(type="text")
     */
    protected $role;

    /**
     * @ORM\Column(type="string")
     */
    protected $userType;

    /**
     * @ORM\Column(type="text")
     */
    protected $profileImg = "";

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = '0';

    /**
     * @ORM\ManyToOne(targetEntity="Faculty")
     */
    protected $faculty;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     */
    protected $country;

    /**
     * @ORM\OneToOne(targetEntity="OfficersProfile", mappedBy="user", cascade={"all"})
     */
    protected $officersProfile;

    /**
     * @ORM\OneToMany(targetEntity="UserInfoRequest", mappedBy="requestedUser")
     */
    protected $infoRequests;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="user")
     */
    protected $organizedEvents;

    /**
     * @ORM\OneToMany(targetEntity="EventCoorganizer", mappedBy="user")
     */
    protected $coorganizedEvents;

    /**
     * @ORM\OneToMany(targetEntity="CpAssignedAf", mappedBy="user")
     */
    protected $assignedIncomings;

    /**
     * @ORM\OneToMany(targetEntity="EventParticipant", mappedBy="user")
     */
    protected $participatedEvents;

    /**
     * @ORM\OneToMany(targetEntity="ExtraPoints", mappedBy="user")
     */
    protected $extraPointsList;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedUser", mappedBy="modifiedUser", cascade={"persist"})
     */
    protected $modifications;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedUserRole", mappedBy="modifiedUser", cascade={"persist"})
     */
    protected $roleModifications;


    public function __construct()
    {
        $this->infoRequests = new ArrayCollection;
        $this->organizedEvents = new ArrayCollection;
        $this->coorganizedEvents = new ArrayCollection;
        $this->assignedIncomings = new ArrayCollection;
        $this->participatedEvents = new ArrayCollection;
        $this->modifications = new ArrayCollection;
        $this->roleModifications = new ArrayCollection;
        $this->extraPointsList = new ArrayCollection;
    }

    public static function createOfficer(
        $username,
        $firstname,
        $surname,
        $email,
        $passwd,
        $role,
        Faculty $faculty,
        Country $country,
        $ifmsaPasswd,
        $ifmsaUsername
    ) {

        $profile = new OfficersProfile;
        $profile->ifmsaPassword = $ifmsaPasswd;
        $profile->ifmsaUsername = $ifmsaUsername;

        $user = new User;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->email = $email;
        $user->password = $passwd;
        $user->role = $role;
        $user->userType = 'officer';
        $user->faculty = $faculty;
        $user->country = $country;

        $profile->user = $user;
        $user->officersProfile = $profile;

        return $user;
    }

    public static function createIncoming(
        $username,
        $firstname,
        $surname,
        $email,
        $passwd,
        $role,
        Faculty $faculty,
        Country $country
    ) {

        $user = new User;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->email = $email;
        $user->password = $passwd;
        $user->role = $role;
        $user->userType = 'incoming';
        $user->faculty = $faculty;
        $user->country = $country;

        return $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return array($this->role);
    }

    public function setPassword($password)
    {
        $this->passwordHash = \Nette\Security\Passwords::hash($password);
    }

    public function getPassword()
    {
        return $this->passwordHash;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedUser($user, $this);
        $this->modifications->add($modified);
    }

    public function roleModified(User $user, $roleOld, $roleNew)
    {
        $modified = new ModifiedUserRole($user, $this, $roleOld, $roleNew);
        $this->roleModifications->add($modified);
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function isOfficer()
    {
        return $this->userType == 'officer' ? true : false;
    }

    public function isIncoming()
    {
        return $this->userType == 'incoming' ? true : false;
    }

    public function countPoints()
    {
        $points = 0;
        foreach ($this->organizedEvents as $event) {
            $points += $event->points;
        }
        foreach ($this->coorganizedEvents as $coorg) {
            $points += $coorg->points;
        }
        foreach ($this->extraPointsList as $extra) {
            $points += $extra->points;
        }
        return $points;
    }

    public function getCoorganizedEvents()
    {
        return $this->coorganizedEvents->filter(function (EventCoorganizer $coorganizer) {
            if ($coorganizer->event) {
                return true;
            }
            return false;
        });
    }

    public function getParticipatedEvents()
    {
        return $this->participatedEvents->filter(function (EventParticipant $participant) {
            if ($participant->event) {
                return true;
            }
            return false;
        });
    }

    public function getAssignedIncoming($afNumber)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("afNumber", $afNumber));
        return $this->assignedIncomings->matching($criteria)->first();
    }

    public function getParticipatedEvent(Event $event)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("event", $event));
        return $this->participatedEvents->matching($criteria)->first();
    }

    public function getCoorganizedEvent(Event $event)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("event", $event));
        return $this->coorganizedEvents->matching($criteria)->first();
    }

    public function getAdditionalInfoRequests()
    {
        return $this->infoRequests->filter(function (UserInfoRequest $request) {
            if ($request->requestDesc == "additional_information") {
                return true;
            }
            return false;
        });
    }
}
