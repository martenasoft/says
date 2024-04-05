<?php

namespace App\Entity\Interfaces;

use App\Entity\Menu;

interface RelatedMenuInterface
{
    public function getMenu(): ?Menu;
    public function setMenu(?Menu $menu): static;

}