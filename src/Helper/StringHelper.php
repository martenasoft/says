<?php

namespace App\Helper;

use Symfony\Component\String\Slugger\AsciiSlugger;

class StringHelper
{
    public static function slug(string $text): string
    {
        $slugger  = new AsciiSlugger();
        $result = $slugger->slug($text);
        return $result;
    }
}