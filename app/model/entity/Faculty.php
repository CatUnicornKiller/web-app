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
    use MagicGetters;

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

    public function setFacultyName($facultyName): void
    {
        $this->facultyName = $facultyName;
    }

    public function getFacultyAddress(): string
    {
        return $this->facultyAddress;
    }

    public function setFacultyAddress(string $facultyAddress): void
    {
        $this->facultyAddress = $facultyAddress;
    }

    public function getFacultyShortcut(): string
    {
        return $this->facultyShortcut;
    }

    public function setFacultyShortcut(string $facultyShortcut): void
    {
        $this->facultyShortcut = $facultyShortcut;
    }

    public function getIfmsaLcNumber(): int
    {
        return $this->ifmsaLcNumber;
    }

    public function setIfmsaLcNumber(int $ifmsaLcNumber): void
    {
        $this->ifmsaLcNumber = $ifmsaLcNumber;
    }
}
