<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * OfficersProfile
 *
 * @ORM\Entity
 */
class OfficersProfile
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $ifmsaUsername;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $ifmsaPassword;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $address = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $city = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $postCode = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $region = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $phone = "";

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="officersProfile")
     */
    protected $user;
}
