<?php

namespace App\Twig\Runtime;

use App\Entity\Page;
use App\Service\MenuService;
use Twig\Extension\RuntimeExtensionInterface;

class NestedMenuRuntime implements RuntimeExtensionInterface
{
    public function __construct(private MenuService $menuService)
    {
        // Inject dependencies if needed
    }

    public function getPath($menuItem): string
    {
        $allPages = $this->menuService->getAllItems(MenuService::ALL_MENU_CACHE_KEY);
        $result = [];
        $isFound = false;
        foreach ($allPages as $page) {
            if ($page->getType() == Page::CONTROLLER_ROUTE_TYPE) {
                continue;
            }
            $result[] = $page->getSlug();
            $isFound = $page->getMenu()->getId() == $menuItem->getId();
            if ($isFound) {
                break;
            }
        }
        if (!$isFound) {
            return '';
        }

        $result[] = $menuItem->getSlug();
        $result = array_unique($result);
        return join("/", $result);
    }
}
