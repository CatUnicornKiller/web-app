<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CpTasksList
 *
 * @ORM\Entity
 */
class DefaultCpTask extends BaseEntity
{
    use MagicGetters;

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

    ////////////////////////////////////////////////////////////////////////////

    public function getId(): int
    {
        return $this->id;
    }

    public function getCpTasksDescription(): string
    {
        return $this->cpTasksDescription;
    }

    public function setCpTasksDescription(string $cpTasksDescription): void
    {
        $this->cpTasksDescription = $cpTasksDescription;
    }

    public function getCpTasksNote(): string
    {
        return $this->cpTasksNote;
    }

    public function setCpTasksNote(string $cpTasksNote): void
    {
        $this->cpTasksNote = $cpTasksNote;
    }
}
