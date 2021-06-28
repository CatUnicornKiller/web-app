<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Faculty
 *
 * @ORM\Entity
 */
class Faculty extends BaseEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $facultyName;

    /**
     * @ORM\Column(type="text")
     */
    protected $facultyAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $facultyShortcut;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ifmsaLcNumber;


    public function __construct($name, $address, $shortcut, $lcNumber = 0)
    {
        $this->facultyName = $name;
        $this->facultyAddress = $address;
        $this->facultyShortcut = $shortcut;
        $this->ifmsaLcNumber = $lcNumber;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getFacultyName(): string
    {
        return $this->facultyName;
    }

    public function getFacultyAddress(): string
    {
        return $this->facultyAddress;
    }

    public function getFacultyShortcut(): string
    {
        return $this->facultyShortcut;
    }

    public function getIfmsaLcNumber(): int
    {
        return $this->ifmsaLcNumber;
    }
}
