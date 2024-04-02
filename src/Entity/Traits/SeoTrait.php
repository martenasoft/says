<?php

namespace App\Entity\Traits;
use Doctrine\ORM\Mapping as ORM;

trait SeoTrait
{
    #[ORM\Column(length: 255, nullable: true)]
    private $seoTitle;

    #[ORM\Column(length: 255, nullable: true)]
    private $seoDescription;

    #[ORM\Column(length: 255, nullable: true)]
    private $seoKeywords;

    #[ORM\Column(length: 255, nullable: true)]
    private $ogTitle;

    #[ORM\Column(length: 255, nullable: true)]
    private $ogDescription;

    #[ORM\Column(length: 255, nullable: true)]
    private $ogUrl;

    #[ORM\Column(length: 255, nullable: true)]
    private $ogImage;
    #[ORM\Column(length: 255, nullable: true)]
    private $ogType;

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): self
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): self
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    public function getSeoKeywords(): ?string
    {
        return $this->seoKeywords;
    }

    public function setSeoKeywords(?string $seoKeywords): self
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle($ogTitle): self
    {
        $this->ogTitle = $ogTitle;
        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription($ogDescription): self
    {
        $this->ogDescription = $ogDescription;
        return $this;
    }

    public function getOgUrl(): ?string
    {
        return $this->ogUrl;
    }

    public function setOgUrl($ogUrl): self
    {
        $this->ogUrl = $ogUrl;
        return $this;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage($ogImage): self
    {
        $this->ogImage = $ogImage;
        return $this;
    }

    public function getOgType(): ?string
    {
        return $this->ogType;
    }
    public function setOgType($ogType): self
    {
        $this->ogType = $ogType;
        return $this;
    }

}