<?php

namespace App\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Doctrine filter for softly deleted entities.
 */
final class SoftdeletableFilter extends SQLFilter
{
    /**
     * Apply soft deleted filter on the soft deletable entities.
     * @param ClassMetadata $targetEntity entity which can be filtered
     * @param string $targetTableAlias alias for entity
     * @return string filter string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getReflectionClass()->hasProperty('deleted')) {
            return "$targetTableAlias.deleted = 0";
        }

        return '';
    }
}
