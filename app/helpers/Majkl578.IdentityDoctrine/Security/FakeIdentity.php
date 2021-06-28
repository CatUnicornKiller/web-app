<?php

namespace Majkl578\NetteAddons\Doctrine2Identity\Security;

use Nette\Security\IIdentity;

/**
 * @author Michael Moravec
 */
class FakeIdentity implements IIdentity
{
    /** @var mixed */
    private $id;

    /** @var string */
    private $class;

    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return array();
    }

    public function getData(): array
    {
        return [
            "id" => $this->getId(),
            "roles" => $this->getRoles()
        ];
    }
}
