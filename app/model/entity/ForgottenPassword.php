<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForgottenPassword
 *
 * @ORM\Entity
 */
class ForgottenPassword
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $ipAddress;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $time;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $token;


    public function __construct(User $user, $ipAddress, $token)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->token = $token;
        $this->time = new DateTime;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): ?User
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
