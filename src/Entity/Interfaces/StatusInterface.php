<?php

namespace App\Entity\Interfaces;


interface StatusInterface
{
    public function getStatus(): ?int;

    public function setStatus(?int $status): static;
}