<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Showroom
 *
 * @ORM\Entity
 */
class Showroom
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="text")
     */
    protected $surname;

    /**
     * @ORM\Column(type="text")
     */
    protected $role;

    /**
     * @ORM\Column(columnDefinition="YEAR")
     */
    protected $startYear;

    /**
     * @ORM\Column(columnDefinition="YEAR")
     */
    protected $endYear;

    /**
     * @ORM\Column(type="text")
     */
    protected $profileImg;

    /**
     * @ORM\Column(type="text")
     */
    protected $information;

    /**
     * @ORM\ManyToOne(targetEntity="Faculty")
     */
    protected $faculty;


    public function __construct(
        $name,
        $surname,
        $role,
        $faculty,
        $startYear,
        $endYear,
        $profileImg,
        $information
    ) {
        $this->firstname = $name;
        $this->surname = $surname;
        $this->role = $role;
        $this->faculty = $faculty;
        $this->startYear = $startYear;
        $this->endYear = $endYear;
        $this->profileImg = $profileImg;
        $this->information = $information;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStartYear(): int
    {
        return $this->startYear;
    }

    public function getEndYear(): int
    {
        return $this->endYear;
    }

    public function getProfileImg(): string
    {
        return $this->profileImg;
    }

    public function getInformation(): string
    {
        return $this->information;
    }

    public function getFaculty(): string
    {
        return $this->faculty;
    }
}
