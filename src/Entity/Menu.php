<?php

namespace App\Entity;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\DefaultStatusInterface;
use App\Entity\Interfaces\IdInterface;
use App\Entity\Interfaces\NameInterface;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\SlugIntrface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Interfaces\TypeInterface;
use App\Entity\Traits\ChangeDataDayTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\NodeTrait;
use App\Entity\Traits\SlugTrait;
use App\Entity\Traits\StatusTrait;
use App\Entity\Traits\TypeTrait;
use App\Repository\MenuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\UniqueConstraint(fields: ["slug", "tree"])]
#[ORM\Index(name: "lft", columns: ["lft"])]
#[ORM\Index(name: "lft_rgt", columns: ["lft", "rgt"])]
#[ORM\Index(name: "id_lft_rgt", columns: ["lft", "rgt", "rgt"])]
#[ORM\Index(name: "is_bottom_menu", columns: ["is_bottom_menu"])]
#[ORM\Index(name: "is_left_menu", columns: ["is_left_menu"])]
#[ORM\Index(name: "is_top_menu", columns: ["is_top_menu"])]
class Menu implements
    IdInterface,
    NameInterface,
    DefaultStatusInterface,
    SlugIntrface,
    NodeInterface,
    StatusInterface,
    ChangeDataDayInterface,
    TypeInterface
{
    use
        IdTrait,
        NameTrait,
        SlugTrait,
        NodeTrait,
        StatusTrait,
        ChangeDataDayTrait,
        TypeTrait
        ;

    public const ITEM_MENU_TYPE = 1;
    public const EXTERNAL_PAGE_TYPE = 2;

    public const TYPES = [
        self::ITEM_MENU_TYPE => 'Item menu',
        self::EXTERNAL_PAGE_TYPE => 'External page'
    ];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column]
    private ?bool $isBottomMenu = false;

    #[ORM\Column]
    private ?bool $isLeftMenu = false;

    #[ORM\Column]
    private ?bool $isTopMenu = false;

    private ?Menu $parent = null;

    #[ORM\Column(length: 6)]
    private ?string $lang = null;

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function isIsBottomMenu(): ?bool
    {
        return $this->isBottomMenu;
    }

    public function setIsBottomMenu(bool $isBottomMenu): static
    {
        $this->isBottomMenu = $isBottomMenu;

        return $this;
    }

    public function isIsLeftMenu(): ?bool
    {
        return $this->isLeftMenu;
    }

    public function setIsLeftMenu(bool $isLeftMenu): static
    {
        $this->isLeftMenu = $isLeftMenu;

        return $this;
    }

    public function isIsTopMenu(): ?bool
    {
        return $this->isTopMenu;
    }

    public function setIsTopMenu(bool $isTopMenu): static
    {
        $this->isTopMenu = $isTopMenu;

        return $this;
    }
    public function getParent(): ?Menu
    {
        return $this->parent;
    }
    public function setParent(?Menu $parent): Menu
    {
        $this->parent = $parent;
        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }
}
