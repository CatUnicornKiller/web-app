<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * EcommError
 *
 * @ORM\Entity
 */
class EcommError
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $errorTime;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $action;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $response;


    public function __construct($action, $response)
    {
        $this->errorTime = new DateTime;
        $this->action = $action;
        $this->response = $response;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getErrorTime(): DateTime
    {
        return $this->errorTime;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
