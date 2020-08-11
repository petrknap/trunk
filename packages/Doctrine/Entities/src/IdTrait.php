<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \RuntimeException(get_class($this) . ' has not been persisted');
        }

        return $this->id;
    }
}
