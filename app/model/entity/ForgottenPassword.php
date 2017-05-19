<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ForgottenPassword
 *
 * @ORM\Entity
 */
class ForgottenPassword
{
    use MagicAccessors;

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
        $this->time = new \DateTime;
    }

    public function getUser()
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
