<?php

namespace App\Entity\Interfaces;

interface IdInterface
{
    public function getId(): ?int;

    public function setId(?int $id): static;
}