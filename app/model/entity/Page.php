<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Page
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_for_faculty", columns={"page_name", "page_subname", "faculty_id"})})
 * @ORM\Entity
 */
class Page
{
    use MagicGetters;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $pageName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $pageSubname;

    /**
     * @ORM\Column(type="text")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Faculty")
     */
    protected $faculty;

    /**
     * @ORM\OneToMany(targetEntity="ModifiedPage", mappedBy="page", cascade={"persist"})
     */
    protected $modifications;


    public function __construct($pageName, $pageSubname, $title, $content, $faculty = null)
    {
        $this->pageName = $pageName;
        $this->pageSubname = $pageSubname;
        $this->title = $title;
        $this->content = $content;
        $this->faculty = $faculty;
        $this->modifications = new ArrayCollection;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function getPageSubname(): string
    {
        return $this->pageSubname;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    public function getModifications(): Collection
    {
        return $this->modifications;
    }

    public function modified(User $user)
    {
        $modified = new ModifiedPage($user, $this);
        $this->modifications->add($modified);
    }
}
