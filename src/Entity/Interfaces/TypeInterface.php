<?php

namespace App\Entity\Interfaces;

interface TypeInterface
{
    public function getType(): ?int;
    public function setType(int $type): static;
}