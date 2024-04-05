<?php

namespace App\Entity\Interfaces;

interface SlugIntrface
{
    public function getSlug(): ?string;
    public function setSlug(string $slug): static;
}