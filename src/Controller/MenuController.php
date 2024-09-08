<?php

namespace App\Controller;

use App\Entity\Interfaces\NodeInterface;
use App\Entity\Page;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\LocaleSwitcher;

class MenuController extends AbstractController
{
    public function __construct(private LocaleSwitcher $localeSwitcher)
    {

    }
    public function treeMenu(MenuRepository $menuRepository, ?int $tree): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder->andWhere('m.lang=:lang')->setParameter('lang', $this->localeSwitcher->getLocale());
        $items = $queryBuilder->getQuery()->getResult();
        return $this->render('menu/tree_menu.html.twig', ['items' => $items]);
    }

    public function leftMenu(MenuRepository $menuRepository, Request $request)
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isLeftMenu=:isLeftMenu")
            ->andWhere('m.lang=:lang')
            ->setParameter('lang', $this->localeSwitcher->getLocale())
            ->setParameter("isLeftMenu", true);

       $items = $queryBuilder->getQuery()->getResult();

        return $this->render('menu/left_menu.html.twig', ['items' => $items, 'request' => $request]);
    }

    public function topMenu(MenuRepository $menuRepository, Request $activeRequest): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isTopMenu=:isTopMenu")
            ->andWhere('m.lang=:lang')
            ->setParameter('lang', $this->localeSwitcher->getLocale())
            ->setParameter("isTopMenu", true);
        $items = $queryBuilder->getQuery()->getResult();
        return $this->render('menu/top_menu.html.twig', ['items' => $items, 'activeRequest' => $activeRequest]);
    }

    public function bottomMenu(MenuRepository $menuRepository): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isBottomMenu=:isBottomMenu")
            ->andWhere('m.lang=:lang')
            ->setParameter('lang', $this->localeSwitcher->getLocale())
            ->setParameter("isBottomMenu", true);
        $items = $queryBuilder->getQuery()->getResult();

        return $this->render('menu/bottom_menu.html.twig', [
            'items' => $items
        ]);
    }

    public function breadcrumbs(MenuRepository $menuRepository, ?Page $page): Response
    {
        if (!$page || !($node = $page->getMenu())) {
            return $this->render('menu/breadcrumbs.html.twig', ['items' => []]);
        }

        $queryBuilder = $menuRepository->getParentsByItemQueryBuilder($node);
        $queryBuilder->andWhere('m.lang=:lang')->setParameter('lang', $this->localeSwitcher->getLocale());

        return $this->render('menu/breadcrumbs.html.twig', ['items' => $queryBuilder->getQuery()->getArrayResult()]);
    }
}
