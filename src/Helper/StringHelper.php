<?php

namespace App\Helper;

use App\Entity\Interfaces\NameInterface;
use App\Entity\Interfaces\SlugIntrface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class StringHelper
{
    public static function slug(string $text): string
    {
        $slugger  = new AsciiSlugger();
        $result = $slugger->slug(strtolower($text));
        return $result;
    }
}