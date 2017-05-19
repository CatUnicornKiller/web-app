<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * News
 *
 * @ORM\Entity
 */
class News
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedNews", mappedBy="news", cascade={"persist"})
     */
    protected $modifications;


    public function __construct(User $user, string $message)
    {
        $this->user = $user;
        $this->message = $message;
        $this->date = new \DateTime;
        $this->modifications = new ArrayCollection;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedNews($user, $this);
        $this->modifications->add($modified);
    }

    public function delete()
    {
        $this->deleted = true;
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
