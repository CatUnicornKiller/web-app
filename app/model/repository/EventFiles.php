<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\EventFile;

/**
 * Repository of operations performed on EventFile entities.
 */
class EventFiles extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, EventFile::class);
    }

    /**
     * Get random event files based on count given as parameter.
     * @param int $n
     * @return array
     */
    public function getRandomFiles($n = 10)
    {
        if ($this->countAll() == 0) {
            return array();
        }

        $highest_id = $this->em->createQueryBuilder()
                ->select('MAX(e.id)')
                ->from(EventFile::class, 'e')
                ->getQuery()
                ->getSingleScalarResult();

        $files = array();
        while ($n > 0) {
            $file = $this->get(\rand(1, $highest_id));
            if ($file && $file->event) {
                $n--;
                $files[] = $file;
            }
        }
        return $files;
    }
}
