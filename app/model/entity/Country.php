<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Country
 *
 * @ORM\Entity
 */
class Country
{
    use MagicAccessors;

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

    
    public function getIsIfmsa()
    {
        return (int)$this->isIfmsa;
    }
}
