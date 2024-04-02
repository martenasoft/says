<?php

namespace App\Controller;

use App\Controller\Traits\PageTrait;
use App\Entity\Page;
use App\Repository\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class PageController extends AbstractController
{
    use PageTrait;
    #[Route('/', name: 'app_page_main', methods: ['GET'])]
    public function main(
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        $limit = $this->getParameter('preview_on_main_limit') ?? 10;
        $items = $pageRepository
            ->getAllQueryBuilder()
            ->andWhere("(p.isPreviewOnMain=:isPreviewOnMain OR p.slug=:main)")
            ->setParameter('isPreviewOnMain', true)
            ->setParameter('main', Page::MAIN_URL)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $page = null;

        foreach ($items as $index => $item) {
            if ($item->getSlug() == Page::MAIN_URL) {
                $page = $item;
                unset($items[$index]);
                continue;
            }
        }

        return $this->render('page/main.html.twig', [
            'items' => $items,
            'page' => $page,
            'pagination' => $this->getPagination($pageRepository, $paginator, $request)
        ]);
    }

    #[Route('/page/{slug}.html', name: 'app_page_show', methods: ['GET'])]
    public function show(Page $page): Response
    {
        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/page/{slug}', name: 'app_page_section', methods: ['GET'])]
    public function section(
        Page               $page,
        PageRepository     $pageRepository,
        PaginatorInterface $paginator,
        Request            $request
    ): Response
    {
        return $this->render('page/section.html.twig', [
            'page' => $page,
            'pagination' => $this->getPagination($pageRepository, $paginator, $request, page: $page),
        ]);
    }

    public function menu(string $type, PageRepository $pageRepository): Response
    {
        $queryBuilder = $this
            ->getItemsQueryBuilder($pageRepository)
            ->andWhere("p.menuType IN (:menuType)")
            ->setParameter('menuType', [
                Page::MENU_TYPE_BOTH_VERTICAL_AND_HORIZONTAL,
                $type
            ]);

        return $this->render('page/menu.html.twig', [
            'type' => $type,
            'items' =>  $queryBuilder->getQuery()->getResult()
        ]);
    }

    public function breadCrumbs(Page $page, PageRepository $pageRepository): Response
    {
        return $this->render('page/bread_crumbs.html.twig', [
            'items' => $pageRepository->breadCrumbs($page),
            'page' => $page
        ]);
    }



}
