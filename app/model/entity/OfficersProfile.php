<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OfficersProfile
 *
 * @ORM\Entity
 */
class OfficersProfile
{
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

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getIfmsaUsername(): string
    {
        return $this->ifmsaUsername;
    }

    public function getIfmsaPassword(): string
    {
        return $this->ifmsaPassword;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
