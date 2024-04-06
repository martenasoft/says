<?php

namespace App\Controller;

use App\Entity\Interfaces\NodeInterface;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function treeMenu(MenuRepository $menuRepository, ?int $tree): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $items = $queryBuilder->getQuery()->getResult();
        return $this->render('menu/tree_menu.html.twig', ['items' => $items]);
    }

    public function leftMenu(MenuRepository $menuRepository, Request $request)
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isLeftMenu=:isLeftMenu")
            ->setParameter("isLeftMenu", true);

       $items = $queryBuilder->getQuery()->getResult();

        return $this->render('menu/left_menu.html.twig', ['items' => $items, 'request' => $request]);
    }

    public function topMenu(MenuRepository $menuRepository,): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isTopMenu=:isTopMenu")
            ->setParameter("isTopMenu", true);
        $items = $queryBuilder->getQuery()->getResult();
        return $this->render('menu/top_menu.html.twig', ['items' => $items]);
    }

    public function bottomMenu(MenuRepository $menuRepository,): Response
    {
        $queryBuilder = $menuRepository->getWithPagesQueryBuilder();
        $queryBuilder
            ->andWhere("m.isBottomMenu=:isBottomMenu")
            ->setParameter("isBottomMenu", true);
        $items = $queryBuilder->getQuery()->getResult();

        return $this->render('menu/bottom_menu.html.twig', [
            'items' => $items
        ]);
    }

    public function breadcrumbs(MenuRepository $menuRepository, NodeInterface $node): Response
    {
        $query = $menuRepository->getParentsByItemQueryBuilder($node)->getQuery();
        return $this->render('menu/breadcrumbs.html.twig', ['items' => $query->getArrayResult()]);
    }
}
