<?php

namespace App\Model\Entity;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;

/**
 * ModifiedNews
 *
 * @ORM\Table(name="modified__news")
 * @ORM\Entity
 */
class ModifiedNews
{
    use MagicGetters;

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
        $this->modTime = new DateTime;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getModTime(): DateTime
    {
        return $this->modTime;
    }

    public function getUser(): ?User
    {
        try {
            $this->user->getDeleted();
            return $this->user; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }

    public function getNews(): ?News
    {
        try {
            $this->news->getDeleted();
            return $this->news; // entity not deleted, return it
        } catch (EntityNotFoundException $e) {
            return null; // could not fetch soft-deleted entity, return null
        }
    }
}
