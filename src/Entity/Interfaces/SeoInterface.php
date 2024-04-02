<?php

namespace App\Entity\Interfaces;

interface SeoInterface
{
    public const OG_TYPE_WEBSITE = 'website';
    public const OG_TYPE_ARTICLE = 'article';

    public const OG_TYPES = [
        self::OG_TYPE_WEBSITE => 'Website',
        self::OG_TYPE_ARTICLE => 'Article',
    ];

    public function getSeoTitle(): ?string;
    public function setSeoTitle(?string $seoTitle): self;
    public function getSeoDescription(): ?string;
    public function setSeoDescription(?string $seoDescription): self;
    public function getSeoKeywords(): ?string;
    public function setSeoKeywords(?string $seoKeywords): self;
    public function getOgTitle(): ?string;
    public function setOgTitle($ogTitle): self;
    public function getOgDescription(): ?string;
    public function setOgDescription($ogDescription): self;
    public function getOgUrl(): ?string;
    public function setOgUrl($ogUrl): self;
    public function getOgImage(): ?string;
    public function setOgImage($ogImage): self;
    public function getOgType(): ?string;
    public function setOgType($ogType): self;
}