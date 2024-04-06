<?php

namespace App\Service;

use App\Entity\Interfaces\NodeInterface;
use App\Repository\MenuRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MenuService
{
    public const ALL_MENU_CACHE_KEY = 'all_menu';
    public function __construct(
        private MenuRepository $menuRepository,
        private CacheInterface $cache
    )
    {

    }

    public function getAllItems(string $cacheKey, ?callable $func = null, int $cacheExpire = 3600): array
    {
        $itemsQueryBuilder = $this->menuRepository->getWithPagesQueryBuilder();
        $func($itemsQueryBuilder);

        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use($cacheExpire, $itemsQueryBuilder) {
            $item->expiresAfter($cacheExpire);
            return $itemsQueryBuilder->getQuery()->getResult();
        });

        return $result;
    }
}