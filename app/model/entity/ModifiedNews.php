<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * ModifiedNews
 *
 * @ORM\Table(name="modified__news")
 * @ORM\Entity
 */
class ModifiedNews
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
    protected $modTime;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="News", inversedBy="modifications")
     */
    protected $news;


    public function __construct(User $user, News $news)
    {
        $this->user = $user;
        $this->news = $news;
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

    public function getNews()
    {
        try {
            $this->news->getDeleted();
            return $this->news; // entity not deleted, return it
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
