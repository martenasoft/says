<?php

namespace App\Controller;

use App\Controller\Traits\PageTrait;
use App\Entity\Page;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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


            ->andWhere("(p.isPreviewOnMain=:isPreviewOnMain OR (p.type != :controllerRouteType AND p.slug=:main))")
            ->setParameter('controllerRouteType', Page::CONTROLLER_ROUTE_TYPE)
            ->setParameter('isPreviewOnMain', true)
            ->setParameter('main',  Page::MAIN_URL)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

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

    #[Route('/page/{slug}.html', name: 'app_page_show', methods: ['GET'], requirements: ['slug' => '[a-zA-Z0-9-\_\/]+'])]
    public function show(string $slug, PageRepository $pageRepository): Response
    {
        $slug = $this->getSlugFromPath($slug);
        return $this->render('page/show.html.twig', [
            'page' => $pageRepository->getOneBySlugQueryBuilder($slug)->getQuery()->getOneOrNullResult(),
        ]);
    }

    #[Route('/page/{slug}', defaults: ['slug' => null], name: 'app_page_section', methods: ['GET'], requirements: ['slug' => '[a-zA-Z0-9-\_\/]+'])]
    public function section(
        ?string                $slug,
        PageRepository         $pageRepository,
        MenuRepository         $menuRepository,
        PaginatorInterface     $paginator,
        Request                $request,
    ): Response
    {
        $slug = $this->getSlugFromPath($slug);
        $isRouteType = false;
        if (empty($slug)) {
            $slug = 'app_page_section';
            $isRouteType = true;
        }
        $queryBuilder = $pageRepository->getOneBySlugQueryBuilder($slug, $isRouteType);
        $page = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$page) {
            throw new NotFoundHttpException("Page [$slug] not found");
        }

        $queryBuilder = $this->getItemsQueryBuilder($pageRepository);
        if ($page->getMenu()) {
            $queryBuilder
                ->innerJoin("p.menu", "m")
                ->addSelect("m");
            $queryBuilder = $menuRepository->getAllSubItemsQueryBuilder(
                $page->getMenu(),
                $queryBuilder,
                deep: 1
            );
        }
         return $this->render('page/section.html.twig', [
            'page' => $page,
            'pagination' => $this->getPagination($pageRepository, $paginator, $request, page: $page, queryBuilder: $queryBuilder),
        ]);
    }

    private function getSlugFromPath(?string $path): string
    {
        if (!empty($path)) {
            $sxp = explode('/', $path);
            $slug = end($sxp);
        } else {
            $slug = '';
        }
        return $slug;
    }

}
