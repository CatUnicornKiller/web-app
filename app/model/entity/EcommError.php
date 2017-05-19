<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * EcommError
 *
 * @ORM\Entity
 */
class EcommError
{
    use MagicAccessors;

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
        $this->errorTime = new \DateTime;
        $this->action = $action;
        $this->response = $response;
    }
}
