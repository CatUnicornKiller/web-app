<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * EcommTransaction
 *
 * @ORM\Entity
 */
class EcommTransaction
{
    use MagicGetters;

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
        $this->tDate = new DateTime;
        $this->response = $response;
        $this->reversalAmount = "";
        $this->makedmsAmount = "";
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransId(): string
    {
        return $this->transId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function getClientIpAddr(): string
    {
        return $this->clientIpAddr;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDmsOk(): string
    {
        return $this->dmsOk;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $result): void
    {
        $this->result = $result;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function setResultCode(string $resultCode): void
    {
        $this->resultCode = $resultCode;
    }

    public function getResult3dsecure(): string
    {
        return $this->result3dsecure;
    }

    public function setResult3dsecure(string $result3dsecure): void
    {
        $this->result3dsecure = $result3dsecure;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): void
    {
        $this->cardNumber = $cardNumber;
    }

    public function getTDate(): DateTime
    {
        return $this->tDate;
    }

    public function getTransEndDate(): ?DateTime
    {
        return $this->transEndDate;
    }

    public function setTransEndDate(?DateTime $transEndDate): void
    {
        $this->transEndDate = $transEndDate;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getReversalAmount(): ?int
    {
        return $this->reversalAmount;
    }

    public function setReversalAmount(?int $reversalAmount): void
    {
        $this->reversalAmount = $reversalAmount;
    }

    public function getMakedmsAmount(): string
    {
        return $this->makedmsAmount;
    }

    public function getExternalService(): string
    {
        return $this->externalService;
    }

    public function isOk(): bool
    {
        return $this->result == "OK";
    }

    public function isExternal(): bool
    {
        return $this->externalService != null;
    }

    public function getEventParticipant(): ?EventParticipant
    {
        if ($this->eventParticipant === null) {
            return null;
        }

        try {
            $this->eventParticipant->getDeleted();
            return $this->eventParticipant; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
