<?php

namespace App\Model\Repository;

use App\Model\Entity\Faculty;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\Page;

/**
 * Repository of operations performed on Page entities.
 */
class Pages extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Page::class);
    }

    /**
     * Find pages by given name and subname.
     * @param string $pageName
     * @param string $pageSubname
     * @return array
     */
    public function findByNameAndSubname($pageName, $pageSubname)
    {
        return $this->findBy(array(
            "pageName" => $pageName,
            "pageSubname" => $pageSubname));
    }

    /**
     * Get one page by given page name, subname and faculty.
     * @param string $pageName
     * @param string $pageSubname
     * @param Faculty $faculty
     * @return Page|NULL
     */
    public function getPage($pageName, $pageSubname, $faculty = null)
    {
        $page = $this->findOneBy(array(
            "pageName" => $pageName,
            "pageSubname" => $pageSubname,
            "faculty" => $faculty));

        if (!$page && $faculty != null) {
            $page = $this->findOneBy(array(
                "pageName" => $pageName,
                "pageSubname" => $pageSubname,
                "faculty" => null));
        }

        return $page;
    }
}
