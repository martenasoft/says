<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\NestedMenuRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NestedMenuExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('menuPath', [NestedMenuRuntime::class, 'getPath']),
        ];
    }

//    public function getFunctions(): array
//    {
//        return [
//            new TwigFunction('menuPath', [NestedMenuRuntime::class, 'getPath']),
//        ];
//    }
}
