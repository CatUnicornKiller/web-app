<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * LoginLog
 *
 * @ORM\Entity
 *
 * @property integer $id
 * @property datetime $loginTime
 * @property string $ipAddress
 * @property string $userAgent
 * @property User $user
 */
class LoginLog
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $loginTime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $ipAddress;

    /**
     * @ORM\Column(type="text")
     */
    protected $userAgent;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;


    public function __construct(User $user, string $ipAddress, string $userAgent)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->loginTime = new \DateTime;
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
