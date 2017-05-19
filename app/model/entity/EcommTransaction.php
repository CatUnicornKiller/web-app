<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * EcommTransaction
 *
 * @ORM\Entity
 */
class EcommTransaction
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $transId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $currency;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $clientIpAddr;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $language;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $dmsOk;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $result;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $resultCode;

    /**
     * @ORM\Column(name="result_3dsecure", type="string", length=50, nullable=true)
     */
    protected $result3dsecure;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $cardNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $tDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $transEndDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $response;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $reversalAmount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $makedmsAmount;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $externalService;

    /**
     * @ORM\ManyToOne(targetEntity="EventParticipant", inversedBy="ecommTransaction")
     */
    protected $eventParticipant;


    public function __construct(
        $transactionId,
        $amount,
        $currency,
        $ip,
        $description,
        $language,
        $response
    ) {

        $this->transId = $transactionId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->clientIpAddr = $ip;
        $this->description = $description;
        $this->language = $language;
        $this->dmsOk = "---";
        $this->result = "???";
        $this->resultCode = "???";
        $this->result3dsecure = "???";
        $this->cardNumber = "???";
        $this->tDate = new \DateTime;
        $this->response = $response;
        $this->reversalAmount = "";
        $this->makedmsAmount = "";
    }

    public function isOk()
    {
        return $this->result == "OK";
    }

    public function isExternal()
    {
        return $this->externalService != null;
    }

    public function getEventParticipant()
    {
        if ($this->eventParticipant === null) {
            return null;
        }

        try {
            $this->eventParticipant->getDeleted();
            return $this->eventParticipant; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
