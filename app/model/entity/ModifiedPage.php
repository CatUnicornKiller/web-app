<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedPage
 *
 * @ORM\Table(name="modified__page")
 * @ORM\Entity
 */
class ModifiedPage
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="modifications")
     */
    protected $page;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modTime;


    public function __construct(User $user, Page $page)
    {
        $this->user = $user;
        $this->page = $page;
        $this->modTime = new \DateTime;
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
