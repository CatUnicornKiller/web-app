<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Entity
 */
class Country
{
    use MagicGetters;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $countryName;

    /**
     * @ORM\Column(type="string")
     */
    protected $countryShortcut;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isIfmsa = '0';

    /**
     * @ORM\Column(type="integer")
     */
    protected $ifmsaClinicalContracts = '0';

    /**
     * @ORM\Column(type="integer")
     */
    protected $ifmsaResearchContracts = '0';

    /**
     * @ORM\Column(columnDefinition="YEAR")
     */
    protected $ifmsaContractsYear = '0000';

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): void
    {
        $this->countryName = $countryName;
    }

    public function getCountryShortcut(): string
    {
        return $this->countryShortcut;
    }

    public function setCountryShortcut(string $countryShortcut): void
    {
        $this->countryShortcut = $countryShortcut;
    }

    public function getIsIfmsa(): int
    {
        return (int)$this->isIfmsa;
    }

    public function setIsIfmsa(int $isIfmsa): void
    {
        $this->isIfmsa = $isIfmsa;
    }

    public function getIfmsaClinicalContracts(): string
    {
        return $this->ifmsaClinicalContracts;
    }

    public function setIfmsaClinicalContracts(string $ifmsaClinicalContracts): void
    {
        $this->ifmsaClinicalContracts = $ifmsaClinicalContracts;
    }

    public function getIfmsaResearchContracts(): string
    {
        return $this->ifmsaResearchContracts;
    }

    public function setIfmsaResearchContracts(string $ifmsaResearchContracts): void
    {
        $this->ifmsaResearchContracts = $ifmsaResearchContracts;
    }

    public function getIfmsaContractsYear(): string
    {
        return $this->ifmsaContractsYear;
    }

    public function setIfmsaContractsYear(string $ifmsaContractsYear): void
    {
        $this->ifmsaContractsYear = $ifmsaContractsYear;
    }
}
