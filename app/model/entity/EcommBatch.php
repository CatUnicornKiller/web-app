<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * EcommBatch
 *
 * @ORM\Entity
 */
class EcommBatch
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $result;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    protected $resultCode;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $countReversal;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $countTransaction;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $amountReversal;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $amountTransaction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closeDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $response;


    public function __construct(
        $result,
        $resultCode,
        $countReversal,
        $countTransaction,
        $amountReversal,
        $amountTransaction,
        $response
    ) {
    
        $this->result = $result;
        $this->resultCode = $resultCode;
        $this->countReversal = $countReversal;
        $this->countTransaction = $countTransaction;
        $this->amountReversal = $amountReversal;
        $this->amountTransaction = $amountTransaction;
        $this->closeDate = new DateTime;
        $this->response = $response;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function getCountReversal(): string
    {
        return $this->countReversal;
    }

    public function getCountTransaction(): string
    {
        return $this->countTransaction;
    }

    public function getAmountReversal(): string
    {
        return $this->amountReversal;
    }

    public function getAmountTransaction(): string
    {
        return $this->amountTransaction;
    }

    public function getCloseDate(): DateTime
    {
        return $this->closeDate;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
