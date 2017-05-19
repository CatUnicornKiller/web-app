<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * IfmsaPerson
 *
 * @ORM\Entity
 */
class IfmsaPerson
{
    use MagicAccessors;

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
        $afArrival = '0000-00-00 00:00:00',
        $accommodation = '',
        $department = ''
    ) {
        $this->afNumber = $afNumber;
        $this->confirmationNumber = $confirmationNumber;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->email = $email;
        $this->photo = $photo;
        $this->afArrival = new \DateTime($afArrival);
        $this->accommodation = $accommodation;
        $this->department = $department;
    }
}
