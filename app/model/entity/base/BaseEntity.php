<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseEntity
{
    public function toArray()
    {
        $reflection = new ReflectionClass($this);
        $details = array();
        foreach ($reflection->getProperties(ReflectionProperty::IS_PROTECTED) as $property) {
            if (!$property->isStatic()) {
                $value = $this->{$property->getName()};

                if ($value instanceof BaseEntity) {
                    $value = $value->getId();
                } elseif ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
                    $value = array_map(function (BaseEntity $entity) {
                        return $entity->getId();
                    }, $value->toArray());
                }
                $details[$property->getName()] = $value;
            }
        }
        return $details;
    }
}
