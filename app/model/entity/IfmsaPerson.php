<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * IfmsaPerson
 *
 * @ORM\Entity
 */
class IfmsaPerson
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    protected $afNumber;

    /**
     * @ORM\Column(type="text")
     */
    protected $confirmationNumber;

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
    protected $email;

    /**
     * @ORM\Column(type="text")
     */
    protected $photo;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $afArrival;

    /**
     * @ORM\Column(type="text")
     */
    protected $accommodation;

    /**
     * @ORM\Column(type="text")
     */
    protected $department;


    public function __construct(
        $afNumber,
        $confirmationNumber,
        $firstname = '',
        $surname = '',
        $email = '',
        $photo = '',
        DateTime $afArrival = null,
        $accommodation = '',
        $department = ''
    ) {
        if (!$afArrival) {
            $afArrival = new DateTime("0000-00-00 00:00:00");
        }

        $this->afNumber = $afNumber;
        $this->confirmationNumber = $confirmationNumber;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->email = $email;
        $this->photo = $photo;
        $this->accommodation = $accommodation;
        $this->department = $department;
        $this->afArrival = $afArrival;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getAfNumber(): int
    {
        return $this->afNumber;
    }

    public function getConfirmationNumber(): string
    {
        return $this->confirmationNumber;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function getAfArrival(): ?DateTime
    {
        return $this->afArrival;
    }

    public function getAccommodation(): string
    {
        return $this->accommodation;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }
}
