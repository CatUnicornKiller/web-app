<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

/**
 * User
 *
 * @ORM\Entity
 */
class User implements IIdentity
{
    use MagicGetters;

    const HASHING_OPTIONS = [
        "cost" => 11
    ];

    /**
     * TODO: This has to be done better! Move it somewhere else!
     */
    private static function createPasswordUtils(): Passwords
    {
        return new Passwords(PASSWORD_DEFAULT, self::HASHING_OPTIONS);
    }

    ////////////////////////////////////////////////////////////////////////////

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
    )
    {

        $profile = new OfficersProfile;
        $profile->setIfmsaPassword($ifmsaPasswd);
        $profile->setIfmsaUsername($ifmsaUsername);

        $user = new User;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->email = $email;
        $user->hashPassword($passwd);
        $user->role = $role;
        $user->userType = 'officer';
        $user->faculty = $faculty;
        $user->country = $country;

        $profile->setUser($user);
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
    )
    {

        $user = new User;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->surname = $surname;
        $user->email = $email;
        $user->hashPassword($passwd);
        $user->role = $role;
        $user->userType = 'incoming';
        $user->faculty = $faculty;
        $user->country = $country;

        return $user;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId()
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return array($this->role);
    }

    public function getData(): array
    {
        return [
            "id" => $this->getId(),
            "roles" => $this->getRoles()
        ];
    }

    public function hashPassword($password)
    {
        $this->passwordHash = self::createPasswordUtils()->hash($password);
    }

    public function matchPasswords($password): bool
    {
        if ($password === null) {
            return false;
        }

        $passwordUtils = self::createPasswordUtils();
        if ($passwordUtils->verify($password, $this->passwordHash)) {
            if ($passwordUtils->needsRehash($this->passwordHash)) {
                $this->hashPassword($password);
            }

            return true;
        }

        return false;
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
        return $this->userType == 'officer';
    }

    public function isIncoming()
    {
        return $this->userType == 'incoming';
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
            if ($coorganizer->getEvent()) {
                return true;
            }
            return false;
        });
    }

    public function getParticipatedEvents()
    {
        return $this->participatedEvents->filter(function (EventParticipant $participant) {
            if ($participant->getEvent()) {
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
            if ($request->getRequestDesc() == "additional_information") {
                return true;
            }
            return false;
        });
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getProfileImg(): string
    {
        return $this->profileImg;
    }

    public function setProfileImg(string $profileImg): void
    {
        $this->profileImg = $profileImg;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getFaculty(): Faculty
    {
        return $this->faculty;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getOfficersProfile(): OfficersProfile
    {
        return $this->officersProfile;
    }

    public function getInfoRequests(): Collection
    {
        return $this->infoRequests;
    }

    public function getOrganizedEvents(): Collection
    {
        return $this->organizedEvents;
    }

    public function getAssignedIncomings(): Collection
    {
        return $this->assignedIncomings;
    }

    public function getExtraPointsList(): Collection
    {
        return $this->extraPointsList;
    }

    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    public function getRoleModifications(): Collection
    {
        return $this->roleModifications;
    }
}
