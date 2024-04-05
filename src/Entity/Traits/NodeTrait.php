<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait NodeTrait
{
    #[ORM\Column(length: 65, nullable: true)]
    private ?string $name = null;
    
    #[ORM\Column(type: 'integer')]
    private ?int $lft = null;

    #[ORM\Column(type: 'integer')]
    private ?int $rgt = null;

    #[ORM\Column(type: 'integer')]
    private ?int $lvl = null;

    #[ORM\Column(type: 'integer')]
    private ?int $tree = null;

    #[ORM\Column(type: 'integer')]
    private ?int $parentId = null;

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setLft(?int $lft): self
    {
        $this->lft = $lft;
        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setRgt(?int $rgt): self
    {
        $this->rgt = $rgt;
        return $this;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setLvl(?int $lvl): self
    {
        $this->lvl = $lvl;
        return $this;
    }

    public function getTree(): ?int
    {
        return $this->tree;
    }

    public function setTree(?int $tree): self
    {
        $this->tree = $tree;
        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }
}
