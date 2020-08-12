<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\Entities;

function throwIfEntityHasNotBeenPersisted($entity, $id): void
{
    if ($id === null) {
        throw new \RuntimeException(get_class($entity) . ' has not been persisted');
    }
}
