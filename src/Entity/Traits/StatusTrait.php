<?php

namespace App\Entity\Traits;

use App\Entity\Interfaces\StatusInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait StatusTrait
{
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;
    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;
        return $this;
    }
}