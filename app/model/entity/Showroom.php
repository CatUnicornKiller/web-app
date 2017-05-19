<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Showroom
 *
 * @ORM\Entity
 *
 * @property string $profileImg
 * @property string $firstname
 * @property string $surname
 * @property string $role
 * @property string $startYear
 * @property string $endYear
 * @property string $profileImg
 * @property string $information
 * @property string $faculty
 */
class Showroom
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
}
