<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * CpTasksList
 *
 * @ORM\Entity
 */
class DefaultCpTask extends BaseEntity
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $cpTasksDescription;

    /**
     * @ORM\Column(type="text")
     */
    protected $cpTasksNote;


    public function __construct($description, $note)
    {
        $this->cpTasksDescription = $description;
        $this->cpTasksNote = $note;
    }
}
