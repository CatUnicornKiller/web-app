<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\EventFile;
use function rand;

/**
 * Repository of operations performed on EventFile entities.
 *
 * @method EventFile|null get($id)
 * @method EventFile[] findAll()
 * @method EventFile[] findBy($params, $orderBy = [])
 * @method EventFile|null findOneBy($params)
 * @method EventFile findOrThrow($id)
 */
class EventFiles extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
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
            $file = $this->get(rand(1, $highest_id));
            if ($file && $file->event) {
                $n--;
                $files[] = $file;
            }
        }
        return $files;
    }
}
