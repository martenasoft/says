<?php

namespace App\Controller;

use App\Entity\Interfaces\NodeInterface;
use App\Repository\MenuRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{


    public function treeMenu(MenuRepository $menuRepository, ?int $tree): Response
    {
        $query = $menuRepository->getAllMenuQueryBuilder()->getQuery();
        $items = $query->getResult();
        return $this->render('menu/tree_menu.html.twig', ['items' => $items]);
    }

    public function leftMenu(MenuRepository $menuRepository, Request $request)
    {
        $query = $menuRepository->getAllMenuQueryBuilder(['isLeftMenu' => true])->getQuery();
        $items = $query->getResult();

        return $this->render('menu/left_menu.html.twig', ['items' =>$items,'request' => $request]);
    }

    public function topMenu(MenuRepository $menuRepository): Response
    {
        $query = $menuRepository ->getAllMenuQueryBuilder(['isTopMenu' => true])->getQuery();
        $items = $query->getResult();

        return $this->render('menu/top_menu.html.twig',['items' => $items]);
    }

    public function bottomMenu(MenuRepository $menuRepository): Response
    {
        $query = $menuRepository->getAllMenuQueryBuilder(['isBottomMenu' => true])->getQuery();
        $items = $query->getResult();

        return $this->render('menu/bottom_menu.html.twig',[
            'items' => $items
        ]);
    }

    public function breadcrumbs(MenuRepository $menuRepository, NodeInterface $node): Response
    {
        $query = $menuRepository->getParentsByItemQueryBuilder($node)->getQuery();

        return $this->render('menu/breadcrumbs.html.twig', ['items' => $query->getArrayResult()]);
    }
}
