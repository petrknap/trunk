<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

trait UuidTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $uuid;

    public function getUuid(): string
    {
        throwIfEntityHasNotBeenPersisted($this, $this->uuid);

        return $this->uuid;
    }
}
