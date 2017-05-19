<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Page
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_for_faculty", columns={"page_name", "page_subname", "faculty_id"})})
 * @ORM\Entity
 */
class Page
{
    use MagicAccessors;

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

    public function modified(User $user)
    {
        $modified = new ModifiedPage($user, $this);
        $this->modifications->add($modified);
    }
}
