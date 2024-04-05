<?php

namespace App\Entity\Traits;

use App\Entity\Menu;
use Doctrine\ORM\Mapping as ORM;

trait RelatedMenuTrait
{
    #[ORM\ManyToOne(cascade: ["persist"])]
    private ?Menu $menu = null;
    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }
}