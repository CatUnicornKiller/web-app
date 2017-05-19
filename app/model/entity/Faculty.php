<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Faculty
 *
 * @ORM\Entity
 */
class Faculty extends BaseEntity
{
    use MagicAccessors;

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
}
